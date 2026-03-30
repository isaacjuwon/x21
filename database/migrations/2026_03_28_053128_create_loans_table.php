<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('outstanding_balance', 15, 2);
            $table->decimal('interest_rate', 5, 4);
            $table->unsignedInteger('repayment_term_months');
            $table->string('interest_method')->default('FlatRate');
            $table->string('status')->default('active');
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamp('eligibility_checked_at')->nullable();
            $table->boolean('eligibility_passed')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
