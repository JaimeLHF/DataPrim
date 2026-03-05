import { useState } from 'react';

type Tab = 'api-push' | 'webhook' | 'xml';

const CodeBlock = ({ code }: { code: string }) => (
    <pre style={{
        background: 'var(--color-bg-secondary)',
        border: '1px solid var(--color-border)',
        borderRadius: 10,
        padding: '16px 20px',
        fontSize: 12,
        lineHeight: 1.8,
        overflowX: 'auto',
        whiteSpace: 'pre',
        color: 'var(--color-text)',
        margin: '12px 0',
    }}><code>{code}</code></pre>
);

const Badge = ({ children, color }: { children: string; color: 'green' | 'yellow' | 'gray' }) => {
    const colors = {
        green: { background: 'var(--color-success-dim)', color: 'var(--color-success)', border: '1px solid var(--color-success)' },
        yellow: { background: 'rgba(251,191,36,0.12)', color: '#f59e0b', border: '1px solid #f59e0b' },
        gray: { background: 'var(--color-bg-secondary)', color: 'var(--color-text-muted)', border: '1px solid var(--color-border)' },
    };
    return (
        <span style={{
            ...colors[color],
            borderRadius: 20,
            padding: '2px 10px',
            fontSize: 11,
            fontWeight: 600,
        }}>{children}</span>
    );
};

const StepBadge = ({ n }: { n: number }) => (
    <span style={{
        background: 'var(--color-primary)',
        color: '#fff',
        width: 28,
        height: 28,
        borderRadius: '50%',
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontWeight: 700,
        fontSize: 13,
        flexShrink: 0,
    }}>{n}</span>
);

