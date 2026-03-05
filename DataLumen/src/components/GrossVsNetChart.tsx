import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip, Legend, ResponsiveContainer, LabelList,
} from 'recharts';
import { useChartTheme } from '../shared/hooks/useChartTheme';
import type { GrossVsNetRow } from '../shared/services/dashboard';

interface Props {
    data: GrossVsNetRow[];
}

export default function GrossVsNetChart({ data }: Props) {
    const ct = useChartTheme();

    if (!data.length) {
        return <div className="state-empty"><span>Sem dados</span></div>;
    }

    const chartData = data.map((row) => ({
        category: row.category,
        'Custo Bruto': row.gross_cost,
        'Custo Líquido': row.net_cost,
        'Crédito %': row.credit_percent,
    }));

    return (
        <ResponsiveContainer width="100%" height={320}>
            <BarChart data={chartData} margin={{ top: 20, right: 10, bottom: 5, left: 10 }}>
                <CartesianGrid strokeDasharray="3 3" stroke={ct.grid} />
                <XAxis
                    dataKey="category"
                    tick={{ fill: ct.tick, fontSize: 11 }}
                    tickLine={false}
                    axisLine={{ stroke: ct.grid }}
                />
                <YAxis
                    tick={{ fill: ct.tick, fontSize: 11 }}
                    tickLine={false}
                    axisLine={false}
                    tickFormatter={(v) =>
                        v >= 1000 ? `R$${(v / 1000).toFixed(0)}k` : `R$${v}`
                    }
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
                    formatter={(v: any, name: any) => {
                        if (v === undefined) return ['—', name];
                        if (name === 'Crédito %') return [`${v.toFixed(1)}%`, name];
                        return [`R$ ${v.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`, name];
                    }}
                />
                <Legend wrapperStyle={{ fontSize: 12, color: ct.tick, paddingTop: 12 }} />
                <Bar dataKey="Custo Bruto" fill="#3b82f6" radius={[4, 4, 0, 0]} />
                <Bar dataKey="Custo Líquido" fill="#22c55e" radius={[4, 4, 0, 0]}>
                    <LabelList
                        dataKey="Crédito %"
                        position="top"
                        // eslint-disable-next-line @typescript-eslint/no-explicit-any
                        formatter={(v: any) => typeof v === 'number' ? `-${v.toFixed(1)}%` : ''}
                        style={{ fill: ct.tick, fontSize: 10, fontWeight: 600 }}
                    />
                </Bar>
            </BarChart>
        </ResponsiveContainer>
    );
}
