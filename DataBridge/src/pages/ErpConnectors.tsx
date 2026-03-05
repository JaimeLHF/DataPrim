import { useState, useEffect, useCallback } from 'react';
import { useCompany } from '../shared/contexts/CompanyContext';
import { getToken } from '../shared/services/auth';
import { useToast } from '../shared/contexts/ToastContext';

// ── Tipos ─────────────────────────────────────────────────────────────────────

interface ErpConnector {
    id: number;
    erp_type: string;
    sync_frequency: number;
    is_active: boolean;
    last_synced_at: string | null;
    last_sync_status: 'ok' | 'error' | null;
    last_sync_error: string | null;
    next_sync_at: string | null;
    created_at: string;
    // OAuth2 (Bling)
    oauth_status?: 'pending' | 'authorized' | 'expired';
    authorize_url?: string;
}

// ERPs que usam OAuth2 authorization_code (não precisam de credenciais no form)
const OAUTH2_ERPS = ['bling'];

// ── Helpers ───────────────────────────────────────────────────────────────────

const ERP_META: Record<string, { label: string; color: string; bg: string; logo: string }> = {
    bling: { label: 'Bling ERP', color: '#2563eb', bg: 'rgba(37,99,235,0.1)', logo: '🔵' },
    tinyerp: { label: 'Tiny ERP', color: '#ea580c', bg: 'rgba(234,88,12,0.1)', logo: '🟠' },
};

