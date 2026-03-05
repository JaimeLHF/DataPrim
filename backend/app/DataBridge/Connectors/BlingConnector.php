<?php

namespace App\DataBridge\Connectors;

use App\DataBridge\Contracts\ConnectorInterface;
use App\DataCore\Exceptions\ConnectorException;
use App\DataCore\Models\ErpConnector;
use Carbon\Carbon;
use Generator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Conector para o Bling ERP — Canal 4 (Pull Ativo).
 *
 * Autentica via OAuth2 authorization_code flow:
 *   1. getAuthorizationUrl()  → redireciona o usuário para o Bling
 *   2. exchangeCodeForToken() → troca o code por access/refresh token
 *   3. refreshAccessToken()   → renova quando expirado
 *   4. getAccessToken()       → retorna o token vigente (renova se necessário)
 *
 * Credenciais (client_id, client_secret) vêm exclusivamente do .env.
 * Os tokens são armazenados criptografados (Laravel encrypt()) no modelo.
 *
 * Documentação API: https://developer.bling.com.br/referencia
 */
class BlingConnector implements ConnectorInterface
{
    private const BASE_URL      = 'https://www.bling.com.br/Api/v3';
    private const AUTHORIZE_URL = 'https://www.bling.com.br/Api/v3/oauth/authorize';
    private const TOKEN_URL     = 'https://www.bling.com.br/Api/v3/oauth/token';

    private ErpConnector $model;
    private array $credentialsOverride = [];

    public function __construct(
        ErpConnector|array $modelOrCredentials,
        private array $config = [],
    ) {
        if (is_array($modelOrCredentials)) {
            $this->model             = new ErpConnector();
            $this->credentialsOverride = $modelOrCredentials;
        } else {
            $this->model = $modelOrCredentials;
        }
    }

    /**
     * Retorna as credenciais do conector (client_id, client_secret).
     * Prioriza credenciais passadas diretamente no construtor (temp / teste)
     * sobre as criptografadas no model.
     */
    private function credentials(): array
    {
        if ($this->credentialsOverride) {
            return $this->credentialsOverride;
        }
        return $this->model->credentials ?? [];
    }

    public function erpType(): string
    {
        return 'bling';
    }

    // ─── Fluxo de Autorização OAuth2 ─────────────────────────────────────

    /**
     * Gera a URL de autorização para redirecionar o usuário ao Bling.
     * O state CSRF é persistido no Cache (15 min) associado ao connector_id,
     * o que permite validação no callback independente de sessão.
     */
    public function getAuthorizationUrl(): string
    {
        $state = Str::random(40);

        // Cache keyed pelo state — TTL de 15 minutos
        Cache::put('bling_oauth_state_' . $state, [
            'connector_id' => $this->model->id,
            'company_id'   => $this->model->company_id,
        ], now()->addMinutes(15));

        return self::AUTHORIZE_URL . '?' . http_build_query([
            'response_type' => 'code',
            'client_id'     => $this->clientId(),
            'redirect_uri'  => $this->redirectUri(),
            'state'         => $state,
        ]);
    }

