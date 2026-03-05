import { useState, useEffect } from 'react';
import { fetchSuppliers, type SupplierRow } from '../shared/services/dashboard';
import {
    ResponsiveContainer, RadarChart, PolarGrid, PolarAngleAxis, Radar,
} from 'recharts';

const fmt = (n: number) => n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
const fmtShort = (n: number) => {
    if (n >= 1_000_000) return `R$ ${(n / 1_000_000).toFixed(1)}M`;
    if (n >= 1_000) return `R$ ${(n / 1_000).toFixed(1)}k`;
    return fmt(n);
};

const CLASS_STYLE: Record<string, { bg: string; text: string }> = {
    'Estratégico': { bg: 'rgba(34,197,94,0.15)', text: '#22c55e' },
    'Alternativo': { bg: 'rgba(251,191,36,0.15)', text: '#fbbf24' },
    'Risco': { bg: 'rgba(239,68,68,0.15)', text: '#ef4444' },
};

function ScoreRing({ score }: { score: number }) {
    const r = 26;
    const circ = 2 * Math.PI * r;
    const pct = score / 100;
    const color = score >= 70 ? '#22c55e' : score >= 40 ? '#fbbf24' : '#ef4444';
    return (
        <svg width={68} height={68} style={{ transform: 'rotate(-90deg)' }}>
            <circle cx={34} cy={34} r={r} fill="none" stroke="var(--color-border)" strokeWidth={5} />
            <circle cx={34} cy={34} r={r} fill="none" stroke={color} strokeWidth={5}
                strokeDasharray={`${circ * pct} ${circ * (1 - pct)}`} strokeLinecap="round" />
            <text x={34} y={34} textAnchor="middle" dominantBaseline="central"
                style={{ transform: 'rotate(90deg)', transformOrigin: '34px 34px', fill: color, fontSize: 14, fontWeight: 700 }}>
                {score}
            </text>
        </svg>
    );
}

