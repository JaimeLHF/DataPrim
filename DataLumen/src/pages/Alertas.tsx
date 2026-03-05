import { useState, useEffect } from 'react';
import { fetchAlerts, type Alert, type AlertsResponse } from '../shared/services/dashboard';

const TYPE_LABELS: Record<string, { icon: string; label: string }> = {
    price_anomaly: { icon: '💸', label: 'Preço Anômalo' },
    high_freight: { icon: '🚚', label: 'Frete Alto' },
    consecutive_rise: { icon: '📈', label: 'Alta Consecutiva' },
    concentration_risk: { icon: '⚠️', label: 'Concentração' },
};

const SEV: Record<string, { bg: string; border: string; badge: string; badgeText: string; label: string }> = {
    high: { bg: 'rgba(239,68,68,0.06)', border: 'rgba(239,68,68,0.35)', badge: 'rgba(239,68,68,0.2)', badgeText: '#ef4444', label: 'Alta' },
    medium: { bg: 'rgba(251,191,36,0.06)', border: 'rgba(251,191,36,0.35)', badge: 'rgba(251,191,36,0.2)', badgeText: '#fbbf24', label: 'Média' },
    low: { bg: 'rgba(34,197,94,0.06)', border: 'rgba(34,197,94,0.35)', badge: 'rgba(34,197,94,0.2)', badgeText: '#22c55e', label: 'Baixa' },
};

function AlertCard({ alert }: { alert: Alert }) {
    const sev = SEV[alert.severity] ?? SEV.medium;
    const type = TYPE_LABELS[alert.type] ?? { icon: '🔔', label: 'Alerta' };

    return (
        <div style={{
            background: sev.bg,
            border: `1px solid ${sev.border}`,
            borderRadius: 12,
            padding: '16px 20px',
            display: 'flex',
            gap: 16,
            alignItems: 'flex-start',
        }}>
            <div style={{ fontSize: 28, flexShrink: 0, marginTop: 2 }}>{type.icon}</div>
            <div style={{ flex: 1 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 6, flexWrap: 'wrap' }}>
                    <strong style={{ fontSize: 14 }}>{alert.title}</strong>
                    <span style={{ padding: '2px 8px', borderRadius: 20, fontSize: 11, fontWeight: 600, background: sev.badge, color: sev.badgeText }}>
                        {sev.label}
                    </span>
                    <span style={{ padding: '2px 8px', borderRadius: 20, fontSize: 11, background: 'var(--color-surface-2)', color: 'var(--color-text-muted)' }}>
                        {type.label}
                    </span>
                </div>
                <p style={{ margin: '0 0 6px', fontSize: 13, lineHeight: 1.5 }}>{alert.message}</p>
                <p style={{ margin: 0, fontSize: 12, color: 'var(--color-text-muted)', fontStyle: 'italic' }}>{alert.detail}</p>
            </div>
        </div>
    );
}

