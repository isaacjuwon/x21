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
        Schema::create('transactions', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $blueprint->decimal('amount', 16, 2);
            $blueprint->string('type'); // TransactionType enum
            $blueprint->string('status'); // TransactionStatus enum
            $blueprint->string('reference')->unique();
            $blueprint->text('notes')->nullable();
            $blueprint->json('meta')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
