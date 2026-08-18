<?php

namespace App\Platforms\AtCoder\Importers;

use App\Core\Contracts\Importers\UserStandingImporter;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
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
use Throwable;

class UserStandingsImporter implements UserStandingImporter
{
    private array $contestMap = [];
    private array $problemMap = [];

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
    ) {}

    public function import(?string $handle = null): ImportResult
    {
        $result = new ImportResult();
        $platformSlug = 'atcoder';

        $platform = $this->platformModel->newQuery()
            ->where('slug', $platformSlug)
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error('User standings import failed: platform not found', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'message' => 'Platform "' . $platformSlug . '" not found in database',
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
        $result->incrementChecked($profiles->count());

        $platformProfilesByHandle = $this->platformProfilesByHandle((int) $platform->id);

        $this->problemMap = $this->problemModel->newQuery()
            ->where('platform_id', $platform->id)
            ->get()
            ->keyBy('platform_problem_id')
            ->all();

        $this->contestMap = $this->contestModel->newQuery()
            ->where('platform_id', $platform->id)
            ->get()
            ->keyBy('id')
            ->all();

        foreach ($profiles as $profile) {
            $normalizedHandle = mb_strtolower(trim((string) $profile->handle));

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

                $contestIds = array_unique(array_filter(array_merge($ratingContestIds, $submissionContestIds)));

                $standingsFetched = 0;

                foreach ($contestIds as $contestDbId) {
                    $contest = $this->contestMap[$contestDbId] ?? null;
                    if ($contest === null) {
                        continue;
                    }

                    $standingsDto = $this->adapter->getUserStandings($contest->platform_contest_id);

                    if (!($standingsDto instanceof ContestStandingsDTO)) {
                        continue;
                    }

                    $standingsFetched++;

                    foreach ($standingsDto->rows as $row) {
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

                        $standing = $this->standingModel->newQuery()->updateOrCreate(
                            [
                                'contest_id' => $contest->id,
                                'participant_key' => $rowProfile->handle,
                            ],
                            [
                                'platform_id' => $platform->id,
                                'platform_profile_id' => $rowProfile->id,
                                'participant_type' => 'CONTESTANT',
                                'participant_name' => $rowProfile->handle,
                                'rank' => $row->rank,
                                'points' => $actualPoints,
                                'penalty' => $row->penalty,
                                'last_submission_time_seconds' => $elapsedSeconds,
                                'last_synced_at' => now(),
                                'metadata' => [
                                    'source' => 'user-standings-import',
                                    'platform' => $platformSlug,
                                    'contest_platform_id' => $contest->platform_contest_id,
                                    'handle' => $rowProfile->handle,
                                    'synced_at' => now(),
                                ],
                                'raw' => $row->raw,
                                'status' => 'Active',
                            ]
                        );

                        if ($standing->wasRecentlyCreated) {
                            $result->incrementCreated();
                        } else {
                            $result->incrementUpdated();
                        }

                        foreach ($row->problemResults as $idx => $pResult) {
                            $problemDto = $standingsDto->problems[$idx] ?? null;
                            if ($problemDto === null) {
                                continue;
                            }

                            $probId = (string) $problemDto->platformProblemId;
                            $problem = $this->problemMap[$probId]
                                ?? $this->problemMap[strtolower($probId)]
                                ?? $this->problemMap[str_replace('_', '-', $probId)]
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

                            $this->standingTaskResultModel->newQuery()->updateOrCreate(
                                [
                                    'standing_id' => $standing->id,
                                    'problem_id' => $problem->id,
                                ],
                                [
                                    'points' => $taskPoints,
                                    'penalty' => $pResult->penalty,
                                    'rejected_attempt_count' => $pResult->rejectedAttemptCount,
                                    'result_type' => $resultType,
                                    'best_submission_time_seconds' => $pResult->bestSubmissionTimeSeconds,
                                    'metadata' => [
                                        'synced_at' => now(),
                                    ],
                                ]
                            );
                        }
                    }
                }

                $result->incrementFetched($standingsFetched);

                $this->platformSyncStateService->markSynced($syncState, [
                    'profile_id' => $profile->id,
                    'handle' => $normalizedHandle,
                    'platform_slug' => $platformSlug,
                    'contests_synced' => count($contestIds),
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
}
