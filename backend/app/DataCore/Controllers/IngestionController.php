<?php

namespace App\DataCore\Controllers;

use App\DataCore\Controllers\Controller;
use App\DataCore\Models\RawIngestion;
use Illuminate\Http\JsonResponse;

/**
 * @group Status de Ingestão
 *
 * Acompanhamento do processamento assíncrono de importações.
 */
class IngestionController extends Controller
{
    /**
     * Consultar status
     *
     * Retorna o status de processamento de uma ingestão específica.
     * Use após importar XML ou JSON para acompanhar se o processamento foi concluído.
     *
     * @urlParam id integer required ID da ingestão retornado pelo endpoint de importação. Example: 42
     *
     * @response 200 {"id":42,"channel":"xml_upload","source":"nfe_xml","status":"done","attempts":1,"error_message":null,"created_at":"2026-01-15T10:00:00+00:00","processed_at":"2026-01-15T10:00:05+00:00"}
     * @response 404 {"error":"Ingestão não encontrada."}
     */
    public function status(int $id): JsonResponse
    {
        $companyId = app('current_company_id');

        $ingestion = RawIngestion::where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$ingestion) {
            return response()->json([
                'error' => 'Ingestão não encontrada.',
            ], 404);
        }

        return response()->json([
            'id'            => $ingestion->id,
            'channel'       => $ingestion->channel,
            'source'        => $ingestion->source,
            'status'        => $ingestion->status,
            'attempts'      => $ingestion->attempts,
            'error_message' => $ingestion->error_message,
            'created_at'    => $ingestion->created_at?->toIso8601String(),
            'processed_at'  => $ingestion->processed_at?->toIso8601String(),
        ]);
    }
}
