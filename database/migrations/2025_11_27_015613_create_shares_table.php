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
        Schema::create("shares", function (Blueprint $table) {
            $table->id();
            $table->morphs("holder");
            $table->string("currency", 10)->default("SHARE");
            $table->bigInteger("quantity")->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(["holder_type", "holder_id", "currency"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("shares");
    }
};
