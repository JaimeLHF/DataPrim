import { useState, useEffect } from 'react';
import { fetchContacts, type ContactRow } from '../shared/services/dashboard';

const fmt = (n: number) => n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

const TERMS_STYLE = {
    below_market: { bg: 'rgba(239,68,68,0.15)', text: '#ef4444', label: 'Pagando antes', icon: '⚠️' },
    on_par: { bg: 'rgba(251,191,36,0.15)', text: '#fbbf24', label: 'Na média', icon: '➡️' },
    above_market: { bg: 'rgba(34,197,94,0.15)', text: '#22c55e', label: 'Prazo ótimo', icon: '✅' },
};

const REGIONS = ['Todas', 'Sul', 'Sudeste', 'Centro-Oeste', 'Norte', 'Nordeste'];

function ContactCard({ c, market }: { c: ContactRow; market: number }) {
    const ts = TERMS_STYLE[c.terms_status] ?? TERMS_STYLE.on_par;

    return (
        <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
            {/* Header */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                <div>
                    <div style={{ fontWeight: 700, fontSize: 15 }}>{c.name}</div>
                    {c.cnpj && <div style={{ fontSize: 11, color: 'var(--color-text-muted)', fontFamily: 'monospace' }}>{c.cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5')}</div>}
                    <div style={{ fontSize: 12, color: 'var(--color-text-muted)', marginTop: 2 }}>📍 {c.state ? `${c.state} — ${c.region}` : c.region}</div>
                </div>
                <span style={{ padding: '3px 10px', borderRadius: 20, fontSize: 11, fontWeight: 600, background: ts.bg, color: ts.text }}>
                    {ts.icon} {ts.label}
                </span>
            </div>

            {/* Contact details */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                {c.contact_name && (
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13 }}>
                        <span style={{ color: 'var(--color-text-muted)', minWidth: 16 }}>👤</span>
                        <span>{c.contact_name}</span>
                    </div>
                )}
                {c.contact_email && (
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13 }}>
                        <span style={{ color: 'var(--color-text-muted)', minWidth: 16 }}>✉️</span>
                        <a href={`mailto:${c.contact_email}`} style={{ color: 'var(--color-primary)' }}>{c.contact_email}</a>
                    </div>
                )}
                {c.contact_phone && (
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13 }}>
                        <span style={{ color: 'var(--color-text-muted)', minWidth: 16 }}>📞</span>
                        <a href={`tel:${c.contact_phone}`} style={{ color: 'var(--color-text)' }}>{c.contact_phone}</a>
                    </div>
                )}
            </div>

            {/* Categories */}
            <div>
                <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginBottom: 6 }}>FORNECE:</div>
                <div style={{ display: 'flex', gap: 4, flexWrap: 'wrap' }}>
                    {c.categories.map(cat => (
                        <span key={cat} className="badge badge-primary" style={{ fontSize: 10 }}>{cat}</span>
                    ))}
                </div>
            </div>

            {/* Payment terms + metrics */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 8, paddingTop: 12, borderTop: '1px solid var(--color-border)' }}>
                <div style={{ textAlign: 'center' }}>
                    <div style={{ fontSize: 10, color: 'var(--color-text-muted)', marginBottom: 3 }}>PRAZO PAGO</div>
                    <div style={{ fontWeight: 700, fontSize: 16, color: ts.text }}>{c.payment_terms}d</div>
                </div>
                <div style={{ textAlign: 'center' }}>
                    <div style={{ fontSize: 10, color: 'var(--color-text-muted)', marginBottom: 3 }}>MERCADO</div>
                    <div style={{ fontWeight: 700, fontSize: 16 }}>{market}d</div>
                </div>
                <div style={{ textAlign: 'center' }}>
                    <div style={{ fontSize: 10, color: 'var(--color-text-muted)', marginBottom: 3 }}>NOTAS</div>
                    <div style={{ fontWeight: 700, fontSize: 16 }}>{c.invoice_count}</div>
                </div>
            </div>

            <div style={{ fontSize: 12, color: 'var(--color-text-muted)', paddingTop: 4, borderTop: '1px solid var(--color-border)' }}>
                Total comprado: <strong style={{ color: 'var(--color-text)' }}>{fmt(c.total_purchased)}</strong>
            </div>
        </div>
    );
}

