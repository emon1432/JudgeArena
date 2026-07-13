<?php

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\SubmissionImporter as SubmissionImporterContract;
use App\Core\DTOs\SubmissionDTO;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\Problem;
use App\Models\Submission;
use App\Platforms\Codeforces\CodeforcesAdapter;
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
        private readonly CodeforcesAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(): ImportResult
    {
        $platformSlug = 'codeforces';

        $result = new ImportResult();

        $contests = $this->contestModel->newQuery()
            ->with('platform')
            ->whereHas('platform', function ($platformQuery) use ($platformSlug): void {
                $platformQuery->where('slug', $platformSlug);
            })
            ->whereNotNull('platform_contest_id')
            ->orderBy('start_time')
            ->get();

        $result->incrementChecked($contests->count());

        foreach ($contests as $contest) {
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
                $result->incrementSkipped();
                continue;
            }

            try {
                $submissions = $this->adapter->getSubmissions((string) $contest->platform_contest_id);

                if (! is_array($submissions)) {
                    $submissions = [];
                }

                $result->incrementFetched(count($submissions));

                $problemsByPlatformId = $this->contestProblemsByPlatformProblemId($contest->id);

                foreach ($submissions as $submissionDto) {
                    if (! $submissionDto instanceof SubmissionDTO) {
                        $result->incrementMetadata('submissions_skipped');
                        continue;
                    }

                    $submission = $this->persistSubmission(
                        $contest,
                        $submissionDto,
                        $platformSlug,
                        $problemsByPlatformId,
                    );

                    if ($submission->wasRecentlyCreated) {
                        $result->incrementCreated();
                        continue;
                    }

                    $result->incrementUpdated();
                }

                $this->platformSyncStateService->markSynced($syncState, [
                    'platform_slug' => $platformSlug,
                    'contest_id' => $contest->id,
                    'platform_contest_id' => $contest->platform_contest_id,
                    'contest_name' => $contest->name,
                    'submissions_fetched' => count($submissions),
                ]);
            } catch (Throwable $e) {
                $result->incrementFailed();

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

        $result->metadata = array_merge(
            $result->metadata,
            [
                'platform' => 'codeforces',
                'entity' => 'submission',
            ]
        );

        return $result;
    }

    private function persistSubmission(
        Contest $contest,
        SubmissionDTO $submissionDto,
        string $platformSlug,
        array $problemsByPlatformId,
    ): Submission {

        $problem = $problemsByPlatformId[$submissionDto->problemPlatformId] ?? null;

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
                'platform_id' => $contest->platform_id,
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

    private function contestProblemsByPlatformProblemId(int $contestId): array
    {
        return $this->problemModel
            ->newQuery()
            ->where('contest_id', $contestId)
            ->get()
            ->keyBy('platform_problem_id')
            ->all();
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