export default function Alertas() {
    const [data, setData] = useState<AlertsResponse | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [filter, setFilter] = useState<'Todos' | 'high' | 'medium'>('Todos');
    const [typeFilter, setTypeFilter] = useState<string>('Todos');

    useEffect(() => {
        setLoading(true);
        fetchAlerts()
            .then(setData)
            .catch(e => setError(e instanceof Error ? e.message : 'Erro'))
            .finally(() => setLoading(false));
    }, []);

    const alerts = data?.alerts ?? [];

    const filtered = alerts.filter(a => {
        if (filter !== 'Todos' && a.severity !== filter) return false;
        if (typeFilter !== 'Todos' && a.type !== typeFilter) return false;
        return true;
    });

    return (
        <div>
            <div className="page-header">
                <h1>🔔 Alertas Inteligentes</h1>
                <p>Desvios detectados automaticamente com base no histórico de compras</p>
            </div>

            {/* Summary Cards */}
            {data && (
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(160px, 1fr))', gap: 12, marginBottom: 24 }}>
                    <div className="card" style={{ textAlign: 'center', padding: 20 }}>
                        <div style={{ fontSize: 32, fontWeight: 800, color: 'var(--color-text)' }}>{data.total}</div>
                        <div style={{ fontSize: 12, color: 'var(--color-text-muted)', marginTop: 4 }}>Total de alertas</div>
                    </div>
                    <div className="card" style={{ textAlign: 'center', padding: 20, border: '1px solid rgba(239,68,68,0.35)' }}>
                        <div style={{ fontSize: 32, fontWeight: 800, color: 'var(--color-danger)' }}>{data.high}</div>
                        <div style={{ fontSize: 12, color: 'var(--color-text-muted)', marginTop: 4 }}>Alta prioridade</div>
                    </div>
                    <div className="card" style={{ textAlign: 'center', padding: 20, border: '1px solid rgba(251,191,36,0.35)' }}>
                        <div style={{ fontSize: 32, fontWeight: 800, color: 'var(--color-warning)' }}>{data.medium}</div>
                        <div style={{ fontSize: 12, color: 'var(--color-text-muted)', marginTop: 4 }}>Média prioridade</div>
                    </div>
                    <div className="card" style={{ textAlign: 'center', padding: 20 }}>
                        <div style={{ fontSize: 32, fontWeight: 800, color: 'var(--color-success)' }}>
                            {alerts.length > 0 ? Math.round((data.high / data.total) * 100) : 0}%
                        </div>
                        <div style={{ fontSize: 12, color: 'var(--color-text-muted)', marginTop: 4 }}>Críticos</div>
                    </div>
                </div>
            )}

            {/* Filters */}
            <div className="filter-bar" style={{ flexWrap: 'wrap', marginBottom: 20 }}>
                <div style={{ display: 'flex', gap: 6 }}>
                    {(['Todos', 'high', 'medium'] as const).map(f => (
                        <button key={f}
                            className={filter === f ? 'btn btn-primary' : 'btn btn-outline'}
                            style={{ padding: '7px 14px', fontSize: 13 }}
                            onClick={() => setFilter(f)}>
                            {f === 'Todos' ? 'Todos' : f === 'high' ? '🔴 Alta' : '🟡 Média'}
                        </button>
                    ))}
                </div>
                <select className="filter-select" value={typeFilter} onChange={e => setTypeFilter(e.target.value)}>
                    <option value="Todos">Todos os tipos</option>
                    <option value="price_anomaly">💸 Preço Anômalo</option>
                    <option value="high_freight">🚚 Frete Alto</option>
                    <option value="consecutive_rise">📈 Alta Consecutiva</option>
                    <option value="concentration_risk">⚠️ Concentração</option>
                </select>
                <span style={{ marginLeft: 'auto', fontSize: 13, color: 'var(--color-text-muted)' }}>
                    {filtered.length} alerta{filtered.length !== 1 ? 's' : ''} exibido{filtered.length !== 1 ? 's' : ''}
                </span>
            </div>

            {error && <div style={{ color: 'var(--color-danger)', marginBottom: 16 }}>⚠️ {error}</div>}

            {loading ? (
                <div className="state-loading"><div className="spinner" /><span>Analisando dados…</span></div>
            ) : filtered.length === 0 ? (
                <div className="card" style={{ textAlign: 'center', padding: 48 }}>
                    <div style={{ fontSize: 48, marginBottom: 12 }}>✅</div>
                    <h3 style={{ margin: '0 0 8px' }}>Nenhum alerta encontrado</h3>
                    <p style={{ color: 'var(--color-text-muted)', margin: 0, fontSize: 14 }}>Tente remover os filtros ou importe mais notas fiscais.</p>
                </div>
            ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                    {filtered.map((alert, i) => (
                        <AlertCard key={i} alert={alert} />
                    ))}
                </div>
            )}

            {/* Legend */}
            <div className="card" style={{ marginTop: 24, padding: 20 }}>
                <div style={{ fontWeight: 600, fontSize: 13, marginBottom: 12 }}>Como os alertas são gerados</div>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(240px, 1fr))', gap: 10, fontSize: 12, color: 'var(--color-text-muted)' }}>
                    <div>💸 <strong>Preço Anômalo</strong> — compra &gt;20% acima da média histórica da categoria</div>
                    <div>🚚 <strong>Frete Alto</strong> — categoria com impacto médio de frete &gt;12%</div>
                    <div>📈 <strong>Alta Consecutiva</strong> — 2+ meses seguidos de alta no preço médio</div>
                    <div>⚠️ <strong>Concentração</strong> — único fornecedor &gt;70% das compras de uma categoria</div>
                </div>
            </div>
        </div>
    );
}
