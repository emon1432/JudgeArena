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
        Schema::create('platform_sync_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')
                ->constrained('platforms')
                ->cascadeOnDelete();
            $table->string('resource', 100);
            $table->text('cursor')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('next_allowed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->json('metadata')->nullable();
            $table->string('status', 50)->default('Active');
            $table->timestamps();
            $table->unique(
                ['platform_id', 'resource'],
                'unique_platform_resource_sync_state'
            );
            $table->index('platform_id');
            $table->index('resource');
            $table->index('status');
            $table->index('last_synced_at');
            $table->index('next_allowed_at');
            $table->index(
                ['status', 'next_allowed_at'],
                'status_next_allowed_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_sync_states');
    }
};
