import {
    LineChart, Line, XAxis, YAxis, CartesianGrid,
    Tooltip, Legend, ResponsiveContainer,
} from 'recharts';
import { useChartTheme } from '../shared/hooks/useChartTheme';

const COLORS = ['#3b82f6', '#8b5cf6', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444'];

interface Props {
    data: Record<string, string | number>[];
    categories: string[];
    marketCategories?: string[];
}

export default function PriceEvolutionChart({ data, categories, marketCategories }: Props) {
    const ct = useChartTheme();

    if (!data.length) {
        return <div className="state-empty"><span>Sem dados</span></div>;
    }

    return (
        <ResponsiveContainer width="100%" height={280}>
            <LineChart data={data} margin={{ top: 5, right: 10, bottom: 5, left: 10 }}>
                <CartesianGrid strokeDasharray="3 3" stroke={ct.grid} />
                <XAxis
                    dataKey="month"
                    tick={{ fill: ct.tick, fontSize: 11 }}
                    tickLine={false}
                    axisLine={{ stroke: ct.grid }}
                />
                <YAxis
                    tick={{ fill: ct.tick, fontSize: 11 }}
                    tickLine={false}
                    axisLine={false}
                    tickFormatter={(v) => `R$${v}`}
                />
                <Tooltip
                    contentStyle={{ background: ct.tooltipBg, border: `1px solid ${ct.tooltipBorder}`, borderRadius: 8 }}
                    labelStyle={{ color: ct.label, fontSize: 12 }}
                    itemStyle={{ fontSize: 12 }}
                    formatter={(v: number | undefined) => [`R$ ${(v ?? 0).toFixed(2)}`, '']}
                />
                <Legend
                    wrapperStyle={{ fontSize: 12, color: ct.tick, paddingTop: 12 }}
                />
                {/* Company lines (solid) */}
                {categories.map((cat, i) => (
                    <Line
                        key={cat}
                        type="monotone"
                        dataKey={cat}
                        stroke={COLORS[i % COLORS.length]}
                        strokeWidth={2}
                        dot={false}
                        activeDot={{ r: 4 }}
                    />
                ))}
                {/* Market lines (dashed) */}
                {marketCategories?.map((mCat, i) => (
                    <Line
                        key={mCat}
                        type="monotone"
                        dataKey={mCat}
                        stroke={COLORS[i % COLORS.length]}
                        strokeWidth={1.5}
                        strokeDasharray="5 5"
                        dot={false}
                        activeDot={{ r: 3 }}
                        opacity={0.6}
                    />
                ))}
            </LineChart>
        </ResponsiveContainer>
    );
}
