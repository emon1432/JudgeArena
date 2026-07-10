<?php

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class ImportSubmissionsCommand extends Command
{
    protected $signature = 'judgearena:import-submissions {platform}';

    protected $description = 'Import user submissions and persist them into the submissions table.';

    public function __construct(
        private readonly PlatformRegistry $platformRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));

        app(ApplicationLogger::class)->info('Submission import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'source' => self::class,
        ]);

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('Submission import skipped: unsupported platform', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platformSlug);
            $this->line('Supported platforms: ' . implode(', ', $this->platformRegistry->supportedPlatforms()));

            return self::FAILURE;
        }

        try {
            $stats = $adapter->submissionImporter()->import();

            $this->line('Platform: ' . $platformSlug);
            $this->line('Contests Checked: ' . ($stats['contests_checked'] ?? 0));
            $this->line('Contests Synced: ' . ($stats['contests_synced'] ?? 0));
            $this->line('Contests Already Synced: ' . ($stats['contests_already_synced'] ?? 0));
            $this->line('Contests Skipped: ' . ($stats['contests_skipped'] ?? 0));
            $this->line('Contests Failed: ' . ($stats['contests_failed'] ?? 0));
            $this->line('Submissions Fetched: ' . ($stats['submissions_fetched'] ?? 0));
            $this->line('Submissions Created: ' . ($stats['submissions_created'] ?? 0));
            $this->line('Submissions Updated: ' . ($stats['submissions_updated'] ?? 0));
            $this->line('Submissions Skipped: ' . ($stats['submissions_skipped'] ?? 0));

            app(ApplicationLogger::class)->info('Submission import completed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'contests_checked' => $stats['contests_checked'] ?? 0,
                'contests_synced' => $stats['contests_synced'] ?? 0,
                'contests_already_synced' => $stats['contests_already_synced'] ?? 0,
                'contests_skipped' => $stats['contests_skipped'] ?? 0,
                'contests_failed' => $stats['contests_failed'] ?? 0,
                'submissions_fetched' => $stats['submissions_fetched'] ?? 0,
                'submissions_created' => $stats['submissions_created'] ?? 0,
                'submissions_updated' => $stats['submissions_updated'] ?? 0,
                'submissions_skipped' => $stats['submissions_skipped'] ?? 0,
            ]);

            return ($stats['contests_failed'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('Submission import failed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Submission import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
