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
            $result = $adapter
                ->contestImporter()
                ->import();

            $this->line('Platform: ' . $platformSlug);
            $this->line('Checked: ' . $result->checked);
            $this->line('Fetched: ' . $result->fetched);
            $this->line('Created: ' . $result->created);
            $this->line('Updated: ' . $result->updated);
            $this->line('Skipped: ' . $result->skipped);
            $this->line('Failed: ' . $result->failed);
            $this->line('Synced: ' . $result->synced());

            $this->info('Contest import completed successfully.');

            app(ApplicationLogger::class)->info(
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
