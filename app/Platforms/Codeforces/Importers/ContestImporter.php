<?php

namespace App\Platforms\Codeforces\Importers;

use App\Core\Contracts\Importers\ContestImporter as ContestImporterContract;
use App\Models\Contest;
use App\Models\Platform;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;


class ContestImporter implements ContestImporterContract
{
    public function __construct(
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
                'Contest import failed: platform not found',
                [
                    'category' => 'import',
                    'platform' => 'codeforces',
                    'source' => self::class,
                    'message' => 'Platform "codeforces" not found in database',
                ]
            );

            return $stats;
        }

        $contests = $this->adapter->getContests();
        $stats['fetched'] = count($contests);

        foreach ($contests as $contestDto) {
            try {
                $contest = $this->contestModel->newQuery()->updateOrCreate(
                    [
                        'platform_id' => $platform->id,
                        'platform_contest_id' => $contestDto->platformContestId,
                    ],
                    [
                        'name' => $contestDto->title,
                        'phase' => $contestDto->phase,
                        'duration_seconds' => $contestDto->durationSeconds,
                        'start_time' => $contestDto->startedAt,
                        'metadata' => [
                            'source' => 'adapter',
                            'imported_at' => now(),
                        ],
                        'raw' => $contestDto->raw,
                    ],
                );

                if ($contest->wasRecentlyCreated) {
                    $stats['created']++;
                } else {
                    $stats['updated']++;
                }
            } catch (\Throwable $e) {
                $stats['failed']++;

                app(ApplicationLogger::class)->error(
                    'Contest import failed',
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
