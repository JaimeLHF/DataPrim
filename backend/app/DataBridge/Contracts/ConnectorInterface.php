<?php

namespace App\DataBridge\Contracts;

use Carbon\Carbon;
use Generator;

/**
 * Contrato para todos os conectores de ERP (Canal 4 — Pull Ativo).
 *
 * Cada ERP suportado implementa esta interface. O ConnectorFactory
 * resolve o concreto correto a partir do ErpConnector::erp_type.
 */
interface ConnectorInterface
{
    /**
     * Busca NF-es emitidas desde a data informada.
     *
     * Retorna um Generator de strings JSON — cada string é um payload
     * bruto compatível com raw_ingestions.payload e processável pelo
     * adapter correspondente (ex: BlingAdapter para source='bling').
     *
     * Usar Generator permite streaming (sem carregar tudo na memória),
     * essencial para empresas com alto volume de notas.
     *
     * @param Carbon $since  Cursor da última sync (ou now()->subDay() na 1ª sync)
     * @return Generator<string>  Yields de payloads JSON, um por NF-e
     *
     * @throws ConnectorException em falhas de autenticação, rede ou API
     */
    public function fetchInvoicesSince(Carbon $since): Generator;

    /**
     * Valida se as credenciais atuais conseguem autenticar na API do ERP.
     *
     * Usado no "Testar Conexão" do frontend antes de salvar o conector.
     * Deve fazer uma chamada mínima (ex: GET /me ou token endpoint).
     *
     * @return bool  true se a conexão funciona
     * @throws ConnectorException com mensagem amigável se falhar
     */
    public function testConnection(): bool;

    /**
     * Identificador único do tipo de ERP.
     * Deve corresponder exatamente ao valor em erp_connectors.erp_type.
     *
     * Exemplos: 'bling', 'tinyerp', 'protheus'
     */
    public function erpType(): string;
}
