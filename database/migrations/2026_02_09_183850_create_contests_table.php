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
        Schema::create('contests', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('platform_id')->constrained()->onDelete('cascade');

            // Contest Identification
            $table->string('platform_contest_id', 150)->comment('Unique contest ID from platform');
            $table->string('slug', 255)->nullable()->comment('URL-friendly contest identifier');
            $table->string('name', 255);

            // Contest Details
            $table->text('description')->nullable();
            $table->enum('type', ['contest', 'practice', 'challenge', 'virtual', 'rated', 'unrated'])->default('contest');
            $table->string('phase', 50)->nullable()->comment('before, coding, finished, etc.');
            $table->unsignedInteger('duration_seconds')->nullable()->comment('Contest duration in seconds');

            // Timing
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();

            // Additional Information
            $table->string('url', 500);
            $table->unsignedInteger('participant_count')->nullable()->default(0);
            $table->boolean('is_rated')->default(false);
            $table->json('tags')->nullable()->comment('Contest tags/categories');
            $table->json('raw')->nullable()->comment('Raw data from platform API');

            // Status
            $table->string('status', 50)->default('Active')->comment('active, inactive, archived');

            // Indexes
            $table->unique(['platform_id', 'platform_contest_id'], 'unique_platform_contest');
            $table->unique(['platform_id', 'slug'], 'unique_platform_contest_slug');
            $table->index('platform_id');
            $table->index('start_time');
            $table->index('end_time');
            $table->index('phase');
            $table->index('is_rated');
            $table->index('type');
            $table->index('status');
            $table->index(['platform_id', 'start_time'], 'platform_start_time_index');
            $table->index(['platform_id', 'status', 'start_time'], 'platform_status_start_time_index');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contests');
    }
};
