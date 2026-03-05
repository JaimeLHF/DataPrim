<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('cnpj', 18)->unique()->nullable()->after('name');
            $table->string('segment', 100)->nullable()->after('region')
                ->comment('Segmento da empresa: Móveis, Construção Civil, etc.');
            $table->char('state', 2)->nullable()->after('segment')
                ->comment('UF da sede da empresa');
            $table->boolean('is_benchmark_participant')->default(true)->after('state')
                ->comment('Opt-in para participar do pool de benchmark');
            $table->uuid('benchmark_anonymized_id')->nullable()->after('is_benchmark_participant')
                ->comment('UUID anônimo rotativo para pool de benchmark');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'cnpj',
                'segment',
                'state',
                'is_benchmark_participant',
                'benchmark_anonymized_id',
            ]);
        });
    }
};
