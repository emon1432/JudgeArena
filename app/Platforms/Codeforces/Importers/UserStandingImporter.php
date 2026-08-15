<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\UserStandingImporter as UserStandingImporterContract;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemResultDTO;
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
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use RuntimeException;
use Throwable;

class UserStandingImporter implements UserStandingImporterContract
{
    private const MAX_CONTESTS_PER_RUN = 50;

    public function __construct(
        private readonly Contest $contestModel,
        private readonly Standing $standingModel,
        private readonly StandingTaskResult $standingTaskResultModel,
        private readonly Problem $problemModel,
        private readonly Platform $platformModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly ContestRatingChange $contestRatingChangeModel,
        private readonly Submission $submissionModel,
        private readonly PlatformSyncStateService $platformSyncStateService,
        private readonly CodeforcesAdapter $adapter,
    ) {}

    public function import(?string $handle = null): ImportResult
    {
        $platformSlug = 'codeforces';
        $result = new ImportResult();

        $platform = $this->contestPlatform();

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

            // If handle is not explicitly specified and profile user standings were already synced, skip!
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
                    ->whereRaw('LOWER(author_handle) = ?', [$normalizedHandle])
                    ->whereNotNull('contest_id')
                    ->distinct()
                    ->pluck('contest_id')
                    ->toArray();

                // Limitation: Discovering participated contests via rating changes and submissions 
                // is a practical strategy but not necessarily complete. E.g. unrated contests 
                // where the user made no submissions won't be found.
                $allParticipatedContestIds = array_unique(array_merge($ratingContestIds, $submissionContestIds));

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

                if (empty($missingContestIds)) {
                    $this->platformSyncStateService->markSynced($syncState, [
                        'profile_id' => $profile->id,
                        'handle' => $normalizedHandle,
                        'status' => 'all_standings_exist',
                    ]);
                    continue;
                }

                $contestsToProcess = array_slice($missingContestIds, 0, self::MAX_CONTESTS_PER_RUN);
                $hasMoreContests = count($missingContestIds) > self::MAX_CONTESTS_PER_RUN;

                $contests = $this->contestModel->newQuery()
                    ->whereIn('id', $contestsToProcess)
                    ->whereNotNull('platform_contest_id')
                    ->get();

                foreach ($contests as $contest) {
                    $this->processContest($contest, $platformSlug, $platformProfilesByHandle, $result);
                }

                if ($hasMoreContests) {
                    // Mark failed or reset for retry so the next run picks it up
                    $this->platformSyncStateService->resetForRetry($syncState, [
                        'profile_id' => $profile->id,
                        'handle' => $normalizedHandle,
                        'status' => 'partial_sync',
                        'remaining' => count($missingContestIds) - count($contestsToProcess),
                    ]);
                } else {
                    $this->platformSyncStateService->markSynced($syncState, [
                        'profile_id' => $profile->id,
                        'handle' => $normalizedHandle,
                        'status' => 'synced',
                    ]);
                }
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

    private function processContest(
        Contest $contest,
        string $platformSlug,
        array $platformProfilesByHandle,
        ImportResult $result
    ): void {
        try {
            $standings = $this->adapter->getUserStandings((string) $contest->platform_contest_id);

            if (! $standings instanceof ContestStandingsDTO) {
                throw new RuntimeException('Adapter returned invalid standings payload.');
            }

            $result->incrementFetched(count($standings->rows));
            $contestProblemsByPlatformProblemId = $this->contestProblemsByPlatformProblemId((int) $contest->id);

            foreach ($standings->rows as $participant) {
                if (! $participant instanceof ParticipantDTO) {
                    continue;
                }

                $identity = $this->participantIdentity($participant);
                $platformProfile = $identity['handle'] !== null
                    ? ($platformProfilesByHandle[mb_strtolower($identity['handle'])] ?? null)
                    : null;

                if ($platformProfile === null) {
                    continue;
                }

                $standing = $this->standingModel->newQuery()->updateOrCreate(
                    [
                        'contest_id' => $contest->id,
                        'participant_key' => $identity['key'],
                    ],
                    [
                        'platform_id' => $contest->platform_id,
                        'platform_profile_id' => $platformProfile->id,
                        'participant_type' => $identity['type'],
                        'participant_name' => $identity['name'],
                        'rank' => $participant->rank,
                        'points' => $participant->points,
                        'penalty' => $participant->penalty,
                        'successful_hack_count' => $this->rawInt($participant->raw, 'successfulHackCount'),
                        'unsuccessful_hack_count' => $this->rawInt($participant->raw, 'unsuccessfulHackCount'),
                        'last_submission_time_seconds' => $this->rawInt($participant->raw, 'lastSubmissionTimeSeconds'),
                        'last_synced_at' => now(),
                        'metadata' => [
                            'source' => 'user-standings-import',
                            'platform' => $platformSlug,
                            'contest_platform_id' => $contest->platform_contest_id,
                            'contest_name' => $contest->name,
                            'members' => $participant->members,
                            'synced_at' => now(),
                        ],
                        'raw' => $participant->raw,
                        'status' => 'Active',
                    ]
                );

                if ($standing->wasRecentlyCreated) {
                    $result->incrementCreated();
                } else {
                    $result->incrementUpdated();
                }

                $this->persistTaskResults(
                    $standing,
                    $contest,
                    $standings,
                    $participant,
                    $contestProblemsByPlatformProblemId,
                    $platformSlug,
                    $result
                );
            }
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('User standings sync contest failed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'contest_id' => $contest->id,
                'platform_contest_id' => $contest->platform_contest_id,
                'contest_name' => $contest->name,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);
        }
    }

    private function contestPlatform(): ?Platform
    {
        return $this->platformModel
            ->newQuery()
            ->where('slug', 'codeforces')
            ->first();
    }

    private function persistTaskResults(
        Standing $standing,
        Contest $contest,
        ContestStandingsDTO $standings,
        ParticipantDTO $participant,
        array $contestProblemsByPlatformProblemId,
        string $platformSlug,
        ImportResult $result
    ): void {
        foreach ($participant->problemResults as $index => $problemResult) {
            if (! $problemResult instanceof ProblemResultDTO) {
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

            $taskResult = $this->standingTaskResultModel->newQuery()->updateOrCreate(
                [
                    'standing_id' => $standing->id,
                    'problem_id' => $problem->id,
                ],
                [
                    'points' => $problemResult->points,
                    'penalty' => $problemResult->penalty,
                    'rejected_attempt_count' => $problemResult->rejectedAttemptCount,
                    'result_type' => $problemResult->type,
                    'best_submission_time_seconds' => $problemResult->bestSubmissionTimeSeconds,
                    'metadata' => [
                        'source' => 'user-standings-import',
                        'platform' => $platformSlug,
                        'contest_platform_id' => $contest->platform_contest_id,
                        'problem_platform_id' => $problemPlatformId,
                        'problem_index' => $index,
                    ],
                    'raw' => [],
                ]
            );

            if ($taskResult->wasRecentlyCreated) {
                $result->incrementMetadata('task_results_created');
            } else {
                $result->incrementMetadata('task_results_updated');
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
                'key' => 'team:' . trim((string) $teamId),
                'type' => $participantType ?? 'TEAM',
                'name' => $teamName !== '' ? $teamName : $this->teamNameFromHandles($handles),
                'handle' => null,
            ];
        }

        if ($teamName !== '') {
            return [
                'key' => 'team:' . $this->slugKey($teamName),
                'type' => $participantType ?? 'TEAM',
                'name' => $teamName,
                'handle' => null,
            ];
        }

        if ($handles !== []) {
            sort($handles);

            return [
                'key' => 'team:' . $this->slugKey(implode(':', $handles)),
                'type' => $participantType ?? 'TEAM',
                'name' => $this->teamNameFromHandles($handles),
                'handle' => null,
            ];
        }

        $encodedRaw = json_encode($raw);
        $hashSource = $encodedRaw !== false && $encodedRaw !== '' ? $encodedRaw : (string) $participant->rank;

        return [
            'key' => 'participant:' . sha1($hashSource),
            'type' => $participantType,
            'name' => null,
            'handle' => null,
        ];
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
