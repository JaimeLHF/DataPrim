import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip, ResponsiveContainer, Cell,
} from 'recharts';
import type { RankingRow } from '../shared/services/dashboard';
import { useChartTheme } from '../shared/hooks/useChartTheme';

const COLORS = ['#3b82f6', '#8b5cf6', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444'];

interface Props { data: RankingRow[] }

export default function CategoryRankingChart({ data }: Props) {
    const ct = useChartTheme();

    if (!data.length) return <div className="state-empty"><span>Sem dados</span></div>;

    return (
        <ResponsiveContainer width="100%" height={280}>
            <BarChart data={data} margin={{ top: 5, right: 10, bottom: 5, left: 10 }}>
                <CartesianGrid strokeDasharray="3 3" stroke={ct.grid} vertical={false} />
                <XAxis dataKey="category" tick={{ fill: ct.tick, fontSize: 11 }} axisLine={{ stroke: ct.grid }} tickLine={false} />
                <YAxis
                    tick={{ fill: ct.tick, fontSize: 11 }}
                    tickLine={false}
                    axisLine={false}
                    tickFormatter={(v) => `R$${(v / 1000).toFixed(0)}k`}
                />
                <Tooltip
                    contentStyle={{ background: ct.tooltipBg, border: `1px solid ${ct.tooltipBorder}`, borderRadius: 8 }}
                    labelStyle={{ color: ct.label, fontSize: 12 }}
                    formatter={(v: number | undefined) => [`R$ ${(v ?? 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`, 'Total']}
                />
                <Bar dataKey="total_value" radius={[6, 6, 0, 0]}>
                    {data.map((_, i) => (
                        <Cell key={i} fill={COLORS[i % COLORS.length]} />
                    ))}
                </Bar>
            </BarChart>
        </ResponsiveContainer>
    );
}
