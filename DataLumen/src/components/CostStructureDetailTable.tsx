import { useState } from 'react';
import type { BenchmarkCategoryData, BenchmarkSummary } from '../shared/services/dashboard';
import CategoryProductsModal from './CategoryProductsModal';

interface Props {
    categories: BenchmarkCategoryData[];
    summary: BenchmarkSummary;
    period: string;
}


const fmtPct = (n: number | null) =>
    n !== null ? `${n > 0 ? '+' : ''}${n.toFixed(1)}%` : '—';

const statusBadge = (status: string) => {
    switch (status) {
        case 'above_market':
            return <span className="badge badge-danger">Acima</span>;
        case 'below_market':
            return <span className="badge badge-success">Abaixo</span>;
        case 'aligned':
            return <span className="badge badge-primary">Alinhado</span>;
        default:
            return <span className="badge">Sem dados</span>;
    }
};

export default function CostStructureDetailTable({ categories, summary, period }: Props) {
    const [selectedCategory, setSelectedCategory] = useState<{ slug: string; name: string } | null>(null);

    if (!categories.length) {
        return <div className="state-empty"><span>Sem dados para exibir</span></div>;
    }

    return (
        <div>
            {/* Summary Cards */}
            <div className="kpi-grid" style={{ marginBottom: 20 }}>
                <div className="card" style={{ textAlign: 'center', padding: '16px 12px' }}>
                    <div style={{ fontSize: 22, fontWeight: 700, color: 'var(--color-danger)' }}>
                        {summary.categories_above_market}
                    </div>
                    <div style={{ fontSize: 12, color: 'var(--color-text-2)', marginTop: 4 }}>
                        Categorias Acima do Mercado
                    </div>
                </div>
                <div className="card" style={{ textAlign: 'center', padding: '16px 12px' }}>
                    <div style={{ fontSize: 22, fontWeight: 700, color: 'var(--color-success)' }}>
                        {summary.categories_below_market}
                    </div>
                    <div style={{ fontSize: 12, color: 'var(--color-text-2)', marginTop: 4 }}>
                        Categorias Abaixo do Mercado
                    </div>
                </div>
                <div className="card" style={{ textAlign: 'center', padding: '16px 12px' }}>
                    <div style={{ fontSize: 22, fontWeight: 700, color: 'var(--color-primary)' }}>
                        {summary.categories_aligned}
                    </div>
                    <div style={{ fontSize: 12, color: 'var(--color-text-2)', marginTop: 4 }}>
                        Categorias Alinhadas
                    </div>
                </div>
            </div>

            {/* Worst category highlight */}
            {summary.worst_category && (
                <div
                    style={{
                        background: 'var(--color-danger-dim)',
                        border: '1px solid var(--color-danger)',
                        borderRadius: 10,
                        padding: '14px 20px',
                        marginBottom: 20,
                        fontSize: 13,
                        color: 'var(--color-danger)',
                    }}
                >
                    ⚠️ <strong>{summary.worst_category.name}</strong> é a categoria com maior desvio:
                    Δ {fmtPct(summary.worst_category.delta)} acima do mercado
                </div>
            )}

            {/* Detail Table */}
            <table className="data-table">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th style={{ textAlign: 'right' }}>Empresa (%)</th>
                        <th style={{ textAlign: 'right' }}>Benchmark (%)</th>
                        <th style={{ textAlign: 'right' }}>Δ</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    {categories.map((row) => (
                        <tr
                            key={row.category_slug}
                            onClick={() => setSelectedCategory({ slug: row.category_slug, name: row.category_name })}
                            style={{ cursor: 'pointer' }}
                            title={`Clique para ver os produtos de ${row.category_name}`}
                        >
                            <td>
                                <strong style={{ color: 'var(--color-primary)', borderBottom: '1px dashed var(--color-primary)' }}>
                                    {row.category_name}
                                </strong>
                                <span style={{ marginLeft: 6, fontSize: 10, color: 'var(--color-text-muted)' }}>🔍</span>
                            </td>
                            <td style={{ textAlign: 'right' }}>{row.company_percentage.toFixed(1)}%</td>
                            <td style={{ textAlign: 'right' }}>
                                {row.benchmark_percentage !== null
                                    ? `${row.benchmark_percentage.toFixed(1)}%`
                                    : '—'}
                            </td>
                            <td
                                style={{
                                    textAlign: 'right',
                                    fontWeight: 600,
                                    color:
                                        row.delta_status === 'above_market'
                                            ? 'var(--color-danger)'
                                            : row.delta_status === 'below_market'
                                                ? 'var(--color-success)'
                                                : 'var(--color-text-2)',
                                }}
                            >
                                {fmtPct(row.delta_percentage)}
                            </td>
                            <td>{statusBadge(row.delta_status)}</td>
                        </tr>
                    ))}
                </tbody>
                <tfoot>
                    <tr style={{ fontWeight: 700 }}>
                        <td>Total</td>
                        <td style={{ textAlign: 'right' }}>100%</td>
                        <td style={{ textAlign: 'right' }}>100%</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            {/* Modal de produtos da categoria */}
            {selectedCategory && (
                <CategoryProductsModal
                    categorySlug={selectedCategory.slug}
                    categoryName={selectedCategory.name}
                    period={period}
                    onClose={() => setSelectedCategory(null)}
                />
            )}
        </div>
    );
}
