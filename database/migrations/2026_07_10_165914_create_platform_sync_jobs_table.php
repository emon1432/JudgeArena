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
        Schema::create('platform_sync_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('entity', 50);
            $table->boolean('enabled')
                ->default(true);
            $table->unsignedInteger('priority')
                ->default(100);
            $table->unsignedInteger('interval_minutes');
            $table->timestamp('last_started_at')
                ->nullable();
            $table->timestamp('last_finished_at')
                ->nullable();
            $table->timestamp('last_failed_at')
                ->nullable();
            $table->timestamp('last_success_at')
                ->nullable();
            $table->text('last_error')
                ->nullable();
            $table->json('metadata')
                ->nullable();
            $table->timestamps();
            $table->unique([
                'platform_id',
                'entity',
            ]);
            $table->index([
                'enabled',
                'priority',
            ]);
            $table->index('last_success_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_sync_jobs');
    }
};
