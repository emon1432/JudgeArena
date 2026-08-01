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
            $table->json('raw')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['platform_id', 'handle'], 'uq_platform_profiles_platform_handle');
            $table->index(['user_id', 'platform_id'], 'idx_platform_profiles_user_platform');
            $table->index('handle');
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
