<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\UserSubmissionImporter as UserSubmissionImporterContract;
use App\Core\DTOs\SubmissionDTO;
use App\Core\Results\ImportResult;
use App\Enums\PlatformSyncEntityType;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\Problem;
use App\Models\Submission;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Carbon\Carbon;
use Throwable;


class UserSubmissionImporter implements UserSubmissionImporterContract
{
    private array $contestMap = [];
    private array $problemMap = [];
    private const PAGE_SIZE = 100;

    public function __construct(
        private readonly Submission $submissionModel,
        private readonly Problem $problemModel,
        private readonly Contest $contestModel,
        private readonly Platform $platformModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly CodeforcesAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(?string $handle = null): ImportResult
    {
        $result = new ImportResult();

        $platform = $this->platformModel
            ->newQuery()
            ->where('slug', 'codeforces')
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error(
                'User submissions import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'codeforces',
                    'source' => self::class,
                    'message' => 'Platform "codeforces" not found in database',
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

        $this->contestMap = $this->contestModel
            ->newQuery()
            ->where('platform_id', $platform->id)
            ->get()
            ->keyBy('platform_contest_id')
            ->all();

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

            $isSynced = $this->platformSyncStateService->isSynced(
                $platform,
                PlatformSyncEntityType::UserSubmissions,
                $normalizedHandle
            );

            // If handle is not explicitly specified and profile user submissions were already synced, skip!
            if ($handle === null && $isSynced) {
                $result->incrementSkipped();
                continue;
            }

            $syncState = $this->platformSyncStateService->markSyncing(
                $platform,
                PlatformSyncEntityType::UserSubmissions,
                $normalizedHandle,
                [
                    'profile_id' => $profile->id,
                    'handle' => $normalizedHandle,
                    'platform_slug' => 'codeforces',
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

                $highestSubmissionId = null;

                $from = 1;
                $submissionCount = 0;

                while (true) {
                    $params = [
                        'handle' => $normalizedHandle,
                        'from' => $from,
                        'count' => self::PAGE_SIZE,
                    ];
                    $submissions = $this->adapter->getUserSubmissions(
                        $params,
                    );

                    if (! is_array($submissions) || $submissions === []) {
                        break;
                    }

                    $result->incrementFetched(count($submissions));

                    $foundLastSubmission = false;

                    foreach ($submissions as $submissionDto) {

                        if (! $submissionDto instanceof SubmissionDTO) {

                            $result->incrementMetadata('submissions_skipped');

                            continue;
                        }

                        if ($highestSubmissionId === null) {
                            $highestSubmissionId = $submissionDto->platformSubmissionId;
                        }

                        if (
                            $lastSubmissionId !== null &&
                            $submissionDto->platformSubmissionId === $lastSubmissionId
                        ) {
                            $foundLastSubmission = true;

                            break;
                        }

                        $contest = null;

                        if ($submissionDto->contestPlatformId !== null) {
                            $contest = $this->contestMap[$submissionDto->contestPlatformId] ?? null;
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

                    if ($foundLastSubmission) {
                        break;
                    }

                    if (count($submissions) < self::PAGE_SIZE) {
                        break;
                    }

                    $from += self::PAGE_SIZE;
                }

                $this->platformSyncStateService->markSynced(
                    $syncState,
                    [
                        'profile_id' => $profile->id,
                        'handle' => $normalizedHandle,
                        'submission_count' => $submissionCount,
                        'last_submission_id' => $highestSubmissionId,
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
                        'handle' => $normalizedHandle,
                        'last_synced_at' => now(),
                    ]
                );

                app(ApplicationLogger::class)->error(
                    'User submission import failed',
                    [
                        'category' => 'import',
                        'platform' => 'codeforces',
                        'source' => self::class,
                        'profile_id' => $profile->id,
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

        $result->metadata = array_merge(
            $result->metadata,
            [
                'platform' => 'codeforces',
                'entity' => 'user_submission',
            ]
        );

        return $result;
    }

    private function persistSubmission(
        PlatformProfile $profile,
        ?Contest $contest,
        ?Problem $problem,
        SubmissionDTO $dto,
    ): Submission {
        return $this->submissionModel
            ->newQuery()
            ->updateOrCreate(
                [
                    'platform_submission_id' => $dto->platformSubmissionId,
                ],
                [
                    'platform_id' => $profile->platform_id,
                    'platform_profile_id' => $profile->id,

                    'contest_id' => $contest?->id,
                    'problem_id' => $problem?->id,

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
                        'platform' => 'codeforces',
                        'handle' => $profile->handle,
                        'contest_platform_id' => $dto->contestPlatformId,
                        'problem_platform_id' => $dto->problemPlatformId,
                        'testset' => $dto->testset,
                        'synced_at' => now(),
                    ],

                    'raw' => $dto->raw,

                    'status' => 'Active',
                ]
            );
    }
}

