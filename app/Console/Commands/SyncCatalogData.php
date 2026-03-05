<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Services\PlatformSync\CatalogSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncCatalogData extends Command
{
    protected $signature = 'judgearena:sync-catalog
                            {--platform=* : Optional platform names (e.g. codeforces leetcode)}
                            {--attempts=3 : Retry attempts per platform}
                            {--force : Ignore platform lock and run immediately}';

    protected $description = 'Sync global contest/problem catalogs separately from user submission/profile sync.';

    public function __construct(private readonly CatalogSyncService $catalogSyncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $selected = collect((array) $this->option('platform'))
            ->map(fn (string $name) => strtolower(trim($name)))
            ->filter()
            ->values();

        $attempts = max(1, (int) $this->option('attempts'));
        $force = (bool) $this->option('force');

        $query = Platform::query()->active();

        if ($selected->isNotEmpty()) {
            $query->whereIn('name', $selected->all());
        }

        $platforms = $query->get();

        if ($platforms->isEmpty()) {
            $this->warn('No active platform found for catalog sync.');
            return self::SUCCESS;
        }

        foreach ($platforms as $platform) {
            $this->syncSinglePlatform($platform, $attempts, $force);
        }

        return self::SUCCESS;
    }

    private function syncSinglePlatform(Platform $platform, int $attempts, bool $force): void
    {
        $lockName = 'catalog-sync:' . $platform->name;
        $lock = Cache::lock($lockName, 6 * 60 * 60);

        if (! $force && ! $lock->get()) {
            $this->warn("[{$platform->name}] Skipped (already running)");
            return;
        }

        $startedAt = now();
        $started = microtime(true);

        try {
            $result = $this->catalogSyncService->syncPlatform($platform, $attempts);
            $durationMs = (int) ((microtime(true) - $started) * 1000);

            Cache::put('catalog-sync:last:' . $platform->name, [
                'status' => $result['skipped'] ?? false ? 'skipped' : 'success',
                'started_at' => $startedAt->toDateTimeString(),
                'finished_at' => now()->toDateTimeString(),
                'duration_ms' => $durationMs,
                'result' => $result,
            ], now()->addDays(7));

            $this->info("[{$platform->name}] OK ({$durationMs}ms) contests={$result['contests_synced']} problems={$result['problems_synced']}");
        } catch (\Throwable $exception) {
            $durationMs = (int) ((microtime(true) - $started) * 1000);

            Cache::put('catalog-sync:last:' . $platform->name, [
                'status' => 'failed',
                'started_at' => $startedAt->toDateTimeString(),
                'finished_at' => now()->toDateTimeString(),
                'duration_ms' => $durationMs,
                'error' => $exception->getMessage(),
            ], now()->addDays(7));

            Log::error('Catalog sync failed', [
                'platform' => $platform->name,
                'duration_ms' => $durationMs,
                'error' => $exception->getMessage(),
            ]);

            $this->error("[{$platform->name}] FAILED: {$exception->getMessage()}");
        } finally {
            if (! $force) {
                optional($lock)->release();
            }
        }
    }
}
