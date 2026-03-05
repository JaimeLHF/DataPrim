<?php

namespace App\DataCore\Commands;

use App\DataForge\Services\MarketBenchmarkCalculatorService;
use Illuminate\Console\Command;

class CalculateMarketBenchmark extends Command
{
    protected $signature = 'benchmark:calculate-market
                            {--period= : Período YYYY-MM específico (default: todos)}
                            {--region= : Região específica (default: todas)}';

    protected $description = 'Calcula índices de benchmark de mercado a partir dos cost structures das empresas participantes';

    public function handle(MarketBenchmarkCalculatorService $service): int
    {
        $period = $this->option('period');
        $region = $this->option('region');

        if ($period && $period !== 'all') {
            $this->info("📈 Calculando benchmark para período: {$period}" . ($region ? ", região: {$region}" : ''));

            $result = $service->calculateForPeriod($period, $region);

            $this->info("  → {$result['indexes_created']} índices criados");

            if ($result['skipped_invalid'] > 0) {
                $this->warn("  → {$result['skipped_invalid']} índices com amostra insuficiente (marcados como inválidos)");
            }
        } else {
            $this->info('📈 Calculando benchmark para todos os períodos...' . ($region ? " Região: {$region}" : ''));

            $result = $service->calculateAllPeriods($region);

            $this->info("  → {$result['periods_processed']} período(s) processados");
            $this->info("  → {$result['indexes_created']} índices criados");

            if ($result['skipped_invalid'] > 0) {
                $this->warn("  → {$result['skipped_invalid']} com amostra insuficiente");
            }
        }

        $this->newLine();
        $this->info('✅ Benchmark de mercado atualizado.');

        return self::SUCCESS;
    }
}
