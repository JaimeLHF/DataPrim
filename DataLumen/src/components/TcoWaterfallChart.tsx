import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip, Legend, ResponsiveContainer,
} from 'recharts';
import { useChartTheme } from '../shared/hooks/useChartTheme';
import type { TcoBreakdownRow } from '../shared/services/dashboard';

interface Props {
    company: TcoBreakdownRow[];
    market: TcoBreakdownRow[];
}

const COLORS = {
    unitPrice: '#3b82f6',
    freight: '#f59e0b',
    tax: '#ef4444',
    market: '#9ca3af',
};

export default function TcoWaterfallChart({ company, market }: Props) {
    const ct = useChartTheme();

    if (!company.length) {
        return <div className="state-empty"><span>Sem dados</span></div>;
    }

    // Build data: for each category, two grouped bars (Empresa vs Mercado)
    const data = company.map((c, i) => {
        const m = market[i];
        return {
            category: c.category,
            'Preço Unit.': c.avg_unit_price,
            'Frete': c.avg_freight,
            'Impostos': c.avg_tax,
            'Mercado TCO': m?.tco_total ?? 0,
        };
    });

    return (
        <ResponsiveContainer width="100%" height={320}>
            <BarChart data={data} margin={{ top: 5, right: 10, bottom: 5, left: 10 }}>
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
                    tickFormatter={(v) => `R$${v}`}
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
                    formatter={(v: any) => [v !== undefined ? `R$ ${v.toFixed(2)}` : '—', '']}
                />
                <Legend wrapperStyle={{ fontSize: 12, color: ct.tick, paddingTop: 12 }} />
                <Bar dataKey="Preço Unit." stackId="empresa" fill={COLORS.unitPrice} radius={[0, 0, 0, 0]} />
                <Bar dataKey="Frete" stackId="empresa" fill={COLORS.freight} />
                <Bar dataKey="Impostos" stackId="empresa" fill={COLORS.tax} radius={[4, 4, 0, 0]} />
                <Bar dataKey="Mercado TCO" fill={COLORS.market} radius={[4, 4, 0, 0]} />
            </BarChart>
        </ResponsiveContainer>
    );
}
