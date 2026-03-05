import { useCallback } from 'react';
import type { BenchmarkCategoryData } from '../shared/services/dashboard';

export interface CategorySimulation {
    slug: string;
    name: string;
    currentPct: number;
    benchmarkPct: number | null;
    targetPct: number;
    currentSpend: number;
    totalCompanySpend: number;
}

interface Props {
    sim: CategorySimulation;
    onChange: (slug: string, targetPct: number) => void;
}

const fmt = (n: number) =>
    n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

export function buildSimulations(
    categories: BenchmarkCategoryData[],
    totalCompanySpend: number,
): CategorySimulation[] {
    return categories.map((c) => ({
        slug: c.category_slug,
        name: c.category_name,
        currentPct: c.company_percentage,
        benchmarkPct: c.benchmark_percentage,
        targetPct: c.company_percentage, // starts at current
        currentSpend: c.company_spend,
        totalCompanySpend,
    }));
}

export function calcSaving(sim: CategorySimulation): number {
    const delta = sim.currentPct - sim.targetPct;
    if (delta <= 0) return 0;
    return (delta / 100) * sim.totalCompanySpend;
}

export default function WhatIfSliderCard({ sim, onChange }: Props) {
    const saving = calcSaving(sim);
    const hasBenchmark = sim.benchmarkPct !== null;

    const handleSlider = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            onChange(sim.slug, parseFloat(e.target.value));
        },
        [sim.slug, onChange],
    );

    const handleReset = useCallback(() => {
        onChange(sim.slug, sim.currentPct);
    }, [sim.slug, sim.currentPct, onChange]);

    const handleAlignBenchmark = useCallback(() => {
        if (sim.benchmarkPct !== null) {
            onChange(sim.slug, sim.benchmarkPct);
        }
    }, [sim.slug, sim.benchmarkPct, onChange]);

    // Determine if currently above market
    const isAbove = hasBenchmark && sim.currentPct > (sim.benchmarkPct ?? 0);
    const isTargetChanged = Math.abs(sim.targetPct - sim.currentPct) > 0.01;

    // Slider range: from 0 to max(currentPct * 1.5, benchmarkPct * 1.5)
    const sliderMax = Math.max(
        sim.currentPct * 1.5,
        (sim.benchmarkPct ?? sim.currentPct) * 1.5,
        1,
    );

    return (
        <div
            style={{
                background: 'var(--color-surface)',
                border: `1px solid ${isTargetChanged ? (saving > 0 ? 'var(--color-success)' : 'var(--color-warning)') : 'var(--color-border)'}`,
                borderRadius: 'var(--radius-lg)',
                padding: '18px 20px',
                transition: 'border-color 0.2s',
            }}
        >
            {/* Header */}
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 }}>
                <div>
                    <div style={{ fontWeight: 700, fontSize: 14, color: 'var(--color-text)' }}>
                        {sim.name}
                    </div>
                    <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 2 }}>
                        Gasto atual: {fmt(sim.currentSpend)}
                    </div>
                </div>
                {isAbove && (
                    <span className="badge badge-danger" style={{ fontSize: 10 }}>
                        Acima do Mercado
                    </span>
                )}
                {hasBenchmark && !isAbove && sim.currentPct < (sim.benchmarkPct ?? 0) - 2 && (
                    <span className="badge badge-success" style={{ fontSize: 10 }}>
                        Abaixo do Mercado
                    </span>
                )}
                {hasBenchmark && !isAbove && Math.abs(sim.currentPct - (sim.benchmarkPct ?? 0)) <= 2 && (
                    <span className="badge badge-primary" style={{ fontSize: 10 }}>
                        Alinhado
                    </span>
                )}
            </div>

            {/* Percentages row */}
            <div style={{ display: 'flex', gap: 16, marginBottom: 14, fontSize: 12 }}>
                <div>
                    <span style={{ color: 'var(--color-text-muted)' }}>Empresa: </span>
                    <strong>{sim.currentPct.toFixed(1)}%</strong>
                </div>
                {hasBenchmark && (
                    <div>
                        <span style={{ color: 'var(--color-text-muted)' }}>Benchmark: </span>
                        <strong style={{ color: '#64748b' }}>{sim.benchmarkPct!.toFixed(1)}%</strong>
                    </div>
                )}
                <div>
                    <span style={{ color: 'var(--color-text-muted)' }}>Alvo: </span>
                    <strong style={{ color: isTargetChanged ? 'var(--color-primary)' : 'var(--color-text)' }}>
                        {sim.targetPct.toFixed(1)}%
                    </strong>
                </div>
            </div>

            {/* Slider */}
            <div style={{ position: 'relative', marginBottom: 8 }}>
                <input
                    type="range"
                    min={0}
                    max={sliderMax}
                    step={0.1}
                    value={sim.targetPct}
                    onChange={handleSlider}
                    style={{
                        width: '100%',
                        accentColor: saving > 0 ? 'var(--color-success)' : 'var(--color-primary)',
                        height: 6,
                    }}
                />
                {/* Markers */}
                <div style={{ position: 'relative', height: 16, fontSize: 9, color: 'var(--color-text-muted)' }}>
                    {/* Current marker */}
                    <div
                        style={{
                            position: 'absolute',
                            left: `${(sim.currentPct / sliderMax) * 100}%`,
                            transform: 'translateX(-50%)',
                            whiteSpace: 'nowrap',
                        }}
                    >
                        <div style={{ width: 1, height: 6, background: 'var(--color-danger)', margin: '0 auto 2px' }} />
                        Atual
                    </div>
                    {/* Benchmark marker */}
                    {hasBenchmark && (
                        <div
                            style={{
                                position: 'absolute',
                                left: `${((sim.benchmarkPct ?? 0) / sliderMax) * 100}%`,
                                transform: 'translateX(-50%)',
                                whiteSpace: 'nowrap',
                            }}
                        >
                            <div style={{ width: 1, height: 6, background: '#64748b', margin: '0 auto 2px' }} />
                            Bench
                        </div>
                    )}
                </div>
            </div>

            {/* Saving result */}
            {isTargetChanged && (
                <div
                    style={{
                        background: saving > 0 ? 'var(--color-success-dim)' : 'var(--color-warning-dim)',
                        borderRadius: 8,
                        padding: '10px 14px',
                        marginTop: 8,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                    }}
                >
                    <span style={{ fontSize: 12, color: saving > 0 ? 'var(--color-success)' : 'var(--color-warning)' }}>
                        {saving > 0 ? '💰 Economia estimada' : '⚠️ Aumento estimado'}
                    </span>
                    <strong style={{ fontSize: 15, color: saving > 0 ? 'var(--color-success)' : 'var(--color-warning)' }}>
                        {saving > 0 ? '-' : '+'}{fmt(Math.abs(saving))}
                    </strong>
                </div>
            )}

            {/* Action buttons */}
            <div style={{ display: 'flex', gap: 8, marginTop: 10 }}>
                {hasBenchmark && (
                    <button
                        onClick={handleAlignBenchmark}
                        style={{
                            flex: 1,
                            padding: '6px 10px',
                            fontSize: 11,
                            fontWeight: 600,
                            borderRadius: 6,
                            border: '1px solid var(--color-border)',
                            background: 'var(--color-surface-2)',
                            color: 'var(--color-text)',
                            cursor: 'pointer',
                        }}
                    >
                        🎯 Alinhar ao Benchmark
                    </button>
                )}
                {isTargetChanged && (
                    <button
                        onClick={handleReset}
                        style={{
                            flex: 1,
                            padding: '6px 10px',
                            fontSize: 11,
                            fontWeight: 600,
                            borderRadius: 6,
                            border: '1px solid var(--color-border)',
                            background: 'transparent',
                            color: 'var(--color-text-muted)',
                            cursor: 'pointer',
                        }}
                    >
                        ↩ Resetar
                    </button>
                )}
            </div>
        </div>
    );
}
