<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class ImportProblemsCommand extends Command
{
    protected $signature = 'judgearena:import-problems {platform} {--contest= : Specific contest platform ID to import} {--limit= : Number of contests to process in this batch} {--all : Process all contests sequentially}';

    protected $description = 'Import problems from a supported platform.';

    public function __construct(
        private readonly PlatformRegistry $platformRegistry,
        private readonly ApplicationLogger $logger,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $platformSlug = strtolower(trim((string) $this->argument('platform')));
        $contestPlatformId = $this->option('contest') ? (string) $this->option('contest') : null;
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $processAll = (bool) $this->option('all');

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            $this->logger->warning(
                'Problem import skipped: unsupported platform',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                ]
            );

            $this->error('Unsupported platform: '.$platformSlug);

            $this->line(
                'Supported platforms: '.
                    implode(', ', $this->platformRegistry->supportedPlatforms())
            );

            return self::FAILURE;
        }

        $this->logger->info(
            'Problem import started',
            [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'contest_platform_id' => $contestPlatformId,
                'limit' => $limit,
                'all' => $processAll,
            ]
        );
        $this->info('Starting problem import for platform: '.$platformSlug);

        try {
            if ($processAll) {
                $totalCreated = 0;
                $totalUpdated = 0;
                $totalSkipped = 0;
                $totalFailed = 0;
                $totalChecked = 0;

                $this->info("Processing all remaining contests in batches for {$platformSlug}...");
                do {
                    $result = $adapter->problemImporter()->import(limit: $limit ?? 20);
                    $totalChecked += $result->checked;
                    $totalCreated += $result->created;
                    $totalUpdated += $result->updated;
                    $totalSkipped += $result->skipped;
                    $totalFailed += $result->failed;

                    $this->line(sprintf(
                        'Batch: Checked: %d | Created: %d | Updated: %d | Skipped: %d | Failed: %d',
                        $result->checked,
                        $result->created,
                        $result->updated,
                        $result->skipped,
                        $result->failed
                    ));
                } while ($result->checked > 0);

                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Platform', $platformSlug],
                        ['Total Checked', $totalChecked],
                        ['Total Created', $totalCreated],
                        ['Total Updated', $totalUpdated],
                        ['Total Skipped', $totalSkipped],
                        ['Total Failed', $totalFailed],
                    ]
                );

                $this->info('All problem imports completed successfully.');

                return self::SUCCESS;
            }

            $result = $adapter
                ->problemImporter()
                ->import($contestPlatformId, $limit);

            $this->line('Platform: '.$platformSlug);
            $this->line('Checked: '.($result->checked ?? 0));
            $this->line('Fetched: '.($result->fetched ?? 0));
            $this->line('Created: '.($result->created ?? 0));
            $this->line('Updated: '.($result->updated ?? 0));
            $this->line('Skipped: '.($result->skipped ?? 0));
            $this->line('Failed: '.($result->failed ?? 0));
            $this->line('Synced: '.$result->synced());

            $this->info('Problem import completed successfully.');

            $this->logger->info(
                'Problem import completed',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                    'result' => $result->toArray(),
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->logger->error(
                'Problem import failed',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                $e
            );

            $this->error('Problem import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
