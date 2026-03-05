<?php

namespace Database\Seeders;

use App\DataCore\Models\CategoryMaster;
use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyCostStructure;
use App\DataCore\Models\MarketBenchmarkIndex;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BenchmarkSeeder extends Seeder
{
    /** Número de empresas fictícias a simular */
    private int $fakeCompanyCount = 15;

    /** Meses para gerar (últimos 6 meses) */
    private int $monthsBack = 6;

    /** Perfis de distribuição realistas para indústria de móveis */
    private array $categoryProfiles = [
        'chapa'     => ['mean' => 35.0, 'std' => 4.0],
        'ferragem'  => ['mean' => 18.0, 'std' => 3.0],
        'quimico'   => ['mean' => 12.0, 'std' => 2.5],
        'embalagem' => ['mean' => 10.0, 'std' => 2.0],
        'frete'     => ['mean' =>  9.0, 'std' => 3.5],
        'acessorio' => ['mean' =>  8.0, 'std' => 2.0],
        'aramado'   => ['mean' =>  5.0, 'std' => 1.5],
        'outros'    => ['mean' =>  3.0, 'std' => 1.5],
    ];

    /** Regiões brasileiras para dispersão */
    private array $regions = ['Sul', 'Sudeste', 'Centro-Oeste', 'Nordeste', 'Norte'];

    /** Nomes fictícios de empresas */
    private array $fakeCompanyNames = [
        'ModularTec Indústria Ltda',
        'MadeiraBrasil S.A.',
        'MóveisSul Fábrica',
        'ArtMóvel Comércio',
        'EstiloPrime Indústria',
        'MóvelFácil S.A.',
        'Marcenaria União Ltda',
        'PlanaMóveis Indústria',
        'TechWood Fábrica',
        'MóveisNorte Comércio',
        'ClassicDesign Indústria',
        'FlexMóvel Ltda',
        'MadeiraNobre S.A.',
        'MóveisVerde Indústria',
        'SuperMóvel Fábrica',
    ];

    public function run(): void
    {
        $this->command->info('🏭 Criando empresas fictícias para benchmark...');

        // 1. Criar empresas fictícias
        $fakeCompanies = $this->createFakeCompanies();

        // 2. Carregar categorias ativas
        $categories = CategoryMaster::active()->ordered()->get()->keyBy('slug');

        if ($categories->isEmpty()) {
            $this->command->error('Nenhuma categoria encontrada. Execute CategoryMasterSeeder primeiro.');
            return;
        }

        // 3. Gerar períodos
        $periods = $this->generatePeriods();

        // 4. Gerar cost structures para cada empresa fictícia
        $this->command->info("📊 Gerando cost structures para {$this->fakeCompanyCount} empresas × " . count($periods) . " meses...");

        foreach ($fakeCompanies as $company) {
            foreach ($periods as $period) {
                $this->generateCostStructureForCompany($company, $categories, $period);
            }
        }

        $totalStructures = CompanyCostStructure::count();
        $this->command->info("✅ {$totalStructures} registros em company_cost_structures");

        // 5. Calcular benchmark indexes a partir dos dados gerados
        $this->command->info('📈 Calculando benchmark indexes...');
        $this->calculateBenchmarkIndexes($categories, $periods);

        $totalIndexes = MarketBenchmarkIndex::count();
        $this->command->info("✅ {$totalIndexes} registros em market_benchmark_indexes");
    }

    /**
     * Cria empresas fictícias com região e benchmark_participant = true.
     */
    private function createFakeCompanies(): array
    {
        $companies = [];

        for ($i = 0; $i < $this->fakeCompanyCount; $i++) {
            $name   = $this->fakeCompanyNames[$i] ?? "Empresa Fictícia " . ($i + 1);
            $region = $this->regions[$i % count($this->regions)];

            $company = Company::updateOrCreate(
                ['name' => $name],
                [
                    'region'                  => $region,
                    'cnpj'                    => $this->fakeCnpj($i),
                    'segment'                 => 'Moveleiro',
                    'state'                   => $this->regionToState($region),
                    'is_benchmark_participant' => true,
                    'benchmark_anonymized_id'  => Str::uuid()->toString(),
                ]
            );

            $companies[] = $company;
        }

        $this->command->info("  {$this->fakeCompanyCount} empresas fictícias criadas/atualizadas.");

        return $companies;
    }

    /**
     * Gera YYYY-MM para os últimos N meses.
     */
    private function generatePeriods(): array
    {
        $periods = [];
        $now = Carbon::now();

        for ($i = $this->monthsBack; $i >= 1; $i--) {
            $periods[] = $now->copy()->subMonths($i)->format('Y-m');
        }

        return $periods;
    }

    /**
     * Gera cost structure de uma empresa para um período.
     * Garante que soma(percentuais) ≈ 100%.
     */
    private function generateCostStructureForCompany(Company $company, $categories, string $period): void
    {
        [$year, $month] = explode('-', $period);
        $seasonFactor = $this->seasonalFactor((int) $month);

        // 1. Gerar percentuais brutos para cada categoria
        $rawPercentages = [];
        foreach ($this->categoryProfiles as $slug => $profile) {
            if (!$categories->has($slug)) {
                continue;
            }

            $mean = $profile['mean'] * $seasonFactor;
            $std  = $profile['std'];

            // Box-Muller transform para distribuição normal
            $value = $this->gaussianRandom($mean, $std);

            // Clamp: mínimo 1%, máximo 60%
            $value = max(1.0, min(60.0, $value));

            $rawPercentages[$slug] = $value;
        }

        // 2. Normalizar para somar 100%
        $total = array_sum($rawPercentages);
        $normalized = [];
        foreach ($rawPercentages as $slug => $val) {
            $normalized[$slug] = round(($val / $total) * 100, 3);
        }

        // 3. Gasto total fictício da empresa (entre 200k e 800k)
        $baseSpend = mt_rand(200000, 800000);
        // Variação sazonal no gasto total
        $totalCompanySpend = round($baseSpend * $seasonFactor, 2);

        $now = Carbon::now();

        // 4. Persistir
        foreach ($normalized as $slug => $percentage) {
            $cat = $categories->get($slug);
            if (!$cat) {
                continue;
            }

            $categorySpend = round($totalCompanySpend * ($percentage / 100), 2);

            CompanyCostStructure::updateOrCreate(
                [
                    'company_id'         => $company->id,
                    'category_master_id' => $cat->id,
                    'period'             => $period,
                ],
                [
                    'region'              => $company->region ?? 'Sul',
                    'total_spend'         => $categorySpend,
                    'total_company_spend' => $totalCompanySpend,
                    'percentage'          => $percentage,
                    'freight_component'   => $slug === 'frete' ? $categorySpend : 0,
                    'tax_component'       => round($categorySpend * 0.12, 2), // ~12% impostos
                    'items_count'         => mt_rand(5, 80),
                    'calculated_at'       => $now,
                ]
            );
        }
    }

    /**
     * Calcula e persiste benchmark indexes para todos os períodos e categorias.
     */
    private function calculateBenchmarkIndexes($categories, array $periods): void
    {
        $now = Carbon::now();

        foreach ($periods as $period) {
            foreach ($categories as $slug => $cat) {
                // Buscar todos os percentuais das empresas participantes
                $percentages = CompanyCostStructure::where('category_master_id', $cat->id)
                    ->where('period', $period)
                    ->join('companies', 'company_cost_structures.company_id', '=', 'companies.id')
                    ->where('companies.is_benchmark_participant', true)
                    ->pluck('company_cost_structures.percentage')
                    ->map(fn ($v) => (float) $v)
                    ->sort()
                    ->values();

                if ($percentages->isEmpty()) {
                    continue;
                }

                $count = $percentages->count();
                $avg   = round($percentages->avg(), 3);

                // Calcular quartis com interpolação linear
                $median = $this->percentile($percentages, 50);
                $p25    = $this->percentile($percentages, 25);
                $p75    = $this->percentile($percentages, 75);

                // Desvio padrão
                $variance = $percentages->reduce(function ($carry, $val) use ($avg) {
                    return $carry + pow($val - $avg, 2);
                }, 0) / $count;
                $stdDev = round(sqrt($variance), 3);

                // Totais
                $totalSpendPool = CompanyCostStructure::where('category_master_id', $cat->id)
                    ->where('period', $period)
                    ->sum('total_spend');

                $avgSpendPerCompany = $count > 0 ? round($totalSpendPool / $count, 2) : 0;

                // Determinar região predominante (ou Nacional)
                $region = 'Nacional';

                MarketBenchmarkIndex::updateOrCreate(
                    [
                        'category_master_id' => $cat->id,
                        'period'             => $period,
                        'region'             => $region,
                        'version'            => 1,
                    ],
                    [
                        'avg_percentage'        => $avg,
                        'median_percentage'     => round($median, 3),
                        'p25_percentage'        => round($p25, 3),
                        'p75_percentage'        => round($p75, 3),
                        'std_deviation'         => $stdDev,
                        'sample_size'           => $count,
                        'total_spend_pool'      => round($totalSpendPool, 2),
                        'avg_spend_per_company' => $avgSpendPerCompany,
                        'is_valid'              => $count >= 3,
                        'min_sample_threshold'  => 3,
                        'calculated_at'         => $now,
                    ]
                );
            }
        }
    }

    /**
     * Fator sazonal por mês.
     */
    private function seasonalFactor(int $month): float
    {
        $factors = [
            1  => 0.92,
            2  => 0.95,
            3  => 1.08,
            4  => 1.02,
            5  => 1.00,
            6  => 0.97,
            7  => 0.95,
            8  => 1.00,
            9  => 1.06,
            10 => 1.10,
            11 => 1.05,
            12 => 0.90,
        ];

        return $factors[$month] ?? 1.0;
    }

    /**
     * Box-Muller transform para gerar valor com distribuição normal.
     */
    private function gaussianRandom(float $mean, float $std): float
    {
        $u1 = mt_rand(1, 32767) / 32767;
        $u2 = mt_rand(1, 32767) / 32767;
        $z  = sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);

        return $mean + $z * $std;
    }

    /**
     * Calcula percentil com interpolação linear.
     */
    private function percentile($sorted, float $p): float
    {
        $count = $sorted->count();
        if ($count === 0) return 0;
        if ($count === 1) return $sorted->first();

        $rank     = ($p / 100) * ($count - 1);
        $lower    = (int) floor($rank);
        $upper    = (int) ceil($rank);
        $fraction = $rank - $lower;

        if ($lower === $upper) return $sorted[$lower];

        return $sorted[$lower] + $fraction * ($sorted[$upper] - $sorted[$lower]);
    }

    /**
     * Gera CNPJ fictício formatado.
     */
    private function fakeCnpj(int $index): string
    {
        $base = str_pad((string) (90000000 + $index * 111), 8, '0', STR_PAD_LEFT);
        return substr($base, 0, 2) . '.' . substr($base, 2, 3) . '.' . substr($base, 5, 3) . '/0001-00';
    }

    /**
     * Mapeia região para UF representativa.
     */
    private function regionToState(string $region): string
    {
        return match ($region) {
            'Sul'          => 'SC',
            'Sudeste'      => 'SP',
            'Centro-Oeste' => 'GO',
            'Nordeste'     => 'BA',
            'Norte'        => 'PA',
            default        => 'SP',
        };
    }
}
