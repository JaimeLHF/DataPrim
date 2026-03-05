import { useState, useEffect, useCallback } from 'react';
import WhatIfSliderCard, { buildSimulations, type CategorySimulation } from '../components/WhatIfSliderCard';
import WhatIfSummary from '../components/WhatIfSummary';
import {
    fetchCostStructureBenchmark,
    fetchBenchmarkPeriods,
    type CostStructureBenchmarkData,
    type BenchmarkPeriodOption,
} from '../shared/services/dashboard';

type Scenario = 'custom' | 'conservative' | 'moderate' | 'aggressive';

const SCENARIOS: { key: Scenario; label: string; icon: string; description: string; factor: number }[] = [
    { key: 'conservative', label: 'Conservador', icon: '🟢', description: 'Move 25% em direção ao benchmark', factor: 0.25 },
    { key: 'moderate', label: 'Moderado', icon: '🟡', description: 'Move 50% em direção ao benchmark', factor: 0.50 },
    { key: 'aggressive', label: 'Agressivo', icon: '🔴', description: 'Alinha 100% ao benchmark', factor: 1.00 },
];

export default function WhatIfSimulator() {
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [benchmarkData, setBenchmarkData] = useState<CostStructureBenchmarkData | null>(null);
    const [simulations, setSimulations] = useState<CategorySimulation[]>([]);
    const [activeScenario, setActiveScenario] = useState<Scenario>('custom');
    const [periods, setPeriods] = useState<BenchmarkPeriodOption[]>([]);
    const [selectedPeriod, setSelectedPeriod] = useState('');

    // Load data
    useEffect(() => {
        setLoading(true);
        Promise.all([
            fetchCostStructureBenchmark(selectedPeriod ? { period: selectedPeriod } : undefined),
            fetchBenchmarkPeriods(),
        ])
            .then(([bm, p]) => {
                setBenchmarkData(bm);
                setPeriods(p.periods);
                setSimulations(buildSimulations(bm.categories, bm.meta.total_company_spend));
                if (!selectedPeriod && bm.meta.period) {
                    setSelectedPeriod(bm.meta.period);
                }
            })
            .catch((e) => setError(e instanceof Error ? e.message : 'Erro ao carregar dados'))
            .finally(() => setLoading(false));
    }, [selectedPeriod]);

    // Handle slider change
    const handleSliderChange = useCallback((slug: string, targetPct: number) => {
        setActiveScenario('custom');
        setSimulations((prev) =>
            prev.map((s) => (s.slug === slug ? { ...s, targetPct } : s)),
        );
    }, []);

    // Apply preset scenario
    const applyScenario = useCallback(
        (scenario: Scenario) => {
            if (!benchmarkData) return;

            if (scenario === 'custom') {
                // Reset all to current
                setSimulations(buildSimulations(benchmarkData.categories, benchmarkData.meta.total_company_spend));
                setActiveScenario('custom');
                return;
            }

            const factor = SCENARIOS.find((s) => s.key === scenario)?.factor ?? 0.5;

            setSimulations((prev) =>
                prev.map((s) => {
                    if (s.benchmarkPct === null) return { ...s, targetPct: s.currentPct };
                    // Only adjust categories that are above benchmark
                    if (s.currentPct <= s.benchmarkPct) return { ...s, targetPct: s.currentPct };
                    const target = s.currentPct - (s.currentPct - s.benchmarkPct) * factor;
                    return { ...s, targetPct: Math.round(target * 10) / 10 };
                }),
            );
            setActiveScenario(scenario);
        },
        [benchmarkData],
    );

    // Reset all
    const resetAll = useCallback(() => {
        if (!benchmarkData) return;
        setSimulations(buildSimulations(benchmarkData.categories, benchmarkData.meta.total_company_spend));
        setActiveScenario('custom');
    }, [benchmarkData]);

    return (
        <div>
            {/* Page Header */}
            <div className="page-header">
                <h1>🎛️ Simulador de Cenários</h1>
                <p>Simule cenários de otimização de custos ajustando a estrutura por categoria</p>
            </div>

            {error && (
                <div style={{ background: 'var(--color-danger-dim)', border: '1px solid var(--color-danger)', borderRadius: 10, padding: '16px 20px', marginBottom: 24, color: 'var(--color-danger)', fontSize: 13 }}>
                    {error}
                </div>
            )}

            {loading ? (
                <div className="state-loading">
                    <div className="spinner" />
                    <span>Carregando dados de benchmark…</span>
                </div>
            ) : benchmarkData && benchmarkData.categories.length > 0 ? (
                <>
                    {/* Controls bar */}
                    <div className="filter-bar" style={{ flexWrap: 'wrap' }}>
                        <label>Período</label>
                        <select
                            className="filter-select"
                            value={selectedPeriod}
                            onChange={(e) => setSelectedPeriod(e.target.value)}
                        >
                            {periods.map((p) => (
                                <option key={p.value} value={p.value}>{p.label}</option>
                            ))}
                        </select>

                        <div style={{ display: 'flex', gap: 8, marginLeft: 'auto', flexWrap: 'wrap' }}>
                            {/* Scenario preset buttons */}
                            {SCENARIOS.map((sc) => (
                                <button
                                    key={sc.key}
                                    onClick={() => applyScenario(sc.key)}
                                    title={sc.description}
                                    className={activeScenario === sc.key ? 'btn btn-primary' : 'btn btn-outline'}
                                    style={{ fontSize: 12, padding: '8px 14px' }}
                                >
                                    {sc.icon} {sc.label}
                                </button>
                            ))}
                            <button
                                onClick={resetAll}
                                className="btn btn-outline"
                                style={{ fontSize: 12, padding: '8px 14px' }}
                            >
                                ↩ Resetar Tudo
                            </button>
                        </div>
                    </div>

                    {/* Info badge */}
                    <div
                        style={{
                            background: 'var(--color-primary-dim)',
                            border: '1px solid var(--color-primary)',
                            borderRadius: 10,
                            padding: '12px 18px',
                            marginBottom: 24,
                            fontSize: 12,
                            color: 'var(--color-primary)',
                            display: 'flex',
                            alignItems: 'center',
                            gap: 8,
                        }}
                    >
                        <span style={{ fontSize: 16 }}>💡</span>
                        <span>
                            <strong>{benchmarkData.meta.period_label}</strong> —
                            Gasto total: <strong>R$ {benchmarkData.meta.total_company_spend.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</strong> ·
                            Amostra benchmark: <strong>{benchmarkData.meta.benchmark_sample_size}</strong> empresas ·
                            Região: <strong>{benchmarkData.meta.benchmark_region}</strong>
                        </span>
                    </div>

                    {/* Two-panel layout */}
                    <div style={{ display: 'flex', gap: 24, alignItems: 'flex-start' }}>
                        {/* LEFT: Sliders */}
                        <div style={{ flex: '0 0 380px', display: 'flex', flexDirection: 'column', gap: 14 }}>
                            <h3 style={{ fontSize: 14, fontWeight: 700, margin: '0 0 4px', color: 'var(--color-text)' }}>
                                Ajustar por Categoria
                            </h3>
                            {simulations.map((sim) => (
                                <WhatIfSliderCard
                                    key={sim.slug}
                                    sim={sim}
                                    onChange={handleSliderChange}
                                />
                            ))}
                        </div>

                        {/* RIGHT: Summary & Charts */}
                        <div style={{ flex: 1, minWidth: 0 }}>
                            <h3 style={{ fontSize: 14, fontWeight: 700, margin: '0 0 14px', color: 'var(--color-text)' }}>
                                Resultado da Simulação
                                {activeScenario !== 'custom' && (
                                    <span className="badge badge-primary" style={{ marginLeft: 8, fontSize: 10 }}>
                                        {SCENARIOS.find((s) => s.key === activeScenario)?.label}
                                    </span>
                                )}
                            </h3>
                            <WhatIfSummary
                                simulations={simulations}
                                totalCompanySpend={benchmarkData.meta.total_company_spend}
                            />
                        </div>
                    </div>
                </>
            ) : (
                <div className="state-empty">
                    <span style={{ fontSize: 40 }}>📭</span>
                    <span>Sem dados de benchmark disponíveis para simulação.</span>
                </div>
            )}
        </div>
    );
}
