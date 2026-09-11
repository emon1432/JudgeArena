<?php

namespace App\Console\Commands;

use App\Enums\SyncRunStatus;
use App\Services\ApplicationLogger;
use App\Services\SyncRunnerService;
use App\Services\SyncSchedulerService;
use Illuminate\Console\Command;

class SyncCommand extends Command
{
    protected $signature = 'judgearena:sync';

    protected $description = 'Run all due platform synchronization jobs.';

    public function __construct(
        private readonly ApplicationLogger $logger,
        private readonly SyncSchedulerService $scheduler,
        private readonly SyncRunnerService $runner,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $this->logger->info('Platform synchronization command started', [
            'category' => 'sync',
            'source' => self::class,
        ]);

        $this->info('┌─────────────────────────────────────────────────────────────┐');
        $this->info('│  JudgeArena » Master Platform Synchronizer                  │');
        $this->info('│  Mode:     Scheduled Due Jobs Sync                          │');
        $this->info('└─────────────────────────────────────────────────────────────┘');
        $this->newLine();

        $jobs = $this->scheduler->getDueJobs();

        if ($jobs->isEmpty()) {
            $this->logger->info('No synchronization jobs are due', [
                'category' => 'sync',
                'source' => self::class,
            ]);

            $this->info('No synchronization jobs are due.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d due synchronization job(s). Starting execution...', $jobs->count()));
        $this->newLine();

        $startTime = microtime(true);
        $progressBar = $this->output->createProgressBar($jobs->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%%  ⏱ %elapsed:6s%  | %message%');
        $progressBar->setBarCharacter('<fg=green>━</>');
        $progressBar->setEmptyBarCharacter('<fg=gray>━</>');
        $progressBar->setProgressCharacter('<fg=green>❯</>');

        $progressBar->setMessage('Preparing jobs...');
        $progressBar->start();

        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($jobs as $job) {
            $progressBar->setMessage(sprintf(
                'Syncing %s » %s',
                ucfirst($job->platform->slug),
                $job->entity->value,
            ));

            $status = $this->runner->run($job);

            match ($status) {
                SyncRunStatus::Success => $success++,
                SyncRunStatus::Skipped => $skipped++,
                SyncRunStatus::Failed => $failed++,
            };

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $duration = round(microtime(true) - $startTime, 2);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Due Jobs', number_format($jobs->count())],
                ['Successful', '<fg=green>'.number_format($success).'</>'],
                ['Skipped', '<fg=yellow>'.number_format($skipped).'</>'],
                ['Failed', ($failed > 0 ? '<fg=red>' : '<fg=green>').number_format($failed).'</>'],
                ['Status', $failed === 0 ? '<fg=green;options=bold>COMPLETED</>' : '<fg=yellow;options=bold>COMPLETED WITH ERRORS</>'],
                ['Elapsed Time', sprintf('%.2f seconds', $duration)],
            ]
        );

        $this->logger->info('Platform synchronization command completed', [
            'category' => 'sync',
            'source' => self::class,
            'due_jobs' => $jobs->count(),
            'successful' => $success,
            'failed' => $failed,
            'skipped' => $skipped,
        ]);

        return self::SUCCESS;
    }
}