// ─── API PUSH ────────────────────────────────────────────────────────────────
function TabApiPush() {
    const [copied, setCopied] = useState<string | null>(null);

    const copy = (key: string, text: string) => {
        navigator.clipboard.writeText(text);
        setCopied(key);
        setTimeout(() => setCopied(null), 2000);
    };

    const CopyBtn = ({ id, text }: { id: string; text: string }) => (
        <button
            onClick={() => copy(id, text)}
            style={{
                background: copied === id ? 'var(--color-success)' : 'var(--color-primary)',
                color: '#fff',
                border: 'none',
                borderRadius: 6,
                padding: '4px 12px',
                fontSize: 11,
                cursor: 'pointer',
                marginLeft: 8,
            }}
        >{copied === id ? '✓ Copiado' : 'Copiar'}</button>
    );

    const curlExample = `curl -X POST https://API_URL/api/v1/invoices/import-json \\
  -H "Authorization: Bearer SUA_API_KEY" \\
  -H "Content-Type: application/json" \\
  -H "X-Company-Id: SEU_COMPANY_ID" \\
  -d '{
    "source": "nome_do_seu_erp",
    "invoices": [{
      "invoice_number": "NF-001-2026",
      "issue_date": "2026-03-01",
      "delivery_date": "2026-03-05",
      "payment_terms": 30,
      "supplier": {
        "cnpj": "12345678000190",
        "name": "Fornecedor Exemplo Ltda",
        "state": "SP"
      },
      "items": [{
        "description": "MDF 18mm Branco",
        "quantity": 100,
        "unit_price": 45.50,
        "total_price": 4550.00,
        "category": "MDF"
      }],
      "totals": {
        "goods": 4550.00,
        "freight": 200.00,
        "tax": 386.75,
        "total": 5136.75
      }
    }]
  }'`;

    const successResponse = `{
  "message": "1 nota(s) recebida(s). Processamento iniciado.",
  "ingestions": [{
    "invoice_number": "NF-001-2026",
    "ingestion_id": 42,
    "status": "pending",
    "skipped": false
  }]
}`;

    const statusResponse = `{
  "id": 42,
  "status": "done",
  "processed_at": "2026-03-01T14:00:03Z",
  "error_message": null
}`;

    const fields = [
        { field: 'source', type: 'string', req: true, desc: 'Identificador do sistema de origem (ex: "oracle_erp")' },
        { field: 'invoices[].invoice_number', type: 'string', req: true, desc: 'Número da nota fiscal' },
        { field: 'invoices[].issue_date', type: 'YYYY-MM-DD', req: true, desc: 'Data de emissão' },
        { field: 'invoices[].supplier.cnpj', type: 'string', req: true, desc: 'CNPJ do fornecedor (só dígitos)' },
        { field: 'invoices[].supplier.name', type: 'string', req: true, desc: 'Nome do fornecedor' },
        { field: 'invoices[].items[].description', type: 'string', req: true, desc: 'Descrição do produto' },
        { field: 'invoices[].items[].quantity', type: 'number', req: true, desc: 'Quantidade' },
        { field: 'invoices[].items[].unit_price', type: 'number', req: true, desc: 'Preço unitário' },
        { field: 'invoices[].totals.total', type: 'number', req: true, desc: 'Valor total da nota' },
        { field: 'invoices[].delivery_date', type: 'YYYY-MM-DD', req: false, desc: 'Data de entrega' },
        { field: 'invoices[].payment_terms', type: 'integer (dias)', req: false, desc: 'Prazo de pagamento em dias' },
        { field: 'invoices[].supplier.state', type: 'string (UF)', req: false, desc: 'Estado do fornecedor (2 letras)' },
        { field: 'invoices[].items[].category', type: 'string', req: false, desc: 'Categoria do produto' },
        { field: 'invoices[].totals.goods', type: 'number', req: false, desc: 'Valor dos produtos' },
        { field: 'invoices[].totals.freight', type: 'number', req: false, desc: 'Valor do frete' },
        { field: 'invoices[].totals.tax', type: 'number', req: false, desc: 'Valor dos impostos' },
    ];

    return (
        <div>
            {/* Overview */}
            <div className="card" style={{ marginBottom: 20 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                    <span style={{ fontSize: 28 }}>🚀</span>
                    <div>
                        <div className="card-title" style={{ marginBottom: 0 }}>API Push — Seu ERP envia dados para a Plataforma</div>
                        <p style={{ fontSize: 13, color: 'var(--color-text-muted)', margin: 0 }}>
                            Ideal para ERPs com capacidade de fazer requisições HTTP de saída
                        </p>
                    </div>
                </div>
                <div style={{ display: 'flex', gap: 32, fontSize: 13 }}>
                    {[
                        { icon: '⚡', text: 'Resposta imediata (202)' },
                        { icon: '📦', text: 'Batch — múltiplas notas por request' },
                        { icon: '🔄', text: 'Deduplicação automática' },
                        { icon: '🔒', text: 'Autenticação via Bearer Token' },
                    ].map(({ icon, text }) => (
                        <div key={text} style={{ display: 'flex', alignItems: 'center', gap: 6, color: 'var(--color-text-muted)' }}>
                            <span>{icon}</span><span>{text}</span>
                        </div>
                    ))}
                </div>
            </div>

            {/* Flow */}
            <div className="card" style={{ marginBottom: 20, background: 'var(--color-bg-secondary)' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, flexWrap: 'wrap', fontSize: 13 }}>
                    {['Seu ERP', '→ POST /import-json', '→ Staging Area (DB)', '→ Fila Laravel', '→ Dashboard ✅'].map((s, i) => (
                        <span key={i} style={{
                            padding: '6px 14px',
                            borderRadius: 20,
                            background: i === 0 || i === 4 ? 'var(--color-primary)' : 'var(--color-bg)',
                            color: i === 0 || i === 4 ? '#fff' : 'var(--color-text-muted)',
                            border: '1px solid var(--color-border)',
                            fontWeight: i === 0 || i === 4 ? 600 : 400,
                        }}>{s}</span>
                    ))}
                </div>
            </div>

            {/* Step 1 — Auth */}
            <div className="card" style={{ marginBottom: 16 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                    <StepBadge n={1} />
                    <div className="card-title" style={{ marginBottom: 0 }}>Autenticação</div>
                </div>
                <p style={{ fontSize: 13, color: 'var(--color-text-muted)', marginBottom: 8 }}>
                    Obtenha uma API Key no painel <strong>Chaves de API</strong> e inclua em todas as requisições:
                </p>
                <CodeBlock code={`Authorization: Bearer SUA_API_KEY\nContent-Type: application/json\nX-Company-Id: SEU_COMPANY_ID`} />
            </div>

            {/* Step 2 — Send */}
            <div className="card" style={{ marginBottom: 16 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                    <StepBadge n={2} />
                    <div className="card-title" style={{ marginBottom: 0 }}>Enviar Notas Fiscais</div>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 8 }}>
                    <span style={{ background: '#3b82f6', color: '#fff', borderRadius: 4, padding: '2px 8px', fontSize: 11, fontWeight: 700 }}>POST</span>
                    <code style={{ fontSize: 13 }}>/api/v1/invoices/import-json</code>
                    <CopyBtn id="curl" text={curlExample} />
                </div>
                <CodeBlock code={curlExample} />

                {/* Fields table */}
                <div style={{ marginTop: 16 }}>
                    <div style={{ fontWeight: 600, fontSize: 13, marginBottom: 10 }}>Campos do JSON</div>
                    <div style={{ overflowX: 'auto' }}>
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Tipo</th>
                                    <th>Obrigatório</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                {fields.map(f => (
                                    <tr key={f.field}>
                                        <td><code style={{ fontSize: 11 }}>{f.field}</code></td>
                                        <td style={{ color: 'var(--color-text-muted)', fontSize: 12 }}>{f.type}</td>
                                        <td>{f.req ? <Badge color="green">Sim</Badge> : <Badge color="gray">Opcional</Badge>}</td>
                                        <td style={{ fontSize: 12, color: 'var(--color-text-muted)' }}>{f.desc}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Response */}
                <div style={{ marginTop: 16 }}>
                    <div style={{ fontWeight: 600, fontSize: 13, marginBottom: 6 }}>Resposta — <Badge color="green">202 Accepted</Badge></div>
                    <CodeBlock code={successResponse} />
                </div>
            </div>

            {/* Step 3 — Status */}
            <div className="card">
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                    <StepBadge n={3} />
                    <div className="card-title" style={{ marginBottom: 0 }}>Verificar Status (opcional)</div>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 8 }}>
                    <span style={{ background: '#10b981', color: '#fff', borderRadius: 4, padding: '2px 8px', fontSize: 11, fontWeight: 700 }}>GET</span>
                    <code style={{ fontSize: 13 }}>/api/v1/ingestions/{'{'}&ZeroWidthSpace;ingestion_id{'}'}/status</code>
                </div>
                <CodeBlock code={statusResponse} />
                <div style={{ display: 'flex', gap: 12, marginTop: 12, flexWrap: 'wrap' }}>
                    {[
                        { s: 'pending', label: 'Na fila', color: 'yellow' as const },
                        { s: 'done', label: '✅ Processado', color: 'green' as const },
                        { s: 'failed', label: '❌ Erro — ver error_message', color: 'gray' as const },
                    ].map(({ s, label, color }) => (
                        <div key={s} style={{ fontSize: 13 }}>
                            <code style={{ fontSize: 12, marginRight: 6 }}>{s}</code>
                            <Badge color={color}>{label}</Badge>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

// ─── WEBHOOK ─────────────────────────────────────────────────────────────────
function TabWebhook() {
    return (
        <div>
            <div className="card" style={{ marginBottom: 20 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                    <span style={{ fontSize: 28 }}>🔗</span>
                    <div>
                        <div className="card-title" style={{ marginBottom: 0 }}>Webhook — ERP notifica a Plataforma em tempo real</div>
                        <p style={{ fontSize: 13, color: 'var(--color-text-muted)', margin: 0 }}>
                            Ideal para Bling, TinyERP, Netsuite e ERPs que emitem eventos nativos
                        </p>
                    </div>
                </div>
                <div style={{ display: 'flex', gap: 32, fontSize: 13 }}>
                    {[
                        { icon: '⚡', text: 'Tempo real — zero delay' },
                        { icon: '🔒', text: 'Validação HMAC SHA-256' },
                        { icon: '⚙️', text: 'Configuração única no ERP' },
                        { icon: '📋', text: 'Logs de recebimento no painel' },
                    ].map(({ icon, text }) => (
                        <div key={text} style={{ display: 'flex', alignItems: 'center', gap: 6, color: 'var(--color-text-muted)' }}>
                            <span>{icon}</span><span>{text}</span>
                        </div>
                    ))}
                </div>
            </div>

            {/* Credentials */}
            <div className="card" style={{ marginBottom: 16 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                    <StepBadge n={1} />
                    <div className="card-title" style={{ marginBottom: 0 }}>Informações fornecidas pela Plataforma</div>
                </div>
                <p style={{ fontSize: 13, color: 'var(--color-text-muted)', marginBottom: 12 }}>
                    Acesse <strong>Integrações → Webhooks</strong> no painel para obter:
                </p>
                <div style={{ display: 'grid', gap: 12 }}>
                    {[
                        { label: 'URL do Webhook', value: 'https://API_URL/api/v1/webhooks/receive/{slug}', desc: 'Configure esta URL no seu ERP' },
                        { label: 'Secret', value: 'wh_secret_xYz123...', desc: 'Usado para validar a autenticidade dos eventos' },
                    ].map(({ label, value, desc }) => (
                        <div key={label} style={{ background: 'var(--color-bg-secondary)', borderRadius: 8, padding: '12px 16px', border: '1px solid var(--color-border)' }}>
                            <div style={{ fontSize: 11, fontWeight: 600, color: 'var(--color-text-muted)', marginBottom: 4 }}>{label}</div>
                            <code style={{ fontSize: 13 }}>{value}</code>
                            <div style={{ fontSize: 12, color: 'var(--color-text-muted)', marginTop: 4 }}>{desc}</div>
                        </div>
                    ))}
                </div>
            </div>

            {/* ERPs */}
            <div className="card" style={{ marginBottom: 16 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 16 }}>
                    <StepBadge n={2} />
                    <div className="card-title" style={{ marginBottom: 0 }}>Configurar no ERP</div>
                </div>

                {[
                    {
                        erp: 'Bling ERP',
                        icon: '🔵',
                        steps: [
                            'Acesse Configurações → Integrações → Webhooks',
                            'Clique em Novo Webhook',
                            'URL: cole a URL fornecida acima',
                            'Evento: nota-fiscal-emitida',
                            'Secret / Token: cole o secret fornecido acima',
                            'Salve e ative',
                        ],
                        note: 'O Bling envia o header X-Bling-Signature com HMAC-SHA256 — a plataforma valida automaticamente.',
                    },
                    {
                        erp: 'TinyERP',
                        icon: '🟠',
                        steps: [
                            'Acesse Configurações → Integrações → Notificações',
                            'Adicione novo endpoint com a URL fornecida acima',
                            'Selecione os eventos de NF-e / Pedidos de Compra',
                            'Salve',
                        ],
                        note: null,
                    },
                    {
                        erp: 'Outros ERPs / Sistemas Próprios',
                        icon: '⚙️',
                        steps: [
                            'Configure um POST automático para a URL acima sempre que uma NF for emitida',
                            'Adicione no header: X-Webhook-Secret: {seu_secret}',
                            'O body deve ser o payload nativo do seu sistema',
                        ],
                        note: null,
                    },
                ].map(({ erp, icon, steps, note }) => (
                    <div key={erp} style={{ marginBottom: 16, paddingBottom: 16, borderBottom: '1px solid var(--color-border)' }}>
                        <div style={{ fontWeight: 600, fontSize: 14, marginBottom: 8 }}>{icon} {erp}</div>
                        <ol style={{ margin: '0 0 8px 20px', padding: 0, fontSize: 13, color: 'var(--color-text-muted)', lineHeight: 1.8 }}>
                            {steps.map(s => <li key={s}>{s}</li>)}
                        </ol>
                        {note && (
                            <div style={{ background: 'rgba(59,130,246,0.08)', border: '1px solid rgba(59,130,246,0.2)', borderRadius: 6, padding: '8px 12px', fontSize: 12, color: '#60a5fa' }}>
                                ℹ️ {note}
                            </div>
                        )}
                    </div>
                ))}
            </div>

            {/* Responses */}
            <div className="card">
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                    <StepBadge n={3} />
                    <div className="card-title" style={{ marginBottom: 0 }}>Respostas da Plataforma</div>
                </div>
                <div style={{ display: 'grid', gap: 10 }}>
                    {[
                        { code: '202', label: 'Recebido', body: '{ "message": "Webhook recebido. Processamento iniciado.", "ingestion_id": 45 }', color: 'var(--color-success)' },
                        { code: '401', label: 'Assinatura inválida', body: '{ "error": "Assinatura inválida.", "message": "Verifique o secret configurado no seu ERP." }', color: 'var(--color-danger)' },
                        { code: '404', label: 'Slug não encontrado', body: '{ "error": "Webhook não encontrado." }', color: 'var(--color-danger)' },
                    ].map(({ code, label, body, color }) => (
                        <div key={code} style={{ background: 'var(--color-bg-secondary)', borderRadius: 8, padding: '10px 14px', border: `1px solid ${color}22` }}>
                            <div style={{ display: 'flex', gap: 8, alignItems: 'center', marginBottom: 4 }}>
                                <span style={{ fontWeight: 700, color, fontSize: 13 }}>{code}</span>
                                <span style={{ fontSize: 12, color: 'var(--color-text-muted)' }}>{label}</span>
                            </div>
                            <code style={{ fontSize: 11, color: 'var(--color-text-muted)' }}>{body}</code>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

// ─── XML UPLOAD ──────────────────────────────────────────────────────────────
function TabXml() {
    const curlPreview = `curl -X POST https://API_URL/api/v1/invoices/preview-xml \\
  -H "Authorization: Bearer SEU_TOKEN" \\
  -H "X-Company-Id: SEU_COMPANY_ID" \\
  -F "xml_file=@/caminho/para/nota.xml"`;

    const curlImport = `curl -X POST https://API_URL/api/v1/invoices/import-xml \\
  -H "Authorization: Bearer SEU_TOKEN" \\
  -H "X-Company-Id: SEU_COMPANY_ID" \\
  -F "xml_file=@/caminho/para/nota.xml"`;

    const curlLogin = `curl -X POST https://API_URL/api/v1/auth/login \\
  -H "Content-Type: application/json" \\
  -d '{ "email": "seu@email.com", "password": "sua_senha" }'

# Resposta:
# { "token": "1|abc123...", "companies": [{ "id": 1, "name": "Sua Empresa" }] }`;

    return (
        <div>
            <div className="card" style={{ marginBottom: 20 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                    <span style={{ fontSize: 28 }}>📄</span>
                    <div>
                        <div className="card-title" style={{ marginBottom: 0 }}>Upload XML — Envio do arquivo NF-e oficial (SEFAZ)</div>
                        <p style={{ fontSize: 13, color: 'var(--color-text-muted)', margin: 0 }}>
                            Ideal para empresas sem ERP integrado ou scripts que baixam XMLs da SEFAZ
                        </p>
                    </div>
                </div>
                <div style={{ display: 'flex', gap: 32, fontSize: 13 }}>
                    {[
                        { icon: '📂', text: 'Formato XML NF-e modelo 55' },
                        { icon: '📏', text: 'Até 10 MB por arquivo' },
                        { icon: '🔄', text: 'Deduplicação automática' },
                        { icon: '👁️', text: 'Preview antes de importar' },
                    ].map(({ icon, text }) => (
                        <div key={text} style={{ display: 'flex', alignItems: 'center', gap: 6, color: 'var(--color-text-muted)' }}>
                            <span>{icon}</span><span>{text}</span>
                        </div>
                    ))}
                </div>
            </div>

            {/* Option 1 - Panel */}
            <div className="card" style={{ marginBottom: 16, borderLeft: '3px solid var(--color-primary)' }}>
                <div style={{ fontWeight: 700, fontSize: 14, marginBottom: 8 }}>Opção 1 — Upload pelo painel (sem programação)</div>
                <ol style={{ margin: '0 0 0 20px', padding: 0, fontSize: 13, color: 'var(--color-text-muted)', lineHeight: 2 }}>
                    <li>Acesse o painel da plataforma</li>
                    <li>Vá em <strong>Integrações → Importar NF-e</strong></li>
                    <li>Clique em <strong>Upload XML</strong></li>
                    <li>Selecione o arquivo <code>.xml</code> da NF-e</li>
                    <li>Aguarde a confirmação de processamento</li>
                </ol>
            </div>

            {/* Option 2 - API */}
            <div className="card" style={{ marginBottom: 16 }}>
                <div style={{ fontWeight: 700, fontSize: 14, marginBottom: 16 }}>Opção 2 — Via API (para automação por script)</div>

                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                    <StepBadge n={1} />
                    <div className="card-title" style={{ marginBottom: 0 }}>Obter Token de Autenticação</div>
                </div>
                <CodeBlock code={curlLogin} />

                <div style={{ display: 'flex', alignItems: 'center', gap: 12, margin: '20px 0 12px' }}>
                    <StepBadge n={2} />
                    <div className="card-title" style={{ marginBottom: 0 }}>Pré-visualizar XML (opcional)</div>
                </div>
                <p style={{ fontSize: 13, color: 'var(--color-text-muted)', marginBottom: 8 }}>
                    Veja os dados extraídos antes de importar definitivamente:
                </p>
                <CodeBlock code={curlPreview} />

                <div style={{ display: 'flex', alignItems: 'center', gap: 12, margin: '20px 0 12px' }}>
                    <StepBadge n={3} />
                    <div className="card-title" style={{ marginBottom: 0 }}>Importar XML</div>
                </div>
                <CodeBlock code={curlImport} />

                <div style={{ display: 'flex', gap: 16, marginTop: 12 }}>
                    <div style={{ flex: 1, background: 'var(--color-bg-secondary)', borderRadius: 8, padding: '12px 16px', border: '1px solid var(--color-border)' }}>
                        <div style={{ fontSize: 11, fontWeight: 600, color: 'var(--color-success)', marginBottom: 4 }}>202 Accepted — Sucesso</div>
                        <code style={{ fontSize: 11 }}>{'{ "ingestion_id": 42, "status": "pending" }'}</code>
                    </div>
                    <div style={{ flex: 1, background: 'var(--color-bg-secondary)', borderRadius: 8, padding: '12px 16px', border: '1px solid var(--color-border)' }}>
                        <div style={{ fontSize: 11, fontWeight: 600, color: 'var(--color-warning)', marginBottom: 4 }}>409 Conflict — Duplicata</div>
                        <code style={{ fontSize: 11 }}>{'{ "message": "Este arquivo já foi recebido anteriormente." }'}</code>
                    </div>
                </div>
            </div>

            {/* Requirements */}
            <div className="card">
                <div className="card-title">Requisitos do arquivo XML</div>
                <div style={{ display: 'flex', gap: 16, flexWrap: 'wrap' }}>
                    {[
                        { label: 'Formato', value: 'NF-e modelo 55 (padrão SEFAZ)' },
                        { label: 'Extensão', value: '.xml' },
                        { label: 'Tamanho máximo', value: '10 MB' },
                        { label: 'Assinatura digital', value: 'Com ou sem — ambos aceitos' },
                    ].map(({ label, value }) => (
                        <div key={label} style={{ background: 'var(--color-bg-secondary)', borderRadius: 8, padding: '10px 14px', border: '1px solid var(--color-border)', minWidth: 180 }}>
                            <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginBottom: 2 }}>{label}</div>
                            <div style={{ fontSize: 13, fontWeight: 600 }}>{value}</div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

// ─── MAIN PAGE ────────────────────────────────────────────────────────────────
export default function DocsIntegracao() {
    const [tab, setTab] = useState<Tab>('api-push');

    const tabs: { id: Tab; icon: string; label: string; badge: string; badgeColor: 'green' | 'yellow' | 'gray' }[] = [
        { id: 'api-push', icon: '🚀', label: 'API Push', badge: 'Recomendado', badgeColor: 'green' },
        { id: 'webhook', icon: '🔗', label: 'Webhook', badge: 'Tempo real', badgeColor: 'yellow' },
        { id: 'xml', icon: '📄', label: 'Upload XML', badge: 'Sem código', badgeColor: 'gray' },
    ];

    return (
        <div>
            <div className="page-header">
                <h1>Documentação de Integração</h1>
                <p>Guia técnico para conectar seu sistema à Plataforma de Inteligência de Compras</p>
            </div>

            {/* Channel selector */}
            <div style={{ display: 'flex', gap: 12, marginBottom: 24, flexWrap: 'wrap' }}>
                {tabs.map(({ id, icon, label, badge, badgeColor }) => (
                    <button
                        key={id}
                        onClick={() => setTab(id)}
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 10,
                            padding: '14px 20px',
                            borderRadius: 12,
                            border: tab === id ? '2px solid var(--color-primary)' : '2px solid var(--color-border)',
                            background: tab === id ? 'var(--color-primary-dim)' : 'var(--color-card)',
                            cursor: 'pointer',
                            transition: 'all 0.15s',
                        }}
                    >
                        <span style={{ fontSize: 22 }}>{icon}</span>
                        <div style={{ textAlign: 'left' }}>
                            <div style={{ fontWeight: 700, fontSize: 14, color: 'var(--color-text)' }}>{label}</div>
                            <Badge color={badgeColor}>{badge}</Badge>
                        </div>
                    </button>
                ))}
            </div>

            {/* Tab content */}
            {tab === 'api-push' && <TabApiPush />}
            {tab === 'webhook' && <TabWebhook />}
            {tab === 'xml' && <TabXml />}
        </div>
    );
}
