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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('service_type'); // airtime, data, cable, electricity, education, exam
            $table->string('name');
            $table->string('type')->nullable(); // category/group: sme, gifting, corporate, prepaid, postpaid, etc.
            $table->string('api_code')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->string('duration')->nullable();
            $table->boolean('status')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['service_type', 'status']);
            $table->index(['brand_id', 'service_type']);
            $table->index('api_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
