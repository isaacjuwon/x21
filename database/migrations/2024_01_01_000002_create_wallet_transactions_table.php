<?php

declare(strict_types=1);

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
        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->morphs('loggable');
            $table->string('status', 20)->default('success');
            $table->decimal('from_balance', 16, 2);
            $table->decimal('to_balance', 16, 2);
            $table->string('wallet_type', 20);
            $table->string('ip_address', 45)->nullable();
            $table->decimal('amount', 16, 2);
            $table->text('notes')->nullable();
            $table->string('reference', 50)->unique();
            $table->string('transaction_type', 20); // increment, decrement
            $table->timestamps();

            $table->index(['wallet_type']);
            $table->index(['status']);
            $table->index(['transaction_type']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
