<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Links a refund transaction back to the original failed one
            $table->unsignedBigInteger('refund_for_id')->nullable()->after('meta');
            $table->foreign('refund_for_id')->references('id')->on('transactions')->nullOnDelete();

            // Human-readable failure reason stored on the original transaction
            $table->string('failure_reason')->nullable()->after('refund_for_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['refund_for_id']);
            $table->dropColumn(['refund_for_id', 'failure_reason']);
        });
    }
};
