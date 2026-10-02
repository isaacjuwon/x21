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
        Schema::table('brands', function (Blueprint $table) {
            $table->string('vtpass_code')->nullable()->after('api_code');
        });

        Schema::table('data_plans', function (Blueprint $table) {
            $table->string('vtpass_code')->nullable()->after('api_code');
        });

        Schema::table('cable_plans', function (Blueprint $table) {
            $table->string('vtpass_code')->nullable()->after('api_code');
        });

        Schema::table('education_plans', function (Blueprint $table) {
            $table->string('vtpass_code')->nullable()->after('api_code');
        });

        Schema::table('electricity_plans', function (Blueprint $table) {
            $table->string('vtpass_code')->nullable()->after('api_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('vtpass_code');
        });

        Schema::table('data_plans', function (Blueprint $table) {
            $table->dropColumn('vtpass_code');
        });

        Schema::table('cable_plans', function (Blueprint $table) {
            $table->dropColumn('vtpass_code');
        });

        Schema::table('education_plans', function (Blueprint $table) {
            $table->dropColumn('vtpass_code');
        });

        Schema::table('electricity_plans', function (Blueprint $table) {
            $table->dropColumn('vtpass_code');
        });
    }
};
