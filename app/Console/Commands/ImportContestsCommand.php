<?php

namespace App\Console\Commands;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Models\Contest;
use App\Models\Platform;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Illuminate\Console\Command;

class ImportContestsCommand extends Command
{
    protected $signature = 'judgearena:import-contests {platform}';

    protected $description = 'Import contests from a supported platform into the contests table.';

    public function __construct(
        private readonly CodeforcesAdapter $codeforcesAdapter,
        private readonly AtCoderAdapter $atCoderAdapter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower((string) $this->argument('platform'));
        $adapter = $this->resolveAdapter($platformSlug);

        if ($adapter === null) {
            $this->error('Unsupported platform: ' . $platformSlug);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        $platform = Platform::query()->where('slug', $platformSlug)->first();

        if ($platform === null) {
            $this->error('Platform record not found for slug: ' . $platformSlug);

            return self::FAILURE;
        }

        $contests = $adapter->getContests();
        $created = 0;
        $updated = 0;

        foreach ($contests as $contestDto) {
            $contest = Contest::query()->updateOrCreate(
                [
                    'platform_id' => $platform->id,
                    'platform_contest_id' => $contestDto->platformContestId,
                ],
                [
                    'name' => $contestDto->title,
                    'phase' => $contestDto->phase,
                    'duration_seconds' => $contestDto->durationSeconds,
                    'start_time' => $contestDto->startedAt,
                    'metadata' => [
                        'source' => 'adapter',
                        'imported_at' => now(),
                    ],
                    'raw' => $contestDto->raw,
                ],
            );

            if ($contest->wasRecentlyCreated) {
                $created++;
                continue;
            }

            $updated++;
        }

        $this->line('Platform: ' . $platformSlug);
        $this->line('Total fetched: ' . count($contests));
        $this->line('Created: ' . $created);
        $this->line('Updated: ' . $updated);

        return self::SUCCESS;
    }

    private function resolveAdapter(string $platformSlug): ?PlatformAdapter
    {
        return match ($platformSlug) {
            'codeforces' => $this->codeforcesAdapter,
            'atcoder' => $this->atCoderAdapter,
            default => null,
        };
    }
}
