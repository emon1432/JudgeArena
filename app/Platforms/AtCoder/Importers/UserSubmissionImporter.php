<?php

declare(strict_types=1);

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
    private array $contestMap = [];
    private array $problemMap = [];
    private const PAGE_SIZE = 500;

    public function __construct(
        private readonly Submission $submissionModel,
        private readonly Problem $problemModel,
        private readonly Contest $contestModel,
        private readonly Platform $platformModel,
        private readonly PlatformProfile $platformProfileModel,
        private readonly AtCoderAdapter $adapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {}

    public function import(?string $handle = null, bool $full = false): ImportResult
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
                    'message' => 'Platform "atcoder" not found in database',
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
            ->keyBy(fn (Contest $c): string => (string) $c->platform_contest_id)
            ->all();

        $this->problemMap = $this->problemModel
            ->newQuery()
            ->where('platform_id', $platform->id)
            ->get()
            ->keyBy(fn (Problem $p): string => (string) $p->platform_problem_id)
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

            // If handle is not explicitly specified and profile submissions were already synced, skip!
            if ($handle === null && $isSynced && ! $full) {
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
                    'platform_slug' => 'atcoder',
                ]
            );

            if ($syncState === null) {
                $result->incrementSkipped();
                continue;
            }

            try {
                $fromSecond = 0;
                if (! $full) {
                    $fromSecond = (int) data_get($syncState->metadata, 'last_epoch_second', 0);
                }

                $highestEpochSecond = $fromSecond;
                $submissionCount = 0;

                while (true) {
                    $response = $this->adapter->getUserSubmissions([
                        'handle' => $normalizedHandle,
                        'from_second' => $fromSecond,
                    ]);

                    $submissions = $response['submissions'] ?? (is_array($response) ? $response : []);

                    if ($submissions === []) {
                        break;
                    }

                    $result->incrementFetched(count($submissions));

                    $batchMaxEpoch = $fromSecond;

                    foreach ($submissions as $submissionDto) {
                        if (! $submissionDto instanceof SubmissionDTO) {
                            continue;
                        }

                        if ($submissionDto->createdAtSeconds !== null && $submissionDto->createdAtSeconds > $highestEpochSecond) {
                            $highestEpochSecond = $submissionDto->createdAtSeconds;
                        }
                        if ($submissionDto->createdAtSeconds !== null && $submissionDto->createdAtSeconds > $batchMaxEpoch) {
                            $batchMaxEpoch = $submissionDto->createdAtSeconds;
                        }

                        $contest = null;
                        if ($submissionDto->contestPlatformId !== null) {
                            $contest = $this->contestMap[$submissionDto->contestPlatformId] ?? null;
                        }

                        $probId = (string) $submissionDto->problemPlatformId;
                        $problem = $this->problemMap[$probId]
                            ?? $this->problemMap[strtolower($probId)]
                            ?? $this->problemMap[str_replace('_', '-', $probId)]
                            ?? null;

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

                    if (count($submissions) < self::PAGE_SIZE) {
                        break;
                    }

                    $fromSecond = $batchMaxEpoch > $fromSecond ? $batchMaxEpoch + 1 : $fromSecond + 1;
                }

                $this->platformSyncStateService->markSynced(
                    $syncState,
                    [
                        'profile_id' => $profile->id,
                        'handle' => $normalizedHandle,
                        'submission_count' => $submissionCount,
                        'last_epoch_second' => $highestEpochSecond,
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
                    ]
                );

                app(ApplicationLogger::class)->error(
                    'AtCoder user submission import failed',
                    [
                        'category' => 'import',
                        'platform' => 'atcoder',
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
                'platform' => 'atcoder',
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
                    'platform_id' => $profile->platform_id,
                    'platform_submission_id' => $dto->platformSubmissionId,
                ],
                [
                    'platform_profile_id' => $profile->id,
                    'contest_id' => $contest?->id,
                    'problem_id' => $problem?->id,
                    'author_handle' => $dto->authorHandle ?: $profile->handle,
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
                    'metadata' => array_merge(
                        [
                            'source' => 'user-submission-import',
                            'platform' => 'atcoder',
                            'handle' => $profile->handle,
                            'contest_platform_id' => $dto->contestPlatformId,
                            'problem_platform_id' => $dto->problemPlatformId,
                            'synced_at' => now(),
                        ],
                        $dto->raw
                    ),
                    'raw' => $dto->raw,
                    'status' => 'Active',
                ]
            );
    }
}
