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
        private readonly ApplicationLogger $logger,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));

        $this->logger->info('Rating change import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'source' => self::class,
        ]);

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            $this->logger->warning('Rating change import skipped: unsupported platform', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platformSlug);
            $this->line('Supported platforms: ' . implode(', ', $this->platformRegistry->supportedPlatforms()));

            return self::FAILURE;
        }

        try {
            $result = $adapter->ratingChangeImporter()->import();

            $this->line('Platform: ' . $platformSlug);
            $this->line('Checked: ' . $result->checked);
            $this->line('Fetched: ' . $result->fetched);
            $this->line('Created: ' . $result->created);
            $this->line('Updated: ' . $result->updated);
            $this->line('Skipped: ' . $result->skipped);
            $this->line('Failed: ' . $result->failed);
            $this->line('Synced: ' . ($result->synced ?? 0));
            $this->info('Rating change import completed successfully.');

            $this->logger->info(
                'Rating change import completed',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                    'result' => $result->toArray(),
                ]
            );
            if (! empty($result->metadata)) {
                $this->newLine();
                $this->table(
                    ['Key', 'Value'],
                    collect($result->metadata)
                        ->map(fn($value, $key) => [$key, is_scalar($value) ? $value : json_encode($value)])
                        ->values()
                        ->all()
                );
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->logger->error('Rating change import failed', [
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
