<?php

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\ProblemImporter as ProblemImporterContract;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\Problem;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;


class ProblemImporter implements ProblemImporterContract
{
    public function __construct(
        private readonly Problem $problemModel,
        private readonly Contest $contestModel,
        private readonly Platform $platformModel,
        private readonly CodeforcesAdapter $adapter,
    ) {}

    public function import(): array
    {
        $stats = [
            'fetched' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
        ];

        $platform = $this->platformModel->newQuery()
            ->where('slug', 'codeforces')
            ->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->error(
                'Problem import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'codeforces',
                    'source' => self::class,
                    'message' => 'Platform "codeforces" not found in database',
                ]
            );

            return $stats;
        }

        $problems = $this->adapter->getProblems();
        $stats['fetched'] = count($problems);

        foreach ($problems as $problemDto) {
            try {
                $problem = $this->problemModel->newQuery()->updateOrCreate(
                    [
                        'platform_id' => $platform->id,
                        'platform_problem_id' => $problemDto->platformProblemId,
                    ],
                    [
                        'contest_id' => $this->contestModel->newQuery()->where('platform_id', $platform->id)->where('platform_contest_id', $problemDto->contestPlatformId)->first()?->id,
                        'slug' => slugify($problemDto->title),
                        'name' => $problemDto->title,
                        'code' => $problemDto->code,
                        'points' => $problemDto->points,
                        'rating' => $problemDto->rating,
                        'tags' => $problemDto->tags,
                        'last_synced_at' => now(),
                        'metadata' => [
                            'source' => 'contest-scoped-sync',
                            'platform' => $problemDto->platform,
                            'contest_platform_id' => $problemDto->contestPlatformId,
                        ],
                        'raw' => $problemDto->raw,
                        'status' => 'Active',
                    ]
                );

                if ($problem->wasRecentlyCreated) {
                    $stats['created']++;
                } else {
                    $stats['updated']++;
                }
            } catch (\Throwable $e) {
                $stats['failed']++;

                app(ApplicationLogger::class)->error(
                    'Problem import failed',
                    [
                        'category' => 'import',
                        'platform' => 'codeforces',
                        'source' => self::class,
                        'message' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    $e
                );
            }
        }

        return $stats;
    }
}
