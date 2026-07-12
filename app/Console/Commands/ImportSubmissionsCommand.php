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

        app(ApplicationLogger::class)->info(
            'Submission import started',
            [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]
        );

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning(
                'Submission import skipped: unsupported platform',
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

        try {
            $result = $adapter->submissionImporter()->import();

            $this->line('Platform: ' . $platformSlug);
            $this->line('Checked: ' . $result->checked);
            $this->line('Fetched: ' . $result->fetched);
            $this->line('Created: ' . $result->created);
            $this->line('Updated: ' . $result->updated);
            $this->line('Skipped: ' . $result->skipped);
            $this->line('Failed: ' . $result->failed);

            if (! empty($result->metadata)) {
                $this->newLine();

                $this->table(
                    ['Metric', 'Value'],
                    collect($result->metadata)
                        ->map(fn ($value, $key) => [
                            $key,
                            is_scalar($value) ? $value : json_encode($value),
                        ])
                        ->values()
                        ->all()
                );
            }

            $this->info('Submission import completed successfully.');

            app(ApplicationLogger::class)->info(
                'Submission import completed',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                    'result' => $result->toArray(),
                ]
            );

            return $result->failed > 0
                ? self::FAILURE
                : self::SUCCESS;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error(
                'Submission import failed',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                    'message' => $e->getMessage(),
                    'exception' => $e::class,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                $e
            );

            $this->error('Submission import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
