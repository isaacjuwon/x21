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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['apartment', 'house', 'condo', 'land', 'commercial', 'other']);
            $table->enum('status', ['available', 'sold', 'rented', 'pending', 'archived'])->default('available');
            $table->enum('listing_type', ['sale', 'rent'])->default('sale');

            $table->decimal('price', 15, 2);
            $table->decimal('price_per_sqft', 10, 2)->nullable();

            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('area_sqft')->nullable();
            $table->integer('year_built')->nullable();
            $table->boolean('has_garage')->default(false);
            $table->boolean('is_furnished')->default(false);   
            $table->integer('parking_spaces')->nullable();

            $table->json('features')->nullable();
            $table->json('images')->nullable();

            $table->string('slug');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('featured_until')->nullable();

            $table->string('owner_name')->nullable();
            $table->string('owner_email')->nullable();
            $table->string('owner_phone')->nullable();

            $table->index(['type', 'status', 'listing_type']);
            $table->index(['city', 'state', 'country']);
            $table->index(['price', 'listing_type']);
            $table->index('is_featured');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};