<?php

namespace App\Console\Commands;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;

class SyncProblemsCommand extends Command
{
    protected $signature = 'judgearena:sync-problems {platform}';

    protected $description = 'Validate problem adapters by fetching problem lists.';

    public function __construct(
        private readonly CodeforcesAdapter $codeforcesAdapter,
        private readonly AtCoderAdapter $atCoderAdapter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platform = strtolower((string) $this->argument('platform'));
        $adapter = $this->resolveAdapter($platform);

        app(ApplicationLogger::class)->info('Problem sync validation started', [
            'category' => 'sync',
            'platform' => $platform,
            'source' => self::class,
        ]);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('Problem sync validation skipped: unsupported platform', [
                'category' => 'sync',
                'platform' => $platform,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platform);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        $this->info('Fetching problem list...');
        $progressBar = $this->output->createProgressBar(1);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Loading problem list');
        $progressBar->start();

        try {
            $result = $adapter->getProblems();
        } catch (\Throwable $e) {
            $progressBar->setMessage('Problem list request failed');
            $progressBar->finish();
            $this->newLine(2);

            app(ApplicationLogger::class)->error('Problem sync validation failed', [
                'category' => 'sync',
                'platform' => $platform,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Error fetching problems: ' . $e->getMessage());

            return self::FAILURE;
        }

        $problems = $result['problems'] ?? [];
        $firstProblem = $problems[0] ?? null;

    $progressBar->setMessage('Problem list loaded');
    $progressBar->advance();
    $progressBar->finish();
    $this->newLine(2);

        $this->info('Platform: ' . $platform);
        $this->info('Total problems: ' . count($problems));

        if ($firstProblem === null) {
            $this->warn('No problems found.');

            app(ApplicationLogger::class)->warning('Problem sync validation returned no problems', [
                'category' => 'sync',
                'platform' => $platform,
                'source' => self::class,
            ]);

            return self::SUCCESS;
        }

        $this->info('First problem title: ' . $firstProblem->title);
        $this->info('First problem contestId: ' . ($firstProblem->contestPlatformId ?? 'N/A'));
        $this->info('First platformProblemId: ' . $firstProblem->platformProblemId);
        $this->info('First contestPlatformId: ' . ($firstProblem->contestPlatformId ?? 'N/A'));

        app(ApplicationLogger::class)->info('Problem sync validation completed', [
            'category' => 'sync',
            'platform' => $platform,
            'source' => self::class,
            'problem_count' => count($problems),
            'first_problem_platform_problem_id' => $firstProblem->platformProblemId,
            'first_problem_contest_platform_id' => $firstProblem->contestPlatformId ?? null,
        ]);

        return self::SUCCESS;
    }

    private function resolveAdapter(string $platform): ?PlatformAdapter
    {
        return match ($platform) {
            'codeforces' => $this->codeforcesAdapter,
            'atcoder' => $this->atCoderAdapter,
            default => null,
        };
    }
}
