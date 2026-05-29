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
        Schema::create('standing_task_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standing_id')
                ->constrained('standings')
                ->cascadeOnDelete();
            $table->foreignId('problem_id')
                ->nullable()
                ->constrained('problems')
                ->nullOnDelete();
            $table->decimal('points', 12, 2)->nullable();
            $table->unsignedInteger('penalty')->nullable();
            $table->unsignedInteger('rejected_attempt_count')->nullable();
            $table->string('result_type', 50)->nullable();
            $table->unsignedInteger('best_submission_time_seconds')->nullable();
            $table->json('metadata')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            $table->unique(['standing_id', 'problem_id'], 'unique_standing_problem_result');
            $table->index('standing_id');
            $table->index('problem_id');
            $table->index('result_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standing_task_results');
    }
};
