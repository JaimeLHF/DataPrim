<?php

namespace App\DataForge\Commands;

use App\DataBridge\Jobs\SyncErpConnectorJob;
use App\DataCore\Models\ErpConnector;
use Illuminate\Console\Command;

/**
 * Comando artisan para disparar sincronização de conectores ERP.
 *
 * Uso:
 *   php artisan erp:sync              → sincroniza apenas os vencidos
 *   php artisan erp:sync --id=5       → força sync de um conector específico
 *   php artisan erp:sync --all        → força sync de todos os ativos
 */
class SyncErpConnectors extends Command
{
    protected $signature = 'erp:sync
                            {--id= : ID específico do conector a sincronizar}
                            {--all  : Força sync de todos os conectores ativos}';

    protected $description = 'Sincroniza conectores ERP ativos cujo intervalo de sync venceu';

    public function handle(): int
    {
        // Opção 1: conector específico por ID
        if ($id = $this->option('id')) {
            $connector = ErpConnector::find($id);

            if (!$connector) {
                $this->error("Conector #{$id} não encontrado.");
                return self::FAILURE;
            }

            $this->info("Disparando sync forçado para conector #{$id} ({$connector->erp_type})...");
            SyncErpConnectorJob::dispatch((int) $id);
            $this->info('Job despachado.');
            return self::SUCCESS;
        }

        // Opção 2: todos os ativos (--all)
        if ($this->option('all')) {
            $connectors = ErpConnector::active()->get();
        } else {
            // Padrão: apenas os vencidos pelo sync_frequency
            $connectors = ErpConnector::due()->get();
        }

        if ($connectors->isEmpty()) {
            $this->info('Nenhum conector ERP pendente de sincronização.');
            return self::SUCCESS;
        }

        $this->info("Disparando {$connectors->count()} job(s) de sync...");

        $connectors->each(function (ErpConnector $connector) {
            SyncErpConnectorJob::dispatch($connector->id);
            $this->line("  → #{$connector->id} {$connector->erp_type} (empresa {$connector->company_id})");
        });

        $this->info('Concluído.');
        return self::SUCCESS;
    }
}
