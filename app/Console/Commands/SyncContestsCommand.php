<?php

namespace App\Console\Commands;

use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
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
            $this->error('Unsupported platform: ' . $platform);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        $contests = $adapter->getContests();
        $firstContest = $contests[0] ?? null;

        $this->info('Platform: ' . $platform);
        $this->info('Total contests: ' . count($contests));
        $this->info('First contest title: ' . ($firstContest?->title ?? 'N/A'));
        $this->info('First contest platformContestId: ' . ($firstContest?->platformContestId ?? 'N/A'));

        return self::SUCCESS;
    }
}
