import {
    ResponsiveContainer, BarChart, Bar, XAxis, YAxis, Tooltip, Cell, ReferenceLine,
} from 'recharts';
import type { CategorySimulation } from './WhatIfSliderCard';
import { calcSaving } from './WhatIfSliderCard';

interface Props {
    simulations: CategorySimulation[];
    totalCompanySpend: number;
}

const fmt = (n: number) =>
    n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

const fmtK = (n: number) => {
    if (Math.abs(n) >= 1_000_000) return `R$ ${(n / 1_000_000).toFixed(1)}M`;
    if (Math.abs(n) >= 1_000) return `R$ ${(n / 1_000).toFixed(1)}k`;
    return fmt(n);
};

export default function WhatIfSummary({ simulations, totalCompanySpend }: Props) {
    const savingsByCategory = simulations
        .map((s) => ({
            name: s.name,
            slug: s.slug,
            saving: calcSaving(s),
            currentPct: s.currentPct,
            targetPct: s.targetPct,
            changed: Math.abs(s.targetPct - s.currentPct) > 0.01,
        }))
        .filter((s) => s.changed);

    const totalSaving = savingsByCategory.reduce((sum, s) => sum + s.saving, 0);
    const categoriesChanged = savingsByCategory.length;
    const totalNewSpend = totalCompanySpend - totalSaving;
    const savingPct = totalCompanySpend > 0 ? (totalSaving / totalCompanySpend) * 100 : 0;

    // Chart data: show all categories with their deltas
    const chartData = simulations
        .filter((s) => Math.abs(s.targetPct - s.currentPct) > 0.01)
        .map((s) => ({
            name: s.name.length > 12 ? s.name.substring(0, 12) + '…' : s.name,
            fullName: s.name,
            economia: calcSaving(s),
        }))
        .sort((a, b) => b.economia - a.economia);

    return (
        <div>
            {/* Summary KPIs */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: 14, marginBottom: 24 }}>
                <div className="card" style={{ textAlign: 'center', padding: '18px 14px' }}>
                    <div style={{
                        fontSize: 26,
                        fontWeight: 800,
                        color: totalSaving > 0 ? 'var(--color-success)' : 'var(--color-text)',
                    }}>
                        {totalSaving > 0 ? `-${fmt(totalSaving)}` : fmt(0)}
                    </div>
                    <div style={{ fontSize: 12, color: 'var(--color-text-muted)', marginTop: 4 }}>
                        💰 Economia Total Projetada
                    </div>
                </div>

                <div className="card" style={{ textAlign: 'center', padding: '18px 14px' }}>
                    <div style={{ fontSize: 26, fontWeight: 800, color: 'var(--color-primary)' }}>
                        {savingPct > 0 ? `-${savingPct.toFixed(1)}%` : '0%'}
                    </div>
                    <div style={{ fontSize: 12, color: 'var(--color-text-muted)', marginTop: 4 }}>
                        📊 Redução Percentual
                    </div>
                </div>

                <div className="card" style={{ textAlign: 'center', padding: '18px 14px' }}>
                    <div style={{ fontSize: 26, fontWeight: 800, color: 'var(--color-text)' }}>
                        {categoriesChanged}
                    </div>
                    <div style={{ fontSize: 12, color: 'var(--color-text-muted)', marginTop: 4 }}>
                        📋 Categorias Ajustadas
                    </div>
                </div>

                <div className="card" style={{ textAlign: 'center', padding: '18px 14px' }}>
                    <div style={{ fontSize: 22, fontWeight: 700 }}>
                        <span style={{ color: 'var(--color-text-muted)', fontSize: 14, textDecoration: 'line-through' }}>
                            {fmtK(totalCompanySpend)}
                        </span>
                        <br />
                        <span style={{ color: totalSaving > 0 ? 'var(--color-success)' : 'var(--color-text)' }}>
                            {fmtK(totalNewSpend)}
                        </span>
                    </div>
                    <div style={{ fontSize: 12, color: 'var(--color-text-muted)', marginTop: 4 }}>
                        🏷️ Novo Gasto Projetado
                    </div>
                </div>
            </div>

            {/* Saving Chart */}
            {chartData.length > 0 && (
                <div className="card" style={{ padding: 20, marginBottom: 24 }}>
                    <h3 style={{ fontSize: 14, fontWeight: 700, margin: '0 0 4px' }}>Economia por Categoria</h3>
                    <p style={{ fontSize: 12, color: 'var(--color-text-muted)', margin: '0 0 16px' }}>
                        Impacto financeiro de cada ajuste
                    </p>
                    <ResponsiveContainer width="100%" height={Math.max(200, chartData.length * 52)}>
                        <BarChart data={chartData} layout="vertical" margin={{ left: 10, right: 30 }}>
                            <XAxis type="number" tickFormatter={fmtK} fontSize={11} />
                            <YAxis type="category" dataKey="name" width={110} fontSize={11} />
                            <Tooltip
                                formatter={(value) => [fmt(Number(value ?? 0)), 'Economia']}
                                contentStyle={{
                                    background: 'var(--color-surface-2)',
                                    border: '1px solid var(--color-border)',
                                    borderRadius: 8,
                                    fontSize: 12,
                                }}
                            />
                            <ReferenceLine x={0} stroke="var(--color-border)" />
                            <Bar dataKey="economia" radius={[0, 6, 6, 0]} maxBarSize={32}>
                                {chartData.map((entry, i) => (
                                    <Cell key={i} fill={entry.economia > 0 ? '#22c55e' : '#fbbf24'} />
                                ))}
                            </Bar>
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            )}

            {/* Detailed savings table */}
            {savingsByCategory.length > 0 && (
                <div className="card" style={{ padding: 20 }}>
                    <h3 style={{ fontSize: 14, fontWeight: 700, margin: '0 0 16px' }}>Detalhamento dos Ajustes</h3>
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th style={{ textAlign: 'right' }}>Atual (%)</th>
                                <th style={{ textAlign: 'right' }}>Alvo (%)</th>
                                <th style={{ textAlign: 'right' }}>Δ (p.p.)</th>
                                <th style={{ textAlign: 'right' }}>Economia (R$)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {savingsByCategory
                                .sort((a, b) => b.saving - a.saving)
                                .map((s) => {
                                    const deltaPP = s.currentPct - s.targetPct;
                                    return (
                                        <tr key={s.slug}>
                                            <td><strong>{s.name}</strong></td>
                                            <td style={{ textAlign: 'right' }}>{s.currentPct.toFixed(1)}%</td>
                                            <td style={{ textAlign: 'right', color: 'var(--color-primary)', fontWeight: 600 }}>
                                                {s.targetPct.toFixed(1)}%
                                            </td>
                                            <td style={{
                                                textAlign: 'right',
                                                fontWeight: 600,
                                                color: deltaPP > 0 ? 'var(--color-success)' : 'var(--color-warning)',
                                            }}>
                                                {deltaPP > 0 ? '-' : '+'}{Math.abs(deltaPP).toFixed(1)} p.p.
                                            </td>
                                            <td style={{
                                                textAlign: 'right',
                                                fontWeight: 700,
                                                color: s.saving > 0 ? 'var(--color-success)' : 'var(--color-warning)',
                                            }}>
                                                {s.saving > 0 ? '-' : '+'}{fmt(Math.abs(s.saving))}
                                            </td>
                                        </tr>
                                    );
                                })}
                        </tbody>
                        <tfoot>
                            <tr style={{ fontWeight: 700 }}>
                                <td>Total</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style={{ textAlign: 'right', color: 'var(--color-success)', fontSize: 15 }}>
                                    -{fmt(totalSaving)}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            )}

            {/* Empty state */}
            {savingsByCategory.length === 0 && (
                <div
                    style={{
                        textAlign: 'center',
                        padding: '40px 20px',
                        color: 'var(--color-text-muted)',
                        fontSize: 14,
                    }}
                >
                    <div style={{ fontSize: 40, marginBottom: 12 }}>🎛️</div>
                    <div style={{ fontWeight: 600 }}>Ajuste os sliders para simular cenários</div>
                    <div style={{ fontSize: 12, marginTop: 4 }}>
                        Mova os controles nas categorias à esquerda ou use os cenários pré-definidos acima
                    </div>
                </div>
            )}
        </div>
    );
}
