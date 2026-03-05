<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categorias master, empresas e usuários
        //    Móveis Ruiz: apenas empresa + usuário criados (sem fornecedores/notas).
        //    Os dados reais serão importados via sincronização com o Bling.
        $this->call([
            CategoryMasterSeeder::class,
            CompanySeeder::class,
            AdminUserSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('ℹ️  Móveis Ruiz: empresa e usuário criados. Dados serão sincronizados via Bling.');

        // 2. Fornecedores e notas fictícias (apenas empresas de benchmark)
        $this->call([
            SupplierSeeder::class,
            InvoiceSeeder::class,
        ]);

        // 3. Mapear categorias dos invoice_items → category_master_id
        $this->command->info('');
        $this->command->info('🔗 Mapeando categorias dos itens...');
        Artisan::call('benchmark:map-categories');
        $this->command->info(Artisan::output());

        // 4. Calcular cost structure das empresas fictícias
        $this->command->info('🏢 Calculando cost structure das empresas fictícias...');
        Artisan::call('benchmark:calculate-company', ['--all' => true]);
        $this->command->info(Artisan::output());

        // 5. Benchmark de mercado (empresas fictícias + indexes)
        $this->call([
            BenchmarkSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Banco populado! Empresas fictícias prontas. Móveis Ruiz aguarda sync com Bling.');
    }
}
