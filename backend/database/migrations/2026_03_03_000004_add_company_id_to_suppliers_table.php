<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Adiciona company_id logo após o id para escopo por tenant
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            // Remove UNIQUE simples em cnpj (criado por migration anterior)
            $table->dropUnique(['cnpj']);

            $table->index('company_id', 'idx_supplier_company');
        });

        // Unique composta separada para maior controle
        Schema::table('suppliers', function (Blueprint $table) {
            $table->unique(['cnpj', 'company_id'], 'uq_supplier_cnpj_company');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex('idx_supplier_company');
            $table->dropUnique('uq_supplier_cnpj_company');
            $table->dropColumn('company_id');

            // Restaurar unique simples em cnpj
            $table->unique('cnpj');
        });
    }
};
