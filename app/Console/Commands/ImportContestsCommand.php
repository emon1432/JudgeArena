<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class ImportContestsCommand extends Command
{
    protected $signature = 'judgearena:import-contests {platform}';

    protected $description = 'Import contests from a supported platform.';

    public function __construct(
        private readonly PlatformRegistry $platformRegistry,
        private readonly ApplicationLogger $logger,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $platformSlug = strtolower(trim((string) $this->argument('platform')));

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            $this->logger->warning(
                'Contest import skipped: unsupported platform',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                ]
            );

            $this->error('Unsupported platform: '.$platformSlug);

            $this->line(
                'Supported platforms: '.
                    implode(', ', $this->platformRegistry->supportedPlatforms())
            );

            return self::FAILURE;
        }

        $this->logger->info(
            'Contest import started',
            [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]
        );

        $this->info('┌─────────────────────────────────────────────────────────────┐');
        $this->info('│  JudgeArena » Contest Importer                              │');
        $this->info('│  Platform: '.str_pad(ucfirst($platformSlug), 49).'│');
        $this->info('└─────────────────────────────────────────────────────────────┘');
        $this->newLine();

        $startTime = microtime(true);
        $progressBar = null;

        try {
            $result = $adapter
                ->contestImporter()
                ->import(
                    onProgress: function (int $total, int $current, string $message) use (&$progressBar) {
                        if ($total <= 0) {
                            return;
                        }
                        if ($progressBar === null) {
                            $progressBar = $this->output->createProgressBar($total);
                            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%%  ⏱ %elapsed:6s%  | %message%');
                            $progressBar->setBarCharacter('<fg=green>━</>');
                            $progressBar->setEmptyBarCharacter('<fg=gray>━</>');
                            $progressBar->setProgressCharacter('<fg=green>❯</>');
                            $progressBar->setMessage($message);
                            $progressBar->start();
                        } else {
                            $progressBar->setMessage($message);
                            $progressBar->setProgress($current);
                        }
                    }
                );

            if ($progressBar !== null) {
                $progressBar->finish();
                $this->newLine(2);
            }

            $duration = round(microtime(true) - $startTime, 2);

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Platform', ucfirst($platformSlug)],
                    ['Total Checked', number_format($result->checked)],
                    ['Total Fetched', number_format($result->fetched)],
                    ['Created', '<fg=green>'.number_format($result->created).'</>'],
                    ['Updated', '<fg=blue>'.number_format($result->updated).'</>'],
                    ['Skipped', '<fg=yellow>'.number_format($result->skipped).'</>'],
                    ['Failed', ($result->failed > 0 ? '<fg=red>' : '<fg=green>').number_format($result->failed).'</>'],
                    ['Status', $result->failed === 0 ? '<fg=green;options=bold>COMPLETED</>' : '<fg=yellow;options=bold>COMPLETED WITH ERRORS</>'],
                    ['Elapsed Time', sprintf('%.2f seconds', $duration)],
                ]
            );

            $this->info('Contest import completed successfully.');

            $this->logger->info(
                'Contest import completed',
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
                'Contest import failed',
                [
                    'category' => 'import',
                    'platform' => $platformSlug,
                    'source' => self::class,
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                $e
            );

            $this->error('Contest import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
