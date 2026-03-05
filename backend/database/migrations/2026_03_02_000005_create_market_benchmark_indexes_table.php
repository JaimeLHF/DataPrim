<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_benchmark_indexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_master_id')->constrained()->cascadeOnDelete();
            $table->char('period', 7)->comment('Formato YYYY-MM');
            $table->string('region', 20)->comment('Sul, Sudeste, Nacional, etc.');
            $table->decimal('avg_percentage', 6, 3)->comment('Média ponderada % do custo total');
            $table->decimal('median_percentage', 6, 3)->nullable();
            $table->decimal('p25_percentage', 6, 3)->nullable()->comment('Percentil 25');
            $table->decimal('p75_percentage', 6, 3)->nullable()->comment('Percentil 75');
            $table->decimal('std_deviation', 6, 3)->nullable();
            $table->unsignedInteger('sample_size')->default(0)->comment('Qtd empresas participantes');
            $table->decimal('total_spend_pool', 16, 2)->default(0)->comment('Soma total do pool anonimizado');
            $table->decimal('avg_spend_per_company', 14, 2)->default(0);
            $table->boolean('is_valid')->default(false)->comment('true se sample_size >= min_sample_threshold');
            $table->unsignedInteger('min_sample_threshold')->default(5);
            $table->unsignedInteger('version')->default(1)->comment('Versionamento de recálculo');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['category_master_id', 'period', 'region', 'version'],
                'mbi_category_period_region_version_unique'
            );
            $table->index(['period', 'region', 'is_valid'], 'mbi_period_region_valid_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_benchmark_indexes');
    }
};
