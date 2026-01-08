<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("loans", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("loan_level_id")->constrained("loan_levels");
            $table->decimal("amount", 15, 2);
            $table->decimal("interest_rate", 5, 2);
            $table->integer("installment_months");
            $table->decimal("monthly_payment", 15, 2);
            $table->decimal("total_repayment", 15, 2);
            $table->decimal("amount_paid", 15, 2)->default(0);
            $table->decimal("balance_remaining", 15, 2);
            $table->integer("shares_required")->default(0);
            $table->decimal("shares_value_at_application", 15, 2)->default(0);
            $table->enum("status", [
                "pending",
                "approved",
                "disbursed",
                "active",
                "fully_paid",
                "defaulted",
            ])->default("pending");
            $table->timestamp("applied_at")->nullable();
            $table->timestamp("approved_at")->nullable();
            $table->timestamp("disbursed_at")->nullable();
            $table->timestamp("fully_paid_at")->nullable();
            $table->date("next_payment_date")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("loans");
    }
};
