<?php

namespace App\DataCore\Controllers;

use App\DataCore\Controllers\Controller;
use App\DataCore\Models\Company;
use App\DataCore\Models\WebhookConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Administração
 * @subgroup Configurações de Webhook
 *
 * Gerencia as configurações de webhook da empresa (requer role admin).
 */
class WebhookConfigController extends Controller
{
    /**
     * Listar configurações
     *
     * Retorna as configurações de webhook ativas da empresa.
     *
     * @response 200 {"data":[{"id":1,"erp_type":"bling","slug":"moveis-ruiz-bling","is_active":true,"last_received_at":null,"webhook_url":"https://api.plataforma.com/api/v1/webhooks/receive/moveis-ruiz-bling","created_at":"2026-01-01T00:00:00Z"}]}
     */
    public function index(): JsonResponse
    {
        $companyId = app('current_company_id');
        $baseUrl   = config('app.url');

        $configs = WebhookConfig::where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($c) => $this->formatConfig($c, $baseUrl));

        return response()->json(['data' => $configs]);
    }

    /**
     * Criar configuração
     *
     * Cria uma nova configuração de webhook para um ERP. Gera automaticamente
     * o slug e o secret. O secret é retornado apenas nesta resposta — guarde-o.
     *
     * @bodyParam erp_type string required Tipo do ERP. Valores: bling, tinyerp. Example: bling
     *
     * @response 201 {"data":{"id":1,"erp_type":"bling","slug":"moveis-ruiz-bling","secret":"a1b2c3...","is_active":true,"webhook_url":"https://api.plataforma.com/api/v1/webhooks/receive/moveis-ruiz-bling","created_at":"2026-01-01T00:00:00Z"}}
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'erp_type' => 'required|string|in:bling,tinyerp',
        ]);

        $companyId = app('current_company_id');
        $company   = Company::findOrFail($companyId);

        // Verificar se já existe config para esse ERP nesta empresa
        $existing = WebhookConfig::where('company_id', $companyId)
            ->where('erp_type', $request->erp_type)
            ->first();

        if ($existing) {
            return response()->json([
                'error'   => 'Configuração já existe.',
                'message' => "Já existe uma configuração para {$request->erp_type} nesta empresa. Revogue a existente antes de criar uma nova.",
            ], 409);
        }

        $secret = WebhookConfig::generateSecret();
        $slug   = WebhookConfig::generateSlug($company, $request->erp_type);

        $config = WebhookConfig::create([
            'company_id' => $companyId,
            'erp_type'   => $request->erp_type,
            'slug'       => $slug,
            'secret'     => $secret,
            'is_active'  => true,
        ]);

        $baseUrl = config('app.url');

        return response()->json([
            'data' => array_merge(
                $this->formatConfig($config, $baseUrl),
                ['secret' => $secret], // Secret visível apenas aqui
            ),
        ], 201);
    }

    /**
     * Revogar configuração
     *
     * Remove a configuração de webhook. O ERP parará de conseguir enviar eventos.
     *
     * @urlParam id integer required ID da configuração. Example: 1
     *
     * @response 204
     */
    public function destroy(int $id): JsonResponse
    {
        $companyId = app('current_company_id');

        $config = WebhookConfig::where('company_id', $companyId)
            ->where('id', $id)
            ->firstOrFail();

        $config->delete();

        return response()->json(null, 204);
    }

    private function formatConfig(WebhookConfig $config, string $baseUrl): array
    {
        return [
            'id'               => $config->id,
            'erp_type'         => $config->erp_type,
            'slug'             => $config->slug,
            'is_active'        => $config->is_active,
            'last_received_at' => $config->last_received_at?->toIso8601String(),
            'webhook_url'      => "{$baseUrl}/api/v1/webhooks/receive/{$config->slug}",
            'created_at'       => $config->created_at?->toIso8601String(),
        ];
    }
}
