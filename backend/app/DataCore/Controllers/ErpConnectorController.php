<?php

namespace App\DataCore\Controllers;

use App\DataBridge\Connectors\BlingConnector;
use App\DataBridge\Connectors\ConnectorFactory;
use App\DataCore\Exceptions\ConnectorException;
use App\DataCore\Controllers\Controller;
use App\DataBridge\Jobs\SyncErpConnectorJob;
use App\DataCore\Models\ErpConnector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * @group Administração
 * @subgroup Conectores ERP (Pull Ativo)
 *
 * Gerencia os conectores ERP ativos (Canal 4 — Benchmark Conector).
 * Requer role admin. Credenciais nunca aparecem nas respostas.
 */
class ErpConnectorController extends Controller
{
    /**
     * Listar conectores
     *
     * Retorna todos os conectores ERP da empresa com status da última sync.
     *
     * @response 200 {"data":[{"id":1,"erp_type":"bling","sync_frequency":360,"is_active":true,"last_synced_at":"2026-01-15T10:00:00Z","last_sync_status":"ok","next_sync_at":"2026-01-15T16:00:00Z","created_at":"2026-01-01T00:00:00Z"}]}
     */
    public function index(): JsonResponse
    {
        $companyId  = app('current_company_id');

        $connectors = ErpConnector::where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($c) => $this->formatConnector($c));

