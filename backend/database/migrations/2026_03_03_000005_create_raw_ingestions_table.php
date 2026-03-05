<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_ingestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('channel', ['xml_upload', 'api_push', 'webhook', 'connector'])->default('xml_upload');
            $table->string('source', 100)->nullable();  // "bling", "protheus", "oracle"
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->longText('payload');                 // XML ou JSON bruto
            $table->string('payload_hash', 64)->nullable(); // SHA-256 para deduplicação
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['company_id', 'status'], 'idx_ingestion_company_status');
            $table->index('payload_hash', 'idx_ingestion_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_ingestions');
    }
};
