<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug', 100)->nullable()->unique()->after('cnpj');
            $table->enum('plan', ['trial', 'starter', 'pro', 'enterprise'])->default('trial')->after('slug');
            $table->boolean('is_active')->default(true)->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['slug', 'plan', 'is_active']);
        });
    }
};
