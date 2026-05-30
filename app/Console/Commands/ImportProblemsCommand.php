<?php

namespace App\Console\Commands;

use App\Services\ProblemSyncService;
use Illuminate\Console\Command;

class ImportProblemsCommand extends Command
{
    protected $signature = 'judgearena:import-problems {platform?}';

    protected $description = 'Import problems by delegating synchronization to the problem sync service.';

    public function __construct(
        private readonly ProblemSyncService $problemSyncService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platform = $this->argument('platform');
        $platformSlug = is_string($platform) ? strtolower(trim($platform)) : null;
        $platformLabel = $platformSlug !== null && $platformSlug !== '' ? $platformSlug : 'all';

        try {
            $stats = $this->problemSyncService->sync(
                $platformLabel === 'all' ? null : $platformLabel
            );
        } catch (\Throwable $e) {
            $this->error('Problem import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $this->line('Platform: ' . $platformLabel);
        $this->line('Contests Checked: ' . ($stats['contests_checked'] ?? 0));
        $this->line('Contests Synced: ' . ($stats['contests_synced'] ?? 0));
        $this->line('Contests Skipped: ' . ($stats['contests_skipped'] ?? 0));
        $this->line('Problems Fetched: ' . ($stats['problems_fetched'] ?? 0));
        $this->line('Problems Created: ' . ($stats['problems_created'] ?? 0));
        $this->line('Problems Updated: ' . ($stats['problems_updated'] ?? 0));
        $this->info('Problem import completed successfully.');
        return self::SUCCESS;
    }
}
