import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip, ResponsiveContainer, Cell, LabelList,
} from 'recharts';
import type { FreightRow } from '../shared/services/dashboard';
import { useChartTheme } from '../shared/hooks/useChartTheme';

const getColor = (pct: number) => {
    if (pct > 10) return '#ef4444';
    if (pct > 6) return '#f59e0b';
    return '#22c55e';
};

interface Props { data: FreightRow[] }

export default function FreightImpactChart({ data }: Props) {
    const ct = useChartTheme();

    if (!data.length) return <div className="state-empty"><span>Sem dados</span></div>;

    const sorted = [...data].sort((a, b) => b.freight_percent - a.freight_percent);

    return (
        <ResponsiveContainer width="100%" height={280}>
            <BarChart
                layout="vertical"
                data={sorted}
                margin={{ top: 5, right: 60, bottom: 5, left: 80 }}
            >
                <CartesianGrid strokeDasharray="3 3" stroke={ct.grid} horizontal={false} />
                <XAxis
                    type="number"
                    tick={{ fill: ct.tick, fontSize: 11 }}
                    tickLine={false}
                    axisLine={false}
                    tickFormatter={(v) => `${v}%`}
                />
                <YAxis
                    type="category"
                    dataKey="category"
                    tick={{ fill: ct.tick, fontSize: 11 }}
                    axisLine={false}
                    tickLine={false}
                    width={70}
                />
                <Tooltip
                    contentStyle={{ background: ct.tooltipBg, border: `1px solid ${ct.tooltipBorder}`, borderRadius: 8 }}
                    labelStyle={{ color: ct.label, fontSize: 12 }}
                    formatter={(v: number | undefined) => [`${(v ?? 0).toFixed(2)}%`, 'Impacto do frete']}
                />
                <Bar dataKey="freight_percent" radius={[0, 6, 6, 0]}>
                    {sorted.map((row, i) => (
                        <Cell key={i} fill={getColor(row.freight_percent)} />
                    ))}
                    <LabelList
                        dataKey="freight_percent"
                        position="right"
                        formatter={(v: unknown) => `${Number(v).toFixed(1)}%`}
                        style={{ fill: ct.tick, fontSize: 11 }}
                    />
                </Bar>
            </BarChart>
        </ResponsiveContainer>
    );
}
