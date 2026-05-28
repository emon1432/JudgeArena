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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('contest_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('problem_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('platform_profile_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('platform_submission_id', 191);
            $table->string('author_handle', 100);
            $table->string('verdict', 50)->nullable();
            $table->string('language', 255)->nullable();
            $table->float('points', 12, 2)->nullable();
            $table->unsignedInteger('passed_test_count')->nullable();
            $table->unsignedBigInteger('time_consumed_ms')->nullable();
            $table->unsignedBigInteger('memory_consumed_bytes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->json('raw')->nullable();
            $table->string('status', 50)->default('active');
            $table->timestamps();
            $table->unique(
                ['platform_id', 'platform_submission_id'],
                'unique_platform_submission'
            );
            $table->index('contest_id');
            $table->index('problem_id');
            $table->index('platform_profile_id');
            $table->index('author_handle');
            $table->index('verdict');
            $table->index('submitted_at');
            $table->index('status');
            $table->index('last_synced_at');
            $table->index(
                ['platform_profile_id', 'verdict', 'submitted_at'],
                'platform_profile_verdict_submitted_index'
            );
            $table->index(
                ['problem_id', 'verdict'],
                'problem_verdict_index'
            );
            $table->index(
                ['contest_id', 'submitted_at'],
                'contest_submitted_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
