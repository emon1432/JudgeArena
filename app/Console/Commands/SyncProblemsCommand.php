<?php

namespace App\Console\Commands;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Illuminate\Console\Command;

class SyncProblemsCommand extends Command
{
    protected $signature = 'judgearena:sync-problems {platform} {contestId?}';

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
        $contestId = trim((string) $this->argument('contestId'));
        $adapter = $this->resolveAdapter($platform);

        if ($adapter === null) {
            $this->error('Unsupported platform: ' . $platform);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        // Prevent accidental whole-platform crawl for AtCoder
        if ($platform === 'atcoder' && $contestId === '') {
            $this->warn(
                'Whole-platform problem validation is not recommended for AtCoder.'
            );

            $this->line(
                'Example: php artisan judgearena:sync-problems atcoder abc460'
            );

            return self::FAILURE;
        }

        try {
            $result = $contestId !== ''
                ? $adapter->getContestProblems($contestId)
                : $adapter->getProblems();
        } catch (\Throwable $e) {
            if ($contestId !== '') {
                $this->error('Contest not found or unavailable.');
                $this->line('Contest ID: ' . $contestId);
            } else {
                $this->error('Problem synchronization failed.');
            }

            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $problems = $result['problems'] ?? [];
        $firstProblem = $problems[0] ?? null;

        $this->info('Platform: ' . $platform);

        if ($contestId !== '') {
            $this->info('Contest ID: ' . $contestId);
        }

        $this->info('Total problems: ' . count($problems));

        if ($firstProblem === null) {
            $this->warn('No problems found.');

            return self::SUCCESS;
        }

        $this->info('First problem title: ' . $firstProblem->title);
        $this->info('First platformProblemId: ' . $firstProblem->platformProblemId);
        $this->info('First contestPlatformId: ' . ($firstProblem->contestPlatformId ?? 'N/A'));

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
