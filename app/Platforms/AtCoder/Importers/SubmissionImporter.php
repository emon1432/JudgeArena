<?php

namespace App\Platforms\AtCoder\Importers;

use App\Core\Contracts\Importers\SubmissionImporter as SubmissionImporterContract;
use App\Core\DTOs\SubmissionDTO;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\Problem;
use App\Models\Submission;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Carbon\Carbon;
use Throwable;

class SubmissionImporter implements SubmissionImporterContract
{
    public function __construct(
        private readonly Contest $contestModel,
        private readonly Problem $problemModel,
        private readonly Submission $submissionModel,
        private readonly AtCoderAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(): array
    {
        $platformSlug = 'atcoder';

        $stats = [
            'contests_checked' => 0,
            'contests_synced' => 0,
            'contests_already_synced' => 0,
            'contests_skipped' => 0,
            'contests_failed' => 0,
            'submissions_fetched' => 0,
            'submissions_created' => 0,
            'submissions_updated' => 0,
            'submissions_skipped' => 0,
        ];

        $contests = $this->contestModel->newQuery()
            ->with('platform')
            ->whereHas('platform', function ($platformQuery) use ($platformSlug): void {
                $platformQuery->where('slug', $platformSlug);
            })
            ->whereNotNull('platform_contest_id')
            ->orderBy('start_time')
            ->get();

        $pendingContests = $contests->filter(function (Contest $contest) use ($platformSlug): bool {
            if ($contest->platform_contest_id === null || $contest->platform_contest_id === '') {
                app(ApplicationLogger::class)->warning('Submission import skipped: contest missing platform contest id', $this->contestLogContext(
                    $platformSlug,
                    $contest
                ));

                return false;
            }

            $syncState = $this->platformSyncStateService->findState(
                $contest->platform,
                PlatformSyncEntityType::ContestSubmissions,
                (string) $contest->platform_contest_id
            );

            $canBeRetried = $this->platformSyncStateService->canBeRetried($syncState);

            if (! $canBeRetried) {
                app(ApplicationLogger::class)->info('Submission import skipped: contest sync state not retryable', $this->contestLogContext(
                    $platformSlug,
                    $contest
                ));
            }

            return $canBeRetried;
        });

        $stats['contests_checked'] = $contests->count();
        $stats['contests_already_synced'] = $contests->filter(function (Contest $contest) use ($platformSlug): bool {
            if ($contest->platform_contest_id === null || $contest->platform_contest_id === '') {
                return false;
            }

            return $this->platformSyncStateService->isSynced(
                $contest->platform,
                PlatformSyncEntityType::ContestSubmissions,
                (string) $contest->platform_contest_id
            );
        })->count();

        foreach ($pendingContests as $contest) {
            $context = $this->contestLogContext($platformSlug, $contest);

            $syncState = $this->platformSyncStateService->markSyncing(
                $contest->platform,
                PlatformSyncEntityType::ContestSubmissions,
                (string) $contest->platform_contest_id,
                [
                    'platform_slug' => $platformSlug,
                    'contest_id' => $contest->id,
                    'platform_contest_id' => $contest->platform_contest_id,
                    'contest_name' => $contest->name,
                ]
            );

            if ($syncState === null) {
                $stats['contests_skipped']++;
                continue;
            }

            try {
                $submissions = $this->adapter->getSubmissions((string) $contest->platform_contest_id);

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
                        $contest,
                        $submissionDto,
                        $platformSlug
                    );

                    if ($submission->wasRecentlyCreated) {
                        $stats['submissions_created']++;

                        continue;
                    }

                    $stats['submissions_updated']++;
                }

                $this->platformSyncStateService->markSynced($syncState, [
                    'platform_slug' => $platformSlug,
                    'contest_id' => $contest->id,
                    'platform_contest_id' => $contest->platform_contest_id,
                    'contest_name' => $contest->name,
                    'submissions_fetched' => count($submissions),
                ]);

                $stats['contests_synced']++;
            } catch (Throwable $e) {
                $stats['contests_failed']++;

                $this->platformSyncStateService->markFailed($syncState, $e, [
                    'platform_slug' => $platformSlug,
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
            }
        }

        return $stats;
    }

    private function persistSubmission(
        Contest $contest,
        SubmissionDTO $submissionDto,
        string $platformSlug,
    ): Submission {
        $problem = $this->findProblem((int) $contest->platform_id, $submissionDto->problemPlatformId);

        if ($problem === null) {
            app(ApplicationLogger::class)->warning('Submission import problem mapping missing', [
                'category' => 'import',
                'platform' => $platformSlug,
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
                'platform_submission_id' => $submissionDto->platformSubmissionId,
            ],
            [
                'contest_id' => $contest->id,
                'problem_id' => $problem?->id,
                'platform_profile_id' => null,
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
                    'testset' => $submissionDto->testset,
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

    private function contestLogContext(string $platformSlug, Contest $contest): array
    {
        return [
            'category' => 'import',
            'platform' => $platformSlug,
            'source' => self::class,
            'contest_id' => $contest->id,
            'platform_contest_id' => $contest->platform_contest_id,
            'contest_name' => $contest->name,
        ];
    }
}
