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
        Schema::create('share_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->unsignedBigInteger('quantity');
            $table->decimal('price_per_share', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('hold_transaction_id')->nullable();
            $table->foreign('hold_transaction_id')->references('id')->on('transactions')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_orders');
    }
};
