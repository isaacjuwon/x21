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
        Schema::create('cable_plans', function (Blueprint $table) {
            $table->id();
             $table->string('name');
            $table->string('description', 160)->nullable();
            $table->boolean('status')->default(false);
            $table->string('api_code')->nullable();
            $table->string('service_id')->nullable();
            $table->string('reference')->nullable();
            $table->string('type')->nullable();
            $table->string('duration')->nullable();
            $table->decimal('price', 16, 2)->nullable();
            $table->decimal('discounted_price', 16, 2)->nullable();
            $table->foreignId('brand_id')->constrained();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cable_plans');
    }
};
