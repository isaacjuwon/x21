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
        Schema::create('electricity_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description', 160)->nullable();
            $table->boolean('status')->default(false);
            $table->string('api_code')->nullable();
            $table->string('service_id')->nullable();
            $table->foreignId('brand_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electricity_plans');
    }
};
