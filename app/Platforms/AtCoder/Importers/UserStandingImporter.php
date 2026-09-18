<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Importers;

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
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use App\Services\StandingsCacheService;
use Illuminate\Support\Str;
use Throwable;

class UserStandingImporter implements UserStandingImporterContract
{
    private array $contestMap = [];

    public function __construct(
        private readonly Standing $standingModel,
        private readonly StandingTaskResult $standingTaskResultModel,
        private readonly Problem $problemModel,
        private readonly Contest $contestModel,
        private readonly Platform $platformModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly ContestRatingChange $contestRatingChangeModel,
        private readonly Submission $submissionModel,
        private readonly AtCoderAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
        private readonly StandingsCacheService $standingsCacheService,
    ) {}

    public function import(?string $handle = null, ?callable $onProgress = null): ImportResult
    {
        $result = new ImportResult;
        $platformSlug = 'atcoder';

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
            $onProgress($totalProfiles, 0, 'Starting AtCoder standings sync...');
        }

        $this->standingsCacheService->warmupPlatform($platformSlug);

        $platformProfilesByHandle = $this->platformProfilesByHandle((int) $platform->id);

        $this->contestMap = $this->contestModel->newQuery()
            ->where('platform_id', $platform->id)
            ->get()
            ->keyBy('id')
            ->all();

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
                    ->where('platform_profile_id', $profile->id)
                    ->distinct()
                    ->pluck('contest_id')
                    ->toArray();

                $contestIds = array_values(array_unique(array_filter(array_merge($ratingContestIds, $submissionContestIds))));

                if (empty($contestIds)) {
                    $this->platformSyncStateService->markSynced($syncState, [
                        'profile_id' => $profile->id,
                        'handle' => $normalizedHandle,
                        'platform_slug' => $platformSlug,
                        'contests_synced' => 0,
                        'status' => 'no_contests_found',
                    ]);

                    continue;
                }

                $existingContestIds = $this->standingModel->newQuery()
                    ->where('platform_id', $platform->id)
                    ->where('platform_profile_id', $profile->id)
                    ->whereIn('contest_id', $contestIds)
                    ->pluck('contest_id')
                    ->toArray();

                $contestsToProcess = array_values(array_diff($contestIds, $existingContestIds));

