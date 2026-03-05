import { useState, useEffect, useCallback } from 'react';
import api from '../shared/services/api';

interface ApiKey {
    id: number;
    name: string;
    last_used_at: string | null;
    created_at: string;
}

interface NewKeyResponse {
    id: number;
    name: string;
    key: string;
    created_at: string;
}

export default function ApiKeys() {
    const [keys, setKeys] = useState<ApiKey[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [newKeyName, setNewKeyName] = useState('');
    const [creating, setCreating] = useState(false);
    const [createdKey, setCreatedKey] = useState<NewKeyResponse | null>(null);
    const [copied, setCopied] = useState(false);

    const fetchKeys = useCallback(async () => {
        try {
            const { data } = await api.get('/api-keys');
            setKeys(data.data ?? data);
        } catch {
            setError('Erro ao carregar chaves de API.');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchKeys();
    }, [fetchKeys]);

    const handleCreate = async () => {
        if (!newKeyName.trim()) return;
        setCreating(true);
        setError('');
        try {
            const { data } = await api.post('/api-keys', { name: newKeyName.trim() });
            setCreatedKey(data.data);
            setNewKeyName('');
            fetchKeys();
        } catch (e: unknown) {
            setError(e instanceof Error ? e.message : 'Erro ao criar chave.');
        } finally {
            setCreating(false);
        }
    };

    const handleDelete = async (id: number) => {
        if (!confirm('Tem certeza que deseja revogar esta chave? Esta acao nao pode ser desfeita.')) return;
        try {
            await api.delete(`/api-keys/${id}`);
            setKeys(keys.filter(k => k.id !== id));
        } catch (e: unknown) {
            setError(e instanceof Error ? e.message : 'Erro ao revogar chave.');
        }
    };

    const handleCopy = async (text: string) => {
        await navigator.clipboard.writeText(text);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const formatDate = (d: string) => new Date(d).toLocaleDateString('pt-BR');

    return (
        <div>
            <div className="page-header">
                <h1>Chaves de API</h1>
                <p>Gerencie chaves para integracao via API Push (POST /api/v1/invoices/import-json)</p>
            </div>

            {error && (
                <div style={{ background: 'var(--color-danger-dim)', border: '1px solid var(--color-danger)', borderRadius: 10, padding: '16px 20px', marginBottom: 24, color: 'var(--color-danger)', fontSize: 13 }}>
                    {error}
                </div>
            )}

            {/* Created Key Alert */}
            {createdKey && (
                <div className="card" style={{ marginBottom: 24, borderColor: 'var(--color-success)' }}>
                    <div className="card-title" style={{ color: 'var(--color-success)' }}>Chave criada com sucesso</div>
                    <p style={{ fontSize: 13, color: 'var(--color-text-muted)', margin: '8px 0 16px' }}>
                        Copie a chave abaixo. Ela nao sera exibida novamente.
                    </p>
                    <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                        <code style={{
                            flex: 1,
                            padding: '10px 14px',
                            background: 'var(--color-bg-secondary)',
                            borderRadius: 8,
                            fontSize: 13,
                            wordBreak: 'break-all',
                            border: '1px solid var(--color-border)',
                        }}>
                            {createdKey.key}
                        </code>
                        <button className="btn btn-primary" onClick={() => handleCopy(createdKey.key)}>
                            {copied ? 'Copiado!' : 'Copiar'}
                        </button>
                    </div>
                    <button
                        className="btn btn-outline"
                        style={{ marginTop: 12 }}
                        onClick={() => setCreatedKey(null)}
                    >
                        Fechar
                    </button>
                </div>
            )}

            {/* Create New Key */}
            <div className="card" style={{ marginBottom: 24 }}>
                <div className="card-title">Nova Chave</div>
                <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
                    <input
                        type="text"
                        className="filter-select"
                        placeholder="Nome da chave (ex: ERP Protheus)"
                        value={newKeyName}
                        onChange={e => setNewKeyName(e.target.value)}
                        onKeyDown={e => e.key === 'Enter' && handleCreate()}
                        style={{ flex: 1 }}
                    />
                    <button
                        className="btn btn-primary"
                        onClick={handleCreate}
                        disabled={creating || !newKeyName.trim()}
                    >
                        {creating ? 'Criando...' : 'Gerar Chave'}
                    </button>
                </div>
            </div>

            {/* Keys List */}
            <div className="card">
                <div className="card-title">Chaves Ativas</div>
                {loading ? (
                    <p style={{ color: 'var(--color-text-muted)', fontSize: 13 }}>Carregando...</p>
                ) : keys.length === 0 ? (
                    <p style={{ color: 'var(--color-text-muted)', fontSize: 13 }}>
                        Nenhuma chave de API criada. Crie uma acima para comecar a integrar.
                    </p>
                ) : (
                    <div style={{ overflowX: 'auto' }}>
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Criada em</th>
                                    <th>Ultimo uso</th>
                                    <th>Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                {keys.map(k => (
                                    <tr key={k.id}>
                                        <td style={{ fontWeight: 600 }}>{k.name}</td>
                                        <td>{formatDate(k.created_at)}</td>
                                        <td>{k.last_used_at ? formatDate(k.last_used_at) : <span style={{ color: 'var(--color-text-muted)' }}>Nunca</span>}</td>
                                        <td>
                                            <button
                                                className="btn btn-outline"
                                                style={{ padding: '4px 12px', fontSize: 12, color: 'var(--color-danger)' }}
                                                onClick={() => handleDelete(k.id)}
                                            >
                                                Revogar
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {/* Usage Instructions */}
            <div className="card" style={{ marginTop: 24 }}>
                <div className="card-title">Como usar</div>
                <div style={{ fontSize: 13, color: 'var(--color-text-muted)' }}>
                    <p style={{ marginBottom: 12 }}>Envie notas fiscais via API usando a chave gerada:</p>
                    <code style={{
                        display: 'block',
                        padding: '14px 18px',
                        background: 'var(--color-bg-secondary)',
                        borderRadius: 8,
                        fontSize: 12,
                        lineHeight: 1.8,
                        border: '1px solid var(--color-border)',
                        whiteSpace: 'pre-wrap',
                    }}>
                        {`POST /api/v1/invoices/import-json
Authorization: Bearer <sua-chave-aqui>
Content-Type: application/json
X-Company-Id: <id-da-empresa>

{
  "source": "seu_erp",
  "invoices": [{
    "invoice_number": "NF-001",
    "issue_date": "2026-01-15",
    "supplier": { "cnpj": "12345678000190", "name": "Fornecedor" },
    "items": [{ "description": "MDF 18mm", "quantity": 100, "unit_price": 45.5, "category": "MDF" }],
    "totals": { "goods": 4550, "freight": 200, "tax": 386.75, "total": 5136.75 }
  }]
}`}
                    </code>
                </div>
            </div>
        </div>
    );
}