    /**
     * Troca o authorization code por access_token + refresh_token.
     * Persiste os tokens criptografados no modelo.
     *
     * POST https://bling.com.br/Api/v3/oauth/token
     * Authorization: Basic base64(client_id:client_secret)
     *
     * @throws ConnectorException em falha de autenticação ou resposta inválida
     */
    public function exchangeCodeForToken(string $code): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->clientId() . ':' . $this->clientSecret()),
        ])
        ->asForm()
        ->timeout(15)
        ->post(self::TOKEN_URL, [
            'grant_type'   => 'authorization_code',
            'code'         => $code,
            'redirect_uri' => $this->redirectUri(),
        ]);

        if ($response->status() === 401 || $response->status() === 400) {
            throw ConnectorException::authFailed('bling', 'Código de autorização inválido ou expirado.');
        }

        if ($response->failed()) {
            throw ConnectorException::apiError('bling', $response->status(), $response->body());
        }

        $data = $response->json();

        if (empty($data['access_token'])) {
            throw ConnectorException::authFailed('bling', 'access_token não retornado após troca de código.');
        }

        $this->persistTokens($data);

        return $data;
    }

    /**
     * Renova o access_token usando o refresh_token armazenado.
     * Salva os novos tokens criptografados no modelo.
     *
     * @throws ConnectorException se o refresh_token for inválido
     */
    public function refreshAccessToken(): void
    {
        if (!$this->model->refresh_token) {
            throw ConnectorException::authFailed(
                'bling',
                'Nenhum refresh_token disponível. O usuário precisa reautorizar o acesso.'
            );
        }

        $refreshToken = decrypt($this->model->refresh_token);

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->clientId() . ':' . $this->clientSecret()),
        ])
        ->asForm()
        ->timeout(15)
        ->post(self::TOKEN_URL, [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        if ($response->status() === 401 || $response->status() === 400) {
            throw ConnectorException::authFailed(
                'bling',
                'Refresh token inválido ou expirado. O usuário precisa reautorizar o acesso em /api/v1/erp-connectors/bling/authorize.'
            );
        }

        if ($response->failed()) {
            throw ConnectorException::apiError('bling', $response->status(), $response->body());
        }

        $data = $response->json();

        if (empty($data['access_token'])) {
            throw ConnectorException::authFailed('bling', 'access_token não retornado após refresh.');
        }

        $this->persistTokens($data);
    }

    // ─── ConnectorInterface ───────────────────────────────────────────────

    /**
     * Busca NF-es de ENTRADA (tipo=1 — recebidas de fornecedores) desde $since.
     *
     * Fluxo em dois passos:
     *   1. Lista paginada via GET /nfe?tipo=1 em janelas de 31 dias
     *      (Bling limita o período do filtro — não aceita ranges > ~31 dias)
     *   2. Para cada NF-e, busca o detalhe completo via GET /nfe/{id}
     *      (com itens, valorNota, valorFrete, serie etc.)
     *
     * Yield cada NF-e detalhada no formato compatível com BlingAdapter.
     */
    public function fetchInvoicesSince(Carbon $since): Generator
    {
        $token      = $this->getAccessToken();
        $windowDays = 30; // janela segura abaixo do limite da API
        $cursor     = $since->copy()->startOfDay();
        $until      = now()->endOfDay();

        // Itera em janelas de 30 dias do passado até hoje
        while ($cursor->lte($until)) {
            $windowEnd = $cursor->copy()->addDays($windowDays)->endOfDay();
            if ($windowEnd->gt($until)) {
                $windowEnd = $until->copy();
            }

            $pagina = 1;

            do {
                $response = Http::withToken($token)
                    ->timeout(30)
                    ->get(self::BASE_URL . '/nfe', [
                        'tipo'               => 0, // 0 = entrada 1 = saída
                        'dataEmissaoInicial' => $cursor->format('Y-m-d'),
                        'dataEmissaoFinal'   => $windowEnd->format('Y-m-d'),
                        'pagina'             => $pagina,
                    ]);

                if ($response->status() === 401) {
                    // Tenta refresh automático e repete
                    $token = $this->getAccessToken();
                    continue;
                }

                if ($response->failed()) {
                    throw ConnectorException::apiError('bling', $response->status(), $response->body());
                }

                $items = $response->json('data') ?? [];

                foreach ($items as $nfeSummary) {
                    $id = $nfeSummary['id'] ?? null;
                    if (!$id) continue;

                    // Busca o detalhe completo da NF-e (com itens, totais, série)
                    $detail = $this->fetchNfeDetail($token, $id);
                    if (!$detail) continue;

                    yield json_encode([
                        'event'   => 'invoice.created',
                        'eventId' => 'connector-bling-' . $id,
                        'data'    => $detail,
                    ]);
                }

                $pagina++;
            } while (!empty($items));

            // Avança para a próxima janela
            $cursor->addDays($windowDays + 1)->startOfDay();
        }
    }

    /**
     * Busca o detalhe completo de uma NF-e pelo ID.
     * Retorna null em caso de erro para não interromper a sync inteira.
     */
    private function fetchNfeDetail(string $token, int|string $id): ?array
    {
        $response = Http::withToken($token)
            ->timeout(30)
            ->get(self::BASE_URL . '/nfe/' . $id);

        if ($response->failed()) {
            return null;
        }

        return $response->json('data');
    }

    /**
     * Verifica se existe um access_token válido armazenado.
     * Se o token expirou, tenta renová-lo via refresh_token.
     *
     * @throws ConnectorException se nenhum token existir ou o refresh falhar
     */
    public function testConnection(): bool
    {
        // Token armazenado → verifica/renova e retorna
        if ($this->model->access_token) {
            $this->getAccessToken();
            return true;
        }

        // Sem token — verifica as credenciais fazendo um probe no endpoint OAuth2.
        // Qualquer resposta diferente de 401 significa que client_id/client_secret são válidos.
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->clientId() . ':' . $this->clientSecret()),
        ])
        ->asForm()
        ->timeout(15)
        ->post(self::TOKEN_URL, ['grant_type' => 'authorization_code', 'code' => 'probe']);

        if ($response->status() === 401) {
            throw ConnectorException::authFailed('bling', 'Credenciais inválidas: client_id ou client_secret incorretos.');
        }

        return true;
    }

    // ─── Token interno ────────────────────────────────────────────────────

    /**
     * Retorna o access_token descriptografado.
     * Renova automaticamente se estiver a menos de 60s do vencimento.
     *
     * @throws ConnectorException se não houver token disponível
     */
    private function getAccessToken(): string
    {
        if (!$this->model->access_token) {
            throw ConnectorException::authFailed(
                'bling',
                'Nenhum access_token encontrado. Complete o fluxo de autorização OAuth2.'
            );
        }

        // Renova se expirado ou a menos de 60s do vencimento
        if ($this->model->token_expires_at && $this->model->token_expires_at->subSeconds(60)->isPast()) {
            $this->refreshAccessToken();
            $this->model->refresh(); // recarrega os novos tokens do banco
        }

        return decrypt($this->model->access_token);
    }

    // ─── Helpers privados ────────────────────────────────────────────────

    /**
     * Persiste os tokens criptografados no modelo.
     * Nunca registra tokens em logs.
     */
    private function persistTokens(array $data): void
    {
        $expiresIn = (int) ($data['expires_in'] ?? 3600);

        // Mantém o refresh_token anterior se a resposta não retornar um novo
        $refreshToken = !empty($data['refresh_token'])
            ? encrypt($data['refresh_token'])
            : $this->model->refresh_token;

        $this->model->update([
            'access_token'     => encrypt($data['access_token']),
            'refresh_token'    => $refreshToken,
            'token_expires_at' => now()->addSeconds($expiresIn),
        ]);
    }

    private function clientId(): string
    {
        $id = $this->credentials()['client_id']
            ?? config('services.bling.client_id', env('BLING_CLIENT_ID', ''));
        if (!$id) {
            throw ConnectorException::authFailed('bling', 'BLING_CLIENT_ID não configurado no .env.');
        }
        return $id;
    }

    private function clientSecret(): string
    {
        $secret = $this->credentials()['client_secret']
            ?? config('services.bling.client_secret', env('BLING_CLIENT_SECRET', ''));
        if (!$secret) {
            throw ConnectorException::authFailed('bling', 'BLING_CLIENT_SECRET não configurado no .env.');
        }
        return $secret;
    }

    private function redirectUri(): string
    {
        return config('services.bling.redirect_uri', env('BLING_REDIRECT_URI', ''));
    }
}
