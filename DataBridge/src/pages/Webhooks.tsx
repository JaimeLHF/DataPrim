import { useState, useEffect, useCallback } from 'react';
import { useCompany } from '../shared/contexts/CompanyContext';
import { getToken } from '../shared/services/auth';
import { useToast } from '../shared/contexts/ToastContext';

// ── Tipos ────────────────────────────────────────────────────────────────────

interface WebhookConfig {
    id: number;
    erp_type: string;
    slug: string;
    is_active: boolean;
    last_received_at: string | null;
    webhook_url: string;
    created_at: string;
    secret?: string; // Só presente na criação
}

interface WebhookLog {
    id: number;
    source: string;
    status: 'pending' | 'done' | 'failed';
    attempts: number;
    error_message: string | null;
    created_at: string;
    processed_at: string | null;
}

// ── Helpers ───────────────────────────────────────────────────────────────────

const statusBadge: Record<string, { label: string; color: string }> = {
    pending: { label: 'Aguardando', color: '#f39c12' },
    done: { label: 'Processado', color: '#27ae60' },
    failed: { label: 'Falha', color: '#e74c3c' },
};

const erpLabels: Record<string, string> = {
    bling: 'Bling ERP',
    tinyerp: 'Tiny ERP',
};

function fmtDate(iso: string | null): string {
    if (!iso) return '—';
    return new Date(iso).toLocaleString('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

// ── Component ─────────────────────────────────────────────────────────────────

export default function Webhooks() {
    const { current: company } = useCompany();
    const token = getToken();
    const { success: toastSuccess, error: toastError } = useToast();
    const [configs, setConfigs] = useState<WebhookConfig[]>([]);
    const [logs, setLogs] = useState<WebhookLog[]>([]);
    const [total, setTotal] = useState(0);
    const [newErp, setNewErp] = useState('bling');
    const [creating, setCreating] = useState(false);
    const [createdConfig, setCreatedConfig] = useState<WebhookConfig | null>(null);
    const [copied, setCopied] = useState<'url' | 'secret' | null>(null);

    const headers = {
        'Authorization': `Bearer ${token}`,
        'X-Company-Id': String(company?.id ?? ''),
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };

    const fetchConfigs = useCallback(async () => {
        try {
            const res = await fetch('/api/v1/webhook-configs', { headers });
            const data = await res.json();
            setConfigs(data.data ?? []);
        } catch {
            toastError('Erro ao carregar configurações de webhook.');
        }
    }, [token, company?.id]);

    const fetchLogs = useCallback(async () => {
        try {
            const res = await fetch('/api/v1/webhooks/logs', { headers });
            const data = await res.json();
            setLogs(data.data ?? []);
            setTotal(data.total ?? 0);
        } catch {
            toastError('Erro ao carregar logs de webhook.');
        }
    }, [token, company?.id]);

    useEffect(() => {
        if (token && company?.id) {
            fetchConfigs();
            fetchLogs();
            const interval = setInterval(fetchLogs, 10000); // polling a cada 10s
            return () => clearInterval(interval);
        }
    }, [fetchConfigs, fetchLogs, token, company?.id]);

    async function handleCreate() {
        setCreating(true);
        try {
            const res = await fetch('/api/v1/webhook-configs', {
                method: 'POST',
                headers,
                body: JSON.stringify({ erp_type: newErp }),
            });
            const data = await res.json();
            if (!res.ok) {
                toastError('Erro ao criar webhook', data.message);
                return;
            }
            setCreatedConfig(data.data);
            toastSuccess('Webhook criado!', 'Guarde o secret — ele não será exibido novamente.');
            await fetchConfigs();
        } catch {
            toastError('Erro de rede', 'Não foi possível criar o webhook.');
        } finally {
            setCreating(false);
        }
    }

    async function handleDelete(id: number) {
        if (!confirm('Deseja revogar esta configuração?')) return;
        try {
            await fetch(`/api/v1/webhook-configs/${id}`, { method: 'DELETE', headers });
            setConfigs(prev => prev.filter(c => c.id !== id));
            setCreatedConfig(null);
            toastSuccess('Webhook revogado.');
        } catch {
            toastError('Erro ao revogar', 'Não foi possível revogar o webhook.');
        }
    }

    function copyToClipboard(text: string, type: 'url' | 'secret') {
        navigator.clipboard.writeText(text).then(() => {
            setCopied(type);
            setTimeout(() => setCopied(null), 2000);
        });
    }

    return (
        <div className="page-container">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
                <div>
                    <h1 className="page-title" style={{ margin: 0 }}>Integrações → Webhooks</h1>
                    <p style={{ margin: '4px 0 0', opacity: 0.6, fontSize: 14 }}>
                        Conecte ERP externo via webhook. Dados chegam automaticamente.
                    </p>
                </div>
            </div>

            {/* ── Configurações ────────────────────────────────────────────── */}
            <section className="card" style={{ marginBottom: 24 }}>
                <h2 style={{ fontSize: 16, fontWeight: 600, marginBottom: 16 }}>🔗 Configurações de Webhook</h2>

                {/* Secret revelado após criação */}
                {createdConfig?.secret && (
                    <div style={{
                        background: '#27ae6022', border: '1px solid #27ae60',
                        borderRadius: 8, padding: 16, marginBottom: 16,
                    }}>
                        <p style={{ margin: '0 0 8px', fontWeight: 600, color: '#27ae60' }}>
                            ✅ Webhook criado! Guarde o secret — ele não será exibido novamente.
                        </p>
                        <div style={{ display: 'flex', gap: 8, marginBottom: 8 }}>
                            <label style={{ opacity: 0.7, fontSize: 12, minWidth: 60 }}>URL:</label>
                            <code style={{ flex: 1, fontSize: 12, wordBreak: 'break-all' }}>{createdConfig.webhook_url}</code>
                            <button
                                id="copy-webhook-url"
                                className="btn-secondary"
                                style={{ padding: '4px 10px', fontSize: 12 }}
                                onClick={() => copyToClipboard(createdConfig.webhook_url, 'url')}
                            >
                                {copied === 'url' ? '✅' : '📋'} Copiar
                            </button>
                        </div>
                        <div style={{ display: 'flex', gap: 8 }}>
                            <label style={{ opacity: 0.7, fontSize: 12, minWidth: 60 }}>Secret:</label>
                            <code style={{ flex: 1, fontSize: 12, wordBreak: 'break-all', fontFamily: 'monospace' }}>
                                {createdConfig.secret}
                            </code>
                            <button
                                id="copy-webhook-secret"
                                className="btn-secondary"
                                style={{ padding: '4px 10px', fontSize: 12 }}
                                onClick={() => copyToClipboard(createdConfig.secret!, 'secret')}
                            >
                                {copied === 'secret' ? '✅' : '📋'} Copiar
                            </button>
                        </div>
                    </div>
                )}

                {/* Configurações existentes */}
                {configs.length > 0 ? (
                    <table className="data-table" style={{ marginBottom: 16 }}>
                        <thead>
                            <tr>
                                <th>ERP</th>
                                <th>URL do Webhook</th>
                                <th>Último Recebimento</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            {configs.map(c => (
                                <tr key={c.id}>
                                    <td><strong>{erpLabels[c.erp_type] ?? c.erp_type}</strong></td>
                                    <td>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                            <code style={{ fontSize: 11, opacity: 0.8 }}>{c.webhook_url}</code>
                                            <button
                                                className="btn-secondary"
                                                style={{ padding: '2px 8px', fontSize: 11 }}
                                                onClick={() => copyToClipboard(c.webhook_url, 'url')}
                                            >📋</button>
                                        </div>
                                    </td>
                                    <td style={{ fontSize: 12 }}>{fmtDate(c.last_received_at)}</td>
                                    <td>
                                        <span style={{
                                            padding: '2px 8px', borderRadius: 12, fontSize: 12,
                                            background: c.is_active ? '#27ae6022' : '#e74c3c22',
                                            color: c.is_active ? '#27ae60' : '#e74c3c',
                                        }}>
                                            {c.is_active ? 'Ativo' : 'Inativo'}
                                        </span>
                                    </td>
                                    <td>
                                        <button
                                            className="btn-danger"
                                            style={{ padding: '4px 10px', fontSize: 12 }}
                                            onClick={() => handleDelete(c.id)}
                                        >
                                            Revogar
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                ) : (
                    <p style={{ opacity: 0.5, fontSize: 14, marginBottom: 16 }}>
                        Nenhuma configuração. Ative um ERP abaixo.
                    </p>
                )}

                {/* Formulário de criação */}
                <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
                    <select
                        id="select-erp-type"
                        value={newErp}
                        onChange={e => setNewErp(e.target.value)}
                        style={{
                            padding: '8px 12px', borderRadius: 8, border: '1px solid var(--border)',
                            background: 'var(--bg-secondary)', color: 'var(--text)',
                        }}
                    >
                        <option value="bling">Bling ERP</option>
                        <option value="tinyerp">Tiny ERP</option>
                    </select>
                    <button
                        id="btn-activate-webhook"
                        className="btn-primary"
                        onClick={handleCreate}
                        disabled={creating}
                    >
                        {creating ? 'Ativando...' : '⚡ Ativar Webhook'}
                    </button>
                </div>
            </section>

            {/* ── Logs ─────────────────────────────────────────────────────── */}
            <section className="card">
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                    <h2 style={{ fontSize: 16, fontWeight: 600, margin: 0 }}>📋 Log de Eventos</h2>
                    <span style={{ opacity: 0.5, fontSize: 13 }}>{total} evento{total !== 1 ? 's' : ''} recebido{total !== 1 ? 's' : ''}</span>
                </div>

                {logs.length === 0 ? (
                    <div style={{ textAlign: 'center', padding: '40px 0', opacity: 0.4 }}>
                        <div style={{ fontSize: 40, marginBottom: 12 }}>📭</div>
                        <p>Nenhum evento recebido ainda.<br />Configure um webhook acima e teste com um envio do ERP.</p>
                    </div>
                ) : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ERP</th>
                                <th>Status</th>
                                <th>Tentativas</th>
                                <th>Recebido em</th>
                                <th>Processado em</th>
                                <th>Erro</th>
                            </tr>
                        </thead>
                        <tbody>
                            {logs.map(log => {
                                const badge = statusBadge[log.status] ?? { label: log.status, color: '#999' };
                                return (
                                    <tr key={log.id}>
                                        <td style={{ fontSize: 12, opacity: 0.6 }}>#{log.id}</td>
                                        <td>{erpLabels[log.source] ?? log.source}</td>
                                        <td>
                                            <span style={{
                                                padding: '3px 10px', borderRadius: 12, fontSize: 12,
                                                background: badge.color + '22', color: badge.color,
                                                fontWeight: 600,
                                            }}>
                                                {badge.label}
                                            </span>
                                        </td>
                                        <td style={{ textAlign: 'center' }}>{log.attempts}</td>
                                        <td style={{ fontSize: 12 }}>{fmtDate(log.created_at)}</td>
                                        <td style={{ fontSize: 12 }}>{fmtDate(log.processed_at)}</td>
                                        <td style={{ fontSize: 11, color: '#e74c3c', maxWidth: 200 }}>
                                            {log.error_message ?? '—'}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                )}
            </section>
        </div>
    );
}
