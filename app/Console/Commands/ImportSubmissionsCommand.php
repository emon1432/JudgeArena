<?php

namespace App\Console\Commands;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\DTOs\SubmissionDTO;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\PlatformProfile;
use App\Models\Problem;
use App\Models\Submission;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class ImportSubmissionsCommand extends Command
{
    protected $signature = 'judgearena:import-submissions {platform} {handle}';

    protected $description = 'Import user submissions and persist them into the submissions table.';

    public function __construct(
        private readonly CodeforcesAdapter $codeforcesAdapter,
        private readonly AtCoderAdapter $atCoderAdapter,
        private readonly PlatformProfile $platformProfileModel,
        private readonly Contest $contestModel,
        private readonly Problem $problemModel,
        private readonly Submission $submissionModel,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));
        $handle = trim((string) $this->argument('handle'));
        $adapter = $this->resolveAdapter($platformSlug);

        app(ApplicationLogger::class)->info('Submission import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'handle' => $handle,
            'source' => self::class,
        ]);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('Submission import skipped: unsupported platform', [
                'category' => 'import',
                'platform' => $platformSlug,
                'handle' => $handle,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platformSlug);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        $platformProfile = $this->findPlatformProfile($platformSlug, $handle);
        if ($platformProfile === null || $platformProfile->platform === null) {
            app(ApplicationLogger::class)->warning('Submission import skipped: profile not found', [
                'category' => 'import',
                'platform' => $platformSlug,
                'handle' => $handle,
                'source' => self::class,
            ]);

            $this->error('Platform profile not found for ' . $platformSlug . ' / ' . $handle);

            return self::FAILURE;
        }

        $contests = $this->contestModel->newQuery()
            ->where('platform_id', $platformProfile->platform_id)
            ->whereNotNull('platform_contest_id')
            ->orderBy('start_time')
            ->get();

        $pendingContests = $contests->filter(function (Contest $contest) use ($platformProfile, $platformSlug, $handle): bool {
            if ($contest->platform_contest_id === null || $contest->platform_contest_id === '') {
                app(ApplicationLogger::class)->warning('Submission import skipped: contest missing platform contest id', $this->contestLogContext(
                    $platformSlug,
                    $handle,
                    $contest
                ));

                return false;
            }

            $syncState = $this->platformSyncStateService->findState(
                $platformProfile->platform,
                PlatformSyncEntityType::ContestSubmissions,
                $this->syncKey($platformSlug, (string) $contest->platform_contest_id, $handle)
            );

            $canBeRetried = $this->platformSyncStateService->canBeRetried($syncState);

            if (! $canBeRetried) {
                app(ApplicationLogger::class)->info('Submission import skipped: contest sync state not retryable', $this->contestLogContext(
                    $platformSlug,
                    $handle,
                    $contest
                ));
            }

            return $canBeRetried;
        });

        $progressBar = null;
        if ($pendingContests->count() > 0) {
            $progressBar = $this->output->createProgressBar($pendingContests->count());
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            $progressBar->setMessage('Preparing submission import');
            $progressBar->start();
        } else {
            $this->info('No contests require submission import.');
        }

        $stats = [
            'contests_checked' => $contests->count(),
            'contests_synced' => 0,
            'contests_already_synced' => $contests->filter(function (Contest $contest) use ($platformProfile, $platformSlug, $handle): bool {
                if ($contest->platform_contest_id === null || $contest->platform_contest_id === '') {
                    return false;
                }

                return $this->platformSyncStateService->isSynced(
                    $platformProfile->platform,
                    PlatformSyncEntityType::ContestSubmissions,
                    $this->syncKey($platformSlug, (string) $contest->platform_contest_id, $handle)
                );
            })->count(),
            'contests_failed' => 0,
            'contests_skipped' => $contests->count() - $pendingContests->count(),
            'submissions_fetched' => 0,
            'submissions_created' => 0,
            'submissions_updated' => 0,
            'submissions_skipped' => 0,
        ];

        foreach ($pendingContests as $contest) {
            $context = $this->contestLogContext($platformSlug, $handle, $contest);
            $syncKey = $this->syncKey($platformSlug, (string) $contest->platform_contest_id, $handle);

            $syncState = $this->platformSyncStateService->markSyncing(
                $platformProfile->platform,
                PlatformSyncEntityType::ContestSubmissions,
                $syncKey,
                [
                    'platform_profile_id' => $platformProfile->id,
                    'platform_id' => $platformProfile->platform_id,
                    'platform_slug' => $platformSlug,
                    'handle' => $handle,
                    'contest_id' => $contest->id,
                    'platform_contest_id' => $contest->platform_contest_id,
                    'contest_name' => $contest->name,
                ]
            );

            if ($syncState === null) {
                $stats['contests_skipped']++;

                app(ApplicationLogger::class)->warning('Submission import skipped: contest sync state not retryable', $context);

                if ($progressBar !== null) {
                    $progressBar->advance();
                }

                continue;
            }

            if ($progressBar !== null) {
                $progressBar->setMessage(sprintf(
                    'Syncing submissions for %s',
                    $contest->name !== '' ? $contest->name : (string) $contest->platform_contest_id
                ));
            }

            app(ApplicationLogger::class)->info('Submission import contest started', $context);

            try {
                $submissions = $adapter->getSubmissions((string) $contest->platform_contest_id, $handle);

                if (! is_array($submissions)) {
                    $submissions = [];
                }

                $stats['submissions_fetched'] += count($submissions);

                foreach ($submissions as $submissionDto) {
                    if (! $submissionDto instanceof SubmissionDTO) {
                        $stats['submissions_skipped']++;
                        continue;
                    }

                    $submission = $this->persistSubmission(
                        $platformProfile,
                        $contest,
                        $submissionDto,
                        $platformSlug,
                        $handle
                    );

                    if ($submission->wasRecentlyCreated) {
                        $stats['submissions_created']++;

                        continue;
                    }

                    $stats['submissions_updated']++;
                }

                $this->platformSyncStateService->markSynced($syncState, [
                    'platform_profile_id' => $platformProfile->id,
                    'platform_id' => $platformProfile->platform_id,
                    'platform_slug' => $platformSlug,
                    'handle' => $handle,
                    'contest_id' => $contest->id,
                    'platform_contest_id' => $contest->platform_contest_id,
                    'contest_name' => $contest->name,
                    'submissions_fetched' => count($submissions),
                ]);

                $stats['contests_synced']++;

                app(ApplicationLogger::class)->info('Submission import contest completed', array_merge($context, [
                    'submissions_fetched' => count($submissions),
                ]));
            } catch (Throwable $e) {
                $stats['contests_failed']++;

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'platform_profile_id' => $platformProfile->id,
                    'platform_id' => $platformProfile->platform_id,
                    'platform_slug' => $platformSlug,
                    'handle' => $handle,
                    'contest_id' => $contest->id,
                    'platform_contest_id' => $contest->platform_contest_id,
                    'contest_name' => $contest->name,
                ]);

                app(ApplicationLogger::class)->error('Submission import contest failed', array_merge($context, [
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]), $e);
            } finally {
                if ($progressBar !== null) {
                    $progressBar->advance();
                }
            }
        }

        if ($progressBar !== null) {
            $progressBar->setMessage('Submission import finished');
            $progressBar->finish();
            $this->newLine(2);
        }

        $this->line('Platform: ' . $platformSlug);
        $this->line('Handle: ' . $handle);
        $this->line('Contests Checked: ' . $stats['contests_checked']);
        $this->line('Contests Synced: ' . $stats['contests_synced']);
        $this->line('Contests Already Synced: ' . $stats['contests_already_synced']);
        $this->line('Contests Skipped: ' . $stats['contests_skipped']);
        $this->line('Contests Failed: ' . $stats['contests_failed']);
        $this->line('Submissions Fetched: ' . $stats['submissions_fetched']);
        $this->line('Submissions Created: ' . $stats['submissions_created']);
        $this->line('Submissions Updated: ' . $stats['submissions_updated']);
        $this->line('Submissions Skipped: ' . $stats['submissions_skipped']);

        app(ApplicationLogger::class)->info('Submission import completed', [
            'category' => 'import',
            'platform' => $platformSlug,
            'handle' => $handle,
            'source' => self::class,
            'contests_checked' => $stats['contests_checked'],
            'contests_synced' => $stats['contests_synced'],
            'contests_already_synced' => $stats['contests_already_synced'],
            'contests_skipped' => $stats['contests_skipped'],
            'contests_failed' => $stats['contests_failed'],
            'submissions_fetched' => $stats['submissions_fetched'],
            'submissions_created' => $stats['submissions_created'],
            'submissions_updated' => $stats['submissions_updated'],
            'submissions_skipped' => $stats['submissions_skipped'],
        ]);

        return $stats['contests_failed'] > 0 ? self::FAILURE : self::SUCCESS;

    }

    private function resolveAdapter(string $platformSlug): ?PlatformAdapter
    {
        return match ($platformSlug) {
            'codeforces' => $this->codeforcesAdapter,
            'atcoder' => $this->atCoderAdapter,
            default => null,
        };
    }

    private function findPlatformProfile(string $platformSlug, string $handle): ?PlatformProfile
    {
        return $this->platformProfileModel->newQuery()
            ->with('platform')
            ->whereHas('platform', function ($platformQuery) use ($platformSlug): void {
                $platformQuery->where('slug', $platformSlug);
            })
            ->whereRaw('LOWER(handle) = ?', [mb_strtolower(trim($handle))])
            ->first();
    }

    private function findProblem(int $platformId, string $problemPlatformId): ?Problem
    {
        $problemPlatformId = trim($problemPlatformId);

        if ($problemPlatformId === '') {
            return null;
        }

        return $this->problemModel->newQuery()
            ->where('platform_id', $platformId)
            ->where('platform_problem_id', $problemPlatformId)
            ->first();
    }

    private function persistSubmission(
        PlatformProfile $platformProfile,
        Contest $contest,
        SubmissionDTO $submissionDto,
        string $platformSlug,
        string $handle,
    ): Submission {
        $problem = $this->findProblem((int) $platformProfile->platform_id, $submissionDto->problemPlatformId);

        if ($problem === null) {
            app(ApplicationLogger::class)->warning('Submission import problem mapping missing', [
                'category' => 'import',
                'platform' => $platformSlug,
                'handle' => $handle,
                'source' => self::class,
                'contest_id' => $contest->id,
                'platform_contest_id' => $contest->platform_contest_id,
                'contest_name' => $contest->name,
                'problem_platform_id' => $submissionDto->problemPlatformId,
                'platform_submission_id' => $submissionDto->platformSubmissionId,
            ]);
        }

        return $this->submissionModel->newQuery()->updateOrCreate(
            [
                'platform_id' => $platformProfile->platform_id,
                'platform_submission_id' => $submissionDto->platformSubmissionId,
            ],
            [
                'contest_id' => $contest->id,
                'problem_id' => $problem?->id,
                'platform_profile_id' => $platformProfile->id,
                'author_handle' => $submissionDto->authorHandle,
                'verdict' => $submissionDto->verdict,
                'language' => $submissionDto->language,
                'points' => $submissionDto->points,
                'passed_test_count' => $submissionDto->passedTestCount,
                'time_consumed_ms' => $submissionDto->timeConsumedMillis,
                'memory_consumed_bytes' => $submissionDto->memoryConsumedBytes,
                'submitted_at' => $submissionDto->createdAtSeconds !== null
                    ? Carbon::createFromTimestamp($submissionDto->createdAtSeconds)
                    : null,
                'last_synced_at' => now(),
                'metadata' => [
                    'source' => 'contest-scoped-submission-import',
                    'platform' => $platformSlug,
                    'handle' => $handle,
                    'testset' => $submissionDto->testset,
                    'platform_profile_id' => $platformProfile->id,
                    'contest_platform_id' => $contest->platform_contest_id,
                    'contest_id' => $contest->id,
                    'problem_platform_id' => $submissionDto->problemPlatformId,
                    'synced_at' => now(),
                ],
                'raw' => $submissionDto->raw,
                'status' => 'Active',
            ]
        );
    }

    private function syncKey(string $platformSlug, string $contestPlatformId, string $handle): string
    {
        return strtolower(trim($platformSlug)) . ':' . trim($contestPlatformId) . ':' . mb_strtolower(trim($handle));
    }

    private function contestLogContext(string $platformSlug, string $handle, Contest $contest): array
    {
        return [
            'category' => 'import',
            'platform' => $platformSlug,
            'handle' => $handle,
            'source' => self::class,
            'contest_id' => $contest->id,
            'platform_contest_id' => $contest->platform_contest_id,
            'contest_name' => $contest->name,
        ];
    }
}
