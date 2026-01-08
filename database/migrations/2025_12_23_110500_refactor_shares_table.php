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
        Schema::table("shares", function (Blueprint $table) {
            // Remove the unique constraint that limits one record per holder per currency
            $table->dropUnique(["holder_type", "holder_id", "currency"]);
            
            // Add approved_at timestamp to track when the holding period starts
            $table->timestamp('approved_at')->nullable()->after('status');
        });

        // For existing approved shares, set approved_at to created_at
        \App\Models\Share::where('status', \App\Enums\ShareStatus::APPROVED)
            ->whereNull('approved_at')
            ->update(['approved_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("shares", function (Blueprint $table) {
            $table->dropColumn('approved_at');
            $table->unique(["holder_type", "holder_id", "currency"]);
        });
    }
};
