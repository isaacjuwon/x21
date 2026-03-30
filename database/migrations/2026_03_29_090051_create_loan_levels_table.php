<?php

use App\Models\LoanLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('max_amount', 15, 2);
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('interest_rate', 5, 2);
            $table->unsignedInteger('max_term_months');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('loan_level_id')->nullable()->constrained('loan_levels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignIdFor(LoanLevel::class);
            $table->dropColumn('loan_level_id');
        });

        Schema::dropIfExists('loan_levels');
    }
};
