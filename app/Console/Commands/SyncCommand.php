<?php

namespace App\Console\Commands;

use App\Enums\SyncRunStatus;
use App\Services\ApplicationLogger;
use App\Services\SyncRunnerService;
use App\Services\SyncSchedulerService;
use Illuminate\Console\Command;
use Throwable;

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
        $this->logger->info('Platform synchronization command started', [
            'category' => 'sync',
            'source' => self::class,
        ]);

        $jobs = $this->scheduler->getDueJobs();

        if ($jobs->isEmpty()) {
            $this->logger->info('No synchronization jobs are due', [
                'category' => 'sync',
                'source' => self::class,
            ]);

            $this->info('No synchronization jobs are due.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Found %d synchronization job(s).',
            $jobs->count()
        ));

        $progressBar = $this->output->createProgressBar($jobs->count());
        $progressBar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% %message%'
        );

        $progressBar->setMessage('Preparing...');
        $progressBar->start();

        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($jobs as $job) {
            $progressBar->setMessage(sprintf(
                '%s - %s',
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

        $this->table(
            ['Metric', 'Value'],
            [
                ['Due Jobs', $jobs->count()],
                ['Successful', $success],
                ['Failed', $failed],
                ['Skipped', $skipped],
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
