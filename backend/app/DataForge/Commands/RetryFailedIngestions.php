<?php

namespace App\DataForge\Commands;

use App\DataBridge\Jobs\ProcessIngestionJob;
use App\DataCore\Models\RawIngestion;
use Illuminate\Console\Command;

/**
 * Reprocessa ingestões com status 'failed'.
 *
 * Lógica de dead-letter:
 *   - Cada despacho manual permite até MAX_QUEUE_TRIES novas tentativas na fila.
 *   - Após MAX_MANUAL_RETRIES despachos (attempts >= limite), a ingestão é considerada
 *     morta e só pode ser reprocessada com --force.
 *
 * Uso:
 *   php artisan ingestions:retry                  → retenta todas as elegíveis
 *   php artisan ingestions:retry --id=42          → retenta uma específica
 *   php artisan ingestions:retry --company=3      → retenta falhas de uma empresa
 *   php artisan ingestions:retry --force          → ignora limite de attempts
 */
class RetryFailedIngestions extends Command
{
    /**
     * Tentativas máximas na fila por despacho (espelha ProcessIngestionJob::$tries).
     */
    private const MAX_QUEUE_TRIES = 3;

    /**
     * Quantidade máxima de despachos manuais antes de considerar dead-letter.
     * Limite total de attempts = MAX_QUEUE_TRIES × MAX_MANUAL_RETRIES = 9.
     */
    private const MAX_MANUAL_RETRIES = 3;

    protected $signature = 'ingestions:retry
                            {--id=    : ID específico da ingestão a retentar}
                            {--company= : Filtra por empresa (company_id)}
                            {--force  : Ignora limite de attempts (dead-letter manual)}';

    protected $description = 'Reprocessa ingestões com status failed (máx. 3 tentativas manuais por padrão)';

    public function handle(): int
    {
        $maxAttempts = self::MAX_QUEUE_TRIES * self::MAX_MANUAL_RETRIES;

        $query = RawIngestion::where('status', 'failed');

        // Filtro por ID específico
        if ($id = $this->option('id')) {
            $ingestion = $query->find((int) $id);

            if (!$ingestion) {
                $this->error("Ingestão #{$id} não encontrada ou não está com status 'failed'.");
                return self::FAILURE;
            }

            if (!$this->option('force') && $ingestion->attempts >= $maxAttempts) {
                $this->warn("Ingestão #{$id} atingiu o limite de {$maxAttempts} tentativas. Use --force para forçar.");
                return self::FAILURE;
            }

            $this->retryOne($ingestion);
            $this->info("Ingestão #{$id} re-despachada.");
            return self::SUCCESS;
        }

        // Filtro por empresa
        if ($companyId = $this->option('company')) {
            $query->where('company_id', (int) $companyId);
        }

        // Sem --force, limita pelas tentativas já realizadas
        if (!$this->option('force')) {
            $query->where('attempts', '<', $maxAttempts);
        }

        $ingestions = $query->orderBy('id')->get();

        if ($ingestions->isEmpty()) {
            $this->info('Nenhuma ingestão elegível para reprocessamento.');
            return self::SUCCESS;
        }

        $this->info("Reprocessando {$ingestions->count()} ingestão(ões) com falha...");

        $ingestions->each(function (RawIngestion $ingestion) {
            $this->retryOne($ingestion);
            $this->line("  → #{$ingestion->id} empresa={$ingestion->company_id} canal={$ingestion->channel} tentativas={$ingestion->attempts}");
        });

        $this->info('Concluído.');
        return self::SUCCESS;
    }

    private function retryOne(RawIngestion $ingestion): void
    {
        $ingestion->markAsPending();
        ProcessIngestionJob::dispatch($ingestion);
    }
}
