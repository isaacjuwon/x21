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
        Schema::create("dividends", function (Blueprint $table) {
            $table->id();
            $table->string("currency", 10)->default("SHARE");
            $table->decimal("amount_per_share", 15, 6);
            $table->string("type")->default("cash");
            $table->date("declaration_date");
            $table->date("ex_dividend_date");
            $table->date("record_date");
            $table->date("payment_date");
            $table->boolean("paid_out")->default(false);
            $table->json("metadata")->nullable();
            $table->timestamps();

            $table->index("currency");
            $table->index("ex_dividend_date");
            $table->index("payment_date");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("dividends");
    }
};
