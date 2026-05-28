<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Standing extends Model
{
    // Schema::create('standings', function (Blueprint $table) {
    //         $table->id();
    //         $table->foreignId('platform_id')
    //             ->constrained()
    //             ->cascadeOnDelete();
    //         $table->foreignId('contest_id')
    //             ->constrained()
    //             ->cascadeOnDelete();
    //         $table->foreignId('platform_profile_id')
    //             ->nullable()
    //             ->constrained()
    //             ->nullOnDelete();
    //         $table->string('participant_key', 255);
    //         $table->string('participant_type', 50)->nullable();
    //         $table->string('participant_name', 255)->nullable();
    //         $table->unsignedInteger('rank')->nullable();
    //         $table->float('points', 12, 2)->nullable();
    //         $table->unsignedInteger('penalty')->nullable();
    //         $table->unsignedInteger('successful_hack_count')->nullable();
    //         $table->unsignedInteger('unsuccessful_hack_count')->nullable();
    //         $table->unsignedInteger('last_submission_time_seconds')->nullable();
    //         $table->timestamp('last_synced_at')->nullable();
    //         $table->json('metadata')->nullable();
    //         $table->json('raw')->nullable();
    //         $table->string('status', 50)->default('Active');
    //         $table->timestamps();
    //         $table->unique(
    //             ['contest_id', 'participant_key'],
    //             'unique_contest_participant'
    //         );
    //         $table->index('contest_id');
    //         $table->index('platform_profile_id');
    //         $table->index('participant_key');
    //         $table->index('participant_type');
    //         $table->index('status');
    //         $table->index('last_synced_at');
    //         $table->index(
    //             ['contest_id', 'rank'],
    //             'contest_rank_index'
    //         );
    //         $table->index(
    //             ['contest_id', 'points', 'penalty'],
    //             'contest_points_penalty_index'
    //         );
    //         $table->index(
    //             ['platform_profile_id', 'contest_id'],
    //             'platform_profile_contest_index'
    //         );
    //         $table->index(
    //             ['platform_id', 'contest_id'],
    //             'platform_contest_index'
    //         );
    //     });

    protected $fillable = [
        'platform_id',
        'contest_id',
        'platform_profile_id',
        'participant_key',
        'participant_type',
        'participant_name',
        'rank',
        'points',
        'penalty',
        'successful_hack_count',
        'unsuccessful_hack_count',
        'last_submission_time_seconds',
        'last_synced_at',
        'metadata',
        'raw',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
        'raw' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function platformProfile()
    {
        return $this->belongsTo(PlatformProfile::class);
    }
}
