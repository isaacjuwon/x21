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
        Schema::create('plan_provider_codes', function (Blueprint $table) {
            $table->id();
            $table->morphs('planable');
            $table->string('provider');
            $table->string('code');
            $table->timestamps();
            $table->unique(['planable_type', 'planable_id', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_provider_codes');
    }
};
