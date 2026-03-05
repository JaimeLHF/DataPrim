<?php

namespace App\DataCore\Commands;

use App\DataCore\Models\Company;
use App\DataLumen\Services\CostStructureCalculatorService;
use Illuminate\Console\Command;

class CalculateCompanyCostStructure extends Command
{
    protected $signature = 'benchmark:calculate-company
                            {--company_id= : ID da empresa (obrigatório se não usar --all)}
                            {--period= : Período YYYY-MM específico (default: todos)}
                            {--all : Calcular para todas as empresas}';

    protected $description = 'Calcula estrutura de custo percentual de uma empresa a partir dos invoice_items';

    public function handle(CostStructureCalculatorService $service): int
    {
        $companyId = $this->option('company_id');
        $period    = $this->option('period');
        $all       = $this->option('all');

        if (!$companyId && !$all) {
            $this->error('Informe --company_id=N ou --all para processar todas.');
            return self::FAILURE;
        }

        $companies = $all
            ? Company::all()
            : Company::where('id', $companyId)->get();

        if ($companies->isEmpty()) {
            $this->error('Nenhuma empresa encontrada.');
            return self::FAILURE;
        }

        $totalRecords = 0;
        $totalPeriods = 0;

        foreach ($companies as $company) {
            $this->info("📊 Processando: {$company->name} (ID: {$company->id})");

            if ($period && $period !== 'all') {
                $results = $service->calculateForPeriod($company->id, $period);
                $this->info("  → {$results->count()} registros para {$period}");
                $totalRecords += $results->count();
                $totalPeriods += 1;
            } else {
                $result = $service->calculateAllPeriods($company->id);
                $this->info("  → {$result['periods_processed']} períodos, {$result['records_created']} registros");
                $totalRecords += $result['records_created'];
                $totalPeriods += $result['periods_processed'];
            }
        }

        $this->newLine();
        $this->info("✅ Concluído: {$companies->count()} empresa(s), {$totalPeriods} período(s), {$totalRecords} registro(s).");

        return self::SUCCESS;
    }
}
