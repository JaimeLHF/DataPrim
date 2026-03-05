import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip, Legend, ResponsiveContainer,
} from 'recharts';
import type { BenchmarkCategoryData } from '../shared/services/dashboard';
import { useChartTheme } from '../shared/hooks/useChartTheme';

interface Props {
    data: BenchmarkCategoryData[];
}

const EMPRESA_COLOR = '#3b82f6';
const BENCH_COLOR = '#64748b';

const fmtPct = (v: number) => `${v.toFixed(1)}%`;

/* ─── Row for the chart ─── */
interface ChartRow {
    name: string;
    empresa: number;
    benchmark: number;
    empresaPct: number;
    benchPct: number;
}

/* ─── Tooltip ─── */
function ChartTooltip({ active, payload, label }: any) {
    if (!active || !payload?.length) return null;
    const row: ChartRow = payload[0]?.payload;
    if (!row) return null;
    return (
        <div style={{
            background: 'var(--chart-tooltip-bg, #1e293b)',
            border: '1px solid var(--chart-tooltip-border, #334155)',
            borderRadius: 8, padding: '10px 14px', fontSize: 12, lineHeight: 1.7,
        }}>
            <div style={{ fontWeight: 700, color: 'var(--chart-label, #e2e8f0)', marginBottom: 4 }}>{label}</div>
            <div style={{ color: EMPRESA_COLOR }}>
                Empresa: {fmtPct(row.empresaPct)}
            </div>
            <div style={{ color: BENCH_COLOR }}>
                Benchmark: {fmtPct(row.benchPct)}
            </div>
        </div>
    );
}

export default function CostStructureBenchmarkChart({ data }: Props) {
    const ct = useChartTheme();

    if (!data.length) {
        return <div className="state-empty"><span>Sem dados de estrutura de custo</span></div>;
    }

    const chartData: ChartRow[] = data.map((d) => ({
        name: d.category_name,
        empresa: d.company_percentage,
        benchmark: d.benchmark_percentage ?? 0,
        empresaPct: d.company_percentage,
        benchPct: d.benchmark_percentage ?? 0,
    }));

    return (
        <ResponsiveContainer width="100%" height={380}>
            <BarChart
                data={chartData}
                margin={{ top: 10, right: 10, bottom: 5, left: 10 }}
                barGap={4}
                barCategoryGap="20%"
            >
                <CartesianGrid strokeDasharray="3 3" stroke={ct.grid} vertical={false} />
                <XAxis
                    dataKey="name"
                    tick={{ fill: ct.tick, fontSize: 10 }}
                    axisLine={{ stroke: ct.grid }}
                    tickLine={false}
                />
                <YAxis
                    tick={{ fill: ct.tick, fontSize: 10 }}
                    tickLine={false}
                    axisLine={false}
                    tickFormatter={(v: number) => `${v.toFixed(0)}%`}
                    domain={[0, 'auto']}
                />
                <Tooltip content={<ChartTooltip />} />
                <Legend
                    wrapperStyle={{ fontSize: 11, paddingTop: 8 }}
                    content={() => (
                        <div style={{ display: 'flex', justifyContent: 'center', gap: 18, paddingTop: 8, fontSize: 11 }}>
                            {[
                                { label: 'Empresa', color: EMPRESA_COLOR },
                                { label: 'Benchmark', color: BENCH_COLOR },
                            ].map((it) => (
                                <span key={it.label} style={{ display: 'inline-flex', alignItems: 'center', gap: 5 }}>
                                    <span style={{ width: 11, height: 11, background: it.color, borderRadius: 2, display: 'inline-block' }} />
                                    {it.label}
                                </span>
                            ))}
                        </div>
                    )}
                />
                <Bar dataKey="empresa" name="Empresa" fill={EMPRESA_COLOR} radius={[4, 4, 0, 0]} />
                <Bar dataKey="benchmark" name="Benchmark" fill={BENCH_COLOR} radius={[4, 4, 0, 0]} />
            </BarChart>
        </ResponsiveContainer>
    );
}
