<?php

namespace App\Console\Commands;

use App\Models\ApplicationLog;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;

class CleanupLogsCommand extends Command
{
    public function __construct(
        private readonly ApplicationLogger $logger,
    ) {
        parent::__construct();
    }
    protected $signature = 'judgearena:cleanup-logs {--retention-days=} {--critical-retention-days=}';

    protected $description = 'Remove old application logs according to configured retention policies.';

    public function handle(): int
    {
        $retentionDays = (int) ($this->option('retention-days') ?: config('app.application_logs.retention_days', 90));
        $criticalRetentionDays = (int) ($this->option('critical-retention-days') ?: config('app.application_logs.critical_retention_days', 365));

        $this->logger->info('Application log cleanup started', [
            'category' => 'system',
            'source' => self::class,
            'retention_days' => $retentionDays,
            'critical_retention_days' => $criticalRetentionDays,
        ]);

        $normalCutoff = now()->subDays($retentionDays);
        $criticalCutoff = now()->subDays($criticalRetentionDays);

        $deletedNormalLogs = ApplicationLog::query()
            ->where('level', '<>', 'critical')
            ->where('created_at', '<', $normalCutoff)
            ->delete();

        $deletedCriticalLogs = ApplicationLog::query()
            ->where('level', 'critical')
            ->where('created_at', '<', $criticalCutoff)
            ->delete();

        $this->logger->info('Application log cleanup completed', [
            'category' => 'system',
            'source' => self::class,
            'deleted_normal_logs' => $deletedNormalLogs,
            'deleted_critical_logs' => $deletedCriticalLogs,
            'retention_days' => $retentionDays,
            'critical_retention_days' => $criticalRetentionDays,
        ]);

        $this->info('Deleted ' . $deletedNormalLogs . ' normal logs and ' . $deletedCriticalLogs . ' critical logs.');

        return self::SUCCESS;
    }
}
