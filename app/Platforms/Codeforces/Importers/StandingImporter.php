<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\StandingImporter as StandingImporterContract;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemResultDTO;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\Problem;
use App\Models\Standing;
use App\Models\StandingTaskResult;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use RuntimeException;
use Throwable;

class StandingImporter implements StandingImporterContract
{
    public function __construct(
        private readonly Contest $contestModel,
        private readonly Standing $standingModel,
        private readonly StandingTaskResult $standingTaskResultModel,
        private readonly Problem $problemModel,
        private readonly Platform $platformModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly PlatformSyncStateService $platformSyncStateService,
        private readonly CodeforcesAdapter $adapter,
    ) {}

    public function import(): ImportResult
    {
        $platformSlug = 'codeforces';

        $result = new ImportResult();

        $platform = $this->contestPlatform();

        if ($platform === null) {
            app(ApplicationLogger::class)->error('Standings import failed: platform not found', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'message' => 'Platform "' . $platformSlug . '" not found in database',
            ]);

            return $result;
        }

        $contests = $this->contestModel->newQuery()
            ->with('platform')
            ->where('platform_id', $platform->id)
            ->whereNotNull('platform_contest_id')
            ->get();

        $result->incrementChecked($contests->count());

        $platformProfilesByHandle = $this->platformProfilesByHandle((int) $platform->id);

        foreach ($contests as $contest) {
            $syncState = $this->platformSyncStateService->markSyncing(
                $contest->platform,
                PlatformSyncEntityType::ContestStandings,
                (string) $contest->platform_contest_id,
                [
                    'contest_id' => $contest->id,
                    'contest_name' => $contest->name,
                    'platform_slug' => $platformSlug,
                    'platform_contest_id' => $contest->platform_contest_id,
                ]
            );

            if ($syncState === null) {
                $result->incrementSkipped();
                continue;
            }

            try {
                $standings = $this->adapter->getStandings((string) $contest->platform_contest_id);

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

                    $standing = $this->standingModel->newQuery()->updateOrCreate(
                        [
                            'contest_id' => $contest->id,
                            'participant_key' => $identity['key'],
                        ],
                        [
                            'platform_id' => $contest->platform_id,
                            'platform_profile_id' => $platformProfile?->id,
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
                                'source' => 'standings-import',
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

                $this->platformSyncStateService->markSynced($syncState, [
                    'contest_id' => $contest->id,
                    'contest_name' => $contest->name,
                    'platform_slug' => $platformSlug,
                    'platform_contest_id' => $contest->platform_contest_id,
                    'standing_count' => count($standings->rows),
                    'problem_count' => count($standings->problems),
                ]);
            } catch (Throwable $e) {
                $result->incrementFailed();

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'contest_id' => $contest->id,
                    'contest_name' => $contest->name,
                    'platform_slug' => $platformSlug,
                    'platform_contest_id' => $contest->platform_contest_id,
                ]);

                app(ApplicationLogger::class)->error('Standings sync failed', [
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

        $result->metadata = array_merge(
            $result->metadata,
            [
                'platform' => 'codeforces',
                'entity' => 'standing',
            ]
        );

        return $result;
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

                app(ApplicationLogger::class)->warning('Standing task result skipped: problem mapping missing', [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                    'contest_id' => $contest->id,
                    'platform_contest_id' => $contest->platform_contest_id,
                    'contest_name' => $contest->name,
                    'standing_id' => $standing->id,
                    'participant_key' => $standing->participant_key,
                    'problem_index' => $index,
                    'problem_platform_id' => $problemPlatformId,
                ]);

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
                        'source' => 'standings-import',
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

                continue;
            }

            $result->incrementMetadata('task_results_updated');
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

