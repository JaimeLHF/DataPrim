<?php

namespace App\DataBridge\Jobs;

use App\DataBridge\Connectors\ConnectorFactory;
use App\DataCore\Models\ErpConnector;
use App\DataCore\Models\RawIngestion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Job que executa a sincronização de um ErpConnector específico.
 *
 * Fluxo:
 *   1. Resolve o connector concreto (BlingConnector etc.)
 *   2. Determina o cursor: last_synced_at ou now()-1dia (1ª sync)
 *   3. Streaming de NF-es com Generator — sem OOM
 *   4. Deduplicação por hash SHA-256
 *   5. Cada nova NF-e → raw_ingestions + ProcessIngestionJob
 *   6. Atualiza last_synced_at e last_sync_status
 */
class SyncErpConnectorJob implements ShouldQueue
{
    use Queueable;

    public int   $tries   = 3;
    public array $backoff = [30, 120, 300]; // 30s → 2min → 5min

    public function __construct(
        public readonly int $connectorId,
    ) {}

    public function handle(): void
    {
        $connector = ErpConnector::findOrFail($this->connectorId);

        if (!$connector->is_active) {
            Log::info("SyncErpConnectorJob: conector #{$this->connectorId} está inativo, pulando.");
            return;
        }

        // Resolve o conector concreto (BlingConnector etc.)
        $impl = ConnectorFactory::make($connector);

        // Define cursor: última sync bem-sucedida ou 3 anos atrás (1ª sync — cobre histórico)
        $since = $connector->last_synced_at
            ? $connector->last_synced_at->copy()
            : now()->subYears(3);

        $imported = 0;
        $skipped  = 0;

        Log::info("SyncErpConnectorJob: iniciando sync ERP={$connector->erp_type} empresa={$connector->company_id} desde={$since->toDateString()}");

        // Streaming via Generator — cada NF-e processada individualmente
        foreach ($impl->fetchInvoicesSince($since) as $rawPayload) {
            $hash = hash('sha256', $rawPayload);

            // Deduplicação por SHA-256 — idempotente mesmo em re-sync
            $exists = RawIngestion::where('payload_hash', $hash)
                ->where('company_id', $connector->company_id)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Staging: persiste raw antes de processar
            $ingestion = RawIngestion::create([
                'company_id'   => $connector->company_id,
                'channel'      => 'connector',
                'source'       => $connector->erp_type,
                'status'       => 'pending',
                'payload'      => $rawPayload,
                'payload_hash' => $hash,
            ]);

            // Delega normalização ao pipeline existente
            ProcessIngestionJob::dispatch($ingestion);
            $imported++;
        }

        // Atualiza cursor para a próxima sync
        $connector->update([
            'last_synced_at'   => now(),
            'last_sync_status' => 'ok',
            'last_sync_error'  => null,
        ]);

        Log::info("SyncErpConnectorJob: concluído. importadas={$imported} duplicadas={$skipped}");
    }

    /**
     * Registra falha no conector após esgotar as retentativas.
     */
    public function failed(Throwable $e): void
    {
        Log::error("SyncErpConnectorJob #{$this->connectorId} falhou: " . $e->getMessage());

        ErpConnector::find($this->connectorId)?->update([
            'last_sync_status' => 'error',
            'last_sync_error'  => substr($e->getMessage(), 0, 1000),
        ]);
    }
}
