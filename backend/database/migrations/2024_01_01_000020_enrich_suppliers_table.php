<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('region');
            $table->string('contact_email')->nullable()->after('contact_name');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->integer('payment_terms')->nullable()->after('contact_phone')
                ->comment('Prazo padrão de pagamento em dias (ex: 30, 45, 60)');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'contact_email', 'contact_phone', 'payment_terms']);
        });
    }
};
