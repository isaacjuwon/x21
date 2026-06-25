<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the unique constraint on user_id to allow multiple share holding
     * lots per user (lot-based tracking for accurate holding period calculation).
     */
    public function up(): void
    {
        Schema::table('share_holdings', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_holdings', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }
};
