<?php

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class ImportRatingChangesCommand extends Command
{
    protected $signature = 'judgearena:import-rating-changes {platform}';

    protected $description = 'Import contest rating changes by delegating synchronization to the contest rating change table.';

    public function __construct(
        private readonly PlatformRegistry $platformRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));

        app(ApplicationLogger::class)->info('Rating change import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'source' => self::class,
        ]);

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('Rating change import skipped: unsupported platform', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platformSlug);
            $this->line('Supported platforms: ' . implode(', ', $this->platformRegistry->supportedPlatforms()));

            return self::FAILURE;
        }

        try {
            $stats = $adapter->ratingChangeImporter()->import();

            $this->line('Platform: ' . $platformSlug);
            $this->line('Contests Checked: ' . ($stats['contests_checked'] ?? 0));
            $this->line('Contests Synced: ' . ($stats['contests_synced'] ?? 0));
            $this->line('Contests Already Synced: ' . ($stats['contests_already_synced'] ?? 0));
            $this->line('Contests Failed: ' . ($stats['contests_failed'] ?? 0));
            $this->line('Unsupported Platform Contests: ' . ($stats['contests_unsupported_platform'] ?? 0));
            $this->line('Rating Changes Fetched: ' . ($stats['rating_changes_fetched'] ?? 0));
            $this->line('Rating Changes Created: ' . ($stats['rating_changes_created'] ?? 0));
            $this->line('Rating Changes Updated: ' . ($stats['rating_changes_updated'] ?? 0));
            $this->info('Rating change import completed successfully.');

            app(ApplicationLogger::class)->info('Rating change import completed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'contests_checked' => $stats['contests_checked'] ?? 0,
                'contests_synced' => $stats['contests_synced'] ?? 0,
                'contests_already_synced' => $stats['contests_already_synced'] ?? 0,
                'contests_failed' => $stats['contests_failed'] ?? 0,
                'contests_unsupported_platform' => $stats['contests_unsupported_platform'] ?? 0,
                'rating_changes_fetched' => $stats['rating_changes_fetched'] ?? 0,
                'rating_changes_created' => $stats['rating_changes_created'] ?? 0,
                'rating_changes_updated' => $stats['rating_changes_updated'] ?? 0,
            ]);

            return self::SUCCESS;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('Rating change import failed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Rating change import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}

