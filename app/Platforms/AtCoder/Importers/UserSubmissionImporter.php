<?php

namespace App\Platforms\AtCoder\Importers;

use App\Core\Contracts\Importers\UserSubmissionImporter as UserSubmissionImporterContract;
use App\Core\DTOs\SubmissionDTO;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\Problem;
use App\Models\Submission;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Carbon\Carbon;
use Throwable;


class UserSubmissionImporter implements UserSubmissionImporterContract
{
    private array $problemMap = [];

    public function __construct(
        private readonly Submission $submissionModel,
        private readonly Problem $problemModel,
        private readonly Contest $contestModel,
        private readonly Platform $platformModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly AtCoderAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(?string $handle = null): ImportResult
    {
        $result = new ImportResult();

        $platform = $this->platformModel
            ->newQuery()
            ->where('slug', 'atcoder')
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error(
                'User submissions import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'atcoder',
                    'source' => self::class,
                ]
            );

            return $result;
        }

        $query = $this->platformProfileModel
            ->newQuery()
            ->where('platform_id', $platform->id)
            ->active();

        if ($handle !== null) {
            $query->whereRaw(
                'LOWER(handle)=?',
                [mb_strtolower(trim($handle))]
            );
        }

        $profiles = $query->get();

        $result->incrementChecked($profiles->count());

        $contests = $this->contestModel
            ->newQuery()
            ->where('platform_id', $platform->id)
            ->latest('start_time')
            ->get();

        $this->problemMap = $this->problemModel
            ->newQuery()
            ->where('platform_id', $platform->id)
            ->get()
            ->keyBy('platform_problem_id')
            ->all();

        foreach ($profiles as $profile) {

            $normalizedHandle = mb_strtolower(
                trim((string) $profile->handle)
            );

            if ($normalizedHandle === '') {
                $result->incrementSkipped();
                continue;
            }

            foreach ($contests as $contest) {

                $syncState = $this->platformSyncStateService->markSyncing(
                    $platform,
                    PlatformSyncEntityType::UserSubmissions,
                    $normalizedHandle . '::' . $contest->platform_contest_id,
                    [
                        'profile_id' => $profile->id,
                        'handle' => $normalizedHandle,
                        'contest_id' => $contest->id,
                        'contest_platform_id' => $contest->platform_contest_id,
                        'platform_slug' => 'atcoder',
                    ]
                );

                if ($syncState === null) {
                    $result->incrementSkipped();
                    continue;
                }

                try {

                    $lastSubmissionId = data_get(
                        $syncState->metadata,
                        'last_submission_id'
                    );

                    /** @var array{
                     *     submissions: SubmissionDTO[],
                     *     reached_stop: bool
                     * } $response
                     */
                    $response = $this->adapter->getUserSubmissions([
                        'contestId' => $contest->platform_contest_id,
                        'handle' => $normalizedHandle,
                        'stopSubmissionId' => $lastSubmissionId,
                    ]);

                    $submissions = $response['submissions'];
                    $reachedStop = $response['reached_stop'];

                    $result->incrementFetched(count($submissions));

                    $highestSubmissionId = null;
                    $submissionCount = 0;

                    foreach ($submissions as $submissionDto) {

                        if (! $submissionDto instanceof SubmissionDTO) {
                            $result->incrementMetadata('submissions_skipped');
                            continue;
                        }

                        if ($highestSubmissionId === null) {
                            $highestSubmissionId = $submissionDto->platformSubmissionId;
                        }

                        $problem = $this->problemMap[$submissionDto->problemPlatformId] ?? null;

                        $submission = $this->persistSubmission(
                            $profile,
                            $contest,
                            $problem,
                            $submissionDto
                        );

                        if ($submission->wasRecentlyCreated) {
                            $result->incrementCreated();
                        } else {
                            $result->incrementUpdated();
                        }

                        $submissionCount++;
                    }

                    $this->platformSyncStateService->markSynced(
                        $syncState,
                        [
                            'profile_id' => $profile->id,
                            'handle' => $normalizedHandle,
                            'contest_platform_id' => $contest->platform_contest_id,
                            'submission_count' => $submissionCount,
                            'last_submission_id' => $highestSubmissionId,
                            'reached_stop' => $reachedStop,
                            'last_synced_at' => now(),
                        ]
                    );
                } catch (Throwable $e) {

                    $result->incrementFailed();

                    $this->platformSyncStateService->markFailed(
                        $syncState,
                        $e,
                        [
                            'profile_id' => $profile->id,
                            'contest_platform_id' => $contest->platform_contest_id,
                            'handle' => $normalizedHandle,
                        ]
                    );

                    app(ApplicationLogger::class)->error(
                        'AtCoder user submission import failed',
                        [
                            'category' => 'import',
                            'platform' => 'atcoder',
                            'source' => self::class,
                            'profile_id' => $profile->id,
                            'contest_id' => $contest->id,
                            'contest_platform_id' => $contest->platform_contest_id,
                            'handle' => $normalizedHandle,
                            'message' => $e->getMessage(),
                            'exception' => get_class($e),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ],
                        $e
                    );
                }
            }
        }

        $result->metadata = array_merge(
            $result->metadata,
            [
                'platform' => 'atcoder',
                'entity' => 'user_submission',
            ]
        );

        return $result;
    }

    private function persistSubmission(
        PlatformProfile $profile,
        Contest $contest,
        ?Problem $problem,
        SubmissionDTO $dto,
    ): Submission {

        return $this->submissionModel
            ->newQuery()
            ->updateOrCreate(

                [
                    'platform_id' => $profile->platform_id,
                    'platform_submission_id' => $dto->platformSubmissionId,
                ],

                [

                    'contest_id' => $contest->id,

                    'problem_id' => $problem?->id,

                    'platform_profile_id' => $profile->id,

                    'author_handle' => $dto->authorHandle,

                    'verdict' => $dto->verdict,

                    'language' => $dto->language,

                    'points' => $dto->points,

                    'passed_test_count' => $dto->passedTestCount,

                    'time_consumed_ms' => $dto->timeConsumedMillis,

                    'memory_consumed_bytes' => $dto->memoryConsumedBytes,

                    'submitted_at' => $dto->createdAtSeconds !== null
                        ? Carbon::createFromTimestamp($dto->createdAtSeconds)
                        : null,

                    'last_synced_at' => now(),

                    'metadata' => [

                        'source' => 'user-submission-import',

                        'platform' => 'atcoder',

                        'handle' => $profile->handle,

                        'contest_platform_id' => $contest->platform_contest_id,

                        'problem_platform_id' => $dto->problemPlatformId,

                        'synced_at' => now(),

                    ],

                    'raw' => $dto->raw,

                    'status' => 'Active',

                ]
            );
    }
}
