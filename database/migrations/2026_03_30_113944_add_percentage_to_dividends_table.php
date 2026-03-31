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
        Schema::table('dividends', function (Blueprint $table) {
            $table->decimal('percentage', 5, 2)->after('id');
            $table->decimal('share_price', 15, 2)->after('percentage');
            $table->decimal('total_amount', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dividends', function (Blueprint $table) {
            $table->dropColumn(['percentage', 'share_price']);
            $table->decimal('total_amount', 15, 2)->nullable(false)->change();
        });
    }
};
