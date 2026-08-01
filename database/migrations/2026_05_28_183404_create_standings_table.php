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
        Schema::create('standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('contest_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('platform_profile_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('participant_key', 255);
            $table->string('participant_type', 50)->nullable();
            $table->string('participant_name', 255)->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->float('points', 12, 2)->nullable();
            $table->unsignedInteger('penalty')->nullable();
            $table->unsignedInteger('successful_hack_count')->nullable();
            $table->unsignedInteger('unsuccessful_hack_count')->nullable();
            $table->unsignedInteger('last_submission_time_seconds')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->json('raw')->nullable();
            $table->string('status', 50)->default('Active');
            $table->timestamps();
            $table->unique(
                ['contest_id', 'participant_key'],
                'uq_standings_contest_participant_key'
            );
            $table->index('contest_id');
            $table->index('platform_profile_id');
            $table->index('participant_key');
            $table->index('participant_type');
            $table->index('status');
            $table->index('last_synced_at');
            $table->index(
                ['contest_id', 'rank'],
                'idx_standings_contest_rank'
            );
            $table->index(
                ['contest_id', 'points', 'penalty'],
                'idx_standings_contest_points_penalty'
            );
            $table->index(
                ['platform_profile_id', 'contest_id'],
                'idx_standings_profile_contest'
            );
            $table->index(
                ['platform_id', 'contest_id'],
                'idx_standings_platform_contest'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standings');
    }
};
