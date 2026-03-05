import { useEffect, useState, useCallback } from 'react';
import type { CategoryProductsData, CategoryProduct } from '../shared/services/dashboard';
import { fetchCategoryProducts } from '../shared/services/dashboard';

interface Props {
    categorySlug: string;
    categoryName: string;
    period: string;
    onClose: () => void;
}



const fmtPct = (n: number | null) =>
    n !== null ? `${n.toFixed(1)}%` : '—';

const statusLabel = (status: string) => {
    switch (status) {
        case 'above_market':
            return { text: 'Acima do Mercado', cls: 'badge-danger' };
        case 'below_market':
            return { text: 'Abaixo do Mercado', cls: 'badge-success' };
        case 'aligned':
            return { text: 'Alinhado', cls: 'badge-primary' };
        default:
            return { text: 'Sem dados', cls: '' };
    }
};

export default function CategoryProductsModal({ categorySlug, categoryName, period, onClose }: Props) {
    const [data, setData] = useState<CategoryProductsData | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [sortField, setSortField] = useState<keyof CategoryProduct>('total_price');
    const [sortAsc, setSortAsc] = useState(false);

    useEffect(() => {
        setLoading(true);
        setError(null);
        fetchCategoryProducts({ category: categorySlug, period })
            .then(setData)
            .catch(() => setError('Erro ao carregar produtos da categoria.'))
            .finally(() => setLoading(false));
    }, [categorySlug, period]);

    // Close on Escape key
    useEffect(() => {
        const handleKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onClose();
        };
        window.addEventListener('keydown', handleKey);
        return () => window.removeEventListener('keydown', handleKey);
    }, [onClose]);

    const handleSort = useCallback((field: keyof CategoryProduct) => {
        setSortAsc(prev => sortField === field ? !prev : false);
        setSortField(field);
    }, [sortField]);

    const sorted = data?.products
        ? [...data.products].sort((a, b) => {
            const av = a[sortField];
            const bv = b[sortField];
            if (typeof av === 'number' && typeof bv === 'number') {
                return sortAsc ? av - bv : bv - av;
            }
            return sortAsc
                ? String(av).localeCompare(String(bv))
                : String(bv).localeCompare(String(av));
        })
        : [];

    // Média de preço unitário dos itens da categoria (base para desvio por item)
    const avgUnitPrice = sorted.length > 0
        ? sorted.reduce((s, p) => s + p.unit_price, 0) / sorted.length
        : null;

    const status = data ? statusLabel(data.category.delta_status) : null;

    return (
        <div
            style={{
                position: 'fixed',
                inset: 0,
                zIndex: 9999,
                background: 'var(--color-overlay)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: 20,
            }}
            onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
        >
            <div
                style={{
                    background: 'var(--color-surface)',
                    borderRadius: 'var(--radius-lg)',
                    border: '1px solid var(--color-border)',
                    width: '100%',
                    maxWidth: 1100,
                    maxHeight: '90vh',
                    display: 'flex',
                    flexDirection: 'column',
                    boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
                }}
            >
                {/* Header */}
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        padding: '20px 24px',
                        borderBottom: '1px solid var(--color-border)',
                        flexShrink: 0,
                    }}
                >
                    <div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                            <h2 style={{ fontSize: 18, fontWeight: 700, color: 'var(--color-text)', margin: 0 }}>
                                📦 {categoryName}
                            </h2>
                            {status && <span className={`badge ${status.cls}`}>{status.text}</span>}
                        </div>
                        <span style={{ fontSize: 12, color: 'var(--color-text-muted)' }}>
                            Produtos da categoria — {period}
                        </span>
                    </div>
                    <button
                        onClick={onClose}
                        style={{
                            background: 'var(--color-surface-2)',
                            border: '1px solid var(--color-border)',
                            borderRadius: 8,
                            width: 36,
                            height: 36,
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            cursor: 'pointer',
                            fontSize: 18,
                            color: 'var(--color-text-muted)',
                        }}
                    >
                        ✕
                    </button>
                </div>

                {/* Content */}
                <div style={{ overflowY: 'auto', flex: 1, padding: '20px 24px' }}>
                    {loading && (
                        <div className="state-loading" style={{ padding: 40 }}>
                            <div className="spinner" />
                            <span>Carregando produtos...</span>
                        </div>
                    )}

                    {error && (
                        <div style={{ color: 'var(--color-danger)', textAlign: 'center', padding: 40, fontSize: 14 }}>
                            {error}
                        </div>
                    )}

                    {data && !loading && (
                        <>
                            {/* Summary KPIs */}
                            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: 12, marginBottom: 20 }}>
                                <div className="card" style={{ textAlign: 'center', padding: '14px 10px' }}>
                                    <div style={{ fontSize: 20, fontWeight: 700 }}>
                                        {fmtPct(data.category.company_percentage)}
                                    </div>
                                    <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 2 }}>
                                        % Empresa
                                    </div>
                                </div>
                                <div className="card" style={{ textAlign: 'center', padding: '14px 10px' }}>
                                    <div style={{ fontSize: 20, fontWeight: 700, color: '#64748b' }}>
                                        {fmtPct(data.category.benchmark_percentage)}
                                    </div>
                                    <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 2 }}>
                                        % Benchmark
                                    </div>
                                </div>
                                <div className="card" style={{ textAlign: 'center', padding: '14px 10px' }}>
                                    <div style={{
                                        fontSize: 20,
                                        fontWeight: 700,
                                        color: data.category.delta_status === 'above_market'
                                            ? 'var(--color-danger)'
                                            : data.category.delta_status === 'below_market'
                                                ? 'var(--color-success)'
                                                : 'var(--color-text)',
                                    }}>
                                        {data.category.delta_percentage !== null
                                            ? `${data.category.delta_percentage > 0 ? '+' : ''}${data.category.delta_percentage.toFixed(1)}%`
                                            : '—'}
                                    </div>
                                    <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 2 }}>
                                        Desvio (Δ)
                                    </div>
                                </div>
                                <div className="card" style={{ textAlign: 'center', padding: '14px 10px' }}>
                                    <div style={{ fontSize: 20, fontWeight: 700, color: 'var(--color-text)' }}>
                                        {data.meta.products_count}
                                    </div>
                                    <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 2 }}>
                                        Produtos
                                    </div>
                                </div>
                                <div className="card" style={{ textAlign: 'center', padding: '14px 10px' }}>
                                    <div style={{ fontSize: 20, fontWeight: 700, color: 'var(--color-text)' }}>
                                        {data.meta.unique_suppliers}
                                    </div>
                                    <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 2 }}>
                                        Fornecedores
                                    </div>
                                </div>
                            </div>

                            {/* Products Table */}
                            {sorted.length === 0 ? (
                                <div className="state-empty"><span>Nenhum produto encontrado.</span></div>
                            ) : (
                                <div style={{ overflowX: 'auto' }}>
                                    <table className="data-table">
                                        <thead>
                                            <tr>
                                                <SortTh field="product_description" label="Produto" current={sortField} asc={sortAsc} onSort={handleSort} />
                                                <SortTh field="supplier_name" label="Fornecedor" current={sortField} asc={sortAsc} onSort={handleSort} />
                                                <SortTh field="quantity" label="Qtd" current={sortField} asc={sortAsc} onSort={handleSort} align="right" />
                                                <SortTh field="pct_of_category" label="% Categoria" current={sortField} asc={sortAsc} onSort={handleSort} align="right" />
                                                <SortTh field="pct_of_company" label="% Empresa" current={sortField} asc={sortAsc} onSort={handleSort} align="right" />
                                                <th style={{ textAlign: 'right', whiteSpace: 'nowrap' }}>Desvio (Δ)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {sorted.map((p) => {
                                                const itemDelta = avgUnitPrice && avgUnitPrice > 0
                                                    ? ((p.unit_price - avgUnitPrice) / avgUnitPrice) * 100
                                                    : null;
                                                const deltaColor = itemDelta === null
                                                    ? 'var(--color-text-muted)'
                                                    : itemDelta > 5
                                                        ? 'var(--color-danger)'
                                                        : itemDelta < -5
                                                            ? 'var(--color-success)'
                                                            : 'var(--color-text-muted)';
                                                return (
                                                    <tr key={p.id}>
                                                        <td style={{ maxWidth: 220, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                                            <strong>{p.product_description}</strong>
                                                        </td>
                                                        <td style={{ fontSize: 12, maxWidth: 160, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                                            {p.supplier_name}
                                                        </td>
                                                        <td style={{ textAlign: 'right' }}>{p.quantity.toLocaleString('pt-BR')}</td>
                                                        <td style={{ textAlign: 'right' }}>
                                                            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end', gap: 6 }}>
                                                                <div
                                                                    style={{
                                                                        width: Math.min(p.pct_of_category, 100) * 0.6,
                                                                        height: 6,
                                                                        borderRadius: 3,
                                                                        background: 'var(--color-primary)',
                                                                        opacity: 0.5,
                                                                    }}
                                                                />
                                                                {p.pct_of_category.toFixed(1)}%
                                                            </div>
                                                        </td>
                                                        <td style={{ textAlign: 'right', fontSize: 12, color: 'var(--color-text-muted)' }}>
                                                            {p.pct_of_company.toFixed(2)}%
                                                        </td>
                                                        <td style={{ textAlign: 'right', fontWeight: 600, fontSize: 13, color: deltaColor }}>
                                                            {itemDelta !== null
                                                                ? `${itemDelta > 0 ? '+' : ''}${itemDelta.toFixed(1)}%`
                                                                : '—'}
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                        <tfoot>
                                            <tr style={{ fontWeight: 700 }}>
                                                <td>Total ({sorted.length} itens)</td>
                                                <td></td>
                                                <td style={{ textAlign: 'right' }}>
                                                    {sorted.reduce((s, p) => s + p.quantity, 0).toLocaleString('pt-BR')}
                                                </td>
                                                <td style={{ textAlign: 'right' }}>100%</td>
                                                <td style={{ textAlign: 'right' }}>{fmtPct(data.category.company_percentage)}</td>
                                                <td style={{
                                                    textAlign: 'right', color: data.category.delta_percentage !== null
                                                        ? data.category.delta_percentage > 5
                                                            ? 'var(--color-danger)'
                                                            : data.category.delta_percentage < -5
                                                                ? 'var(--color-success)'
                                                                : 'var(--color-text-muted)'
                                                        : 'var(--color-text-muted)'
                                                }}>
                                                    {data.category.delta_percentage !== null
                                                        ? `${data.category.delta_percentage > 0 ? '+' : ''}${data.category.delta_percentage.toFixed(1)}%`
                                                        : '—'}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}

/* ── Sortable table header ──────────────────────────────────────────── */
function SortTh({
    field,
    label,
    current,
    asc,
    onSort,
    align = 'left',
}: {
    field: keyof CategoryProduct;
    label: string;
    current: keyof CategoryProduct;
    asc: boolean;
    onSort: (f: keyof CategoryProduct) => void;
    align?: 'left' | 'right';
}) {
    const active = current === field;
    return (
        <th
            onClick={() => onSort(field)}
            style={{
                textAlign: align,
                cursor: 'pointer',
                userSelect: 'none',
                whiteSpace: 'nowrap',
            }}
        >
            {label} {active ? (asc ? '▲' : '▼') : ''}
        </th>
    );
}
