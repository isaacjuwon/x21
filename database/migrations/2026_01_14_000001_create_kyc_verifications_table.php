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
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Verification details
            $table->enum('type', ['bvn', 'nin']); // Type of identification
            $table->string('id_number'); // BVN or NIN number
            $table->date('dob')->nullable(); // Date of birth
            $table->string('phone')->nullable(); // Phone number
            $table->string('email')->nullable(); // Email address
            
            // Verification status and mode
            $table->enum('status', ['pending', 'verified', 'failed'])->default('pending');
            $table->enum('verification_mode', ['automatic', 'manual'])->default('automatic');
            
            // Document and response data
            $table->string('document_path')->nullable(); // Path to uploaded document for manual verification
            $table->json('response')->nullable(); // Response from Dojah API or verification service
            $table->json('meta')->nullable(); // Additional metadata
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
