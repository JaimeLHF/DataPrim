import { useState, useRef, useEffect, useCallback } from 'react';
import {
    importXml,
    previewXml,
    checkIngestionStatus,
    type AsyncImportResponse,
    type IngestionStatus,
    type PreviewResult,
} from '../shared/services/dashboard';

export default function Import() {
    const [file, setFile] = useState<File | null>(null);
    const [previewing, setPreviewing] = useState(false);
    const [importing, setImporting] = useState(false);
    const [error, setError] = useState('');
    const [preview, setPreview] = useState<PreviewResult | null>(null);
    const [dragOver, setDragOver] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    // Async ingestion tracking
    const [ingestion, setIngestion] = useState<AsyncImportResponse | null>(null);
    const [ingestionStatus, setIngestionStatus] = useState<IngestionStatus | null>(null);
    const [polling, setPolling] = useState(false);

    const handleFile = (f: File) => {
        setFile(f);
        setError('');
        setPreview(null);
        setIngestion(null);
        setIngestionStatus(null);
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        setDragOver(false);
        const f = e.dataTransfer.files[0];
        if (f) handleFile(f);
    };

    const handlePreview = async () => {
        if (!file) return;
        setPreviewing(true);
        setError('');
        setPreview(null);
        try {
            const res = await previewXml(file);
            setPreview(res);
        } catch (e: unknown) {
            setError(e instanceof Error ? e.message : 'Erro ao visualizar dados');
        } finally {
            setPreviewing(false);
        }
    };

    const handleImport = async () => {
        if (!file) return;
        setImporting(true);
        setError('');
        try {
            const res = await importXml(file);
            setIngestion(res);
            setPreview(null);
            setFile(null);
            setPolling(true);
        } catch (e: unknown) {
            setError(e instanceof Error ? e.message : 'Erro na importação');
        } finally {
            setImporting(false);
        }
    };

    // Polling for ingestion status
    const pollStatus = useCallback(async () => {
        if (!ingestion) return;
        try {
            const status = await checkIngestionStatus(ingestion.ingestion_id);
            setIngestionStatus(status);
            if (status.status === 'done' || status.status === 'failed') {
                setPolling(false);
            }
        } catch {
            setPolling(false);
            setError('Erro ao verificar status do processamento.');
        }
    }, [ingestion]);

    useEffect(() => {
        if (!polling) return;
        pollStatus();
        const interval = setInterval(pollStatus, 2000);
        return () => clearInterval(interval);
    }, [polling, pollStatus]);

    const handleCancel = () => {
        setFile(null);
        setPreview(null);
        setError('');
    };

    const handleReset = () => {
        setFile(null);
        setPreview(null);
        setError('');
        setIngestion(null);
        setIngestionStatus(null);
        setPolling(false);
    };

    const fmt = (n: number) =>
        n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    const isProcessing = ingestion && (!ingestionStatus || ingestionStatus.status === 'pending' || ingestionStatus.status === 'processing');
    const isDone = ingestionStatus?.status === 'done';
    const isFailed = ingestionStatus?.status === 'failed';

    return (
        <div>
            <div className="page-header">
                <h1>Importar NF-e</h1>
                <p>Faça upload de um arquivo XML de NF-e modelo 55 para importar os dados</p>
            </div>

            {/* Upload Zone */}
            {!preview && !ingestion && (
                <div className="card" style={{ marginBottom: 24 }}>
                    <div className="card-title">Arquivo XML</div>
                    <div
                        className={`upload-zone${dragOver ? ' drag-over' : ''}`}
                        onClick={() => inputRef.current?.click()}
                        onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
                        onDragLeave={() => setDragOver(false)}
                        onDrop={handleDrop}
                    >
                        <input
                            ref={inputRef}
                            type="file"
                            accept=".xml,application/xml,text/xml"
                            style={{ display: 'none' }}
                            onChange={(e) => { const f = e.target.files?.[0]; if (f) handleFile(f); }}
                        />
                        <div className="upload-icon">📄</div>
                        {file ? (
                            <>
                                <div className="upload-title">{file.name}</div>
                                <div className="upload-sub">{(file.size / 1024).toFixed(1)} KB — Pronto para visualizar</div>
                            </>
                        ) : (
                            <>
                                <div className="upload-title">Arraste ou clique para selecionar</div>
                                <div className="upload-sub">Apenas arquivos XML NF-e modelo 55 — Max. 10MB</div>
                            </>
                        )}
                    </div>

                    {file && (
                        <div style={{ display: 'flex', gap: 12, marginTop: 16 }}>
                            <button className="btn btn-primary" onClick={handlePreview} disabled={previewing}>
                                {previewing ? 'Carregando...' : 'Visualizar dados'}
                            </button>
                            <button className="btn btn-outline" onClick={handleCancel} disabled={previewing}>
                                Cancelar
                            </button>
                        </div>
                    )}
                </div>
            )}

            {/* Error */}
            {error && (
                <div style={{ background: 'var(--color-danger-dim)', border: '1px solid var(--color-danger)', borderRadius: 10, padding: '16px 20px', marginBottom: 24, color: 'var(--color-danger)', fontSize: 13 }}>
                    {error}
                </div>
            )}

            {/* Preview */}
            {preview && !ingestion && (
                <>
                    <div className="card" style={{ marginBottom: 20, borderColor: 'var(--color-warning)' }}>
                        <div className="card-title">Dados Extraidos</div>
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: 16, marginTop: 4 }}>
                            {[
                                { label: 'Numero da Nota', value: preview.invoice_number },
                                { label: 'Data de Emissao', value: preview.issue_date },
                                { label: 'Fornecedor', value: preview.supplier },
                                { label: 'CNPJ', value: preview.supplier_cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5') },
                                { label: 'Estado / Regiao', value: `${preview.supplier_state} — ${preview.supplier_region}` },
                                { label: 'Valor Total', value: fmt(preview.total_value) },
                                { label: 'Frete', value: fmt(preview.freight_value) },
                                { label: 'Impostos', value: fmt(preview.tax_value) },
                                { label: 'Total de Itens', value: preview.items_count.toString() },
                            ].map((item) => (
                                <div key={item.label}>
                                    <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginBottom: 2 }}>{item.label}</div>
                                    <div style={{ fontWeight: 600, fontSize: 14 }}>{item.value}</div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Preview Items Table */}
                    <div className="card" style={{ marginBottom: 20 }}>
                        <div className="card-title">Itens da Nota ({preview.items_count})</div>
                        <div style={{ overflowX: 'auto' }}>
                            <table className="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Descricao</th>
                                        <th>Categoria</th>
                                        <th>Qtd</th>
                                        <th>Preco Unit.</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {preview.items.map((item, idx) => (
                                        <tr key={idx}>
                                            <td style={{ color: 'var(--color-text-muted)' }}>{idx + 1}</td>
                                            <td>{item.product_description}</td>
                                            <td><span className="badge badge-primary">{item.category}</span></td>
                                            <td>{item.quantity}</td>
                                            <td>{fmt(item.unit_price)}</td>
                                            <td style={{ fontWeight: 600 }}>{fmt(item.total_price)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Action Buttons */}
                    <div style={{ display: 'flex', gap: 12 }}>
                        <button
                            className="btn btn-primary"
                            style={{ background: 'var(--color-success)' }}
                            onClick={handleImport}
                            disabled={importing}
                        >
                            {importing ? 'Enviando...' : 'Importar Dados'}
                        </button>
                        <button className="btn btn-outline" onClick={handleCancel} disabled={importing}>
                            Cancelar
                        </button>
                    </div>
                </>
            )}

            {/* Processing State */}
            {isProcessing && (
                <div className="card" style={{ marginBottom: 20, textAlign: 'center', padding: 48 }}>
                    <div style={{ fontSize: 40, marginBottom: 16 }}>⏳</div>
                    <div style={{ fontSize: 18, fontWeight: 600, marginBottom: 8 }}>Processando NF-e...</div>
                    <div style={{ color: 'var(--color-text-muted)', fontSize: 13, marginBottom: 16 }}>
                        O arquivo foi recebido e esta sendo processado em segundo plano.
                    </div>
                    <span className="badge badge-primary">
                        Status: {ingestionStatus?.status ?? 'pending'}
                    </span>
                </div>
            )}

            {/* Done State */}
            {isDone && (
                <>
                    <div className="card" style={{ marginBottom: 20, borderColor: 'var(--color-success)' }}>
                        <div className="card-title">Importacao Realizada</div>
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: 16, marginTop: 4 }}>
                            {[
                                { label: 'ID da Ingestao', value: `#${ingestion?.ingestion_id}` },
                                { label: 'Status', value: 'Processado com sucesso' },
                                { label: 'Processado em', value: ingestionStatus?.processed_at ? new Date(ingestionStatus.processed_at).toLocaleString('pt-BR') : '-' },
                            ].map((item) => (
                                <div key={item.label}>
                                    <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginBottom: 2 }}>{item.label}</div>
                                    <div style={{ fontWeight: 600, fontSize: 14 }}>{item.value}</div>
                                </div>
                            ))}
                        </div>
                    </div>
                    <button className="btn btn-primary" onClick={handleReset}>
                        Importar outra NF-e
                    </button>
                </>
            )}

            {/* Failed State */}
            {isFailed && (
                <>
                    <div className="card" style={{ marginBottom: 20, borderColor: 'var(--color-danger)' }}>
                        <div className="card-title" style={{ color: 'var(--color-danger)' }}>Erro no Processamento</div>
                        <p style={{ color: 'var(--color-text-muted)', fontSize: 13, margin: '8px 0' }}>
                            {ingestionStatus?.error_message ?? 'Erro desconhecido durante o processamento.'}
                        </p>
                        <span className="badge" style={{ background: 'var(--color-danger-dim)', color: 'var(--color-danger)' }}>
                            Tentativas: {ingestionStatus?.attempts}
                        </span>
                    </div>
                    <button className="btn btn-primary" onClick={handleReset}>
                        Tentar novamente
                    </button>
                </>
            )}

            {/* Instructions */}
            {!preview && !ingestion && !error && (
                <div className="card" style={{ marginTop: 24 }}>
                    <div className="card-title">Informacoes</div>
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20, fontSize: 13, color: 'var(--color-text-muted)' }}>
                        <div>
                            <p style={{ fontWeight: 600, color: 'var(--color-text)', marginBottom: 8 }}>O que e importado:</p>
                            <ul style={{ listStyle: 'none', display: 'flex', flexDirection: 'column', gap: 4 }}>
                                {['Numero da nota e serie', 'Data de emissao', 'Dados do fornecedor (emitente)', 'Produtos com qtd, preco unit. e total', 'Valor do frete', 'ICMS + IPI'].map(i => (
                                    <li key={i}>• {i}</li>
                                ))}
                            </ul>
                        </div>
                        <div>
                            <p style={{ fontWeight: 600, color: 'var(--color-text)', marginBottom: 8 }}>Validacoes aplicadas:</p>
                            <ul style={{ listStyle: 'none', display: 'flex', flexDirection: 'column', gap: 4 }}>
                                {['Apenas modelo 55 aceito', 'Duplicidade detectada por hash do arquivo', 'Arquivo deve ser XML valido', 'Tamanho maximo: 10MB'].map(i => (
                                    <li key={i}>• {i}</li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
