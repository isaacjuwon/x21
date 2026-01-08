<?php

use App\Enums\Transaction\Type;
use App\Enums\Transaction\Status;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 16, 2)->default(0);
            $table->string('description')->nullable();
            $table->string('recipient')->nullable();
            $table->string('status')->default(Status::Success->value);
            $table->string('type')->default(Type::Payment->value);
            $table->string('reference')->nullable();
            $table->string('response')->nullable();
            $table->json('meta')->nullable();
            $table->string('payment_method')->nullable();
            $table->boolean('archived')->default(false)->index();
            $table->foreignId('user_id')->constrained();
            $table->nullableMorphs('transactable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};