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
        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('contest_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('platform_problem_id', 191);
            $table->string('slug')->nullable();
            $table->string('name');
            $table->string('code', 64)->nullable();
            $table->integer('rating')->nullable();
            $table->float('points', 8, 2)->nullable();
            $table->unsignedInteger('time_limit_ms')->nullable();
            $table->unsignedInteger('memory_limit_mb')->nullable();
            $table->unsignedInteger('total_submissions')->default(0);
            $table->unsignedInteger('accepted_submissions')->default(0);
            $table->unsignedInteger('solved_count')->default(0);
            $table->json('tags')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('editorial_url', 500)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->json('raw')->nullable();
            $table->string('status', 50)
                ->default('Active');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(
                ['platform_id', 'platform_problem_id'],
                'unique_platform_problem'
            );
            $table->index('slug');
            $table->index('contest_id');
            $table->index('code');
            $table->index('rating');
            $table->index('status');
            $table->index('solved_count');
            $table->index('last_synced_at');
            $table->index(
                ['platform_id', 'rating'],
                'platform_rating_index'
            );
            $table->index(
                ['contest_id', 'code'],
                'contest_code_index'
            );
            $table->index(
                ['platform_id', 'status', 'updated_at'],
                'platform_status_updated_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};
