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
        Schema::create("loan_payments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("loan_id")->constrained()->cascadeOnDelete();
            $table->decimal("amount", 15, 2);
            $table->enum("payment_type", ["scheduled", "early", "penalty"])->default(
                "scheduled",
            );
            $table->timestamp("payment_date");
            $table->date("due_date")->nullable();
            $table->decimal("principal_amount", 15, 2);
            $table->decimal("interest_amount", 15, 2);
            $table->decimal("balance_after", 15, 2);
            $table->foreignId("wallet_transaction_id")->nullable()->constrained(
                "wallet_transactions",
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("loan_payments");
    }
};
