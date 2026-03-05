<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('source_system', 100)->nullable()->after('company_id');
            $table->string('source_id', 255)->nullable()->after('source_system');

            // Unique composta: mesma nota de um mesmo ERP na mesma empresa não pode duplicar
            $table->unique(['company_id', 'source_system', 'source_id'], 'uq_invoice_source');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('uq_invoice_source');
            $table->dropColumn(['source_system', 'source_id']);
        });
    }
};
