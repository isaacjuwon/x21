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
        Schema::create('dividend_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dividend_id')->constrained()->cascadeOnDelete();
            $table->morphs('holder');
            $table->bigInteger('shares_held');
            $table->decimal('amount_per_share', 15, 6);
            $table->decimal('total_amount', 15, 2);
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dividend_payments');
    }
};
