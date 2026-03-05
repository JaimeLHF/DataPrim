import type { BenchmarkCategoryData, BenchmarkSummary, BenchmarkMeta } from '../shared/services/dashboard';

interface Props {
    categories: BenchmarkCategoryData[];
    summary: BenchmarkSummary;
    meta: BenchmarkMeta;
}

interface Insight {
    icon: string;
    severity: 'danger' | 'warning' | 'success' | 'info';
    title: string;
    description: string;
}

const fmtR$ = (v: number) =>
    v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 });

const fmtPct = (v: number) => `${Math.abs(v).toFixed(1)}%`;

const SEVERITY_STYLES: Record<string, { bg: string; border: string; color: string }> = {
    danger:  { bg: 'rgba(239,68,68,0.08)',  border: '#ef4444', color: '#fca5a5' },
    warning: { bg: 'rgba(245,158,11,0.08)', border: '#f59e0b', color: '#fcd34d' },
    success: { bg: 'rgba(34,197,94,0.08)',  border: '#22c55e', color: '#86efac' },
    info:    { bg: 'rgba(59,130,246,0.08)', border: '#3b82f6', color: '#93c5fd' },
};

function generateInsights(categories: BenchmarkCategoryData[], summary: BenchmarkSummary, meta: BenchmarkMeta): Insight[] {
    const insights: Insight[] = [];

    // 1. Worst category — biggest delta above market
    if (summary.worst_category && summary.worst_category.delta > 5) {
        insights.push({
            icon: '🚨',
            severity: 'danger',
            title: `${summary.worst_category.name} está ${fmtPct(summary.worst_category.delta)} acima do mercado`,
            description: `Essa categoria representa a maior oportunidade de redução. O potencial de economia é de ${fmtR$(summary.worst_category.impact)} se alinhada ao benchmark.`,
        });
    }

    // 2. Total potential saving
    if (summary.total_potential_saving > 0) {
        insights.push({
            icon: '💰',
            severity: 'warning',
            title: `Potencial de economia total: ${fmtR$(summary.total_potential_saving)}`,
            description: `Existem ${summary.categories_above_market} categoria(s) acima da média de mercado. Renegociar esses contratos pode gerar economia significativa.`,
        });
    }

    // 3. Categories significantly below market — possible over-optimization or quality risk
    const farBelowMarket = categories.filter(
        (c) => c.delta_percentage != null && c.delta_percentage < -10
    );
    if (farBelowMarket.length > 0) {
        const names = farBelowMarket.map((c) => c.category_name).join(', ');
        insights.push({
            icon: '⚠️',
            severity: 'warning',
            title: `${farBelowMarket.length} categoria(s) muito abaixo do mercado`,
            description: `${names} — gastos muito abaixo da média podem indicar subfornecimento ou risco de qualidade. Avalie se o volume está adequado.`,
        });
    }

    // 4. Concentration risk — one category > 50% of total spend
    const concentrated = categories.find((c) => c.company_percentage > 50);
    if (concentrated) {
        insights.push({
            icon: '📊',
            severity: 'warning',
            title: `Concentração elevada: ${concentrated.category_name} representa ${fmtPct(concentrated.company_percentage)} dos gastos`,
            description: `Alta dependência de uma única categoria aumenta o risco operacional. Considere diversificar fornecedores ou renegociar condições.`,
        });
    }

    // 5. Freight above market
    const freightCat = categories.find((c) => c.category_slug === 'frete');
    if (freightCat && freightCat.delta_percentage != null && freightCat.delta_percentage > 2) {
        insights.push({
            icon: '🚚',
            severity: 'danger',
            title: `Frete ${fmtPct(freightCat.delta_percentage)} acima do benchmark`,
            description: `Custos logísticos elevados impactam diretamente a margem. Avalie consolidação de cargas, rotas alternativas ou renegociação com transportadoras.`,
        });
    }

    // 6. Well-aligned categories — positive reinforcement
    if (summary.categories_aligned > 0) {
        insights.push({
            icon: '✅',
            severity: 'success',
            title: `${summary.categories_aligned} categoria(s) alinhada(s) ao mercado`,
            description: `Bom posicionamento competitivo nessas categorias. Continue monitorando para manter o alinhamento.`,
        });
    }

    // 7. Categories below market — competitive advantage
    if (summary.categories_below_market > 0) {
        const belowNames = categories
            .filter((c) => c.delta_status === 'below_market')
            .map((c) => c.category_name)
            .slice(0, 3)
            .join(', ');
        insights.push({
            icon: '🏆',
            severity: 'success',
            title: `${summary.categories_below_market} categoria(s) abaixo do mercado`,
            description: `${belowNames} — vantagem competitiva nos custos. Boas negociações ou fornecedores eficientes.`,
        });
    }

    // 8. Sample size warning
    if (meta.benchmark_sample_size < 5) {
        insights.push({
            icon: 'ℹ️',
            severity: 'info',
            title: `Amostra reduzida: apenas ${meta.benchmark_sample_size} empresas`,
            description: `O benchmark é mais confiável com amostras maiores. Os percentuais podem variar à medida que mais empresas participarem.`,
        });
    }

    return insights;
}

export default function BenchmarkInsights({ categories, summary, meta }: Props) {
    const insights = generateInsights(categories, summary, meta);

    if (!insights.length) return null;

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            {insights.map((insight, i) => {
                const s = SEVERITY_STYLES[insight.severity];
                return (
                    <div
                        key={i}
                        style={{
                            display: 'flex',
                            alignItems: 'flex-start',
                            gap: 12,
                            background: s.bg,
                            border: `1px solid ${s.border}`,
                            borderRadius: 10,
                            padding: '12px 16px',
                            fontSize: 13,
                            lineHeight: 1.5,
                        }}
                    >
                        <span style={{ fontSize: 18, flexShrink: 0, marginTop: 1 }}>{insight.icon}</span>
                        <div>
                            <div style={{ fontWeight: 700, color: s.color, marginBottom: 2 }}>
                                {insight.title}
                            </div>
                            <div style={{ color: 'var(--color-text-2, #94a3b8)', fontSize: 12 }}>
                                {insight.description}
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}
