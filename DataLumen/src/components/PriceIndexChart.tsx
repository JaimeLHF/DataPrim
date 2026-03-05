import {
    LineChart, Line, XAxis, YAxis, CartesianGrid,
    Tooltip, Legend, ResponsiveContainer, ReferenceLine,
} from 'recharts';
import { useChartTheme } from '../shared/hooks/useChartTheme';

const COLORS = ['#3b82f6', '#8b5cf6', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444'];

interface Props {
    data: Record<string, string | number>[];
    categories: string[];
}

export default function PriceIndexChart({ data, categories }: Props) {
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
                    tickFormatter={(v) => `${v}%`}
                    domain={['auto', 'auto']}
                />
                <ReferenceLine
                    y={100}
                    stroke={ct.tick}
                    strokeDasharray="4 4"
                    strokeOpacity={0.6}
                    label={{
                        value: 'Base 100',
                        position: 'right',
                        fill: ct.tick,
                        fontSize: 10,
                    }}
                />
                <Tooltip
                    contentStyle={{
                        background: ct.tooltipBg,
                        border: `1px solid ${ct.tooltipBorder}`,
                        borderRadius: 8,
                    }}
                    labelStyle={{ color: ct.label, fontSize: 12 }}
                    itemStyle={{ fontSize: 12 }}
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    formatter={(v: any) => {
                        if (v === undefined) return ['—', ''];
                        const diff = v - 100;
                        const sign = diff >= 0 ? '+' : '';
                        return [`${v.toFixed(1)} (${sign}${diff.toFixed(1)}%)`, ''];
                    }}
                />
                <Legend wrapperStyle={{ fontSize: 12, color: ct.tick, paddingTop: 12 }} />
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
            </LineChart>
        </ResponsiveContainer>
    );
}
