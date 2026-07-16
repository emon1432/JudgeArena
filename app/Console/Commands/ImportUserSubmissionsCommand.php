<?php

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class ImportUserSubmissionsCommand extends Command
{
    protected $signature = 'judgearena:import-user-submissions {platform} {handle?}';
    protected $description = 'Import user submissions by delegating synchronization to the platform submissions table.';

    public function __construct(
        private readonly PlatformRegistry $platformRegistry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));
        $handle = trim((string) $this->argument('handle')) ?: null;

        app(ApplicationLogger::class)->info('User submissions import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'source' => self::class,
        ]);

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('User submissions import skipped: unsupported platform', [
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
                ->userSubmissionImporter()
                ->import($handle);

            $this->line('Platform: ' . $platformSlug);
            $this->line('Checked: ' . $result->checked);
            $this->line('Created: ' . $result->created);
            $this->line('Updated: ' . $result->updated);
            $this->line('Failed: ' . $result->failed);
            $this->line('Skipped: ' . $result->skipped);
            $this->info('User submissions import completed successfully.');

            app(ApplicationLogger::class)->info('User submissions import completed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'result' => $result->toArray(),
            ]);

            return self::SUCCESS;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('User submissions import failed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('User submissions import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
