<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'airtime_plans',
            'data_plans',
            'cable_plans',
            'education_plans',
            'electricity_plans',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('name')->after('id')->nullable();
            });
        }
    }

    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'airtime_plans',
            'data_plans',
            'cable_plans',
            'education_plans',
            'electricity_plans',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};
