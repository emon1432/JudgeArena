<?php

namespace App\Console\Commands;

use App\Core\Platforms\PlatformRegistry;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class ImportUsersCommand extends Command
{
    protected $signature = 'judgearena:import-users {platform} {handle?}';

    protected $description = 'Import user profiles by delegating synchronization to the platform profile table.';

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
        $handle = trim((string) $this->argument('handle')) ?: null;

        $this->logger->info('User import started', [
            'category' => 'import',
            'platform' => $platformSlug,
            'source' => self::class,
        ]);

        $adapter = $this->platformRegistry->resolve($platformSlug);

        if ($adapter === null) {
            $this->logger->warning('User import skipped: unsupported platform', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: '.$platformSlug);
            $this->line('Supported platforms: '.implode(', ', $this->platformRegistry->supportedPlatforms()));

            return self::FAILURE;
        }

        $this->info('┌─────────────────────────────────────────────────────────────┐');
        $this->info('│  JudgeArena » User Profile Importer                         │');
        $this->info('│  Platform: '.str_pad(ucfirst($platformSlug), 49).'│');
        if ($handle !== null) {
            $this->info('│  Target:   '.str_pad($handle, 49).'│');
        }
        $this->info('└─────────────────────────────────────────────────────────────┘');
        $this->newLine();

        $startTime = microtime(true);
        $progressBar = null;

        try {
            $result = $adapter
                ->userImporter()
                ->import(
                    $handle,
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
                    ['Profiles Checked', number_format($result->checked)],
                    ['Profiles Updated', '<fg=green>'.number_format($result->updated).'</>'],
                    ['Profiles Skipped', '<fg=yellow>'.number_format($result->skipped).'</>'],
                    ['Profiles Failed', ($result->failed > 0 ? '<fg=red>' : '<fg=green>').number_format($result->failed).'</>'],
                    ['Status', $result->failed === 0 ? '<fg=green;options=bold>COMPLETED</>' : '<fg=yellow;options=bold>COMPLETED WITH ERRORS</>'],
                    ['Elapsed Time', sprintf('%.2f seconds', $duration)],
                ]
            );

            $this->info('User import completed successfully.');

            $this->logger->info('User import completed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'result' => $result->toArray(),
            ]);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->logger->error('User import failed', [
                'category' => 'import',
                'platform' => $platformSlug,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('User import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
