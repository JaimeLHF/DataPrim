<?php

namespace App\DataBridge\Connectors;

use App\DataBridge\Contracts\ConnectorInterface;
use App\DataCore\Exceptions\ConnectorException;
use App\DataCore\Models\ErpConnector;
use Carbon\Carbon;
use Generator;

/**
 * Conectores fake para testes.
 *
 * Retorna NF-es geradas em memória sem chamadas HTTP reais.
 * Registrado apenas no ambiente de testes — não aparece no ConnectorFactory de produção.
 *
 * Uso no teste:
 *   $connector = new StubConnector(invoiceCount: 3, shouldFailAuth: false);
 *   $connector->fetchInvoicesSince(now()->subDay());
 */
class StubConnector implements ConnectorInterface
{
    public function __construct(
        private int  $invoiceCount  = 2,
        private bool $shouldFailAuth = false,
        private bool $shouldFailFetch = false,
        private string $numero = '000001',
    ) {}

    public function erpType(): string
    {
        return 'stub';
    }

    public function testConnection(): bool
    {
        if ($this->shouldFailAuth) {
            throw ConnectorException::authFailed('stub', 'credenciais inválidas (stub)');
        }
        return true;
    }

    public function fetchInvoicesSince(Carbon $since): Generator
    {
        if ($this->shouldFailFetch) {
            throw ConnectorException::apiError('stub', 500, 'Internal server error (stub)');
        }

        for ($i = 0; $i < $this->invoiceCount; $i++) {
            $num = str_pad((int) $this->numero + $i, 6, '0', STR_PAD_LEFT);

            yield json_encode([
                'event'   => 'invoice.created',
                'eventId' => "stub-connector-{$num}",
                'data'    => [
                    'id'          => 900000 + $i,
                    'numero'      => $num,
                    'serie'       => '1',
                    'dataEmissao' => $since->format('Y-m-d') . ' 10:00:00',
                    'contato'     => [
                        'nome'            => 'Fornecedor Stub SA',
                        'numeroDocumento' => '11222333000181',
                        'uf'              => 'SP',
                    ],
                    'itens' => [
                        [
                            'descricao'  => 'Produto Stub ' . $num,
                            'quantidade' => 10,
                            'valor'      => 100.00,
                        ],
                    ],
                    'totalProdutos' => 1000.00,
                    'totalFrete'    => 50.00,
                    'totalImpostos' => 85.00,
                    'total'         => 1135.00,
                ],
            ]);
        }
    }
}
