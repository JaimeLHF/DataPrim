import { useState, useEffect, useCallback } from 'react';
import KpiCard from '../shared/components/KpiCard';
import TcoWaterfallChart from '../components/TcoWaterfallChart';
import GrossVsNetChart from '../components/GrossVsNetChart';
import PriceEvolutionChart from '../components/PriceEvolutionChart';
import PriceIndexChart from '../components/PriceIndexChart';
import CategoryRankingChart from '../components/CategoryRankingChart';
import PriceDispersionChart from '../components/PriceDispersionChart';
import FreightImpactChart from '../components/FreightImpactChart';
import CostStructureBenchmarkChart from '../components/CostStructureBenchmarkChart';
import BenchmarkComparisonTable from '../components/BenchmarkComparisonTable';
import BenchmarkInsights from '../components/BenchmarkInsights';
import CostStructureDetailTable from '../components/CostStructureDetailTable';
import {
    fetchKpis, fetchPriceEvolution, fetchDispersion,
    fetchFreightImpact, fetchCategoryRanking,
    fetchTcoBreakdown, fetchGrossVsNet, fetchPriceIndex,
    fetchCostStructureBenchmark,
    type KpiData, type PriceEvolutionData, type DispersionRow,
    type FreightRow, type RankingRow,
    type TcoBreakdownRow, type GrossVsNetRow, type PriceIndexData,
    type CostStructureBenchmarkData,
} from '../shared/services/dashboard';

const CATEGORIES = ['Todos', 'MDF', 'Ferragens', 'Químicos', 'Aramados', 'Embalagens', 'Acessórios'];
const MONTHS = [
    { label: 'Últimos 12 meses', value: '' },
    { label: 'Últimos 3 meses', value: '3' },
    { label: 'Últimos 6 meses', value: '6' },
];

function getDateRange(months: string): Record<string, string> {
    if (!months) return {};
    const end = new Date();
    const start = new Date();
    start.setMonth(start.getMonth() - parseInt(months));
    return {
        start_date: start.toISOString().slice(0, 10),
        end_date: end.toISOString().slice(0, 10),
    };
}

