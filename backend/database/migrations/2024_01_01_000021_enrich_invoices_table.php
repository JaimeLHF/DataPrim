<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('delivery_date')->nullable()->after('issue_date')
                ->comment('Data real de entrega na fábrica (para cálculo de lead time)');
            $table->integer('payment_terms')->nullable()->after('tax_value')
                ->comment('Prazo de pagamento negociado em dias');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['delivery_date', 'payment_terms']);
        });
    }
};
