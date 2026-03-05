import { useState, useEffect } from 'react';
import { fetchSaving, type SavingData } from '../shared/services/dashboard';

const fmt = (n: number) => n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
const fmtPct = (n: number) => `${n > 0 ? '+' : ''}${n.toFixed(1)}%`;

const STATUS_STYLE = {
    saving: { cls: 'badge-success', label: 'Economizando', icon: '📉' },
    stable: { cls: 'badge-warning', label: 'Estável', icon: '➡️' },
    overpaying: { cls: 'badge-danger', label: 'Acima', icon: '📈' },
};

export default function Saving() {
    const [data, setData] = useState<SavingData | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        fetchSaving()
            .then(setData)
            .catch(e => setError(e instanceof Error ? e.message : 'Erro ao carregar'))
            .finally(() => setLoading(false));
    }, []);

    return (
        <div>
            <div className="page-header">
                <h1>💰 Saving & Cost Avoidance</h1>
                <p>Economia real vs. período anterior e custo evitado em relação ao índice de mercado simulado</p>
            </div>

            {error && <div style={{ color: 'var(--color-danger)', marginBottom: 16 }}>⚠️ {error}</div>}

            {loading ? (
                <div className="state-loading"><div className="spinner" /></div>
            ) : data && (
                <>
                    {/* Summary KPIs */}
                    <div className="kpi-grid" style={{ marginBottom: 28 }}>
                        <div className="kpi-card" style={{ borderColor: data.total_saving >= 0 ? 'var(--color-success)' : 'var(--color-danger)' }}>
                            <div className="kpi-label">💵 Saving Acumulado</div>
                            <div className={`kpi-value ${data.total_saving >= 0 ? 'positive' : 'negative'}`}>
                                {fmt(Math.abs(data.total_saving))}
                            </div>
                            <div className="kpi-sub">{data.period_label}</div>
                        </div>

                        <div className="kpi-card" style={{ borderColor: 'var(--color-primary)' }}>
                            <div className="kpi-label">🛡️ Cost Avoidance</div>
                            <div className="kpi-value accent">{fmt(data.total_cost_avoid)}</div>
                            <div className="kpi-sub">Custo evitado vs. alta do mercado</div>
                        </div>

                        <div className="kpi-card">
                            <div className="kpi-label">📊 Categorias analisadas</div>
                            <div className="kpi-value">{data.categories.length}</div>
                            <div className="kpi-sub">Com histórico suficiente</div>
                        </div>

                        <div className="kpi-card">
                            <div className="kpi-label">🏆 Melhor categoria</div>
                            <div className="kpi-value positive" style={{ fontSize: 18, marginTop: 4 }}>
                                {data.categories.find(c => c.status === 'saving')?.category ?? '—'}
                            </div>
                            <div className="kpi-sub">Maior economia absoluta</div>
                        </div>
                    </div>

                    {/* Category breakdown table */}
                    <div className="card" style={{ padding: 0, overflow: 'hidden' }}>
                        <div style={{ padding: '20px 24px 16px', borderBottom: '1px solid var(--color-border)' }}>
                            <div style={{ fontWeight: 700, fontSize: 15 }}>Detalhamento por Categoria</div>
                            <div style={{ fontSize: 12, color: 'var(--color-text-muted)', marginTop: 3 }}>
                                Comparativo entre últimos 3 meses e 3 meses anteriores
                            </div>
                        </div>
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th>Preço Anterior</th>
                                    <th>Preço Atual</th>
                                    <th>Variação</th>
                                    <th>Saving Abs.</th>
                                    <th>Cost Avoid</th>
                                    <th>Índice Mercado</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.categories.map(cat => {
                                    const st = STATUS_STYLE[cat.status as keyof typeof STATUS_STYLE] ?? STATUS_STYLE.stable;
                                    return (
                                        <tr key={cat.category}>
                                            <td style={{ fontWeight: 600 }}>{cat.category}</td>
                                            <td>{cat.prev_avg_price != null ? fmt(cat.prev_avg_price) : '—'}</td>
                                            <td>{fmt(cat.curr_avg_price)}</td>
                                            <td>
                                                <span style={{
                                                    fontWeight: 700,
                                                    color: cat.price_change_pct > 0 ? 'var(--color-success)' : cat.price_change_pct < -2 ? 'var(--color-danger)' : 'var(--color-warning)',
                                                }}>
                                                    {fmtPct(cat.price_change_pct)}
                                                </span>
                                            </td>
                                            <td style={{ color: cat.saving_abs >= 0 ? 'var(--color-success)' : 'var(--color-danger)', fontWeight: 600 }}>
                                                {fmt(Math.abs(cat.saving_abs))}
                                            </td>
                                            <td style={{ color: 'var(--color-primary)', fontWeight: 600 }}>
                                                {fmt(cat.cost_avoid_abs)}
                                            </td>
                                            <td>
                                                <span style={{ color: 'var(--color-text-muted)' }}>
                                                    +{cat.market_index_pct.toFixed(1)}% a.a.
                                                </span>
                                            </td>
                                            <td>
                                                <span className={`badge ${st.cls}`}>
                                                    {st.icon} {st.label}
                                                </span>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>

                    {/* Explanation */}
                    <div className="card" style={{ marginTop: 20, padding: 20 }}>
                        <div style={{ fontWeight: 600, fontSize: 13, marginBottom: 10 }}>Como são calculados os indicadores</div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, fontSize: 12, color: 'var(--color-text-muted)' }}>
                            <div>
                                <strong style={{ color: 'var(--color-text)' }}>💵 Saving</strong><br />
                                Diferença entre o preço médio pago nos últimos 3 meses vs. os 3 meses anteriores, multiplicada pelo volume comprado.
                            </div>
                            <div>
                                <strong style={{ color: 'var(--color-text)' }}>🛡️ Cost Avoidance</strong><br />
                                Quanto você pagou a menos em relação ao que pagaria se o preço tivesse subido na mesma proporção do índice de mercado simulado por categoria.
                            </div>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