function SupplierCard({ s, onSelect }: { s: SupplierRow; onSelect: () => void }) {
    const cl = CLASS_STYLE[s.classification] ?? CLASS_STYLE['Risco'];
    return (
        <div className="card" style={{ cursor: 'pointer', transition: 'transform 0.15s, box-shadow 0.15s' }}
            onClick={onSelect}
            onMouseEnter={e => { (e.currentTarget as HTMLDivElement).style.transform = 'translateY(-2px)'; }}
            onMouseLeave={e => { (e.currentTarget as HTMLDivElement).style.transform = ''; }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 16, marginBottom: 16 }}>
                <ScoreRing score={s.score} />
                <div style={{ flex: 1 }}>
                    <div style={{ fontWeight: 700, fontSize: 15 }}>{s.supplier_name}</div>
                    {s.cnpj && <div style={{ fontSize: 11, color: 'var(--color-text-muted)', fontFamily: 'monospace' }}>{s.cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5')}</div>}
                    <div style={{ fontSize: 12, color: 'var(--color-text-muted)' }}>{s.state ? `${s.state} — ${s.region}` : s.region}</div>
                    <span style={{ display: 'inline-block', marginTop: 6, padding: '2px 10px', borderRadius: 20, fontSize: 11, fontWeight: 600, background: cl.bg, color: cl.text }}>
                        {s.classification}
                    </span>
                </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10 }}>
                {[
                    { label: 'Total comprado', value: fmtShort(s.total_purchased) },
                    { label: 'Notas emitidas', value: s.invoice_count },
                    { label: 'Frete médio', value: `${s.avg_freight_pct.toFixed(1)}%` },
                    { label: 'Categorias', value: s.category_count },
                ].map(item => (
                    <div key={item.label} style={{ background: 'var(--color-bg)', borderRadius: 8, padding: '8px 12px' }}>
                        <div style={{ fontSize: 10, color: 'var(--color-text-muted)', marginBottom: 2 }}>{item.label}</div>
                        <div style={{ fontWeight: 600, fontSize: 14 }}>{item.value}</div>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default function Fornecedores() {
    const [suppliers, setSuppliers] = useState<SupplierRow[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [selected, setSelected] = useState<SupplierRow | null>(null);
    const [filter, setFilter] = useState<'Todos' | 'Estratégico' | 'Alternativo' | 'Risco'>('Todos');

    useEffect(() => {
        setLoading(true);
        fetchSuppliers()
            .then(res => setSuppliers(res.data))
            .catch(e => setError(e instanceof Error ? e.message : 'Erro'))
            .finally(() => setLoading(false));
    }, []);

    const filtered = filter === 'Todos' ? suppliers : suppliers.filter(s => s.classification === filter);

    // Build radar data for selected supplier
    const radarData = selected ? [
        { subject: 'Volume', value: Math.round((selected.total_purchased / (suppliers[0]?.total_purchased || 1)) * 100) },
        { subject: 'Frete', value: Math.max(0, 100 - (selected.avg_freight_pct * 3)) },
        { subject: 'Consistência', value: Math.min(100, selected.invoice_count * 8) },
        { subject: 'Categorias', value: selected.category_count * 20 },
        { subject: 'Score', value: selected.score },
    ] : [];

    return (
        <div>
            <div className="page-header">
                <h1>Fornecedores</h1>
                <p>Análise de performance, score e classificação estratégica</p>
            </div>

            {/* Summary KPIs */}
            {suppliers.length > 0 && (
                <div className="kpi-grid" style={{ marginBottom: 24 }}>
                    {[
                        { icon: '🏆', label: 'Estratégicos', value: suppliers.filter(s => s.classification === 'Estratégico').length, variant: 'positive' },
                        { icon: '🔄', label: 'Alternativos', value: suppliers.filter(s => s.classification === 'Alternativo').length, variant: 'default' },
                        { icon: '⚠️', label: 'Em Risco', value: suppliers.filter(s => s.classification === 'Risco').length, variant: 'negative' },
                        { icon: '💰', label: 'Total Gasto', value: fmtShort(suppliers.reduce((a, s) => a + s.total_purchased, 0)), variant: 'accent' },
                    ].map(k => (
                        <div key={k.label} className="kpi-card" style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                            <div style={{ fontSize: 28 }}>{k.icon}</div>
                            <div>
                                <div className="kpi-label">{k.label}</div>
                                <div className={`kpi-value ${k.variant}`}>{k.value}</div>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Filter */}
            <div className="filter-bar" style={{ marginBottom: 20 }}>
                {(['Todos', 'Estratégico', 'Alternativo', 'Risco'] as const).map(f => (
                    <button key={f}
                        className={filter === f ? 'btn btn-primary' : 'btn btn-outline'}
                        style={{ padding: '8px 18px' }}
                        onClick={() => setFilter(f)}>
                        {f}
                    </button>
                ))}
            </div>

            {error && <div style={{ color: 'var(--color-danger)', marginBottom: 16 }}>⚠️ {error}</div>}

            {loading ? (
                <div className="state-loading"><div className="spinner" /><span>Carregando fornecedores…</span></div>
            ) : (
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: 16 }}>
                    {filtered.map(s => (
                        <SupplierCard key={s.supplier_id} s={s} onSelect={() => setSelected(s)} />
                    ))}
                </div>
            )}

            {/* Detail Modal */}
            {selected && (
                <div style={{ position: 'fixed', inset: 0, background: 'var(--color-overlay)', zIndex: 100, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 24 }}
                    onClick={() => setSelected(null)}>
                    <div style={{ background: 'var(--color-surface)', borderRadius: 20, padding: 32, maxWidth: 600, width: '100%', boxShadow: 'var(--shadow-lg)' }}
                        onClick={e => e.stopPropagation()}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 24 }}>
                            <div>
                                <h2 style={{ margin: 0 }}>{selected.supplier_name}</h2>
                                {selected.cnpj && <div style={{ color: 'var(--color-text-muted)', fontSize: 12, fontFamily: 'monospace' }}>{selected.cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5')}</div>}
                                <div style={{ color: 'var(--color-text-muted)', fontSize: 13 }}>{selected.state ? `${selected.state} — ${selected.region}` : selected.region}</div>
                            </div>
                            <button onClick={() => setSelected(null)} style={{ background: 'none', border: 'none', color: 'var(--color-text-muted)', fontSize: 20, cursor: 'pointer' }}>✕</button>
                        </div>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16, marginBottom: 24 }}>
                            <div>
                                <h4 style={{ margin: '0 0 8px', fontSize: 13, color: 'var(--color-text-muted)' }}>MÉTRICAS</h4>
                                {[
                                    ['Total comprado', fmt(selected.total_purchased)],
                                    ['Notas emitidas', selected.invoice_count],
                                    ['Ticket médio', fmt(selected.avg_invoice_value)],
                                    ['Frete médio', `${selected.avg_freight_pct.toFixed(1)}%`],
                                    ['Categorias', selected.category_count],
                                    ['1ª compra', selected.first_purchase?.slice(0, 10) ?? '—'],
                                    ['Última compra', selected.last_purchase?.slice(0, 10) ?? '—'],
                                ].map(([label, value]) => (
                                    <div key={label as string} style={{ display: 'flex', justifyContent: 'space-between', padding: '6px 0', borderBottom: '1px solid var(--color-border)', fontSize: 13 }}>
                                        <span style={{ color: 'var(--color-text-muted)' }}>{label}</span>
                                        <strong>{value}</strong>
                                    </div>
                                ))}
                            </div>

                            <div>
                                <h4 style={{ margin: '0 0 8px', fontSize: 13, color: 'var(--color-text-muted)' }}>RADAR DE PERFORMANCE</h4>
                                <ResponsiveContainer width="100%" height={220}>
                                    <RadarChart data={radarData}>
                                        <PolarGrid stroke="var(--chart-polar-grid)" />
                                        <PolarAngleAxis dataKey="subject" tick={{ fill: 'var(--chart-tick)', fontSize: 11 }} />
                                        <Radar name="Score" dataKey="value" stroke="#6366f1" fill="#6366f1" fillOpacity={0.3} />
                                    </RadarChart>
                                </ResponsiveContainer>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
