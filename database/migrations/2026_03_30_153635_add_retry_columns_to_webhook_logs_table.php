<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts')->default(0)->after('status');
            $table->unsignedTinyInteger('max_attempts')->default(3)->after('attempts');
            $table->timestamp('next_retry_at')->nullable()->after('max_attempts');
            $table->string('idempotency_key')->nullable()->unique()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropColumn(['attempts', 'max_attempts', 'next_retry_at', 'idempotency_key']);
        });
    }
};
