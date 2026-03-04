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
        Schema::create('platform_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->string('handle', 100);
            $table->string('platform_user_id', 191)->nullable();
            $table->string('name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->unsignedInteger('ranking')->nullable();
            $table->integer('rating')->nullable();
            $table->integer('total_solved')->default(0);
            $table->json('profile_data')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('profile_source', 20)->default('api');
            $table->string('visibility_status', 20)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->string('last_sync_status', 20)->nullable();
            $table->text('last_sync_error')->nullable();
            $table->unsignedInteger('last_sync_duration_ms')->nullable();
            $table->unsignedInteger('sync_attempts')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'platform_id']);
            $table->index('handle');
            $table->index(['platform_id', 'handle']);
            $table->index(['user_id', 'status']);
            $table->index(['platform_id', 'status']);
            $table->index(['platform_id', 'rating']);
            $table->index(['platform_id', 'total_solved']);
            $table->index(['platform_id', 'last_synced_at']);
            $table->index(['platform_id', 'platform_user_id']);
            $table->index('captured_at');
            $table->index('last_sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_profiles');
    }
};
