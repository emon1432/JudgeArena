<?php

namespace App\Console\Commands;

use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemResultDTO;
use App\Core\Platforms\PlatformRegistry;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\PlatformProfile;
use App\Models\Problem;
use App\Models\Standing;
use App\Models\StandingTaskResult;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;

class ImportStandingsCommand extends Command
{
    protected $signature = 'judgearena:import-standings {platform?}';

    protected $description = 'Import contest standings and standing task results.';

    public function __construct(
        private readonly Contest $contestModel,
        private readonly Standing $standingModel,
        private readonly StandingTaskResult $standingTaskResultModel,
        private readonly Problem $problemModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly PlatformSyncStateService $platformSyncStateService,
        private readonly PlatformRegistry $platformRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platform = $this->argument('platform');
        $platformSlug = is_string($platform) ? strtolower(trim($platform)) : null;
        $platformLabel = $platformSlug !== null && $platformSlug !== '' ? $platformSlug : 'all';

        app(ApplicationLogger::class)->info('Standings import started', [
            'category' => 'import',
            'platform' => $platformLabel,
            'source' => self::class,
        ]);

        try {
            $stats = $this->sync($platformLabel === 'all' ? null : $platformLabel);
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('Standings import failed', [
                'category' => 'import',
                'platform' => $platformLabel,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Standings import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $this->line('Platform: ' . $platformLabel);
        $this->line('Contests Checked: ' . ($stats['contests_checked'] ?? 0));
        $this->line('Contests Synced: ' . ($stats['contests_synced'] ?? 0));
        $this->line('Contests Already Synced: ' . ($stats['contests_already_synced'] ?? 0));
        $this->line('Contests Failed: ' . ($stats['contests_failed'] ?? 0));
        $this->line('Unsupported Platform Contests: ' . ($stats['contests_unsupported_platform'] ?? 0));
        $this->line('Standings Fetched: ' . ($stats['standings_fetched'] ?? 0));
        $this->line('Standings Created: ' . ($stats['standings_created'] ?? 0));
        $this->line('Standings Updated: ' . ($stats['standings_updated'] ?? 0));
        $this->line('Task Results Created: ' . ($stats['task_results_created'] ?? 0));
        $this->line('Task Results Updated: ' . ($stats['task_results_updated'] ?? 0));
        $this->line('Task Results Skipped: ' . ($stats['task_results_skipped'] ?? 0));
        $this->info('Standings import completed successfully.');

        app(ApplicationLogger::class)->info('Standings import completed', [
            'category' => 'import',
            'platform' => $platformLabel,
            'source' => self::class,
            'contests_checked' => $stats['contests_checked'] ?? 0,
            'contests_synced' => $stats['contests_synced'] ?? 0,
            'contests_already_synced' => $stats['contests_already_synced'] ?? 0,
            'contests_failed' => $stats['contests_failed'] ?? 0,
            'contests_unsupported_platform' => $stats['contests_unsupported_platform'] ?? 0,
            'standings_fetched' => $stats['standings_fetched'] ?? 0,
            'standings_created' => $stats['standings_created'] ?? 0,
            'standings_updated' => $stats['standings_updated'] ?? 0,
            'task_results_created' => $stats['task_results_created'] ?? 0,
            'task_results_updated' => $stats['task_results_updated'] ?? 0,
            'task_results_skipped' => $stats['task_results_skipped'] ?? 0,
        ]);

        return self::SUCCESS;
    }

    private function sync(?string $platformSlug = null): array
    {
        $query = $this->contestModel->newQuery()
            ->with('platform');

        $normalizedPlatformSlug = $this->normalizePlatformSlug($platformSlug);
        if ($normalizedPlatformSlug !== null) {
            $query->whereHas('platform', function ($platformQuery) use ($normalizedPlatformSlug): void {
                $platformQuery->where('slug', $normalizedPlatformSlug);
            });
        }

        $contests = $query->get();

        $pendingContests = $contests->filter(function (Contest $contest): bool {
            $platform = $contest->platform;

            if ($platform === null || $contest->platform_contest_id === null || $contest->platform_contest_id === '') {
                app(ApplicationLogger::class)->warning('Standings sync skipped: contest missing platform context', [
                    'category' => 'import',
                    'platform' => $platform?->slug,
                    'source' => self::class,
                    'contest_id' => $contest->id,
                    'platform_contest_id' => $contest->platform_contest_id,
                    'contest_name' => $contest->name,
                ]);

                return false;
            }

            $syncState = $this->platformSyncStateService->findState(
                $platform,
                PlatformSyncEntityType::ContestStandings,
                (string) $contest->platform_contest_id
            );

            $canBeRetried = $this->platformSyncStateService->canBeRetried($syncState);

            if (! $canBeRetried) {
                app(ApplicationLogger::class)->info('Standings sync skipped: contest sync state not retryable', [
                    'category' => 'import',
                    'platform' => $platform->slug,
                    'source' => self::class,
                    'contest_id' => $contest->id,
                    'platform_contest_id' => $contest->platform_contest_id,
                    'contest_name' => $contest->name,
                ]);
            }

            return $canBeRetried;
        });

        $pendingContestCount = $pendingContests->count();

        $progressBar = null;
        if ($pendingContestCount > 0) {
            $progressBar = $this->output->createProgressBar($pendingContestCount);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            $progressBar->setMessage('Preparing standings sync');
            $progressBar->start();
        } else {
            $this->info('No contests require standings sync.');
        }

        $stats = [
            'contests_checked' => $contests->count(),
            'contests_synced' => 0,
            'contests_already_synced' => $contests->filter(function (Contest $contest): bool {
                $platform = $contest->platform;

                if ($platform === null || $contest->platform_contest_id === null || $contest->platform_contest_id === '') {
                    return false;
                }

                return $this->platformSyncStateService->isSynced(
                    $platform,
                    PlatformSyncEntityType::ContestStandings,
                    (string) $contest->platform_contest_id
                );
            })->count(),
            'contests_failed' => 0,
            'contests_unsupported_platform' => 0,
            'standings_fetched' => 0,
            'standings_created' => 0,
            'standings_updated' => 0,
            'task_results_created' => 0,
            'task_results_updated' => 0,
            'task_results_skipped' => 0,
        ];

        /** @var Collection<string, Collection<int, Contest>> $contestsByPlatform */
        $contestsByPlatform = $pendingContests->groupBy(function (Contest $contest): string {
            return (string) ($contest->platform?->slug ?? '');
        });

        foreach ($contestsByPlatform as $platformSlugKey => $platformContests) {
            $adapter = $this->platformRegistry->resolve($platformSlugKey);

            if ($adapter === null) {
                $stats['contests_unsupported_platform'] += $platformContests->count();

                app(ApplicationLogger::class)->warning('Skipping standings for unsupported platform', [
                    'category' => 'import',
                    'platform' => $platformSlugKey,
                    'source' => self::class,
                    'contest_count' => $platformContests->count(),
                ]);

                if ($progressBar !== null) {
                    $progressBar->setMessage('Skipping unsupported platform ' . $platformSlugKey);
                    $progressBar->advance($platformContests->count());
                }

                continue;
            }

            $platformId = (int) ($platformContests->first()?->platform_id ?? 0);
            $platformProfilesByHandle = $this->platformProfilesByHandle($platformId);

            foreach ($platformContests as $contest) {
                $syncState = $this->platformSyncStateService->markSyncing(
                    $contest->platform,
                    PlatformSyncEntityType::ContestStandings,
                    (string) $contest->platform_contest_id,
                    [
                        'contest_id' => $contest->id,
                        'contest_name' => $contest->name,
                        'platform_slug' => $platformSlugKey,
                        'platform_contest_id' => $contest->platform_contest_id,
                    ]
                );

                if ($syncState === null) {
                    app(ApplicationLogger::class)->info('Standings sync skipped: contest already claimed', [
                        'category' => 'import',
                        'platform' => $platformSlugKey,
                        'source' => self::class,
                        'contest_id' => $contest->id,
                        'platform_contest_id' => $contest->platform_contest_id,
                        'contest_name' => $contest->name,
                    ]);

                    if ($progressBar !== null) {
                        $progressBar->advance();
                    }

                    continue;
                }

                if ($progressBar !== null) {
                    $progressBar->setMessage(sprintf(
                        'Syncing standings for %s',
                        $contest->name !== '' ? $contest->name : (string) $contest->id
                    ));
                }

                app(ApplicationLogger::class)->info('Standings sync started', [
                    'category' => 'import',
                    'platform' => $platformSlugKey,
                    'source' => self::class,
                    'contest_id' => $contest->id,
                    'platform_contest_id' => $contest->platform_contest_id,
                    'contest_name' => $contest->name,
                ]);

                try {
                    $standings = $adapter->getContest((string) $contest->platform_contest_id);

                    if (! $standings instanceof ContestStandingsDTO) {
                        throw new \RuntimeException('Adapter returned invalid standings payload.');
                    }

                    $stats['standings_fetched'] += count($standings->rows);
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
                                    'platform' => $platformSlugKey,
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
                            $stats['standings_created']++;
                        } else {
                            $stats['standings_updated']++;
                        }

                        $this->persistTaskResults(
                            $standing,
                            $contest,
                            $standings,
                            $participant,
                            $contestProblemsByPlatformProblemId,
                            $platformSlugKey,
                            $stats
                        );
                    }

                    $this->platformSyncStateService->markSynced($syncState, [
                        'contest_id' => $contest->id,
                        'contest_name' => $contest->name,
                        'platform_slug' => $platformSlugKey,
                        'platform_contest_id' => $contest->platform_contest_id,
                        'standing_count' => count($standings->rows),
                        'problem_count' => count($standings->problems),
                    ]);

                    $stats['contests_synced']++;

                    app(ApplicationLogger::class)->info('Standings sync completed', [
                        'category' => 'import',
                        'platform' => $platformSlugKey,
                        'source' => self::class,
                        'contest_id' => $contest->id,
                        'platform_contest_id' => $contest->platform_contest_id,
                        'contest_name' => $contest->name,
                        'standing_count' => count($standings->rows),
                        'problem_count' => count($standings->problems),
                    ]);
                } catch (Throwable $e) {
                    $stats['contests_failed']++;

                    $this->platformSyncStateService->markFailed($syncState, $e, [
                        'contest_id' => $contest->id,
                        'contest_name' => $contest->name,
                        'platform_slug' => $platformSlugKey,
                        'platform_contest_id' => $contest->platform_contest_id,
                    ]);

                    app(ApplicationLogger::class)->error('Standings sync failed', [
                        'category' => 'import',
                        'platform' => $platformSlugKey,
                        'source' => self::class,
                        'contest_id' => $contest->id,
                        'platform_contest_id' => $contest->platform_contest_id,
                        'contest_name' => $contest->name,
                        'message' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ], $e);
                } finally {
                    if ($progressBar !== null) {
                        $progressBar->advance();
                    }
                }
            }
        }

        if ($progressBar !== null) {
            $progressBar->setMessage('Standings sync finished');
            $progressBar->finish();
            $this->newLine(2);
        }

        return $stats;
    }

    private function persistTaskResults(
        Standing $standing,
        Contest $contest,
        ContestStandingsDTO $standings,
        ParticipantDTO $participant,
        array $contestProblemsByPlatformProblemId,
        string $platformSlug,
        array &$stats,
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
                $stats['task_results_skipped']++;

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
                $stats['task_results_created']++;

                continue;
            }

            $stats['task_results_updated']++;
        }
    }

    private function normalizePlatformSlug(?string $platformSlug): ?string
    {
        $platformSlug = trim((string) $platformSlug);

        return $platformSlug === '' ? null : strtolower($platformSlug);
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
