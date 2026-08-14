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
        private readonly ApplicationLogger $logger,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));

        $this->logger->info('Standings import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'source' => self::class,
        ]);

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            $this->logger->warning(
                'Standings import skipped: unsupported platform',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                ]
            );

            $this->error('Unsupported platform: ' . $platformSlug);
            $this->line(
                'Supported platforms: ' . implode(', ', $this->platformRegistry->supportedPlatforms())
            );

            return self::FAILURE;
        }

        try {
            $result = $adapter->standingImporter()->import();

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
                        ->map(fn($value, $key) => [
                            $key,
                            is_scalar($value) ? $value : json_encode($value),
                        ])
                        ->values()
                        ->all()
                );
            }

            $this->info('Standings import completed successfully.');

            $this->logger->info(
                'Standings import completed',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                    'result' => $result->toArray(),
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->logger->error(
                'Standings import failed',
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

            $this->error('Standings import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