                if (! empty($contestsToProcess)) {
                    $chunks = array_chunk($contestsToProcess, 10);
                    $totalChunks = count($chunks);

                    foreach ($chunks as $chunkIndex => $contestIdsChunk) {
                        if ($onProgress !== null) {
                            $onProgress(
                                $totalProfiles,
                                $index + 1,
                                "Standings {$profile->handle}: batch ".($chunkIndex + 1)."/{$totalChunks}"
                            );
                        }

                        $this->processContestBatch(
                            $contestIdsChunk,
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
                    'platform_slug' => $platformSlug,
                    'contests_synced' => count($contestIds),
                    'last_synced_at' => now(),
                ]);
            } catch (Throwable $e) {
                $result->incrementFailed();

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'profile_id' => $profile->id,
                    'handle' => $normalizedHandle,
                    'platform_slug' => $platformSlug,
                ]);

                app(ApplicationLogger::class)->error('AtCoder user standings import failed', [
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
                'entity' => 'user_standings',
            ]
        );

        return $result;
    }

    /**
     * @param array<int, int> $contestDbIds
     * @param array<string, PlatformProfile> $platformProfilesByHandle
     */
    private function processContestBatch(
        array $contestDbIds,
        string $platformSlug,
        array $platformProfilesByHandle,
        ImportResult $result
    ): void {
        $platform = $this->platformModel->newQuery()->where('slug', $platformSlug)->first();
        if ($platform === null) {
            return;
        }

        $contests = [];
        $platformContestIds = [];
        foreach ($contestDbIds as $cId) {
            $c = $this->contestMap[$cId] ?? null;
            if ($c !== null && $c->platform_contest_id !== null) {
                $contests[] = $c;
                $platformContestIds[] = (string) $c->platform_contest_id;
            }
        }

        if (empty($platformContestIds)) {
            return;
        }

        // 1. Fetch compressed binaries in parallel from cache
        $binariesMap = $this->standingsCacheService->getMultipleBinaries($platformSlug, $platformContestIds);

        // 2. Prepare bulk records sequentially to keep memory minimal (< 40 MB)
        $standingRows = [];
        $pendingTaskResults = [];

        foreach ($contests as $contest) {
            $cId = (string) $contest->platform_contest_id;
            $binary = $binariesMap[$cId] ?? null;
            unset($binariesMap[$cId]);

            $standingsDto = null;
            if ($binary !== null && $binary !== '') {
                $standingsDto = $this->standingsCacheService->decodeBinary($platformSlug, $cId, $binary);
                unset($binary);
            }

            // Fallback to adapter for any contest missing from remote cache
            if (! $standingsDto instanceof ContestStandingsDTO) {
                try {
                    $standingsDto = $this->adapter->getUserStandings($cId);
                } catch (Throwable $e) {
                    app(ApplicationLogger::class)->warning('AtCoder standings fetch fallback failed', [
                        'category' => 'import',
                        'platform' => $platformSlug,
                        'source' => self::class,
                        'contest_id' => $contest->id,
                        'platform_contest_id' => $cId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (! $standingsDto instanceof ContestStandingsDTO) {
                continue;
            }

            $result->incrementFetched(count($standingsDto->rows));
            $problemMap = $this->ensureContestProblems($contest, $standingsDto);

            foreach ($standingsDto->rows as $row) {
                if (! ($row instanceof ParticipantDTO)) {
                    continue;
                }

                $member = $row->members[0] ?? [];
                $rowHandle = mb_strtolower(trim((string) ($member['handle'] ?? $member['name'] ?? '')));

                if ($rowHandle === '') {
                    continue;
                }

                $rowProfile = $platformProfilesByHandle[$rowHandle] ?? null;
                if ($rowProfile === null) {
                    continue;
                }

                $actualPoints = $row->points !== null ? (float) ($row->points / 100) : null;
                $elapsedNs = $row->raw['totalResult']['elapsed'] ?? $row->raw['TotalResult']['Elapsed'] ?? null;
                $elapsedSeconds = is_numeric($elapsedNs) ? (int) floor(((float) $elapsedNs) / 1000000000) : null;

                $compositeKey = $contest->id.'_'.$rowProfile->handle;
                $standingRows[] = [
                    'platform_id' => $platform->id,
                    'contest_id' => $contest->id,
                    'platform_profile_id' => $rowProfile->id,
                    'participant_key' => $rowProfile->handle,
                    'participant_type' => 'CONTESTANT',
                    'participant_name' => $rowProfile->handle,
                    'rank' => $row->rank,
                    'points' => $actualPoints,
                    'penalty' => $row->penalty,
                    'successful_hack_count' => null,
                    'unsuccessful_hack_count' => null,
                    'last_submission_time_seconds' => $elapsedSeconds,
                    'last_synced_at' => now(),
                    'metadata' => json_encode([
                        'source' => 'user-standings-import',
                        'platform' => $platformSlug,
                        'contest_platform_id' => $contest->platform_contest_id,
                        'handle' => $rowProfile->handle,
                        'synced_at' => now()->toIso8601String(),
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'raw' => json_encode($row->raw ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                foreach ($row->problemResults as $idx => $pResult) {
                    if (! ($pResult instanceof ProblemResultDTO)) {
                        continue;
                    }

                    $isAttempted = ($pResult->points !== null && (float) $pResult->points > 0.0)
                        || ($pResult->rejectedAttemptCount !== null && (int) $pResult->rejectedAttemptCount > 0)
                        || ($pResult->bestSubmissionTimeSeconds !== null);

                    if (! $isAttempted) {
                        continue;
                    }

                    $problemDto = $standingsDto->problems[$idx] ?? null;
                    if ($problemDto === null) {
                        continue;
                    }

                    $probId = (string) $problemDto->platformProblemId;
                    $problem = $problemMap[$probId]
                        ?? $problemMap[strtolower($probId)]
                        ?? $problemMap[str_replace('_', '-', $probId)]
                        ?? null;

                    if ($problem === null) {
                        continue;
                    }

                    $taskPoints = $pResult->points !== null ? (float) ($pResult->points / 100) : null;
                    $resultType = $pResult->type;
                    if ($resultType === '1' || $resultType === 1) {
                        $resultType = 'AC';
                    } elseif ($resultType === '0' || $resultType === 0) {
                        $resultType = ($pResult->rejectedAttemptCount ?? 0) > 0 ? 'WA' : 'NO_SUBMISSION';
                    }

                    $pendingTaskResults[$compositeKey][] = [
                        'problem_id' => $problem->id,
                        'points' => $taskPoints,
                        'penalty' => $pResult->penalty,
                        'rejected_attempt_count' => $pResult->rejectedAttemptCount,
                        'result_type' => $resultType,
                        'best_submission_time_seconds' => $pResult->bestSubmissionTimeSeconds,
                        'metadata' => json_encode([
                            'synced_at' => now()->toIso8601String(),
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'raw' => json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Immediately free the entire contest object and all its participant rows
            unset($standingsDto);
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
        foreach ($pendingTaskResults as $compositeKey => $items) {
            $standingId = $standingIdMap[$compositeKey] ?? null;
            if ($standingId === null) {
                continue;
            }

            foreach ($items as $item) {
                $item['standing_id'] = $standingId;
                $taskResultRows[] = $item;
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

            // Strict Filter: ONLY within contest time window
            $liveSubmissions = $userSubmissions->filter(function (Submission $sub) use ($contestStartTime, $contestEndTime) {
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
                    } elseif (isset($sub->raw['Elapsed']) && is_numeric($sub->raw['Elapsed'])) {
                        $subTimeSec = (int) floor(((float) $sub->raw['Elapsed']) / 1000000000);
                    } elseif ($contestStartTime !== null && $sub->submitted_at !== null) {
                        $subTimeSec = max(0, (int) $sub->submitted_at->diffInSeconds($contestStartTime));
                    }

                    if ($isAc) {
                        $hasAc = true;
                        $bestSubTimeSec = $subTimeSec;
                        $problemPoints = $sub->points !== null && $sub->points > 0
                            ? (float) $sub->points
                            : (float) ($problem->points ?? 100.0);

                        if ($subTimeSec !== null && ($lastSubmissionTimeSeconds === null || $subTimeSec > $lastSubmissionTimeSeconds)) {
                            $lastSubmissionTimeSeconds = $subTimeSec;
                        }

                        break;
                    } else {
                        $rejectedAttempts++;
                    }
                }

                $resultType = $hasAc ? 'AC' : ($rejectedAttempts > 0 ? 'WA' : 'NO_SUBMISSION');
                $penalty = $hasAc && $bestSubTimeSec !== null ? (int) floor($bestSubTimeSec / 60) + ($rejectedAttempts * 5) : 0;

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
                        'platform' => 'atcoder',
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
                    'platform' => 'atcoder',
                    'old_rating' => $rc->old_rating,
                    'new_rating' => $rc->new_rating,
                    'performance' => $rc->performance,
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

    private function ensureContestProblems(Contest $contest, ContestStandingsDTO $standingsDto): array
    {
        $contestProblems = $this->problemModel->newQuery()
            ->where('contest_id', $contest->id)
            ->get();

        $existing = [];
        foreach ($contestProblems as $p) {
            $existing[(string) $p->platform_problem_id] = $p;
        }

        $contestPlatformId = (string) ($contest->platform_contest_id ?? '');

        foreach ($standingsDto->problems as $problemDto) {
            $problemPlatformId = (string) ($problemDto->platformProblemId ?? '');
            if ($problemPlatformId === '') {
                continue;
            }

            if (! isset($existing[$problemPlatformId])) {
                $code = (string) ($problemDto->code ?? '');
                $title = (string) ($problemDto->title ?? '');

                $slug = Str::slug($contestPlatformId.'-'.strtolower($code).'-'.$title);
                if ($slug === '' || $slug === '-') {
                    $slug = Str::slug($problemPlatformId.'-'.$title);
                }

                $problem = $this->problemModel->newQuery()->updateOrCreate(
                    [
                        'platform_id' => $contest->platform_id,
                        'platform_problem_id' => $problemPlatformId,
                    ],
                    [
                        'contest_id' => $contest->id,
                        'slug' => $slug,
                        'name' => $title !== '' ? $title : $problemPlatformId,
                        'code' => $code !== '' ? $code : null,
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
                            'platform' => 'atcoder',
                            'contest_platform_id' => $contestPlatformId,
                        ],
                        'raw' => $problemDto->raw ?? [],
                        'status' => 'Active',
                    ]
                );

                // Self-healing backlink: link any submissions with null problem_id
                $this->submissionModel->newQuery()
                    ->where('platform_id', $contest->platform_id)
                    ->whereNull('problem_id')
                    ->where(function ($q) use ($contest) {
                        $q->where('contest_id', $contest->id)
                            ->orWhere('metadata->contest_platform_id', $contest->platform_contest_id);
                    })
                    ->where(function ($q) use ($problemPlatformId, $code) {
                        $q->where('metadata->problem_platform_id', $problemPlatformId)
                            ->orWhere('metadata->problem_platform_id', strtolower($problemPlatformId))
                            ->orWhere('metadata->problem_platform_id', str_replace('-', '_', $problemPlatformId))
                            ->orWhere('metadata->problem_platform_id', str_replace('_', '-', $problemPlatformId));
                        if ($code !== '') {
                            $q->orWhere('metadata->problem_platform_id', $code);
                        }
                    })
                    ->update([
                        'contest_id' => $contest->id,
                        'problem_id' => $problem->id,
                    ]);

                $existing[$problemPlatformId] = $problem;
            }
        }

        $problemMap = [];
        foreach ($existing as $probPlatformId => $p) {
            $problemMap[$probPlatformId] = $p;
            $problemMap[strtolower($probPlatformId)] = $p;
            $problemMap[str_replace('_', '-', $probPlatformId)] = $p;
            $problemMap[str_replace('-', '_', $probPlatformId)] = $p;
        }

        return $problemMap;
    }
}
