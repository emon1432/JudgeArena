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
        Schema::create('contest_rating_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')
                ->constrained('platforms')
                ->cascadeOnDelete();
            $table->foreignId('contest_id')
                ->constrained('contests')
                ->cascadeOnDelete();
            $table->foreignId('platform_profile_id')
                ->nullable()
                ->constrained('platform_profiles')
                ->nullOnDelete();
            $table->string('handle', 100);
            $table->boolean('is_rated')->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->integer('old_rating')->nullable();
            $table->integer('new_rating')->nullable();
            $table->integer('rating_change')->nullable();
            $table->integer('performance')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->json('raw')->nullable();
            $table->string('status', 50)->default('Active');
            $table->timestamps();

            $table->unique(['contest_id', 'handle'], 'unique_contest_handle');
            $table->index('contest_id');
            $table->index('platform_profile_id');
            $table->index('handle');
            $table->index('rating_change');
            $table->index('status');
            $table->index('last_synced_at');
            $table->index(['contest_id', 'rank'], 'contest_rank_index');
            $table->index(['platform_profile_id', 'contest_id'], 'platform_profile_contest_index');
            $table->index(
                ['platform_profile_id', 'new_rating'],
                'profile_rating_index'
            );
            $table->index(
                ['platform_id', 'handle'],
                'platform_handle_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contest_rating_changes');
    }
};
