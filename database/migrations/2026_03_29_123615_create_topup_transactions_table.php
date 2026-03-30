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
        Schema::create('topup_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('plan'); // Morph to airtime_plans, data_plans, etc.
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // airtime, data, cable, education, electricity
            $table->decimal('amount', 15, 2);
            $table->string('recipient'); // phone number, smartcard number, meter number, etc.
            $table->string('reference')->unique();
            $table->string('api_reference')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed, reversed
            $table->text('response_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topup_transactions');
    }
};
