<?php

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class ImportProblemsCommand extends Command
{
    protected $signature = 'judgearena:import-problems {platform}';

    protected $description = 'Import problems from a supported platform.';

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
                'Problem import skipped: unsupported platform',
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
            'Problem import started',
            [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]
        );
        $this->info('Starting problem import for platform: ' . $platformSlug);

        try {
            $stats = $adapter
                ->problemImporter()
                ->import();

            $this->line('Platform: ' . $platformSlug);
            $this->line('Fetched: ' . ($stats['fetched'] ?? 0));
            $this->line('Created: ' . ($stats['created'] ?? 0));
            $this->line('Updated: ' . ($stats['updated'] ?? 0));
            $this->line('Failed: ' . ($stats['failed'] ?? 0));

            $this->info('Problem import completed successfully.');

            app(ApplicationLogger::class)->info(
                'Problem import completed',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                    'fetched' => $stats['fetched'] ?? 0,
                    'created' => $stats['created'] ?? 0,
                    'updated' => $stats['updated'] ?? 0,
                    'failed' => $stats['failed'] ?? 0,
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error(
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
