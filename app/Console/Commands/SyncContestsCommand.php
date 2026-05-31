<?php

namespace App\Console\Commands;

use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;

class SyncContestsCommand extends Command
{
    protected $signature = 'judgearena:sync-contests {platform}';

    protected $description = 'Validate contest adapters by fetching contest lists.';

    public function __construct(
        private readonly CodeforcesAdapter $codeforcesAdapter,
        private readonly AtCoderAdapter $atCoderAdapter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platform = strtolower((string) $this->argument('platform'));

        $adapter = match ($platform) {
            'codeforces' => $this->codeforcesAdapter,
            'atcoder' => $this->atCoderAdapter,
            default => null,
        };

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('Contest sync validation skipped: unsupported platform', [
                'category' => 'sync',
                'platform' => $platform,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platform);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        $this->info('Fetching contests...');
        $progressBar = $this->output->createProgressBar(1);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Loading contest list');
        $progressBar->start();

        try {
            $contests = $adapter->getContests();
            $progressBar->setMessage('Contest list loaded');
            $progressBar->advance();
            $progressBar->finish();
            $this->newLine(2);

            $firstContest = $contests[0] ?? null;

            $this->info('Platform: ' . $platform);
            $this->info('Total contests: ' . count($contests));
            $this->info('First contest title: ' . ($firstContest?->title ?? 'N/A'));
            $this->info('First contest platformContestId: ' . ($firstContest?->platformContestId ?? 'N/A'));

            app(ApplicationLogger::class)->info('Contest sync validation completed', [
                'category' => 'sync',
                'platform' => $platform,
                'source' => self::class,
                'contest_count' => count($contests),
                'first_contest_platform_contest_id' => $firstContest?->platformContestId ?? null,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $progressBar->setMessage('Contest list request failed');
            $progressBar->finish();
            $this->newLine(2);

            app(ApplicationLogger::class)->error('Contest sync validation failed', [
                'category' => 'sync',
                'platform' => $platform,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Error fetching contests: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
