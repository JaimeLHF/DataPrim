<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const UF_TO_REGION = [
        'PR' => 'Sul', 'SC' => 'Sul', 'RS' => 'Sul',
        'SP' => 'Sudeste', 'RJ' => 'Sudeste', 'MG' => 'Sudeste', 'ES' => 'Sudeste',
        'MT' => 'Centro-Oeste', 'MS' => 'Centro-Oeste', 'GO' => 'Centro-Oeste', 'DF' => 'Centro-Oeste',
        'BA' => 'Nordeste', 'SE' => 'Nordeste', 'AL' => 'Nordeste', 'PE' => 'Nordeste',
        'PB' => 'Nordeste', 'RN' => 'Nordeste', 'CE' => 'Nordeste', 'PI' => 'Nordeste', 'MA' => 'Nordeste',
        'AM' => 'Norte', 'PA' => 'Norte', 'AC' => 'Norte', 'RO' => 'Norte',
        'RR' => 'Norte', 'AP' => 'Norte', 'TO' => 'Norte',
    ];

    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('cnpj', 14)->nullable()->unique()->after('name');
            $table->string('state', 2)->nullable()->after('region');
        });

        // Migrar registros existentes: copiar UF para state e mapear region para nome da região
        $suppliers = DB::table('suppliers')->whereNotNull('region')->get();

        foreach ($suppliers as $supplier) {
            $uf = strtoupper(trim($supplier->region));
            $region = self::UF_TO_REGION[$uf] ?? null;

            if ($region) {
                DB::table('suppliers')->where('id', $supplier->id)->update([
                    'state'  => $uf,
                    'region' => $region,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Restaurar UF na coluna region antes de remover state
        $suppliers = DB::table('suppliers')->whereNotNull('state')->get();

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->where('id', $supplier->id)->update([
                'region' => $supplier->state,
            ]);
        }

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropUnique(['cnpj']);
            $table->dropColumn(['cnpj', 'state']);
        });
    }
};