function fmtDate(iso: string | null): string {
    if (!iso) return '—';
    return new Date(iso).toLocaleString('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function fmtFreq(min: number): string {
    if (min < 60) return `${min} min`;
    if (min === 60) return '1 hora';
    return `${min / 60}h`;
}

function syncStatus(c: ErpConnector) {
    // OAuth pendente tem prioridade — ainda não autorizou
    if (c.oauth_status === 'pending')
        return { label: 'Autorização pendente', color: 'var(--color-warning)', bg: 'var(--color-warning-dim)', dot: '#f59e0b' };
    if (c.oauth_status === 'expired')
        return { label: 'Token expirado', color: 'var(--color-warning)', bg: 'var(--color-warning-dim)', dot: '#f59e0b' };
    if (!c.last_synced_at && !c.last_sync_status)
        return { label: 'Nunca sincronizado', color: 'var(--color-text-faint)', bg: 'var(--color-surface-2)', dot: '#6b7280' };
    if (c.last_sync_status === 'ok')
        return { label: 'Sincronizado', color: 'var(--color-success)', bg: 'var(--color-success-dim)', dot: '#22c55e' };
    if (c.last_sync_status === 'error')
        return { label: 'Erro na sync', color: 'var(--color-danger)', bg: 'var(--color-danger-dim)', dot: '#ef4444' };
    return { label: 'Aguardando', color: 'var(--color-warning)', bg: 'var(--color-warning-dim)', dot: '#f59e0b' };
}

// ── Componente de Input reutilizável ──────────────────────────────────────────

function Field({ label, children }: { label: string; children: React.ReactNode }) {
    return (
        <div>
            <label style={{
                display: 'block', fontSize: 11.5, fontWeight: 600,
                textTransform: 'uppercase', letterSpacing: '0.06em',
                color: 'var(--color-text-muted)', marginBottom: 8,
            }}>
                {label}
            </label>
            {children}
        </div>
    );
}

const inputStyle: React.CSSProperties = {
    width: '100%', padding: '10px 14px',
    borderRadius: 8, border: '1px solid var(--color-border)',
    background: 'var(--color-bg)', color: 'var(--color-text)',
    fontSize: 13.5, fontFamily: 'inherit', outline: 'none',
    transition: 'border-color 0.15s, box-shadow 0.15s',
    boxSizing: 'border-box',
};

// ── Component Principal ───────────────────────────────────────────────────────

export default function ErpConnectors() {
    const { current: company } = useCompany();
    const token = getToken();
    const { success: toastSuccess, error: toastError, info: toastInfo } = useToast();

    const [connectors, setConnectors] = useState<ErpConnector[]>([]);
    const [loading, setLoading] = useState(true);
    const [syncing, setSyncing] = useState<number | null>(null);

    // Formulário
    const [showForm, setShowForm] = useState(false);
    const [formErpType, setFormErpType] = useState('bling');
    const [formClientId, setFormClientId] = useState('');
    const [formClientSecret, setFormClientSecret] = useState('');
    const [formFrequency, setFormFrequency] = useState('360');
    const [creating, setCreating] = useState(false);
    const [testing, setTesting] = useState(false);
    const [testResult, setTestResult] = useState<{ ok: boolean; message: string } | null>(null);
    const [formError, setFormError] = useState<string | null>(null);

    const headers = {
        'Authorization': `Bearer ${token}`,
        'X-Company-Id': String(company?.id ?? ''),
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };

    // ── Fetch ─────────────────────────────────────────────────────────────

    const fetchConnectors = useCallback(async () => {
        if (!token || !company?.id) return;
        try {
            const res = await fetch('/api/v1/erp-connectors', { headers });
            const data = await res.json();
            setConnectors(data.data ?? []);
        } catch {
            toastError('Erro ao carregar conectores.');
        } finally {
            setLoading(false);
        }
    }, [token, company?.id]);

    useEffect(() => {
        fetchConnectors();
        const iv = setInterval(fetchConnectors, 30000);
        return () => clearInterval(iv);
    }, [fetchConnectors]);

    // Detecta retorno do callback OAuth2 do Bling (?success=bling_connected ou ?error=...)
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const success = params.get('success');
        const error   = params.get('error');
        if (success === 'bling_connected') {
            toastSuccess('Bling conectado!', 'Autorização concluída. A primeira sincronização será iniciada em breve.');
            fetchConnectors();
        } else if (error) {
            const msgs: Record<string, string> = {
                csrf_invalid:          'State CSRF inválido ou expirado. Tente novamente.',
                token_exchange_failed: 'Falha ao obter tokens do Bling. Tente autorizar novamente.',
                access_denied:         'Autorização negada pelo usuário.',
            };
            toastError('Erro na autorização Bling', msgs[error] ?? `Código: ${error}`);
        }
        if (success || error) {
            // Remove os params da URL sem recarregar a página
            window.history.replaceState({}, '', window.location.pathname);
        }
    }, []);

    // ── Ações ─────────────────────────────────────────────────────────────

    async function handleTestConnection() {
        setTesting(true);
        setTestResult(null);
        setFormError(null);
        try {
            const res = await fetch('/api/v1/erp-connectors/test-connection', {
                method: 'POST', headers,
                body: JSON.stringify({
                    erp_type: formErpType,
                    credentials: { client_id: formClientId, client_secret: formClientSecret },
                }),
            });
            const data = await res.json();
            setTestResult({ ok: data.ok, message: data.message });
        } catch {
            setTestResult({ ok: false, message: 'Erro de rede ao testar conexão.' });
        } finally {
            setTesting(false);
        }
    }

    async function handleCreate() {
        const isOAuth2 = OAUTH2_ERPS.includes(formErpType);

        if (!isOAuth2 && (!formClientId || !formClientSecret)) {
            setFormError('Preencha Client ID e Client Secret.');
            return;
        }
        setCreating(true);
        setFormError(null);
        try {
            const body: Record<string, unknown> = {
                erp_type: formErpType,
                sync_frequency: parseInt(formFrequency, 10),
            };
            if (!isOAuth2) {
                body.credentials = { client_id: formClientId, client_secret: formClientSecret };
            }
            const res = await fetch('/api/v1/erp-connectors', {
                method: 'POST', headers,
                body: JSON.stringify(body),
            });
            const data = await res.json();
            if (!res.ok) { setFormError(data.message ?? 'Erro ao criar conector.'); return; }
            setShowForm(false);
            setFormClientId(''); setFormClientSecret(''); setTestResult(null);
            if (isOAuth2 && data.authorize_url) {
                toastSuccess('Conector criado!', 'Você será redirecionado para autorizar o acesso no Bling.');
                setTimeout(() => handleAuthorize(), 1500);
            } else {
                toastSuccess('Conector criado!', 'A primeira sincronização foi iniciada em background.');
            }
            await fetchConnectors();
        } catch {
            setFormError('Erro de rede. Tente novamente.');
        } finally {
            setCreating(false);
        }
    }

    async function handleAuthorize() {
        try {
            const res = await fetch('/api/v1/erp-connectors/bling/authorize', { headers });
            if (!res.ok) {
                toastError('Erro ao iniciar autorização.', 'Verifique se o conector Bling está criado.');
                return;
            }
            const data = await res.json();
            if (data.url) {
                window.location.href = data.url;
            }
        } catch {
            toastError('Erro de rede ao iniciar autorização.');
        }
    }

    async function handleSync(id: number) {
        setSyncing(id);
        try {
            await fetch(`/api/v1/erp-connectors/${id}/sync`, { method: 'POST', headers });
            toastInfo('Sincronização iniciada', 'Os dados serão importados em background.');

            // Polling: verifica o status a cada 3s por até 60s.
            // Mantém `syncing` ativo até detectar mudança de status ou timeout.
            let attempts = 0;
            let lastStatus: string | null = null;

            const poll = async () => {
                if (attempts++ >= 20) {
                    setSyncing(null);
                    return;
                }
                try {
                    const res = await fetch('/api/v1/erp-connectors', { headers });
                    const data = await res.json();
                    const updated = (data.data ?? []).find((c: ErpConnector) => c.id === id);
                    setConnectors(data.data ?? []);

                    if (updated) {
                        const currentStatus = updated.last_sync_status + '|' + updated.last_synced_at;
                        if (lastStatus !== null && currentStatus !== lastStatus) {
                            // Status mudou → sync concluída
                            setSyncing(null);
                            return;
                        }
                        lastStatus = currentStatus;
                    }
                } catch { /* silencia erros de poll */ }

                setTimeout(poll, 3000);
            };

            // Primeiro poll: captura o status inicial antes de começar a comparar
            const resInit = await fetch('/api/v1/erp-connectors', { headers });
            const dataInit = await resInit.json();
            const connInit = (dataInit.data ?? []).find((c: ErpConnector) => c.id === id);
            if (connInit) lastStatus = connInit.last_sync_status + '|' + connInit.last_synced_at;
            setConnectors(dataInit.data ?? []);

            setTimeout(poll, 3000);
        } catch {
            toastError('Erro ao iniciar sincronização.');
            setSyncing(null);
        }
    }

    async function handleDelete(id: number, erpType: string) {
        const label = ERP_META[erpType]?.label ?? erpType;
        if (!confirm(`Remover conector ${label}? Esta ação é irreversível.`)) return;
        try {
            await fetch(`/api/v1/erp-connectors/${id}`, { method: 'DELETE', headers });
            setConnectors(prev => prev.filter(c => c.id !== id));
            toastSuccess('Conector removido.');
        } catch {
            toastError('Erro ao remover conector.');
        }
    }

    // ── Render ────────────────────────────────────────────────────────────

    return (
        <div>
            {/* ── Page header ──────────────────────────────────────────── */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 28 }}>
                <div className="page-header" style={{ marginBottom: 0 }}>
                    <h1>Conectores ERP</h1>
                    <p>Pull ativo — a plataforma busca NF-es automaticamente no ERP.</p>
                </div>
                {!showForm && (
                    <button
                        id="btn-add-connector"
                        className="btn-primary"
                        onClick={() => { setShowForm(true); setFormError(null); setTestResult(null); }}
                    >
                        ＋ Novo Conector
                    </button>
                )}
            </div>

            {/* ── Formulário de criação ─────────────────────────────────── */}
            {showForm && (
                <div className="card" style={{ marginBottom: 28, borderColor: 'var(--color-primary)', borderWidth: 1.5 }}>
                    {/* Header do formulário */}
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
                        <div>
                            <h2 style={{ fontSize: 15, fontWeight: 700, margin: 0 }}>🔌 Configurar Novo Conector</h2>
                            <p style={{ fontSize: 12.5, color: 'var(--color-text-muted)', margin: '4px 0 0' }}>
                                {OAUTH2_ERPS.includes(formErpType)
                                    ? 'Após salvar, você será redirecionado para autorizar o acesso no Bling.'
                                    : 'As credenciais são armazenadas criptografadas (AES-256).'}
                            </p>
                        </div>
                        <button
                            className="btn-ghost btn-sm"
                            onClick={() => { setShowForm(false); setTestResult(null); setFormError(null); }}
                        >
                            ✕ Cancelar
                        </button>
                    </div>

                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16, marginBottom: 20 }}>
                        <Field label="Tipo de ERP">
                            <select
                                id="select-connector-erp-type"
                                value={formErpType}
                                onChange={e => { setFormErpType(e.target.value); setTestResult(null); setFormClientId(''); setFormClientSecret(''); }}
                                style={{ ...inputStyle, cursor: 'pointer' }}
                            >
                                <option value="bling">Bling ERP</option>
                                <option value="tinyerp">Tiny ERP</option>
                            </select>
                        </Field>

                        <Field label="Frequência de sincronização">
                            <select
                                id="select-sync-frequency"
                                value={formFrequency}
                                onChange={e => setFormFrequency(e.target.value)}
                                style={{ ...inputStyle, cursor: 'pointer' }}
                            >
                                <option value="60">A cada 1 hora</option>
                                <option value="180">A cada 3 horas</option>
                                <option value="360">A cada 6 horas (recomendado)</option>
                                <option value="720">A cada 12 horas</option>
                                <option value="1440">Diariamente</option>
                            </select>
                        </Field>

                        {/* Credenciais — apenas para ERPs que não usam OAuth2 */}
                        {!OAUTH2_ERPS.includes(formErpType) && (<>
                            <Field label="Client ID">
                                <input
                                    id="input-client-id"
                                    type="text"
                                    value={formClientId}
                                    onChange={e => { setFormClientId(e.target.value); setTestResult(null); }}
                                    placeholder="Cole o Client ID"
                                    style={inputStyle}
                                    onFocus={e => { e.currentTarget.style.borderColor = 'var(--color-primary)'; e.currentTarget.style.boxShadow = '0 0 0 3px var(--color-primary-dim)'; }}
                                    onBlur={e => { e.currentTarget.style.borderColor = 'var(--color-border)'; e.currentTarget.style.boxShadow = 'none'; }}
                                />
                            </Field>
                            <Field label="Client Secret">
                                <input
                                    id="input-client-secret"
                                    type="password"
                                    value={formClientSecret}
                                    onChange={e => { setFormClientSecret(e.target.value); setTestResult(null); }}
                                    placeholder="Cole o Client Secret"
                                    style={inputStyle}
                                    onFocus={e => { e.currentTarget.style.borderColor = 'var(--color-primary)'; e.currentTarget.style.boxShadow = '0 0 0 3px var(--color-primary-dim)'; }}
                                    onBlur={e => { e.currentTarget.style.borderColor = 'var(--color-border)'; e.currentTarget.style.boxShadow = 'none'; }}
                                />
                            </Field>
                        </>)}
                    </div>

                    {/* Aviso OAuth2 para Bling */}
                    {OAUTH2_ERPS.includes(formErpType) && (
                        <div style={{
                            display: 'flex', alignItems: 'flex-start', gap: 12,
                            padding: '12px 16px', borderRadius: 8, marginBottom: 16,
                            background: 'rgba(37,99,235,0.07)',
                            border: '1px solid rgba(37,99,235,0.2)',
                            fontSize: 13, color: 'var(--color-text)',
                            lineHeight: 1.6,
                        }}>
                            <span style={{ fontSize: 18, flexShrink: 0, marginTop: 1 }}>🔑</span>
                            <div>
                                <strong>Autorização via OAuth2</strong><br />
                                O Bling não usa Client Secret no formulário. Após salvar, você será redirecionado
                                para o site do Bling para autorizar o acesso à sua conta.
                                Os tokens são armazenados criptografados.
                            </div>
                        </div>
                    )}

                    {/* Resultado do teste (apenas para ERPs com credenciais) */}
                    {testResult && !OAUTH2_ERPS.includes(formErpType) && (
                        <div style={{
                            display: 'flex', alignItems: 'center', gap: 10,
                            padding: '10px 14px', borderRadius: 8, marginBottom: 16,
                            background: testResult.ok ? 'var(--color-success-dim)' : 'var(--color-danger-dim)',
                            border: `1px solid ${testResult.ok ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)'}`,
                            color: testResult.ok ? 'var(--color-success)' : 'var(--color-danger)',
                            fontSize: 13,
                        }}>
                            <span style={{ fontSize: 16 }}>{testResult.ok ? '✅' : '❌'}</span>
                            {testResult.message}
                        </div>
                    )}

                    {formError && (
                        <div style={{ color: 'var(--color-danger)', fontSize: 12.5, marginBottom: 14, display: 'flex', alignItems: 'center', gap: 6 }}>
                            ⚠️ {formError}
                        </div>
                    )}

                    {/* Ações do formulário */}
                    <div style={{ display: 'flex', gap: 10, paddingTop: 4 }}>
                        {/* Botão Testar Conexão — apenas para ERPs com credenciais */}
                        {!OAUTH2_ERPS.includes(formErpType) && (
                            <button
                                id="btn-test-connection"
                                className="btn-outline"
                                onClick={handleTestConnection}
                                disabled={testing || !formClientId || !formClientSecret}
                            >
                                {testing ? (
                                    <><span style={{ display: 'inline-block', width: 14, height: 14, border: '2px solid currentColor', borderTopColor: 'transparent', borderRadius: '50%', animation: 'spin 0.7s linear infinite' }} /> Testando…</>
                                ) : '🔍 Testar Conexão'}
                            </button>
                        )}

                        <button
                            id="btn-save-connector"
                            className="btn-primary"
                            onClick={handleCreate}
                            disabled={creating || (!OAUTH2_ERPS.includes(formErpType) && (!formClientId || !formClientSecret))}
                        >
                            {creating ? (
                                <><span style={{ display: 'inline-block', width: 14, height: 14, border: '2px solid rgba(255,255,255,0.5)', borderTopColor: '#fff', borderRadius: '50%', animation: 'spin 0.7s linear infinite' }} /> Salvando…</>
                            ) : OAUTH2_ERPS.includes(formErpType) ? '🔑 Salvar e Autorizar no Bling' : '✅ Salvar Conector'}
                        </button>
                    </div>
                </div>
            )}

            {/* ── Lista de conectores ───────────────────────────────────── */}
            {loading ? (
                <div className="state-loading">
                    <div className="spinner" />
                    <span>Carregando conectores…</span>
                </div>
            ) : connectors.length === 0 ? (
                /* Empty state */
                <div className="card" style={{ textAlign: 'center', padding: '60px 40px' }}>
                    <div style={{ fontSize: 52, marginBottom: 16, opacity: 0.6 }}>🔌</div>
                    <h2 style={{ fontSize: 17, fontWeight: 700, marginBottom: 8 }}>Nenhum conector configurado</h2>
                    <p style={{ color: 'var(--color-text-muted)', fontSize: 13.5, marginBottom: 24, maxWidth: 400, margin: '0 auto 24px' }}>
                        Configure um conector ERP e a plataforma buscará NF-es automaticamente — sem precisar de webhooks ou importação manual.
                    </p>
                    <button className="btn-primary" onClick={() => setShowForm(true)}>
                        ＋ Configurar Primeiro Conector
                    </button>
                </div>
            ) : (
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(370px, 1fr))', gap: 20 }}>
                    {connectors.map(c => {
                        const meta = ERP_META[c.erp_type] ?? { label: c.erp_type, color: 'var(--color-text)', bg: 'var(--color-surface-2)', logo: '⚡' };
                        const status = syncStatus(c);

                        return (
                            <div key={c.id} className="card" style={{ display: 'flex', flexDirection: 'column', gap: 0, padding: 0, overflow: 'hidden' }}>

                                {/* Barra de cor do ERP */}
                                <div style={{ height: 4, background: meta.color, borderRadius: '10px 10px 0 0' }} />

                                {/* Conteúdo */}
                                <div style={{ padding: 20, flex: 1 }}>
                                    {/* Header: logo + nome + badge */}
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 16 }}>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                            <div style={{
                                                width: 42, height: 42, borderRadius: 10,
                                                background: meta.bg, display: 'flex', alignItems: 'center',
                                                justifyContent: 'center', fontSize: 22, flexShrink: 0,
                                            }}>
                                                {meta.logo}
                                            </div>
                                            <div>
                                                <div style={{ fontWeight: 700, fontSize: 15 }}>{meta.label}</div>
                                                <div style={{ fontSize: 11.5, color: 'var(--color-text-muted)', marginTop: 2 }}>
                                                    Sync a cada {fmtFreq(c.sync_frequency)}
                                                </div>
                                            </div>
                                        </div>

                                        {/* Status badge */}
                                        {syncing === c.id ? (
                                            <span style={{
                                                display: 'inline-flex', alignItems: 'center', gap: 6,
                                                padding: '4px 10px', borderRadius: 20,
                                                background: 'var(--color-primary-dim)', color: 'var(--color-primary)',
                                                fontSize: 11.5, fontWeight: 600,
                                            }}>
                                                <span style={{
                                                    display: 'inline-block', width: 10, height: 10, flexShrink: 0,
                                                    border: '2px solid currentColor', borderTopColor: 'transparent',
                                                    borderRadius: '50%', animation: 'spin 0.7s linear infinite',
                                                }} />
                                                Sincronizando Dados
                                            </span>
                                        ) : (
                                            <span style={{
                                                display: 'inline-flex', alignItems: 'center', gap: 6,
                                                padding: '4px 10px', borderRadius: 20,
                                                background: status.bg, color: status.color,
                                                fontSize: 11.5, fontWeight: 600,
                                            }}>
                                                <span style={{ width: 6, height: 6, borderRadius: '50%', background: status.dot, display: 'inline-block' }} />
                                                {status.label}
                                            </span>
                                        )}
                                    </div>

                                    {/* Info de datas */}
                                    <div style={{
                                        background: 'var(--color-bg)', borderRadius: 8, padding: '12px 14px',
                                        display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px 16px',
                                        marginBottom: 16, fontSize: 12.5,
                                    }}>
                                        <div>
                                            <div style={{ color: 'var(--color-text-muted)', marginBottom: 2 }}>Última sync</div>
                                            <div style={{ fontWeight: 600 }}>{fmtDate(c.last_synced_at)}</div>
                                        </div>
                                        <div>
                                            <div style={{ color: 'var(--color-text-muted)', marginBottom: 2 }}>Próxima sync</div>
                                            <div style={{ fontWeight: 600 }}>{fmtDate(c.next_sync_at)}</div>
                                        </div>
                                    </div>

                                    {/* Erro da última sync */}
                                    {c.last_sync_error && (
                                        <div style={{
                                            background: 'var(--color-danger-dim)',
                                            border: '1px solid rgba(239,68,68,0.25)',
                                            borderRadius: 6, padding: '8px 12px', marginBottom: 16,
                                            fontSize: 12, color: 'var(--color-danger)',
                                            lineHeight: 1.5,
                                        }}>
                                            <strong>Último erro:</strong> {c.last_sync_error}
                                        </div>
                                    )}
                                </div>

                                {/* Footer: ações */}
                                <div style={{
                                    borderTop: '1px solid var(--color-border)',
                                    padding: '12px 20px',
                                    display: 'flex', gap: 8, alignItems: 'center',
                                }}>
                                    {/* Bling pendente de autorização → botão de autorizar */}
                                    {c.oauth_status === 'pending' ? (
                                        <button
                                            className="btn-primary btn-sm"
                                            onClick={() => handleAuthorize()}
                                            style={{ flex: 1 }}
                                        >
                                            🔑 Autorizar no Bling
                                        </button>
                                    ) : (
                                        <button
                                            id={`btn-sync-${c.id}`}
                                            className="btn-primary btn-sm btn-full"
                                            onClick={() => handleSync(c.id)}
                                            disabled={syncing === c.id}
                                            style={{ flex: 1 }}
                                        >
                                            {syncing === c.id
                                                ? <><span style={{ display: 'inline-block', width: 12, height: 12, border: '2px solid rgba(255,255,255,0.5)', borderTopColor: '#fff', borderRadius: '50%', animation: 'spin 0.7s linear infinite' }} /> Sincronizando…</>
                                                : '🔄 Sincronizar Agora'
                                            }
                                        </button>
                                    )}
                                    <button
                                        className="btn-danger btn-sm"
                                        onClick={() => handleDelete(c.id, c.erp_type)}
                                        title="Remover conector"
                                        style={{ padding: '6px 12px' }}
                                    >
                                        🗑️ Remover
                                    </button>
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
