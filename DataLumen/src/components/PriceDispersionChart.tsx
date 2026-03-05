import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip, Legend, ResponsiveContainer,
} from 'recharts';
import type { DispersionRow } from '../shared/services/dashboard';
import { useChartTheme } from '../shared/hooks/useChartTheme';

interface Props { data: DispersionRow[] }

export default function PriceDispersionChart({ data }: Props) {
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
                    tickFormatter={(v) => `R$${v}`}
                />
                <Tooltip
                    contentStyle={{ background: ct.tooltipBg, border: `1px solid ${ct.tooltipBorder}`, borderRadius: 8 }}
                    labelStyle={{ color: ct.label, fontSize: 12 }}
                    formatter={(v: number | undefined, name?: string) => [`R$ ${(v ?? 0).toFixed(2)}`, name ?? '']}
                />
                <Legend wrapperStyle={{ fontSize: 11, color: ct.tick, paddingTop: 10 }} />
                <Bar dataKey="min_price" name="Mínimo" fill="#22c55e" radius={[4, 4, 0, 0]} />
                <Bar dataKey="avg_price" name="Média" fill="#3b82f6" radius={[4, 4, 0, 0]} />
                <Bar dataKey="max_price" name="Máximo" fill="#ef4444" radius={[4, 4, 0, 0]} />
            </BarChart>
        </ResponsiveContainer>
    );
}
