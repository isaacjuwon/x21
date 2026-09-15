<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('banner-popup.table_name', 'banner_popups'), function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('name', 150)->comment('Internal reference name');

            // Visibility
            $table->boolean('is_active')->default(false);

            // Trigger behaviour
            $table->enum('trigger', ['page_load', 'delay', 'exit_intent'])->default('page_load');
            $table->unsignedSmallInteger('trigger_delay')->nullable()
                ->comment('Seconds before showing (only used when trigger = delay)');

            // Display rules
            $table->json('target_pages')->nullable()
                ->comment('Array of route names. NULL means all pages.');
            $table->string('link_url')->nullable();
            $table->string('link_target', 10)->default('_self');

            // Scheduling
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Frequency: 0=always, 1/3/7/30=days between shows, -1=only once
            $table->smallInteger('show_frequency')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('banner-popup.table_name', 'banner_popups'));
    }
};
