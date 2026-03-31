<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('airtime_plans', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
        });

        Schema::table('cable_plans', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
        });

        Schema::table('education_plans', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('airtime_plans', function (Blueprint $table) {
            $table->string('type')->nullable(false)->change();
        });

        Schema::table('cable_plans', function (Blueprint $table) {
            $table->string('type')->nullable(false)->change();
        });

        Schema::table('education_plans', function (Blueprint $table) {
            $table->string('type')->nullable(false)->change();
        });
    }
};
