<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class ImportContestsCommand extends Command
{
    protected $signature = 'judgearena:import-contests {platform} {--full : Force a full deep scan of all archive pages}';

    protected $description = 'Import contests from a supported platform.';

    public function __construct(
        private readonly PlatformRegistry $platformRegistry,
        private readonly ApplicationLogger $logger,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));
        $fullSync = (bool) $this->option('full');

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            $this->logger->warning(
                'Contest import skipped: unsupported platform',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                ]
            );

            $this->error('Unsupported platform: ' . $platformSlug);

            $this->line(
                'Supported platforms: ' .
                    implode(', ', $this->platformRegistry->supportedPlatforms())
            );

            return self::FAILURE;
        }

        $this->logger->info(
            'Contest import started',
            [
                'category' => 'import',
                'platform' => $platformSlug,
                'full_sync' => $fullSync,
                'source' => self::class,
            ]
        );
        $this->info('Starting contest import for platform: ' . $platformSlug . ($fullSync ? ' [FULL SCAN]' : ' [INCREMENTAL]'));

        try {
            $result = $adapter
                ->contestImporter()
                ->import($fullSync);

            $this->line('Platform: ' . $platformSlug);
            $this->line('Checked: ' . $result->checked);
            $this->line('Fetched: ' . $result->fetched);
            $this->line('Created: ' . $result->created);
            $this->line('Updated: ' . $result->updated);
            $this->line('Skipped: ' . $result->skipped);
            $this->line('Failed: ' . $result->failed);
            $this->line('Synced: ' . $result->synced());

            $this->info('Contest import completed successfully.');

            $this->logger->info(
                'Contest import completed',
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
                'Contest import failed',
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

            $this->error('Contest import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
