<?php

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class ImportContestsCommand extends Command
{
    protected $signature = 'judgearena:import-contests {platform}';

    protected $description = 'Import contests from a supported platform.';

    public function __construct(
        private readonly PlatformRegistry $platformRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning(
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

        app(ApplicationLogger::class)->info(
            'Contest import started',
            [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]
        );
        $this->info('Starting contest import for platform: ' . $platformSlug);

        try {
            $stats = $adapter
                ->contestImporter()
                ->import();

            $this->line('Platform: ' . $platformSlug);
            $this->line('Contests Checked: ' . ($stats['contests_checked'] ?? 0));
            $this->line('Contests Synced: ' . ($stats['contests_synced'] ?? 0));
            $this->line('Contests Already Synced: ' . ($stats['contests_already_synced'] ?? 0));
            $this->line('Contests Failed: ' . ($stats['contests_failed'] ?? 0));
            $this->line('Fetched: ' . ($stats['fetched'] ?? 0));
            $this->line('Created: ' . ($stats['created'] ?? 0));
            $this->line('Updated: ' . ($stats['updated'] ?? 0));
            $this->line('Failed: ' . ($stats['failed'] ?? 0));

            $this->info('Contest import completed successfully.');

            app(ApplicationLogger::class)->info(
                'Contest import completed',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                    'contests_checked' => $stats['contests_checked'] ?? 0,
                    'contests_synced' => $stats['contests_synced'] ?? 0,
                    'contests_already_synced' => $stats['contests_already_synced'] ?? 0,
                    'contests_failed' => $stats['contests_failed'] ?? 0,
                    'fetched' => $stats['fetched'] ?? 0,
                    'created' => $stats['created'] ?? 0,
                    'updated' => $stats['updated'] ?? 0,
                    'failed' => $stats['failed'] ?? 0,
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error(
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