        return response()->json(['data' => $connectors]);
    }

    /**
     * Criar conector
     *
     * Cria um novo conector ERP. Valida as credenciais via testConnection()
     * antes de salvar. Credenciais são armazenadas criptografadas (AES-256).
     *
     * @bodyParam erp_type string required Tipo do ERP. Valores: bling. Example: bling
     * @bodyParam credentials object required Credenciais do ERP (varia por tipo).
     * @bodyParam credentials.client_id string required (Bling) Client ID OAuth2. Example: abc123
     * @bodyParam credentials.client_secret string required (Bling) Client Secret OAuth2. Example: xyz789
     * @bodyParam sync_frequency integer Frequência de sync em minutos. Default: 360. Example: 360
     *
     * @response 201 {"data":{"id":1,"erp_type":"bling","sync_frequency":360,"is_active":true,"last_sync_status":null,"created_at":"2026-01-01T00:00:00Z"}}
     * @response 422 {"error":"Falha de conexão","message":"Credenciais inválidas: ..."}
     * @response 409 {"error":"Conector já existe","message":"Já existe um conector bling ativo para esta empresa."}
     */
    public function store(Request $request): JsonResponse
    {
        $supportedTypes = ConnectorFactory::supportedTypes();

        $request->validate([
            'erp_type'       => 'required|string|in:' . implode(',', $supportedTypes),
            'credentials'    => 'sometimes|array', // obrigatório apenas para ERPs sem OAuth2 (validado abaixo)
            'sync_frequency' => 'integer|min:60|max:10080',
        ]);

        $companyId = app('current_company_id');

        // Evitar duplicata do mesmo ERP na mesma empresa
        $existing = ErpConnector::where('company_id', $companyId)
            ->where('erp_type', $request->erp_type)
            ->where('is_active', true)
            ->first();

        if ($existing) {
            return response()->json([
                'error'   => 'Conector já existe.',
                'message' => "Já existe um conector {$request->erp_type} ativo para esta empresa. Desative o existente antes de criar um novo.",
            ], 409);
        }

        // Valida credenciais para todos os ERPs (incluindo Bling — verifica client_id/secret via probe OAuth2)
        $request->validate(['credentials' => 'required|array']);

        try {
            $tempModel = new ErpConnector([
                'erp_type'    => $request->erp_type,
                'credentials' => $request->credentials,
                'config'      => $request->config ?? [],
            ]);
            $impl = ConnectorFactory::make($tempModel);
            $impl->testConnection();
        } catch (ConnectorException $e) {
            return response()->json([
                'error'   => 'Falha de conexão.',
                'message' => $e->getMessage(),
            ], 422);
        }

        // Salva o conector (credentials são criptografadas automaticamente pelo mutator)
        $connector = ErpConnector::create([
            'company_id'     => $companyId,
            'erp_type'       => $request->erp_type,
            'credentials'    => $request->credentials,
            'config'         => $request->config ?? null,
            'sync_frequency' => $request->sync_frequency ?? 360,
            'is_active'      => true,
        ]);

        // Dispara primeira sync para todos os ERPs
        SyncErpConnectorJob::dispatch($connector->id);

        $responseData = ['data' => $this->formatConnector($connector)];

        // Para Bling, inclui URL de autorização OAuth2 como informação adicional
        if ($request->erp_type === 'bling') {
            $responseData['authorize_url'] = url('/api/v1/erp-connectors/bling/authorize');
            $responseData['message'] = 'Conector criado. Para habilitar sync automático, acesse authorize_url para autorizar o Bling.';
        }

        return response()->json($responseData, 201);
    }

    /**
     * Revogar conector
     *
     * Desativa e remove o conector. O scheduler não irá mais sincronizar.
     *
     * @urlParam id integer required ID do conector. Example: 1
     *
     * @response 204
     */
    public function destroy(int $id): JsonResponse
    {
        $companyId = app('current_company_id');

        $connector = ErpConnector::where('company_id', $companyId)
            ->where('id', $id)
            ->firstOrFail();

        $connector->delete();

        return response()->json(null, 204);
    }

    /**
     * Sincronizar agora
     *
     * Dispara uma sincronização manual imediata para o conector.
     * O job roda em background via queue.
     *
     * @urlParam id integer required ID do conector. Example: 1
     *
     * @response 202 {"message":"Sincronização iniciada.","connector_id":1}
     */
    public function sync(int $id): JsonResponse
    {
        $companyId = app('current_company_id');

        $connector = ErpConnector::where('company_id', $companyId)
            ->where('id', $id)
            ->firstOrFail();

        SyncErpConnectorJob::dispatch($connector->id);

        return response()->json([
            'message'      => 'Sincronização iniciada.',
            'connector_id' => $connector->id,
        ], 202);
    }

    /**
     * Testar conexão
     *
     * Testa se as credenciais fornecidas conseguem autenticar na API do ERP.
     * Útil antes de criar o conector.
     *
     * @bodyParam erp_type string required Tipo do ERP. Example: bling
     * @bodyParam credentials object required Credenciais a testar.
     *
     * @response 200 {"ok":true,"message":"Conexão estabelecida com sucesso."}
     * @response 422 {"ok":false,"message":"Credenciais inválidas: ..."}
     */
    public function testConnection(Request $request): JsonResponse
    {
        $request->validate([
            'erp_type' => 'required|string|in:' . implode(',', ConnectorFactory::supportedTypes()),
        ]);

        $request->validate(['credentials' => 'required|array']);

        try {
            $tempModel = new ErpConnector([
                'erp_type'    => $request->erp_type,
                'credentials' => $request->credentials,
                'config'      => $request->config ?? [],
            ]);
            $impl = ConnectorFactory::make($tempModel);
            $impl->testConnection();

            return response()->json([
                'ok'      => true,
                'message' => 'Conexão estabelecida com sucesso.',
            ]);
        } catch (ConnectorException $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ─── Bling OAuth2 ─────────────────────────────────────────────────────

    /**
     * Iniciar autorização Bling
     *
     * Retorna a URL de autorização do Bling como JSON.
     * O frontend faz fetch autenticado e redireciona via window.location.href.
     * O state CSRF é armazenado no Cache (não na sessão) — compatível com SPAs.
     *
     * @response 200 {"url":"https://bling.com.br/Api/v3/oauth/authorize?..."}
     */
    public function authorize(Request $request): JsonResponse
    {
        $companyId = app('current_company_id');

        $connector = ErpConnector::where('company_id', $companyId)
            ->where('erp_type', 'bling')
            ->where('is_active', true)
            ->firstOrFail();

        $bling = new BlingConnector($connector, $connector->config ?? []);
        $url   = $bling->getAuthorizationUrl();

        return response()->json(['url' => $url]);
    }

    /**
     * Callback do OAuth2 do Bling
     *
     * Recebe o `code` e `state` do Bling após autorização do usuário.
     * Valida o state CSRF, troca o code por access/refresh tokens e os persiste.
     * Redireciona para o frontend com indicação de sucesso ou erro.
     *
     * @queryParam code string required Código de autorização gerado pelo Bling.
     * @queryParam state string required State CSRF enviado na requisição de autorização.
     * @response 302 Redirect para o frontend
     */
    public function callback(Request $request): RedirectResponse
    {
        $state    = $request->query('state', '');
        $cacheKey = 'bling_oauth_state_' . $state;

        // Valida o state CSRF via Cache (não depende de sessão)
        $cached = Cache::get($cacheKey);

        if (!$cached || !$state) {
            Log::warning('Bling OAuth callback: state CSRF inválido ou expirado.', [
                'ip' => $request->ip(),
            ]);
            return redirect()->away($this->frontendUrl('/settings/erp-connectors?error=csrf_invalid'));
        }

        // State é de uso único — remove do Cache imediatamente
        Cache::forget($cacheKey);

        $code        = $request->query('code');
        $connectorId = $cached['connector_id'];
        $companyId   = $cached['company_id'];

        if (!$code) {
            $error = $request->query('error', 'access_denied');
            return redirect()->away($this->frontendUrl("/settings/erp-connectors?error={$error}"));
        }

        try {
            $connector = ErpConnector::where('id', $connectorId)
                ->where('company_id', $companyId)
                ->where('erp_type', 'bling')
                ->firstOrFail();

            $bling = new BlingConnector($connector, $connector->config ?? []);
            $bling->exchangeCodeForToken($code);

            // Dispara a primeira sync agora que os tokens estão disponíveis
            SyncErpConnectorJob::dispatch($connector->id);

            Log::info('Bling OAuth autorizado com sucesso.', [
                'company_id'   => $companyId,
                'connector_id' => $connector->id,
            ]);

            return redirect()->away($this->frontendUrl('/settings/erp-connectors?success=bling_connected'));
        } catch (ConnectorException $e) {
            Log::error('Bling OAuth callback falhou.', [
                'company_id' => $companyId,
                'error'      => $e->getMessage(),
            ]);
            return redirect()->away($this->frontendUrl('/settings/erp-connectors?error=token_exchange_failed'));
        }
    }

    // ─── Helper ──────────────────────────────────────────────────────────

    private function frontendUrl(string $path): string
    {
        $base = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        return $base . $path;
    }

    private function formatConnector(ErpConnector $connector): array
    {
        // Calcula próxima sync estimada a partir do last_synced_at + frequency
        $nextSyncAt = null;
        if ($connector->last_synced_at) {
            $nextSyncAt = $connector->last_synced_at
                ->copy()
                ->addMinutes($connector->sync_frequency)
                ->toIso8601String();
        }

        $data = [
            'id'               => $connector->id,
            'erp_type'         => $connector->erp_type,
            'sync_frequency'   => $connector->sync_frequency,
            'is_active'        => $connector->is_active,
            'last_synced_at'   => $connector->last_synced_at?->toIso8601String(),
            'last_sync_status' => $connector->last_sync_status,
            'last_sync_error'  => $connector->last_sync_error,
            'next_sync_at'     => $nextSyncAt,
            'created_at'       => $connector->created_at?->toIso8601String(),
        ];

        // Para ERPs com OAuth2 authorization_code, expõe o estado do token
        if ($connector->erp_type === 'bling') {
            $data['oauth_status']   = $this->blingOauthStatus($connector);
            $data['authorize_url']  = url('/api/v1/erp-connectors/bling/authorize');
        }

        return $data;
    }

    /**
     * Retorna o estado atual do token OAuth2 do Bling:
     *   - 'pending'    → nunca autorizado (access_token ausente)
     *   - 'authorized' → token válido
     *   - 'expired'    → token expirado (será renovado automaticamente no próximo sync)
     */
    private function blingOauthStatus(ErpConnector $connector): string
    {
        if (!$connector->access_token) {
            return 'pending';
        }

        if ($connector->token_expires_at && $connector->token_expires_at->isPast()) {
            return 'expired';
        }

        return 'authorized';
    }
}
