<?php

declare(strict_types=1);

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
        private readonly ApplicationLogger $logger,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));
        $handle = trim((string) $this->argument('handle')) ?: null;

        $this->logger->info('User rating history import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'source' => self::class,
        ]);

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            $this->logger->warning('User rating history import skipped: unsupported platform', [
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
            $this->line('Checked: ' . ($result->checked ?? 0));
            $this->line('Fetched: ' . ($result->fetched ?? 0));
            $this->line('Created: ' . ($result->created ?? 0));
            $this->line('Updated: ' . ($result->updated ?? 0));
            $this->line('Skipped: ' . ($result->skipped ?? 0));
            $this->line('Failed: ' . ($result->failed ?? 0));
            $this->line('Synced: ' . $result->synced());
            $this->info('User rating history import completed successfully.');

            $this->logger->info('User rating history import completed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'result' => $result->toArray(),
            ]);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->logger->error('User rating history import failed', [
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
