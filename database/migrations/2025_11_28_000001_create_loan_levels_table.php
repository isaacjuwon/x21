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
        Schema::create("loan_levels", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("slug")->unique();
            $table->decimal("maximum_loan_amount", 15, 2);
            $table->integer("installment_period_months");
            $table->decimal("interest_rate", 5, 2)->comment("Annual interest rate percentage");
            $table->integer('repayments_required_for_upgrade')->default(0);
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("loan_levels");
    }
};
