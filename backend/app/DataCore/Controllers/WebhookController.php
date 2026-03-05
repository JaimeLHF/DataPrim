<?php

namespace App\DataCore\Controllers;

use App\DataBridge\Contracts\WebhookAdapterInterface;
use App\DataCore\Controllers\Controller;
use App\DataBridge\Jobs\ProcessIngestionJob;
use App\DataCore\Models\RawIngestion;
use App\DataCore\Models\WebhookConfig;
use App\DataForge\Services\NormalizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Canal Webhook
 *
 * Recebe eventos de ERPs externos (Bling, TinyERP, etc.) via webhook.
 * O endpoint de recepção é público e identificado por slug único.
 */
class WebhookController extends Controller
{
    public function __construct(
        private NormalizationService $normalization,
    ) {}

    /**
     * Receber webhook
     *
     * Endpoint público chamado pelo ERP do cliente quando um evento ocorre.
     * A autenticidade é verificada via assinatura HMAC no header específico do ERP.
     * A resposta é imediata (202) e o processamento ocorre de forma assíncrona.
     *
     * @urlParam slug string required Slug do webhook (obtido no painel de Integrações). Example: moveis-ruiz-bling
     *
     * @response 202 {"message":"Webhook recebido. Processamento iniciado.","ingestion_id":45,"status":"pending"}
     * @response 202 {"message":"Evento já processado anteriormente.","ingestion_id":43,"status":"done","skipped":true}
     * @response 404 {"error":"Webhook não encontrado."}
     * @response 401 {"error":"Assinatura inválida.","message":"Verifique o secret configurado no seu ERP."}
     */
    public function receive(Request $request, string $slug): JsonResponse
    {
        // 1. Busca a config pelo slug
        $config = WebhookConfig::where('slug', $slug)->where('is_active', true)->first();

        if (!$config) {
            return response()->json(['error' => 'Webhook não encontrado.'], 404);
        }

        // 2. Lê o body raw (ANTES de qualquer parsing — crítico para HMAC)
        $rawPayload = $request->getContent();

        // 3. Resolve o adapter correto para validar a assinatura
        $adapter = $this->normalization->resolveAdapterPublic('webhook', $config->erp_type);

        // 4. Valida assinatura HMAC (se o adapter suportar)
        if ($adapter instanceof WebhookAdapterInterface) {
            $signatureHeader = $request->header($adapter->signatureHeader(), '');
            if (!$adapter->validateSignature($rawPayload, $signatureHeader, $config->secret)) {
                return response()->json([
                    'error'   => 'Assinatura inválida.',
                    'message' => 'Verifique o secret configurado no seu ERP.',
                ], 401);
            }
        }

        // 5. Deduplicação por hash SHA-256
        $hash     = hash('sha256', $rawPayload);
        $existing = RawIngestion::where('payload_hash', $hash)
            ->where('company_id', $config->company_id)
            ->first();

        if ($existing) {
            return response()->json([
                'message'      => 'Evento já processado anteriormente.',
                'ingestion_id' => $existing->id,
                'status'       => $existing->status,
                'skipped'      => true,
            ], 202);
        }

        // 6. Persiste na staging area
        $ingestion = RawIngestion::create([
            'company_id'   => $config->company_id,
            'channel'      => 'webhook',
            'source'       => $config->erp_type,
            'status'       => 'pending',
            'payload'      => $rawPayload,
            'payload_hash' => $hash,
        ]);

        // 7. Atualiza timestamp de último recebimento
        $config->update(['last_received_at' => now()]);

        // 8. Dispara processamento assíncrono
        ProcessIngestionJob::dispatch($ingestion);

        return response()->json([
            'message'      => 'Webhook recebido. Processamento iniciado.',
            'ingestion_id' => $ingestion->id,
            'status'       => 'pending',
        ], 202);
    }

    /**
     * Logs de webhooks
     *
     * Lista os webhooks recebidos pela empresa autenticada, com status de processamento.
     *
     * @response 200 {"data":[{"id":45,"source":"bling","status":"done","created_at":"2026-01-15T10:00:00Z","processed_at":"2026-01-15T10:00:02Z","error_message":null}],"total":1}
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = app('current_company_id');

        $logs = RawIngestion::where('company_id', $companyId)
            ->where('channel', 'webhook')
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json([
            'data'  => $logs->map(fn($r) => [
                'id'            => $r->id,
                'source'        => $r->source,
                'status'        => $r->status,
                'attempts'      => $r->attempts,
                'error_message' => $r->error_message,
                'created_at'    => $r->created_at?->toIso8601String(),
                'processed_at'  => $r->processed_at?->toIso8601String(),
            ]),
            'total' => $logs->total(),
        ]);
    }
}
