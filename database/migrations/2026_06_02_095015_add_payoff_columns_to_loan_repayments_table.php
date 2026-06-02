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
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->decimal('principal_component', 15, 2)->default(0)->after('amount');
            $table->decimal('interest_component', 15, 2)->default(0)->after('principal_component');
            $table->decimal('penalty_component', 15, 2)->default(0)->after('interest_component');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->dropColumn(['principal_component', 'interest_component', 'penalty_component']);
        });
    }
};
