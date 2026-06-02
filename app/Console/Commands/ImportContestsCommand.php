<?php

namespace App\Console\Commands;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Models\Contest;
use App\Models\Platform;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use App\Services\PlatformSyncStateService;
use Illuminate\Console\Command;

class ImportContestsCommand extends Command
{
    protected $signature = 'judgearena:import-contests {platform}';

    protected $description = 'Import contests from a supported platform into the contests table.';

    public function __construct(
        private readonly CodeforcesAdapter $codeforcesAdapter,
        private readonly AtCoderAdapter $atCoderAdapter,
        private readonly PlatformSyncStateService $platformSyncStateService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower((string) $this->argument('platform'));
        $adapter = $this->resolveAdapter($platformSlug);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('Contest import skipped: unsupported platform', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platformSlug);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        $platform = Platform::query()->where('slug', $platformSlug)->first();

        if ($platform === null) {
            app(ApplicationLogger::class)->warning('Contest import skipped: platform record not found', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]);

            $this->error('Platform record not found for slug: ' . $platformSlug);

            return self::FAILURE;
        }

        app(ApplicationLogger::class)->info('Contest import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'source' => self::class,
        ]);

        try {
            $this->info('Fetching contests...');
            $progressBar = $this->output->createProgressBar(1);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            $progressBar->setMessage('Loading contest list');
            $progressBar->start();

            $contests = $adapter->getContests();
            $progressBar->setMessage('Contest list loaded');
            $progressBar->advance();
            $progressBar->finish();
            $this->newLine(2);

            $created = 0;
            $updated = 0;

            $this->line('Importing contests...');
            $importProgressBar = $this->output->createProgressBar(count($contests));
            $importProgressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            $importProgressBar->setMessage('Preparing contest import');
            $importProgressBar->start();

            foreach ($contests as $contestDto) {
                $importProgressBar->setMessage('Syncing ' . $contestDto->title);

                $contest = Contest::query()->updateOrCreate(
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
                    $created++;
                    $importProgressBar->advance();

                    continue;
                }

                $updated++;
                $importProgressBar->advance();
            }

            $importProgressBar->setMessage('Contest import finished');
            $importProgressBar->finish();
            $this->newLine(2);

            $this->line('Platform: ' . $platformSlug);
            $this->line('Total fetched: ' . count($contests));
            $this->line('Created: ' . $created);
            $this->line('Updated: ' . $updated);

            app(ApplicationLogger::class)->info('Contest import completed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'total_fetched' => count($contests),
                'created' => $created,
                'updated' => $updated,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            app(ApplicationLogger::class)->error('Contest import failed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'platform_id' => $platform->id,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Contest import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }

    private function resolveAdapter(string $platformSlug): ?PlatformAdapter
    {
        return match ($platformSlug) {
            'codeforces' => $this->codeforcesAdapter,
            'atcoder' => $this->atCoderAdapter,
            default => null,
        };
    }
}
