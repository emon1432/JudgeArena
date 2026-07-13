<?php

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class ImportUserRatingHistoryCommand extends Command
{
    protected $signature = 'judgearena:import-user-rating-history {platform} {handle?}';
    protected $description = 'Import user rating history by delegating synchronization to the platform rating history table.';

    public function __construct(
        private readonly PlatformRegistry $platformRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));
        $handle = trim((string) $this->argument('handle')) ?: null;

        app(ApplicationLogger::class)->info('User rating history import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'source' => self::class,
        ]);

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('User rating history import skipped: unsupported platform', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platformSlug);
            $this->line('Supported platforms: ' . implode(', ', $this->platformRegistry->supportedPlatforms()));

            return self::FAILURE;
        }

        try {
            $result = $adapter
                ->userRatingHistoryImporter()
                ->import($handle);

            $this->line('Platform: ' . $platformSlug);
            $this->line('Checked: ' . $result->checked);
            $this->line('Created: ' . $result->created);
            $this->line('Updated: ' . $result->updated);
            $this->line('Failed: ' . $result->failed);
            $this->line('Skipped: ' . $result->skipped);
            $this->info('User rating history import completed successfully.');

            app(ApplicationLogger::class)->info('User rating history import completed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'result' => $result->toArray(),
            ]);

            return self::SUCCESS;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('User rating history import failed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('User rating history import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
