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
            $table->string('entity_type', 50);
            $table->string('entity_platform_id', 191)->nullable();
            $table->string('sync_status', 20)->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(
                ['platform_id', 'entity_type', 'entity_platform_id'],
                'uq_platform_sync_states_entity'
            );
            $table->index('platform_id');
            $table->index('entity_type');
            $table->index('sync_status');
            $table->index('last_synced_at');
            $table->index('last_attempted_at');
            $table->index(
                ['platform_id', 'entity_type', 'sync_status'],
                'idx_platform_sync_states_entity_status'
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
