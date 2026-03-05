<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('erp_type', 50);          // 'bling', 'tinyerp', 'netsuite'
            $table->string('slug', 100)->unique();    // 'moveis-ruiz-bling' → URL do webhook
            $table->string('secret', 255);            // secret para HMAC (armazenado em texto -- considerar criptografia em produção)
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_received_at')->nullable();
            $table->timestamps();

            $table->index('company_id', 'idx_webhook_company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_configs');
    }
};
