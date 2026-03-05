import type { BenchmarkCategoryData } from '../shared/services/dashboard';

interface Props {
    data: BenchmarkCategoryData[];
}

const EMPRESA_COLOR = '#3b82f6';
const BENCH_COLOR = '#64748b';

const fmtPct = (v: number) => `${v.toFixed(1)}%`;

export default function BenchmarkComparisonTable({ data }: Props) {
    if (!data.length) {
        return <div className="state-empty"><span>Sem dados</span></div>;
    }

    return (
        <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 12 }}>
            <thead>
                <tr style={{
                    borderBottom: '2px solid var(--color-border, #475569)',
                    textTransform: 'uppercase',
                    fontSize: 10,
                    letterSpacing: 0.5,
                    color: 'var(--color-text-2, #94a3b8)',
                }}>
                    <th style={{ textAlign: 'left', padding: '6px 4px', fontWeight: 600 }}>Categoria</th>
                    <th style={{ textAlign: 'right', padding: '6px 4px', fontWeight: 600 }}>% Cliente</th>
                    <th style={{ textAlign: 'right', padding: '6px 4px', fontWeight: 600 }}>% Benchmark</th>
                    <th style={{ textAlign: 'right', padding: '6px 4px', fontWeight: 600 }}>Diferença</th>
                </tr>
            </thead>
            <tbody>
                {data.map((d) => {
                    const delta = d.delta_percentage ?? 0;
                    const absDelta = Math.abs(delta);
                    const dColor =
                        absDelta <= 2
                            ? 'var(--color-text-2, #94a3b8)'
                            : delta > 0 ? '#ef4444' : '#22c55e';
                    const arrow = absDelta <= 2 ? '' : delta > 0 ? '▲ ' : '▼ ';

                    return (
                        <tr
                            key={d.category_slug}
                            style={{ borderBottom: '1px solid var(--color-border, rgba(51,65,85,0.35))' }}
                        >
                            <td style={{ padding: '8px 4px', fontWeight: 600 }}>
                                {d.category_name}
                            </td>
                            <td style={{ padding: '8px 4px', textAlign: 'right', color: EMPRESA_COLOR, fontWeight: 600 }}>
                                {fmtPct(d.company_percentage)}
                            </td>
                            <td style={{ padding: '8px 4px', textAlign: 'right', color: BENCH_COLOR, fontWeight: 600 }}>
                                {d.benchmark_percentage != null ? fmtPct(d.benchmark_percentage) : '—'}
                            </td>
                            <td style={{ padding: '8px 4px', textAlign: 'right', fontWeight: 700, color: dColor }}>
                                {d.delta_percentage != null
                                    ? `${arrow}${delta > 0 ? '+' : ''}${fmtPct(delta)}`
                                    : '—'}
                            </td>
                        </tr>
                    );
                })}
            </tbody>
            <tfoot>
                <tr style={{ borderTop: '2px solid var(--color-border, #475569)', fontWeight: 700 }}>
                    <td style={{ padding: '8px 4px' }}>Total</td>
                    <td style={{ padding: '8px 4px', textAlign: 'right', color: EMPRESA_COLOR }}>100.0%</td>
                    <td style={{ padding: '8px 4px', textAlign: 'right', color: BENCH_COLOR }}>100.0%</td>
                    <td style={{ padding: '8px 4px', textAlign: 'right' }}>—</td>
                </tr>
            </tfoot>
        </table>
    );
}
