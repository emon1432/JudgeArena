<?php

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class ImportStandingsCommand extends Command
{
    protected $signature = 'judgearena:import-standings {platform}';

    protected $description = 'Import contest standings and standing task results.';

    public function __construct(
        private readonly PlatformRegistry $platformRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));

        app(ApplicationLogger::class)->info('Standings import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'source' => self::class,
        ]);

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('Standings import skipped: unsupported platform', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platformSlug);
            $this->line('Supported platforms: ' . implode(', ', $this->platformRegistry->supportedPlatforms()));

            return self::FAILURE;
        }

        try {
            $stats = $adapter->standingsImporter()->import();

            $this->line('Platform: ' . $platformSlug);
            $this->line('Contests Checked: ' . ($stats['contests_checked'] ?? 0));
            $this->line('Contests Synced: ' . ($stats['contests_synced'] ?? 0));
            $this->line('Contests Already Synced: ' . ($stats['contests_already_synced'] ?? 0));
            $this->line('Contests Failed: ' . ($stats['contests_failed'] ?? 0));
            $this->line('Unsupported Platform Contests: ' . ($stats['contests_unsupported_platform'] ?? 0));
            $this->line('Standings Fetched: ' . ($stats['standings_fetched'] ?? 0));
            $this->line('Standings Created: ' . ($stats['standings_created'] ?? 0));
            $this->line('Standings Updated: ' . ($stats['standings_updated'] ?? 0));
            $this->line('Task Results Created: ' . ($stats['task_results_created'] ?? 0));
            $this->line('Task Results Updated: ' . ($stats['task_results_updated'] ?? 0));
            $this->line('Task Results Skipped: ' . ($stats['task_results_skipped'] ?? 0));
            $this->info('Standings import completed successfully.');

            app(ApplicationLogger::class)->info('Standings import completed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'contests_checked' => $stats['contests_checked'] ?? 0,
                'contests_synced' => $stats['contests_synced'] ?? 0,
                'contests_already_synced' => $stats['contests_already_synced'] ?? 0,
                'contests_failed' => $stats['contests_failed'] ?? 0,
                'contests_unsupported_platform' => $stats['contests_unsupported_platform'] ?? 0,
                'standings_fetched' => $stats['standings_fetched'] ?? 0,
                'standings_created' => $stats['standings_created'] ?? 0,
                'standings_updated' => $stats['standings_updated'] ?? 0,
                'task_results_created' => $stats['task_results_created'] ?? 0,
                'task_results_updated' => $stats['task_results_updated'] ?? 0,
                'task_results_skipped' => $stats['task_results_skipped'] ?? 0,
            ]);

            return self::SUCCESS;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('Standings import failed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Standings import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
