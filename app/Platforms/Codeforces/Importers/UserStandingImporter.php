<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\UserStandingImporter as UserStandingImporterContract;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemResultDTO;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Enums\SubmissionVerdict;
use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\Problem;
use App\Models\Standing;
use App\Models\StandingTaskResult;
use App\Models\Submission;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use App\Services\StandingsCacheService;
use Illuminate\Support\Str;
use Throwable;

class UserStandingImporter implements UserStandingImporterContract
{
    public function __construct(
        private readonly Standing $standingModel,
        private readonly StandingTaskResult $standingTaskResultModel,
        private readonly Problem $problemModel,
        private readonly Contest $contestModel,
        private readonly Platform $platformModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly ContestRatingChange $contestRatingChangeModel,
        private readonly Submission $submissionModel,
        private readonly CodeforcesAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
        private readonly StandingsCacheService $standingsCacheService,
    ) {}

    public function import(?string $handle = null, ?callable $onProgress = null): ImportResult
    {
        $result = new ImportResult;
        $platformSlug = 'codeforces';

        $platform = $this->platformModel->newQuery()
            ->where('slug', $platformSlug)
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error('User standings import failed: platform not found', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'message' => 'Platform "'.$platformSlug.'" not found in database',
            ]);

            return $result;
        }

        $query = $this->platformProfileModel->newQuery()
            ->where('platform_id', $platform->id)
            ->active();

        if ($handle !== null && trim($handle) !== '') {
            $query->whereRaw('LOWER(handle) = ?', [mb_strtolower(trim($handle))]);
        }

        $profiles = $query->get();
        $totalProfiles = $profiles->count();
        $result->incrementChecked($totalProfiles);

        if ($onProgress !== null) {
            $onProgress($totalProfiles, 0, 'Starting Codeforces standings sync...');
        }

        $this->standingsCacheService->warmupPlatform($platformSlug);

        $platformProfilesByHandle = $this->platformProfilesByHandle((int) $platform->id);

        foreach ($profiles as $index => $profile) {
            $normalizedHandle = mb_strtolower(trim((string) $profile->handle));

            if ($onProgress !== null) {
                $onProgress($totalProfiles, $index + 1, "Standings: {$profile->handle}");
            }

            if ($normalizedHandle === '') {
                $result->incrementSkipped();

                continue;
            }

            $isSynced = $this->platformSyncStateService->isSynced(
                $platform,
                PlatformSyncEntityType::UserStandings,
                $normalizedHandle
            );

            // If handle is not explicitly specified and profile user standings were already synced, skip!
            if ($handle === null && $isSynced) {
                $result->incrementSkipped();

                continue;
            }

            if ($handle !== null) {
                $existingState = $this->platformSyncStateService->findState(
                    $platform,
                    PlatformSyncEntityType::UserStandings,
                    $normalizedHandle
                );
                if ($existingState !== null) {
                    $this->platformSyncStateService->resetForRetry($existingState);
                }
            }

            $syncState = $this->platformSyncStateService->markSyncing(
                $platform,
                PlatformSyncEntityType::UserStandings,
                $normalizedHandle,
                [
                    'profile_id' => $profile->id,
                    'handle' => $normalizedHandle,
                    'platform_slug' => $platformSlug,
                ]
            );

            if ($syncState === null) {
                $result->incrementSkipped();

                continue;
            }

            $profilesForBatch = $handle !== null
                ? [$normalizedHandle => $profile]
                : $platformProfilesByHandle;

            try {
                $ratingContestIds = $this->contestRatingChangeModel->newQuery()
                    ->where('platform_id', $platform->id)
                    ->whereRaw('LOWER(handle) = ?', [$normalizedHandle])
                    ->distinct()
                    ->pluck('contest_id')
                    ->toArray();

                $submissionContestIds = $this->submissionModel->newQuery()
                    ->where('platform_id', $platform->id)
                    ->whereRaw('LOWER(author_handle) = ?', [$normalizedHandle])
                    ->whereNotNull('contest_id')
                    ->where(function ($q) {
                        $q->whereNull('raw->author->participantType')
                            ->orWhereRaw("UPPER(JSON_UNQUOTE(JSON_EXTRACT(raw, '$.author.participantType'))) = 'CONTESTANT'");
                    })
                    ->distinct()
                    ->pluck('contest_id')
                    ->toArray();

                // Limitation: Discovering participated contests via rating changes and submissions
                // is a practical strategy but not necessarily complete. E.g. unrated contests
                // where the user made no submissions won't be found.
                $allParticipatedContestIds = array_values(array_unique(array_filter(array_merge($ratingContestIds, $submissionContestIds))));
                if (empty($allParticipatedContestIds)) {
                    $this->platformSyncStateService->markSynced($syncState, [
                        'profile_id' => $profile->id,
                        'handle' => $normalizedHandle,
                        'status' => 'no_contests_found',
                    ]);

                    continue;
                }

                $existingContestIds = $this->standingModel->newQuery()
                    ->where('platform_id', $platform->id)
                    ->where('platform_profile_id', $profile->id)
                    ->whereIn('contest_id', $allParticipatedContestIds)
                    ->pluck('contest_id')
                    ->toArray();

                $missingContestIds = array_diff($allParticipatedContestIds, $existingContestIds);

                if (! empty($missingContestIds)) {
                    $contests = $this->contestModel->newQuery()
                        ->whereIn('id', $missingContestIds)
                        ->whereNotNull('platform_contest_id')
                        ->get();

                    $chunks = $contests->chunk(10);
                    $totalChunks = $chunks->count();

                    foreach ($chunks as $chunkIndex => $contestChunk) {
                        if ($onProgress !== null) {
                            $onProgress(
                                $totalProfiles,
                                $index + 1,
                                "Standings {$profile->handle}: batch ".($chunkIndex + 1)."/{$totalChunks}"
                            );
                        }

                        $this->processContestBatch(
                            $contestChunk,
                            $platformSlug,
                            $profilesForBatch,
                            $result
                        );

                        gc_collect_cycles();
                    }
                }

                // Reconcile any missing rated contest standings using contest_rating_changes
                $this->reconcileMissingRatedStandings($platform, $profile, $result);

                $this->platformSyncStateService->markSynced($syncState, [
                    'profile_id' => $profile->id,
                    'handle' => $normalizedHandle,
                    'status' => 'synced',
                ]);
            } catch (Throwable $e) {
                $result->incrementFailed();

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'profile_id' => $profile->id,
                    'handle' => $normalizedHandle,
                ]);

                app(ApplicationLogger::class)->error('User standings sync failed', [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                    'profile_id' => $profile->id,
                    'handle' => $normalizedHandle,
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], $e);
            }
        }

        $result->metadata = array_merge(
            $result->metadata,
            [
                'platform' => $platformSlug,
                'entity' => 'user_standing',
            ]
        );

        return $result;
    }

    /**
     * @param \Illuminate\Support\Collection<int, Contest> $contestChunk
     * @param array<string, PlatformProfile> $platformProfilesByHandle
     */
    private function processContestBatch(
        \Illuminate\Support\Collection $contestChunk,
        string $platformSlug,
        array $platformProfilesByHandle,
        ImportResult $result
    ): void {
        $platformContestIds = $contestChunk->pluck('platform_contest_id')
            ->filter()
            ->unique()
            ->values()
            ->map('strval')
            ->all();

        if (empty($platformContestIds)) {
            return;
        }

        // 1. Fetch compressed binaries in parallel from cache
        $binariesMap = $this->standingsCacheService->getMultipleBinaries($platformSlug, $platformContestIds);

        // 2. Prepare bulk records sequentially to keep memory minimal (< 40 MB)
        $standingRows = [];
        $pendingTaskResults = [];

        foreach ($contestChunk as $contest) {
            $cId = (string) $contest->platform_contest_id;
            $binary = $binariesMap[$cId] ?? null;
            unset($binariesMap[$cId]);

            $standings = null;
            if ($binary !== null && $binary !== '') {
                $standings = $this->standingsCacheService->decodeBinary($platformSlug, $cId, $binary);
                unset($binary);
            }

            // Fallback to adapter for any contest missing from remote cache
            if (! $standings instanceof ContestStandingsDTO) {
                try {
                    $standings = $this->adapter->getUserStandings($cId);
                } catch (Throwable $e) {
                    app(ApplicationLogger::class)->warning('Codeforces standings fetch fallback failed', [
                        'category' => 'import',
                        'platform' => $platformSlug,
                        'source' => self::class,
                        'contest_id' => $contest->id,
                        'platform_contest_id' => $cId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (! $standings instanceof ContestStandingsDTO) {
                continue;
            }

            $result->incrementFetched(count($standings->rows));
            $contestProblemsByPlatformProblemId = $this->ensureContestProblems($contest, $standings);

            foreach ($standings->rows as $participant) {
                if (! $participant instanceof ParticipantDTO) {
                    continue;
                }

                $identity = $this->participantIdentity($participant);
                $participantType = strtoupper((string) ($identity['type'] ?? ''));
                if ($participantType !== 'CONTESTANT') {
                    continue;
                }

                $platformProfile = $identity['handle'] !== null
                    ? ($platformProfilesByHandle[mb_strtolower($identity['handle'])] ?? null)
                    : null;

                if ($platformProfile === null) {
                    continue;
                }

                $compositeKey = $contest->id.'_'.$identity['key'];
                $standingRows[] = [
                    'platform_id' => $contest->platform_id,
                    'contest_id' => $contest->id,
                    'platform_profile_id' => $platformProfile->id,
                    'participant_key' => $identity['key'],
                    'participant_type' => $identity['type'],
                    'participant_name' => $identity['name'],
                    'rank' => $participant->rank,
                    'points' => $participant->points,
                    'penalty' => $participant->penalty,
                    'successful_hack_count' => $this->rawInt($participant->raw, 'successfulHackCount'),
                    'unsuccessful_hack_count' => $this->rawInt($participant->raw, 'unsuccessfulHackCount'),
                    'last_submission_time_seconds' => $this->rawInt($participant->raw, 'lastSubmissionTimeSeconds'),
                    'last_synced_at' => now(),
                    'metadata' => json_encode([
                        'source' => 'user-standings-import',
                        'platform' => $platformSlug,
                        'contest_platform_id' => $contest->platform_contest_id,
                        'contest_name' => $contest->name,
                        'members' => $participant->members,
                        'synced_at' => now()->toIso8601String(),
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'raw' => json_encode($participant->raw ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                foreach ($participant->problemResults as $index => $problemResult) {
                    if (! $problemResult instanceof ProblemResultDTO) {
                        continue;
                    }

                    $isAttempted = ($problemResult->points !== null && (float) $problemResult->points > 0.0)
                        || ($problemResult->rejectedAttemptCount !== null && (int) $problemResult->rejectedAttemptCount > 0)
                        || ($problemResult->bestSubmissionTimeSeconds !== null);

                    if (! $isAttempted) {
                        continue;
                    }

                    $problemDto = $standings->problems[$index] ?? null;
                    $problemPlatformId = $problemDto?->platformProblemId;
                    $problem = is_string($problemPlatformId)
                        ? ($contestProblemsByPlatformProblemId[$problemPlatformId] ?? null)
                        : null;

                    if ($problem === null) {
                        $result->incrementMetadata('task_results_skipped');
                        continue;
                    }

                    $pendingTaskResults[$compositeKey][] = [
                        'problem_id' => $problem->id,
                        'points' => $problemResult->points,
                        'penalty' => $problemResult->penalty,
                        'rejected_attempt_count' => $problemResult->rejectedAttemptCount,
                        'result_type' => $problemResult->type,
                        'best_submission_time_seconds' => $problemResult->bestSubmissionTimeSeconds,
                        'metadata' => json_encode([
                            'source' => 'user-standings-import',
                            'platform' => $platformSlug,
                            'contest_platform_id' => $contest->platform_contest_id,
                            'problem_platform_id' => $problemPlatformId,
                            'problem_index' => $index,
                            'synced_at' => now()->toIso8601String(),
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'raw' => json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Immediately free the entire contest object and its 25,000 participant rows
            unset($standings);
        }

        if (empty($standingRows)) {
            return;
        }

        // 3. Bulk Upsert Standings
        $contestIdsInBatch = array_values(array_unique(array_column($standingRows, 'contest_id')));
        $participantKeysInBatch = array_values(array_unique(array_column($standingRows, 'participant_key')));

        $existingCount = $this->standingModel->newQuery()
            ->whereIn('contest_id', $contestIdsInBatch)
            ->whereIn('participant_key', $participantKeysInBatch)
            ->count();

        $this->standingModel->newQuery()->upsert(
            $standingRows,
            ['contest_id', 'participant_key'],
            [
                'platform_id',
                'platform_profile_id',
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
                'updated_at',
            ]
        );

        $newCount = count($standingRows) - $existingCount;
        if ($newCount > 0) {
            $result->incrementCreated($newCount);
        }
        if ($existingCount > 0) {
            $result->incrementUpdated($existingCount);
        }

        // 4. Query upserted standing IDs
        $standingRecords = $this->standingModel->newQuery()
            ->whereIn('contest_id', $contestIdsInBatch)
            ->whereIn('participant_key', $participantKeysInBatch)
            ->get(['id', 'contest_id', 'participant_key']);

        $standingIdMap = [];
        foreach ($standingRecords as $sr) {
            $standingIdMap[$sr->contest_id.'_'.$sr->participant_key] = $sr->id;
        }

        // 5. Build and bulk upsert task results
        $taskResultRows = [];
        foreach ($pendingTaskResults as $compositeKey => $taskRows) {
            $standingId = $standingIdMap[$compositeKey] ?? null;
            if ($standingId === null) {
                continue;
            }

            foreach ($taskRows as $tr) {
                $tr['standing_id'] = $standingId;
                $taskResultRows[] = $tr;
            }
        }

        if (! empty($taskResultRows)) {
            $this->standingTaskResultModel->newQuery()->upsert(
                $taskResultRows,
                ['standing_id', 'problem_id'],
                [
                    'points',
                    'penalty',
                    'rejected_attempt_count',
                    'result_type',
                    'best_submission_time_seconds',
                    'metadata',
                    'raw',
                    'updated_at',
                ]
            );

            $result->incrementMetadata('task_results_created', count($taskResultRows));
        }
    }

    private function platformProfilesByHandle(int $platformId): array
    {
        $profiles = $this->platformProfileModel->newQuery()
            ->where('platform_id', $platformId)
            ->get();

        $indexedProfiles = [];

        foreach ($profiles as $profile) {
            $handle = mb_strtolower(trim((string) $profile->handle));

            if ($handle === '') {
                continue;
            }

            $indexedProfiles[$handle] = $profile;
        }

        return $indexedProfiles;
    }

    private function contestProblemsByPlatformProblemId(int $contestId): array
    {
        $problems = $this->problemModel->newQuery()
            ->where('contest_id', $contestId)
            ->get();

        $indexedProblems = [];

        foreach ($problems as $problem) {
            $platformProblemId = trim((string) $problem->platform_problem_id);

            if ($platformProblemId === '') {
                continue;
            }

            $indexedProblems[$platformProblemId] = $problem;
        }

        return $indexedProblems;
    }

    private function ensureContestProblems(Contest $contest, ContestStandingsDTO $standings): array
    {
        $existing = $this->contestProblemsByPlatformProblemId((int) $contest->id);

        foreach ($standings->problems as $problemDto) {
            $problemPlatformId = (string) ($problemDto->platformProblemId ?? '');
            if ($problemPlatformId === '') {
                continue;
            }

            if (! isset($existing[$problemPlatformId])) {
                $problem = $this->problemModel->newQuery()->updateOrCreate(
                    [
                        'platform_id' => $contest->platform_id,
                        'platform_problem_id' => $problemPlatformId,
                    ],
                    [
                        'contest_id' => $contest->id,
                        'slug' => Str::slug(($problemDto->title ?? 'problem').'-'.$problemPlatformId),
                        'name' => $problemDto->title ?? '',
                        'code' => $problemDto->code ?? null,
                        'points' => $problemDto->points ?? null,
                        'rating' => $problemDto->rating ?? null,
                        'time_limit_ms' => $problemDto->timeLimit ?? null,
                        'memory_limit_mb' => $problemDto->memoryLimit ?? null,
                        'solved_count' => $problemDto->solvedCount ?? 0,
                        'tags' => $problemDto->tags ?? [],
                        'url' => $problemDto->url ?? null,
                        'last_synced_at' => now(),
                        'metadata' => [
                            'source' => 'standings-jit-sync',
                            'platform' => 'codeforces',
                            'contest_platform_id' => $contest->platform_contest_id,
                        ],
                        'raw' => $problemDto->raw ?? [],
                        'status' => 'Active',
                    ]
                );

                // Self-healing backlink: link any submissions that had problem_id = null
                $this->submissionModel->newQuery()
                    ->where('platform_id', $contest->platform_id)
                    ->whereNull('problem_id')
                    ->where(function ($q) use ($contest) {
                        $q->where('contest_id', $contest->id)
                            ->orWhere('metadata->contest_platform_id', $contest->platform_contest_id);
                    })
                    ->where(function ($q) use ($problemPlatformId, $problemDto) {
                        $q->where('metadata->problem_platform_id', $problemPlatformId);
                        if (! empty($problemDto->code)) {
                            $q->orWhere('metadata->problem_platform_id', $problemDto->code);
                        }
                    })
                    ->update([
                        'contest_id' => $contest->id,
                        'problem_id' => $problem->id,
                    ]);

                $existing[$problemPlatformId] = $problem;
            }
        }

        return $existing;
    }

    private function participantIdentity(ParticipantDTO $participant): array
    {
        $raw = $participant->raw;
        $party = is_array($raw['party'] ?? null) ? $raw['party'] : [];
        $members = $participant->members;

        if ($members === [] && isset($raw['userScreenName'])) {
            $members[] = [
                'handle' => $raw['userScreenName'],
                'name' => $raw['userName'] ?? null,
            ];
        }

        $handles = [];
        foreach ($members as $member) {
            if (! is_array($member)) {
                continue;
            }

            $handle = trim((string) ($member['handle'] ?? ''));

            if ($handle !== '') {
                $handles[] = $handle;
            }
        }

        $teamId = $party['teamId'] ?? $raw['teamId'] ?? null;
        $teamName = trim((string) ($party['teamName'] ?? $raw['teamName'] ?? ''));
        $isTeam = (bool) ($raw['isTeam'] ?? false) || $teamId !== null || $teamName !== '';
        $participantType = $party['participantType'] ?? ($isTeam ? 'TEAM' : null);

        $firstHandle = $handles[0] ?? null;

        if (! $isTeam && count($handles) === 1) {
            $handle = $handles[0];

            return [
                'key' => $handle,
                'type' => $participantType ?? 'CONTESTANT',
                'name' => $this->firstMemberName($members) ?? $handle,
                'handle' => $handle,
            ];
        }

        if ($teamId !== null && $teamId !== '') {
            return [
                'key' => 'team:'.trim((string) $teamId),
                'type' => $participantType ?? 'TEAM',
                'name' => $teamName !== '' ? $teamName : $this->teamNameFromHandles($handles),
                'handle' => $firstHandle,
            ];
        }

        if ($teamName !== '') {
            return [
                'key' => 'team:'.$this->slugKey($teamName),
                'type' => $participantType ?? 'TEAM',
                'name' => $teamName,
                'handle' => $firstHandle,
            ];
        }

        if ($handles !== []) {
            sort($handles);

            return [
                'key' => 'team:'.$this->slugKey(implode(':', $handles)),
                'type' => $participantType ?? 'TEAM',
                'name' => $this->teamNameFromHandles($handles),
                'handle' => $firstHandle,
            ];
        }

        $encodedRaw = json_encode($raw);
        $hashSource = $encodedRaw !== false && $encodedRaw !== '' ? $encodedRaw : (string) $participant->rank;

        return [
            'key' => 'participant:'.sha1($hashSource),
            'type' => $participantType,
            'name' => null,
            'handle' => null,
        ];
    }

    private function reconcileMissingRatedStandings(
        Platform $platform,
        PlatformProfile $profile,
        ImportResult $result
    ): void {
        $missingRatingChanges = $this->contestRatingChangeModel->newQuery()
            ->where('platform_id', $platform->id)
            ->where('platform_profile_id', $profile->id)
            ->whereNotIn('contest_id', function ($query) use ($platform, $profile) {
                $query->select('contest_id')
                    ->from('standings')
                    ->where('platform_id', $platform->id)
                    ->where('platform_profile_id', $profile->id);
            })
            ->get();

        if ($missingRatingChanges->isEmpty()) {
            return;
        }

        $standingRows = [];
        $taskResultRowsByContestId = [];

        foreach ($missingRatingChanges as $rc) {
            $contest = $this->contestModel->newQuery()->find($rc->contest_id);
            $contestStartTime = $contest?->start_time;
            $contestEndTime = $contest?->end_time
                ?? ($contestStartTime && $contest?->duration_seconds ? $contestStartTime->copy()->addSeconds((int) $contest->duration_seconds) : null);

            $problems = $this->problemModel->newQuery()
                ->where('contest_id', $rc->contest_id)
                ->get();

            $userSubmissions = $this->submissionModel->newQuery()
                ->where('platform_id', $platform->id)
                ->where('platform_profile_id', $profile->id)
                ->where('contest_id', $rc->contest_id)
                ->orderBy('submitted_at', 'asc')
                ->get();

            // Strict Filter: ONLY 'CONTESTANT' participant type and within contest time window
            $liveSubmissions = $userSubmissions->filter(function (Submission $sub) use ($contestStartTime, $contestEndTime) {
                $pType = strtoupper((string) ($sub->raw['author']['participantType'] ?? 'CONTESTANT'));
                if ($pType !== 'CONTESTANT') {
                    return false;
                }

                if ($contestEndTime !== null && $sub->submitted_at !== null && $sub->submitted_at > $contestEndTime) {
                    return false;
                }

                if ($contestStartTime !== null && $sub->submitted_at !== null && $sub->submitted_at < $contestStartTime) {
                    return false;
                }

                return true;
            });

            $submissionsByProblem = $liveSubmissions->groupBy(function (Submission $s) {
                return (string) ($s->problem_id ?: $s->metadata['problem_platform_id'] ?? '');
            });

            $totalPoints = 0.0;
            $totalPenalty = 0;
            $lastSubmissionTimeSeconds = null;
            $taskResultsForThisContest = [];

            foreach ($problems as $problem) {
                $probSubs = $submissionsByProblem->get((string) $problem->id)
                    ?? $submissionsByProblem->get((string) $problem->platform_problem_id)
                    ?? collect();

                if ($probSubs->isEmpty()) {
                    continue;
                }

                $hasAc = false;
                $rejectedAttempts = 0;
                $bestSubTimeSec = null;
                $problemPoints = 0.0;

                foreach ($probSubs as $sub) {
                    $verdict = $sub->verdict;
                    $isAc = $verdict === SubmissionVerdict::AC
                        || ($verdict instanceof SubmissionVerdict && $verdict->isAccepted())
                        || ($verdict === 'AC' || $verdict === 'OK');

                    $subTimeSec = null;
                    if (isset($sub->raw['relativeTimeSeconds']) && is_numeric($sub->raw['relativeTimeSeconds'])) {
                        $subTimeSec = (int) $sub->raw['relativeTimeSeconds'];
                    } elseif ($contestStartTime !== null && $sub->submitted_at !== null) {
                        $subTimeSec = max(0, (int) $sub->submitted_at->diffInSeconds($contestStartTime));
                    }

                    if ($isAc) {
                        $hasAc = true;
                        $bestSubTimeSec = $subTimeSec;
                        $problemPoints = $sub->points !== null && $sub->points > 0
                            ? (float) $sub->points
                            : (float) ($problem->points ?? 500.0);

                        if ($subTimeSec !== null && ($lastSubmissionTimeSeconds === null || $subTimeSec > $lastSubmissionTimeSeconds)) {
                            $lastSubmissionTimeSeconds = $subTimeSec;
                        }

                        break;
                    } else {
                        $rejectedAttempts++;
                    }
                }

                $resultType = $hasAc ? 'FINAL' : ($rejectedAttempts > 0 ? 'WA' : 'NO_SUBMISSION');
                $penalty = $hasAc && $bestSubTimeSec !== null ? (int) floor($bestSubTimeSec / 60) + ($rejectedAttempts * 20) : 0;

                $totalPoints += $problemPoints;
                if ($hasAc) {
                    $totalPenalty += $penalty;
                }

                $taskResultsForThisContest[] = [
                    'problem_id' => $problem->id,
                    'points' => $hasAc ? $problemPoints : 0.0,
                    'penalty' => $penalty,
                    'rejected_attempt_count' => $rejectedAttempts,
                    'result_type' => $resultType,
                    'best_submission_time_seconds' => $bestSubTimeSec,
                    'metadata' => json_encode([
                        'source' => 'rating-change-fallback',
                        'platform' => 'codeforces',
                        'contest_platform_id' => $contest?->platform_contest_id,
                        'problem_platform_id' => $problem->platform_problem_id,
                        'synced_at' => now()->toIso8601String(),
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'raw' => json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $standingRows[] = [
                'platform_id' => $platform->id,
                'contest_id' => $rc->contest_id,
                'platform_profile_id' => $profile->id,
                'participant_key' => $profile->handle,
                'participant_type' => 'CONTESTANT',
                'participant_name' => $profile->handle,
                'rank' => $rc->rank,
                'points' => ! empty($taskResultsForThisContest) ? $totalPoints : null,
                'penalty' => ! empty($taskResultsForThisContest) ? $totalPenalty : null,
                'successful_hack_count' => null,
                'unsuccessful_hack_count' => null,
                'last_submission_time_seconds' => $lastSubmissionTimeSeconds,
                'last_synced_at' => now(),
                'metadata' => json_encode([
                    'source' => 'rating-change-fallback',
                    'platform' => 'codeforces',
                    'old_rating' => $rc->old_rating,
                    'new_rating' => $rc->new_rating,
                    'rating_change' => $rc->rating_change,
                    'synced_at' => now()->toIso8601String(),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'raw' => json_encode($rc->raw ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (! empty($taskResultsForThisContest)) {
                $taskResultRowsByContestId[$rc->contest_id] = $taskResultsForThisContest;
            }
        }

        if (! empty($standingRows)) {
            $this->standingModel->newQuery()->upsert(
                $standingRows,
                ['contest_id', 'participant_key'],
                [
                    'platform_id',
                    'platform_profile_id',
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
                    'updated_at',
                ]
            );

            $result->incrementCreated(count($standingRows));

            // Link and upsert StandingTaskResult rows
            if (! empty($taskResultRowsByContestId)) {
                $contestIds = array_keys($taskResultRowsByContestId);
                $standingRecords = $this->standingModel->newQuery()
                    ->whereIn('contest_id', $contestIds)
                    ->where('platform_profile_id', $profile->id)
                    ->get(['id', 'contest_id']);

                $standingIdByContestId = $standingRecords->pluck('id', 'contest_id')->toArray();
                $allTaskResultRows = [];

                foreach ($taskResultRowsByContestId as $cId => $tRows) {
                    $standingId = $standingIdByContestId[$cId] ?? null;
                    if ($standingId === null) {
                        continue;
                    }

                    foreach ($tRows as $tr) {
                        $tr['standing_id'] = $standingId;
                        $allTaskResultRows[] = $tr;
                    }
                }

                if (! empty($allTaskResultRows)) {
                    $this->standingTaskResultModel->newQuery()->upsert(
                        $allTaskResultRows,
                        ['standing_id', 'problem_id'],
                        [
                            'points',
                            'penalty',
                            'rejected_attempt_count',
                            'result_type',
                            'best_submission_time_seconds',
                            'metadata',
                            'raw',
                            'updated_at',
                        ]
                    );

                    $result->incrementMetadata('task_results_created', count($allTaskResultRows));
                }
            }
        }
    }

    private function firstMemberName(array $members): ?string
    {
        foreach ($members as $member) {
            if (! is_array($member)) {
                continue;
            }

            $name = trim((string) ($member['name'] ?? ''));

            if ($name !== '') {
                return $name;
            }
        }

        return null;
    }

    private function teamNameFromHandles(array $handles): ?string
    {
        return $handles === [] ? null : implode(', ', $handles);
    }

    private function slugKey(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9:_-]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? mb_substr($value, 0, 200) : sha1($value);
    }

    private function rawInt(array $raw, string $key): ?int
    {
        if (! isset($raw[$key]) || ! is_numeric($raw[$key])) {
            return null;
        }

        return (int) $raw[$key];
    }
}
