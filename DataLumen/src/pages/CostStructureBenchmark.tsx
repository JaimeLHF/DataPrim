import { useState, useEffect, useCallback } from 'react';
import CostStructureBenchmarkChart from '../components/CostStructureBenchmarkChart';
import BenchmarkComparisonTable from '../components/BenchmarkComparisonTable';
import BenchmarkInsights from '../components/BenchmarkInsights';
import CostStructureDetailTable from '../components/CostStructureDetailTable';
import {
    fetchCostStructureBenchmark,
    fetchBenchmarkPeriods,
    type CostStructureBenchmarkData,
    type BenchmarkPeriodOption,
} from '../shared/services/dashboard';

export default function CostStructureBenchmark() {
    const [period, setPeriod] = useState('');
    const [periods, setPeriods] = useState<BenchmarkPeriodOption[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [data, setData] = useState<CostStructureBenchmarkData | null>(null);

    // Load available periods
    useEffect(() => {
        fetchBenchmarkPeriods()
            .then((res) => setPeriods(res.periods))
            .catch(() => setPeriods([]));
    }, []);

    const load = useCallback(async () => {
        setLoading(true);
        setError('');
        try {
            const params: Record<string, string> = {};
            if (period) params.period = period;
            const result = await fetchCostStructureBenchmark(params);
            setData(result);
        } catch (e: unknown) {
            setError(e instanceof Error ? e.message : 'Erro ao carregar benchmark');
        } finally {
            setLoading(false);
        }
    }, [period]);

    useEffect(() => { load(); }, [load]);

    return (
        <div>
            <div className="page-header">
                <h1>Estrutura de Custo vs Benchmark</h1>
                <p>Comparação percentual da composição de custos com a média de mercado</p>
            </div>

            {/* Filter Bar */}
            <div className="filter-bar">
                <label>Período</label>
                <select
                    className="filter-select"
                    value={period}
                    onChange={(e) => setPeriod(e.target.value)}
                >
                    <option value="">Mais recente</option>
                    {periods.map((p) => (
                        <option key={p.value} value={p.value}>{p.label}</option>
                    ))}
                </select>

                {data?.meta && (
                    <span style={{ marginLeft: 16, fontSize: 12, color: 'var(--color-text-2)' }}>
                        Região: <strong>{data.meta.benchmark_region}</strong>
                    </span>
                )}

                <button className="btn btn-outline" onClick={load} style={{ marginLeft: 'auto' }}>
                    ↻ Atualizar
                </button>
            </div>

            {/* Simulated benchmark badge */}
            <div
                style={{
                    background: 'var(--color-warning-dim)',
                    border: '1px solid var(--color-warning)',
                    borderRadius: 10,
                    padding: '10px 16px',
                    marginBottom: 20,
                    fontSize: 12,
                    color: 'var(--color-warning)',
                    display: 'flex',
                    alignItems: 'center',
                    gap: 8,
                }}
            >
                🔶 <strong>Benchmark simulado</strong> — dados de demonstração. Os índices de mercado serão substituídos por dados reais quando houver múltiplas empresas na plataforma.
            </div>

            {error && (
                <div
                    style={{
                        background: 'var(--color-danger-dim)',
                        border: '1px solid var(--color-danger)',
                        borderRadius: 10,
                        padding: '16px 20px',
                        marginBottom: 24,
                        color: 'var(--color-danger)',
                        fontSize: 13,
                    }}
                >
                    {error} — verifique se o backend está rodando em http://localhost:8000
                </div>
            )}

            {loading ? (
                <div className="state-loading">
                    <div className="spinner" />
                    <span>Carregando estrutura de custo…</span>
                </div>
            ) : data ? (
                <>
                    {/* Chart + Table side by side */}
                    <div style={{ display: 'flex', gap: 20, marginBottom: 0 }}>
                        {/* LEFT: Waterfall Chart */}
                        <div className="chart-card" style={{ flex: '1 1 60%', minWidth: 0 }}>
                            <h3>Estrutura de Custo ( % )</h3>
                            <p>{data.meta.period_label}</p>
                            <CostStructureBenchmarkChart data={data.categories} />
                        </div>

                        {/* RIGHT: Comparison Table */}
                        <div className="chart-card" style={{ flex: '0 0 auto', minWidth: 320, maxWidth: 400 }}>
                            <h3>% Cliente vs Benchmark</h3>
                            <BenchmarkComparisonTable data={data.categories} />
                        </div>
                    </div>

                    {/* Insights / Alertas */}
                    <div style={{ marginTop: 16 }}>
                        <BenchmarkInsights
                            categories={data.categories}
                            summary={data.summary}
                            meta={data.meta}
                        />
                    </div>

                    {/* Detail Table */}
                    <div className="card" style={{ marginTop: 24 }}>
                        <div className="card-title">Detalhamento por Categoria</div>
                        <CostStructureDetailTable
                            categories={data.categories}
                            summary={data.summary}
                            period={data.meta.period}
                        />
                    </div>
                </>
            ) : null}
        </div>
    );
}