export default function Contatos() {
    const [contacts, setContacts] = useState<ContactRow[]>([]);
    const [market, setMarket] = useState(42);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [search, setSearch] = useState('');
    const [region, setRegion] = useState('Todas');

    useEffect(() => {
        setLoading(true);
        fetchContacts()
            .then(res => { setContacts(res.data); setMarket(res.market_payment_terms); })
            .catch(e => setError(e instanceof Error ? e.message : 'Erro'))
            .finally(() => setLoading(false));
    }, []);

    const filtered = contacts.filter(c => {
        const q = search.toLowerCase();
        const matchesSearch = !q ||
            c.name.toLowerCase().includes(q) ||
            (c.contact_name ?? '').toLowerCase().includes(q) ||
            (c.contact_email ?? '').toLowerCase().includes(q);
        const matchesRegion = region === 'Todas' || c.region === region;
        return matchesSearch && matchesRegion;
    });

    // Payment terms summary stats
    const avgTerms = contacts.length
        ? Math.round(contacts.reduce((a, c) => a + c.payment_terms, 0) / contacts.length)
        : 0;
    const belowMarket = contacts.filter(c => c.terms_status === 'below_market').length;

    return (
        <div>
            <div className="page-header">
                <h1>📋 Contatos de Fornecedores</h1>
                <p>Diretório de fornecedores com dados de contato, especialidades e benchmarking de prazo de pagamento</p>
            </div>

            {/* Payment terms summary */}
            {contacts.length > 0 && (
                <div className="kpi-grid" style={{ marginBottom: 24 }}>
                    <div className="kpi-card">
                        <div className="kpi-label">⏱️ Prazo Médio — Você</div>
                        <div className={`kpi-value ${avgTerms < market ? 'negative' : 'positive'}`}>{avgTerms} dias</div>
                        <div className="kpi-sub">vs. {market}d de mercado</div>
                    </div>
                    <div className="kpi-card">
                        <div className="kpi-label">📅 Benchmark de Mercado</div>
                        <div className="kpi-value accent">{market} dias</div>
                        <div className="kpi-sub">Prazo médio do setor moveleiro</div>
                    </div>
                    <div className="kpi-card" style={{ borderColor: belowMarket > 0 ? 'var(--color-danger)' : 'inherit' }}>
                        <div className="kpi-label">⚠️ Pagando Antes do Mercado</div>
                        <div className={`kpi-value ${belowMarket > 0 ? 'negative' : 'positive'}`}>{belowMarket}</div>
                        <div className="kpi-sub">fornecedor{belowMarket !== 1 ? 'es' : ''} com prazo abaixo do mercado</div>
                    </div>
                    <div className="kpi-card">
                        <div className="kpi-label">🏢 Fornecedores Cadastrados</div>
                        <div className="kpi-value">{contacts.length}</div>
                        <div className="kpi-sub">com dados de contato</div>
                    </div>
                </div>
            )}

            {/* Filters */}
            <div className="filter-bar" style={{ marginBottom: 24 }}>
                <input
                    className="filter-select"
                    style={{ minWidth: 220 }}
                    placeholder="🔍 Buscar por nome, contato…"
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                />
                <select className="filter-select" value={region} onChange={e => setRegion(e.target.value)}>
                    {REGIONS.map(r => <option key={r}>{r}</option>)}
                </select>
                <span style={{ marginLeft: 'auto', fontSize: 13, color: 'var(--color-text-muted)' }}>
                    {filtered.length} fornecedor{filtered.length !== 1 ? 'es' : ''}
                </span>
            </div>

            {error && <div style={{ color: 'var(--color-danger)', marginBottom: 16 }}>⚠️ {error}</div>}

            {loading ? (
                <div className="state-loading"><div className="spinner" /></div>
            ) : filtered.length === 0 ? (
                <div className="card" style={{ textAlign: 'center', padding: 48 }}>
                    <div style={{ fontSize: 40, marginBottom: 12 }}>📋</div>
                    <h3 style={{ margin: '0 0 8px' }}>Nenhum fornecedor encontrado</h3>
                    <p style={{ color: 'var(--color-text-muted)', margin: 0, fontSize: 14 }}>Tente ajustar os filtros.</p>
                </div>
            ) : (
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(320px, 1fr))', gap: 16 }}>
                    {filtered.map(c => (
                        <ContactCard key={c.id} c={c} market={market} />
                    ))}
                </div>
            )}

            {/* Payment terms explanation */}
            <div className="card" style={{ marginTop: 24, padding: 20 }}>
                <div style={{ fontWeight: 600, fontSize: 13, marginBottom: 10 }}>Entendendo o benchmarking de prazo</div>
                <div style={{ fontSize: 12, color: 'var(--color-text-muted)', lineHeight: 1.8 }}>
                    O mercado moveleiro paga fornecedores em média em <strong style={{ color: 'var(--color-text)' }}>{market} dias</strong>.
                    Pagar antes do mercado significa imobilizar caixa desnecessariamente.
                    Cada dia adicional de prazo equivale a crédito sem juros.
                    <span style={{ color: 'var(--color-danger)' }}> ⚠️ Pagando antes</span> = prazo negociado abaixo do mercado ·&nbsp;
                    <span style={{ color: 'var(--color-warning)' }}> ➡️ Na média</span> = dentro do padrão ·&nbsp;
                    <span style={{ color: 'var(--color-success)' }}> ✅ Prazo ótimo</span> = pagando depois do mercado (positivo para o caixa)
                </div>
            </div>
        </div>
    );
}
