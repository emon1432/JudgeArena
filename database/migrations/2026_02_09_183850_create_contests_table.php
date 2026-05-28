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
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('platform_contest_id', 150);
            $table->string('slug')->nullable();
            $table->string('name');
            $table->string('type', 50)->nullable();
            $table->string('phase', 50)->nullable();
            $table->boolean('is_rated')->default(false);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('url', 500)->nullable();
            $table->unsignedInteger('participant_count')
                ->nullable()
                ->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->json('raw')->nullable();
            $table->string('status', 50)
                ->default('Active');
            $table->timestamps();
            $table->unique(
                ['platform_id', 'platform_contest_id'],
                'unique_platform_contest'
            );
            $table->index('slug');
            $table->index('phase');
            $table->index('status');
            $table->index('is_rated');
            $table->index('start_time');
            $table->index('end_time');
            $table->index('last_synced_at');
            $table->index(
                ['platform_id', 'start_time'],
                'platform_start_time_index'
            );
            $table->index(
                ['platform_id', 'status', 'start_time'],
                'platform_status_start_time_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contests');
    }
};
