<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_cost_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_master_id')->constrained()->cascadeOnDelete();
            $table->char('period', 7)->comment('Formato YYYY-MM');
            $table->string('region', 20)->comment('Desnormalizado da empresa para evitar JOIN');
            $table->decimal('total_spend', 14, 2)->default(0)->comment('Gasto na categoria no período');
            $table->decimal('total_company_spend', 14, 2)->default(0)->comment('Gasto total da empresa no período');
            $table->decimal('percentage', 6, 3)->default(0)->comment('(total_spend / total_company_spend) * 100');
            $table->decimal('freight_component', 14, 2)->default(0)->comment('Frete alocado proporcionalmente');
            $table->decimal('tax_component', 14, 2)->default(0)->comment('Impostos alocados proporcionalmente');
            $table->unsignedInteger('items_count')->default(0);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['company_id', 'category_master_id', 'period'],
                'ccs_company_category_period_unique'
            );
            $table->index(['period', 'region'], 'ccs_period_region_index');
            $table->index(['category_master_id', 'period'], 'ccs_category_period_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_cost_structures');
    }
};
