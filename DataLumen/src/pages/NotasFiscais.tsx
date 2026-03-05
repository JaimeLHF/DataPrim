import { useState, useEffect, useCallback } from 'react';
import {
    fetchInvoices, fetchInvoice, deleteInvoice,
    type InvoiceRow, type InvoiceDetail, type PageMeta,
} from '../shared/services/dashboard';
import { useToast } from '../shared/contexts/ToastContext';

const CATEGORIES = ['Todas', 'MDF', 'Ferragens', 'Químicos', 'Aramados', 'Embalagens', 'Acessórios'];

const fmt = (n: number) => n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

export default function NotasFiscais() {
    const { error: toastError, success: toastSuccess } = useToast();
    const [invoices, setInvoices] = useState<InvoiceRow[]>([]);
    const [meta, setMeta] = useState<PageMeta | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    // Filters
    const [search, setSearch] = useState('');
    const [category, setCategory] = useState('Todas');
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const [page, setPage] = useState(1);

    // Detail modal
    const [detail, setDetail] = useState<InvoiceDetail | null>(null);
    const [detailLoading, setDetailLoading] = useState(false);

    // Delete confirmation
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null);

    const load = useCallback(async () => {
        setLoading(true);
        setError('');
        try {
            const params: Record<string, string> = { page: String(page), per_page: '12' };
            if (search) params.search = search;
            if (category !== 'Todas') params.category = category;
            if (startDate) params.start_date = startDate;
            if (endDate) params.end_date = endDate;

            const res = await fetchInvoices(params);
            setInvoices(res.data);
            setMeta(res.meta);
        } catch (e: unknown) {
            setError(e instanceof Error ? e.message : 'Erro ao carregar notas');
        } finally {
            setLoading(false);
        }
    }, [search, category, startDate, endDate, page]);

    useEffect(() => { load(); }, [load]);

    const openDetail = async (id: number) => {
        setDetailLoading(true);
        try {
            setDetail(await fetchInvoice(id));
        } finally {
            setDetailLoading(false);
        }
    };

    const handleDelete = async (id: number) => {
        try {
            await deleteInvoice(id);
            setConfirmDelete(null);
            toastSuccess('Nota fiscal excluída com sucesso.');
            load();
        } catch {
            toastError('Erro ao excluir nota', 'Verifique sua conexão e tente novamente.');
        }
    };

    return (
        <div>
            <div className="page-header">
                <h1>Notas Fiscais</h1>
                <p>Histórico de NF-e importadas — pesquise, filtre e gerencie</p>
            </div>

            {/* Filters */}
            <div className="filter-bar" style={{ flexWrap: 'wrap', gap: 8 }}>
                <input
                    className="filter-select"
                    style={{ minWidth: 180 }}
                    placeholder="🔍 Buscar nota ou fornecedor…"
                    value={search}
                    onChange={(e) => { setSearch(e.target.value); setPage(1); }}
                />
                <select className="filter-select" value={category} onChange={(e) => { setCategory(e.target.value); setPage(1); }}>
                    {CATEGORIES.map(c => <option key={c}>{c}</option>)}
                </select>
                <input type="date" className="filter-select" value={startDate} onChange={(e) => { setStartDate(e.target.value); setPage(1); }} />
                <input type="date" className="filter-select" value={endDate} onChange={(e) => { setEndDate(e.target.value); setPage(1); }} />
                <button className="btn btn-outline" onClick={() => { setSearch(''); setCategory('Todas'); setStartDate(''); setEndDate(''); setPage(1); }}>
                    ✕ Limpar
                </button>
                <span style={{ marginLeft: 'auto', color: 'var(--color-text-muted)', fontSize: 13 }}>
                    {meta ? `${meta.total} notas encontradas` : ''}
                </span>
            </div>

            {error && (
                <div style={{ background: 'var(--color-danger-dim)', border: '1px solid var(--color-danger)', borderRadius: 10, padding: '16px 20px', marginBottom: 24, color: 'var(--color-danger)', fontSize: 13 }}>
                    ⚠️ {error}
                </div>
            )}

            {loading ? (
                <div className="state-loading"><div className="spinner" /><span>Carregando notas…</span></div>
            ) : (
                <>
                    <div className="card" style={{ padding: 0, overflow: 'hidden' }}>
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Nota Fiscal</th>
                                    <th>Data</th>
                                    <th>Fornecedor</th>
                                    <th>Categorias</th>
                                    <th>Itens</th>
                                    <th>Frete</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {invoices.length === 0 ? (
                                    <tr><td colSpan={8} style={{ textAlign: 'center', color: 'var(--color-text-muted)', padding: 32 }}>Nenhuma nota encontrada</td></tr>
                                ) : invoices.map((inv) => (
                                    <tr key={inv.id}>
                                        <td><strong style={{ color: 'var(--color-accent)' }}>{inv.invoice_number}</strong></td>
                                        <td style={{ whiteSpace: 'nowrap' }}>{inv.issue_date}</td>
                                        <td>{inv.supplier}</td>
                                        <td>
                                            <div style={{ display: 'flex', gap: 4, flexWrap: 'wrap' }}>
                                                {inv.categories.slice(0, 2).map(c => (
                                                    <span key={c} className="badge badge-primary" style={{ fontSize: 10 }}>{c}</span>
                                                ))}
                                                {inv.categories.length > 2 && <span className="badge" style={{ fontSize: 10 }}>+{inv.categories.length - 2}</span>}
                                            </div>
                                        </td>
                                        <td style={{ textAlign: 'center' }}>{inv.items_count}</td>
                                        <td style={{ color: inv.freight_value > 200 ? 'var(--color-warning)' : 'inherit' }}>{fmt(inv.freight_value)}</td>
                                        <td style={{ fontWeight: 600 }}>{fmt(inv.total_value)}</td>
                                        <td>
                                            <div style={{ display: 'flex', gap: 8 }}>
                                                <button className="btn btn-outline" style={{ padding: '4px 10px', fontSize: 12 }}
                                                    onClick={() => openDetail(inv.id)}>
                                                    Ver
                                                </button>
                                                <button style={{ background: 'var(--color-danger-dim)', border: '1px solid var(--color-danger)', borderRadius: 6, padding: '4px 10px', fontSize: 12, color: 'var(--color-danger)', cursor: 'pointer' }}
                                                    onClick={() => setConfirmDelete(inv.id)}>
                                                    Excluir
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {meta && meta.last_page > 1 && (
                        <div style={{ display: 'flex', justifyContent: 'center', gap: 8, marginTop: 20 }}>
                            <button className="btn btn-outline" disabled={page <= 1} onClick={() => setPage(p => p - 1)}>← Anterior</button>
                            <span style={{ padding: '8px 16px', fontSize: 13, color: 'var(--color-text-muted)' }}>
                                Página {page} de {meta.last_page}
                            </span>
                            <button className="btn btn-outline" disabled={page >= meta.last_page} onClick={() => setPage(p => p + 1)}>Próxima →</button>
                        </div>
                    )}
                </>
            )}

            {/* Detail Modal */}
            {(detail || detailLoading) && (
                <div style={{ position: 'fixed', inset: 0, background: 'var(--color-overlay)', zIndex: 100, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 24 }}
                    onClick={() => setDetail(null)}>
                    <div style={{ background: 'var(--color-surface)', borderRadius: 16, padding: 28, maxWidth: 720, width: '100%', maxHeight: '80vh', overflow: 'auto', boxShadow: 'var(--shadow-lg)' }}
                        onClick={e => e.stopPropagation()}>
                        {detailLoading ? <div className="state-loading"><div className="spinner" /></div> : detail && (
                            <>
                                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 20 }}>
                                    <div>
                                        <h2 style={{ margin: 0 }}>NF-e {detail.invoice_number}</h2>
                                        <div style={{ color: 'var(--color-text-muted)', fontSize: 13, marginTop: 4 }}>
                                            {detail.issue_date} · {detail.supplier}
                                        </div>
                                    </div>
                                    <button onClick={() => setDetail(null)} style={{ background: 'none', border: 'none', color: 'var(--color-text-muted)', fontSize: 20, cursor: 'pointer' }}>✕</button>
                                </div>

                                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 12, marginBottom: 20 }}>
                                    {[
                                        { label: 'Total', value: fmt(detail.total_value) },
                                        { label: 'Frete', value: fmt(detail.freight_value) },
                                        { label: 'Impostos', value: fmt(detail.tax_value) },
                                    ].map(item => (
                                        <div key={item.label} style={{ background: 'var(--color-bg)', borderRadius: 10, padding: '12px 16px' }}>
                                            <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginBottom: 4 }}>{item.label}</div>
                                            <div style={{ fontWeight: 700, fontSize: 16 }}>{item.value}</div>
                                        </div>
                                    ))}
                                </div>

                                <table className="data-table">
                                    <thead><tr><th>Produto</th><th>Categoria</th><th>Qtd</th><th>Preço Unit.</th><th>Total</th></tr></thead>
                                    <tbody>
                                        {detail.items.map((item, i) => (
                                            <tr key={i}>
                                                <td>{item.product_description}</td>
                                                <td><span className="badge badge-primary">{item.category}</span></td>
                                                <td>{item.quantity}</td>
                                                <td>{fmt(item.unit_price)}</td>
                                                <td style={{ fontWeight: 600 }}>{fmt(item.total_price)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </>
                        )}
                    </div>
                </div>
            )}

            {/* Delete Confirmation */}
            {confirmDelete && (
                <div style={{ position: 'fixed', inset: 0, background: 'var(--color-overlay)', zIndex: 100, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <div style={{ background: 'var(--color-surface)', borderRadius: 16, padding: 32, maxWidth: 400, width: '100%', textAlign: 'center', boxShadow: 'var(--shadow-lg)' }}>
                        <div style={{ fontSize: 40, marginBottom: 12 }}>🗑️</div>
                        <h3 style={{ margin: '0 0 8px' }}>Excluir nota fiscal?</h3>
                        <p style={{ color: 'var(--color-text-muted)', marginBottom: 24, fontSize: 14 }}>Esta ação é irreversível. Todos os itens associados também serão removidos.</p>
                        <div style={{ display: 'flex', gap: 12, justifyContent: 'center' }}>
                            <button className="btn btn-outline" onClick={() => setConfirmDelete(null)}>Cancelar</button>
                            <button style={{ background: 'var(--color-danger)', border: 'none', borderRadius: 8, padding: '10px 20px', color: '#fff', fontWeight: 600, cursor: 'pointer' }}
                                onClick={() => handleDelete(confirmDelete)}>
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
