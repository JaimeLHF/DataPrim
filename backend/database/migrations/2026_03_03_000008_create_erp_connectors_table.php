<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_connectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            $table->string('erp_type', 100);
            // Valores suportados: 'bling', 'tinyerp', 'protheus', 'oracle', 'sap'

            $table->text('credentials');
            // JSON criptografado com Laravel encrypt() (AES-256-CBC + APP_KEY)
            // Bling:    { "client_id": "...", "client_secret": "..." }
            // TinyERP:  { "api_token": "..." }
            // Protheus: { "base_url": "...", "username": "...", "password": "..." }

            $table->json('config')->nullable();
            // Parâmetros opcionais por ERP
            // Bling:    { "company_id_bling": 12345 }
            // Protheus: { "filial": "01" }

            $table->smallInteger('sync_frequency')->unsigned()->default(360);
            // Minutos entre syncs. Padrão 360 = 6h. Mínimo recomendado: 60

            $table->timestamp('last_synced_at')->nullable();
            // Cursor da última sync bem-sucedida.
            // fetchInvoicesSince() usa este valor como filtro de data.

            $table->enum('last_sync_status', ['ok', 'error'])->nullable();
            $table->text('last_sync_error')->nullable();
            // Mensagem do último erro (stacktrace truncado a 1000 chars)

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('company_id', 'idx_connectors_company');
            $table->index(['is_active', 'last_synced_at'], 'idx_connectors_sync');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_connectors');
    }
};