export default function Dashboard() {
    const [period, setPeriod] = useState('');
    const [category, setCategory] = useState('Todos');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    const [kpis, setKpis] = useState<KpiData | null>(null);
    const [tcoCompany, setTcoCompany] = useState<TcoBreakdownRow[]>([]);
    const [tcoMarket, setTcoMarket] = useState<TcoBreakdownRow[]>([]);
    const [grossVsNet, setGrossVsNet] = useState<GrossVsNetRow[]>([]);
    const [evolution, setEvolution] = useState<PriceEvolutionData>({ data: [], categories: [] });
    const [priceIndex, setPriceIndex] = useState<PriceIndexData>({ data: [], categories: [] });
    const [dispersion, setDispersion] = useState<DispersionRow[]>([]);
    const [freight, setFreight] = useState<FreightRow[]>([]);
    const [ranking, setRanking] = useState<RankingRow[]>([]);
    const [benchmark, setBenchmark] = useState<CostStructureBenchmarkData | null>(null);

    const load = useCallback(async () => {
        setLoading(true);
        setError('');
        try {
            const params = getDateRange(period);
            const catParam: Record<string, string> = category !== 'Todos' ? { category } : {};

            const [k, tb, gn, e, pi, d, f, r, bm] = await Promise.all([
                fetchKpis(params),
                fetchTcoBreakdown(params),
                fetchGrossVsNet(params),
                fetchPriceEvolution({ ...params, ...catParam }),
                fetchPriceIndex(catParam),
                fetchDispersion(params),
                fetchFreightImpact(params),
                fetchCategoryRanking(params),
                fetchCostStructureBenchmark().catch(() => null),
            ]);

            setKpis(k);
            setTcoCompany(tb.company);
            setTcoMarket(tb.market);
            setGrossVsNet(gn.data);
            setEvolution(e);
            setPriceIndex(pi);
            setDispersion(d.data);
            setFreight(f.data);
            setRanking(r.data);
            setBenchmark(bm);
        } catch (e: unknown) {
            setError(e instanceof Error ? e.message : 'Erro ao carregar dados');
        } finally {
            setLoading(false);
        }
    }, [period, category]);

    useEffect(() => { load(); }, [load]);

    const fmt = (n: number) =>
        n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    const fmtPct = (n: number) => `${n > 0 ? '+' : ''}${n.toFixed(1)}%`;

    const variationVariant = () => {
        if (!kpis) return 'default';
        return kpis.market_variation_percent <= 0 ? 'positive' : 'negative';
    };

    return (
        <div>
            <div className="page-header">
                <h1>Dashboard de Compras</h1>
                <p>Análise estratégica de custos e benchmark de mercado</p>
            </div>

            {/* Filter Bar */}
            <div className="filter-bar">
                <label>Período</label>
                <select
                    className="filter-select"
                    value={period}
                    onChange={(e) => setPeriod(e.target.value)}
                >
                    {MONTHS.map((m) => (
                        <option key={m.value} value={m.value}>{m.label}</option>
                    ))}
                </select>

                <label style={{ marginLeft: 8 }}>Categoria</label>
                <select
                    className="filter-select"
                    value={category}
                    onChange={(e) => setCategory(e.target.value)}
                >
                    {CATEGORIES.map((c) => (
                        <option key={c} value={c}>{c}</option>
                    ))}
                </select>

                <button className="btn btn-outline" onClick={load} style={{ marginLeft: 'auto' }}>
                    ↻ Atualizar
                </button>
            </div>

            {error && (
                <div style={{ background: 'var(--color-danger-dim)', border: '1px solid var(--color-danger)', borderRadius: 10, padding: '16px 20px', marginBottom: 24, color: 'var(--color-danger)', fontSize: 13 }}>
                    {error} — verifique se o backend está rodando em http://localhost:8000
                </div>
            )}

            {loading ? (
                <div className="state-loading">
                    <div className="spinner" />
                    <span>Carregando indicadores…</span>
                </div>
            ) : (
                <>
                    {/* ── 1. KPI Cards ── */}
                    <div className="kpi-grid">
                        <KpiCard
                            icon="💰"
                            label="TCO Médio"
                            value={kpis ? fmt(kpis.overall_tco_average) : '—'}
                            sub="Custo total de aquisição médio"
                        />
                        <KpiCard
                            icon="📈"
                            label="Variação vs Mercado"
                            value={kpis ? fmtPct(kpis.market_variation_percent) : '—'}
                            sub={kpis?.market_variation_percent !== undefined
                                ? kpis.market_variation_percent <= 0
                                    ? 'Abaixo da média de mercado'
                                    : 'Acima da média de mercado'
                                : ''}
                            variant={variationVariant()}
                        />
                        <KpiCard
                            icon="🚚"
                            label="Peso Médio do Frete"
                            value={kpis ? `${kpis.freight_weight_percent.toFixed(1)}%` : '—'}
                            sub="Percentual do frete no custo total"
                            variant={kpis && kpis.freight_weight_percent > 8 ? 'negative' : 'default'}
                        />
                        <KpiCard
                            icon="🏆"
                            label="Top Categoria"
                            value={kpis?.top_category?.category ?? '—'}
                            sub={kpis?.top_category ? `${fmt(kpis.top_category.total_value)} investidos` : ''}
                            variant="accent"
                        />
                    </div>

                    {/* ── 2. Estrutura de Custo vs Benchmark ── */}
                    {benchmark && benchmark.categories.length > 0 && (
                        <>
                            <div style={{ display: 'flex', gap: 20, marginBottom: 0 }}>
                                {/* LEFT: Waterfall Chart */}
                                <div className="chart-card" style={{ flex: '1 1 60%', minWidth: 0 }}>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 4 }}>
                                        <h3 style={{ margin: 0 }}>Estrutura de Custo ( % )</h3>
                                    </div>
                                    <p>
                                        {benchmark.meta.period_label}
                                        {' · Região: '}
                                        {benchmark.meta.benchmark_region}
                                    </p>
                                    <CostStructureBenchmarkChart data={benchmark.categories} />
                                </div>

                                {/* RIGHT: Comparison Table */}
                                <div className="chart-card" style={{ flex: '0 0 auto', minWidth: 320, maxWidth: 400 }}>
                                    <h3>% Cliente vs Benchmark</h3>
                                    <BenchmarkComparisonTable data={benchmark.categories} />
                                </div>
                            </div>

                            {/* Insights / Alertas */}
                            <div style={{ marginTop: 16 }}>
                                <BenchmarkInsights
                                    categories={benchmark.categories}
                                    summary={benchmark.summary}
                                    meta={benchmark.meta}
                                />
                            </div>

                            <div className="card" style={{ marginTop: 24, marginBottom: 24 }}>
                                <div className="card-title">Detalhamento: Estrutura de Custo vs Benchmark</div>
                                <CostStructureDetailTable
                                    categories={benchmark.categories}
                                    summary={benchmark.summary}
                                    period={benchmark.meta.period}
                                />
                            </div>
                        </>
                    )}

                    {/* ── 3. Cascata TCO (composição do custo) ── */}
                    <div className="charts-grid">
                        <div className="chart-card full-width">
                            <h3>Composição do TCO por Categoria</h3>
                            <p>Preço unitário + frete + impostos (empresa vs benchmark de mercado)</p>
                            <TcoWaterfallChart company={tcoCompany} market={tcoMarket} />
                        </div>

                        {/* ── 3. Custo Bruto vs Líquido ── */}
                        <div className="chart-card full-width">
                            <h3>Custo Bruto vs Líquido</h3>
                            <p>Valor total antes e depois do crédito de impostos (ICMS/IPI) por categoria</p>
                            <GrossVsNetChart data={grossVsNet} />
                        </div>

                        {/* ── 4. Evolução de Preços + curva de mercado ── */}
                        <div className="chart-card full-width">
                            <h3>Evolução de Preços Mensal</h3>
                            <p>Média de preço unitário por categoria — linhas sólidas (empresa) vs tracejadas (mercado)</p>
                            <PriceEvolutionChart
                                data={evolution.data}
                                categories={evolution.categories}
                                marketCategories={evolution.market_categories}
                            />
                        </div>

                        {/* ── 5. Índice % / Sazonalidade ── */}
                        <div className="chart-card full-width">
                            <h3>Índice de Preços (Sazonalidade)</h3>
                            <p>Variação percentual relativa ao primeiro mês (base 100) — identifica padrões sazonais</p>
                            <PriceIndexChart data={priceIndex.data} categories={priceIndex.categories} />
                        </div>

                        {/* ── 6. Dispersão de Preços ── */}
                        <div className="chart-card">
                            <h3>Dispersão de Preços</h3>
                            <p>Mínimo, média e máximo do preço unitário por categoria</p>
                            <PriceDispersionChart data={dispersion} />
                        </div>

                        {/* ── 7. Impacto do Frete ── */}
                        <div className="chart-card">
                            <h3>Impacto do Frete por Categoria</h3>
                            <p>Percentual do frete em relação ao custo total</p>
                            <FreightImpactChart data={freight} />
                        </div>

                        {/* ── 8. Ranking por Categoria ── */}
                        <div className="chart-card full-width">
                            <h3>Ranking de Categorias</h3>
                            <p>Volume total investido por categoria (R$)</p>
                            <CategoryRankingChart data={ranking} />
                        </div>

                    </div>

                    {/* ── 10. Tabela Benchmark TCO (legado) ── */}
                    {kpis?.benchmark_by_category && kpis.benchmark_by_category.length > 0 && (
                        <div className="card" style={{ marginTop: 24 }}>
                            <div className="card-title">Benchmark por Categoria</div>
                            <table className="data-table">
                                <thead>
                                    <tr>
                                        <th>Categoria</th>
                                        <th>TCO Empresa (R$)</th>
                                        <th>Média Mercado (R$)</th>
                                        <th>Variação</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {kpis.benchmark_by_category.map((row) => (
                                        <tr key={row.category}>
                                            <td><strong>{row.category}</strong></td>
                                            <td>{fmt(row.company_tco_avg)}</td>
                                            <td>{fmt(row.market_tco_avg)}</td>
                                            <td style={{ color: row.variation_percent > 0 ? 'var(--color-danger)' : 'var(--color-success)', fontWeight: 600 }}>
                                                {fmtPct(row.variation_percent)}
                                            </td>
                                            <td>
                                                <span className={`badge ${row.status === 'below_market' ? 'badge-success' : 'badge-danger'}`}>
                                                    {row.status === 'below_market' ? 'Abaixo' : 'Acima'}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </>
            )}
        </div>
    );
}
