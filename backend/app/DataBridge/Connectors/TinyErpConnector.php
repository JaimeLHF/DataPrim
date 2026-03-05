<?php

namespace App\DataBridge\Connectors;

use App\DataBridge\Contracts\ConnectorInterface;
use App\DataCore\Exceptions\ConnectorException;
use Carbon\Carbon;
use Generator;
use Illuminate\Support\Facades\Http;

/**
 * Conector para o Tiny ERP (Olist Tiny) — Canal 4 (Pull Ativo).
 *
 * Autenticação via API Token (query param `token`).
 * Busca notas fiscais de ENTRADA pela API v2 do Tiny,
 * paginadas, desde um cursor de data.
 *
 * O payload gerado é compatível com TinyErpAdapter::normalize().
 *
 * Documentação da API: https://www.tiny.com.br/api
 */
class TinyErpConnector implements ConnectorInterface
{
    private const BASE_URL = 'https://api.tiny.com.br/api2';

    public function __construct(
        private array $credentials, // ['api_token' => '...']
        private array $config = [],  // ['tipo_nota' => 'E'] opcional
    ) {}

    public function erpType(): string
    {
        return 'tinyerp';
    }

    // ─── ConnectorInterface ───────────────────────────────────────────────

    /**
     * Busca notas fiscais emitidas desde $since, página a página.
     *
     * Yield cada nota no formato de evento Tiny compatível com TinyErpAdapter:
     * { event: 'nota_fiscal.inserida', data: { nota_fiscal: {...} } }
     */
    public function fetchInvoicesSince(Carbon $since): Generator
    {
        $token  = $this->getToken();
        $pagina = 1;

        // TinyERP usa formato dd/mm/yyyy
        $dataInicial = $since->format('d/m/Y');
        $dataFinal   = now()->format('d/m/Y');

        // Tipo padrão: E (Entrada). Pode ser sobrescrito por config.
        $tipoNota = $this->config['tipo_nota'] ?? 'E';

        do {
            $response = Http::timeout(30)
                ->get(self::BASE_URL . '/notas.fiscais.pesquisa.php', [
                    'token'        => $token,
                    'formato'      => 'json',
                    'dataInicial'  => $dataInicial,
                    'dataFinal'    => $dataFinal,
                    'tipoNota'     => $tipoNota,
                    'pagina'       => $pagina,
                ]);

            if ($response->status() === 401) {
                throw ConnectorException::authFailed('tinyerp', 'Token inválido ou expirado.');
            }

            if ($response->failed()) {
                throw ConnectorException::apiError('tinyerp', $response->status(), $response->body());
            }

            $retorno = $response->json('retorno') ?? [];

            // Tiny retorna status 'Erro' em respostas 200 inválidas
            $status = $retorno['status'] ?? 'OK';
            if ($status === 'Erro') {
                $erros = collect($retorno['erros'] ?? [])
                    ->pluck('erro')
                    ->implode('; ');
                throw ConnectorException::apiError('tinyerp', 200, $erros);
            }

            $items = $retorno['notas_fiscais'] ?? [];

            foreach ($items as $wrapper) {
                $nf = $wrapper['nota_fiscal'] ?? null;
                if (!$nf) continue;

                yield json_encode([
                    'event'  => 'nota_fiscal.inserida',
                    'source' => 'tinyerp',
                    'data'   => ['nota_fiscal' => $nf],
                ]);
            }

            $pagina++;
        } while (!empty($items));
    }

    /**
     * Testa a conexão chamando um endpoint leve (pesquisa com 0 resultados esperados).
     *
     * @throws ConnectorException se o token for inválido
     */
    public function testConnection(): bool
    {
        $token = $this->getToken();

        $response = Http::timeout(15)
            ->get(self::BASE_URL . '/notas.fiscais.pesquisa.php', [
                'token'       => $token,
                'formato'     => 'json',
                // Busca apenas do dia de hoje — rápido e com 0 ou poucos resultados
                'dataInicial' => now()->format('d/m/Y'),
                'dataFinal'   => now()->format('d/m/Y'),
            ]);

        if ($response->status() === 401) {
            throw ConnectorException::authFailed('tinyerp', 'Token inválido.');
        }

        if ($response->failed()) {
            throw ConnectorException::apiError('tinyerp', $response->status(), $response->body());
        }

        $retorno = $response->json('retorno') ?? [];
        $status  = $retorno['status'] ?? 'OK';

        if ($status === 'Erro') {
            $erros = collect($retorno['erros'] ?? [])->pluck('erro')->implode('; ');
            throw ConnectorException::authFailed('tinyerp', $erros);
        }

        return true;
    }

    // ─── Token ────────────────────────────────────────────────────────────

    private function getToken(): string
    {
        $token = $this->credentials['api_token'] ?? '';

        if (!$token) {
            throw ConnectorException::authFailed('tinyerp', 'api_token não configurado.');
        }

        return $token;
    }
}
