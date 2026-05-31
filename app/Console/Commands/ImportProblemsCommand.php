<?php

namespace App\Console\Commands;

use App\Models\Contest;
use App\Models\Problem;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;

class ImportProblemsCommand extends Command
{
    protected $signature = 'judgearena:import-problems {platform?}';

    protected $description = 'Import problems by delegating synchronization to the problem sync service.';

    public function __construct(
        private readonly Contest $contestModel,
        private readonly Problem $problemModel,
        private readonly CodeforcesAdapter $codeforcesAdapter,
        private readonly AtCoderAdapter $atCoderAdapter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platform = $this->argument('platform');
        $platformSlug = is_string($platform) ? strtolower(trim($platform)) : null;
        $platformLabel = $platformSlug !== null && $platformSlug !== '' ? $platformSlug : 'all';

        app(ApplicationLogger::class)->info('Problem import started', [
            'category' => 'import',
            'platform' => $platformLabel,
            'source' => self::class,
        ]);

        try {
            $stats = $this->sync(
                $platformLabel === 'all' ? null : $platformLabel
            );
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('Problem import failed', [
                'category' => 'import',
                'platform' => $platformLabel,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Problem import failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $this->line('Platform: ' . $platformLabel);
        $this->line('Contests Checked: ' . ($stats['contests_checked'] ?? 0));
        $this->line('Contests Synced: ' . ($stats['contests_synced'] ?? 0));
        $this->line('Contests Already Synced: ' . ($stats['contests_already_synced'] ?? 0));
        $this->line('Contests Failed: ' . ($stats['contests_failed'] ?? 0));
        $this->line('Unsupported Platform Contests: ' . ($stats['contests_unsupported_platform'] ?? 0));
        $this->line('Problems Fetched: ' . ($stats['problems_fetched'] ?? 0));
        $this->line('Problems Created: ' . ($stats['problems_created'] ?? 0));
        $this->line('Problems Updated: ' . ($stats['problems_updated'] ?? 0));
        $this->info('Problem import completed successfully.');

        app(ApplicationLogger::class)->info('Problem import completed', [
            'category' => 'import',
            'platform' => $platformLabel,
            'source' => self::class,
            'contests_checked' => $stats['contests_checked'] ?? 0,
            'contests_synced' => $stats['contests_synced'] ?? 0,
            'contests_already_synced' => $stats['contests_already_synced'] ?? 0,
            'contests_failed' => $stats['contests_failed'] ?? 0,
            'contests_unsupported_platform' => $stats['contests_unsupported_platform'] ?? 0,
            'problems_fetched' => $stats['problems_fetched'] ?? 0,
            'problems_created' => $stats['problems_created'] ?? 0,
            'problems_updated' => $stats['problems_updated'] ?? 0,
        ]);

        return self::SUCCESS;
    }

    private function sync(?string $platformSlug = null): array
    {
        $query = $this->contestModel->newQuery()
            ->with('platform')
            ->withCount('problems');

        $normalizedPlatformSlug = $this->normalizePlatformSlug($platformSlug);
        if ($normalizedPlatformSlug !== null) {
            $query->whereHas('platform', function ($platformQuery) use ($normalizedPlatformSlug): void {
                $platformQuery->where('slug', $normalizedPlatformSlug);
            });
        }

        $contests = $query->get();
        // TODO: Replace problems_count heuristic with explicit sync tracking using platform_sync_states.
        // Reason: A contest may contain some imported problems while still being incomplete.
        $pendingContests = $contests->filter(
            fn(Contest $contest): bool => (int) ($contest->problems_count ?? 0) === 0
        );
        $pendingContestCount = $pendingContests->count();

        $progressBar = null;
        if ($pendingContestCount > 0) {
            $progressBar = $this->output->createProgressBar($pendingContestCount);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            $progressBar->setMessage('Preparing contest sync');
            $progressBar->start();
        } else {
            $this->info('No contests require problem sync.');
        }

        $stats = [
            'contests_checked' => $contests->count(),
            'contests_synced' => 0,
            'contests_already_synced' => $contests->filter(
                fn(Contest $contest): bool => (int) ($contest->problems_count ?? 0) > 0
            )->count(),
            'contests_failed' => 0,
            'contests_unsupported_platform' => 0,
            'problems_fetched' => 0,
            'problems_created' => 0,
            'problems_updated' => 0,
        ];

        /** @var Collection<string, Collection<int, Contest>> $contestsByPlatform */
        $contestsByPlatform = $pendingContests->groupBy(function (Contest $contest): string {
            return (string) ($contest->platform?->slug ?? '');
        });

        foreach ($contestsByPlatform as $platformSlugKey => $platformContests) {
            $adapter = $this->resolveAdapter($platformSlugKey);

            if ($adapter === null) {
                $stats['contests_unsupported_platform'] += $platformContests->count();

                app(ApplicationLogger::class)->warning('Skipping contests for unsupported platform', [
                    'category' => 'sync',
                    'platform' => $platformSlugKey,
                    'source' => self::class,
                    'contest_count' => $platformContests->count(),
                ]);

                if ($progressBar !== null) {
                    $progressBar->setMessage('Skipping unsupported platform ' . $platformSlugKey);
                    $progressBar->advance($platformContests->count());
                }

                continue;
            }

            foreach ($platformContests as $contest) {
                if ($progressBar !== null) {
                    $progressBar->setMessage(sprintf(
                        'Syncing contest %s',
                        $contest->name !== '' ? $contest->name : (string) $contest->id
                    ));
                }

                try {
                    $result = $adapter->getContestProblems((string) $contest->platform_contest_id);
                    $problems = $result['problems'] ?? [];

                    if (! is_array($problems)) {
                        $problems = [];
                    }

                    $stats['problems_fetched'] += count($problems);

                    foreach ($problems as $problemDto) {
                        $problem = $this->problemModel->newQuery()->updateOrCreate(
                            [
                                'platform_id' => $contest->platform_id,
                                'platform_problem_id' => $problemDto->platformProblemId,
                            ],
                            [
                                'contest_id' => $contest->id,
                                'slug' => slugify($problemDto->title),
                                'name' => $problemDto->title,
                                'code' => $problemDto->code,
                                'points' => $problemDto->points,
                                'rating' => $problemDto->rating,
                                'tags' => $problemDto->tags,
                                'last_synced_at' => now(),
                                'metadata' => [
                                    'source' => 'contest-scoped-sync',
                                    'platform' => $problemDto->platform,
                                    'contest_platform_id' => $contest->platform_contest_id,
                                ],
                                'raw' => $problemDto->raw,
                                'status' => 'Active',
                            ]
                        );

                        if ($problem->wasRecentlyCreated) {
                            $stats['problems_created']++;

                            continue;
                        }

                        $stats['problems_updated']++;
                    }

                    $stats['contests_synced']++;
                } catch (Throwable $e) {
                    $stats['contests_failed']++;

                    app(ApplicationLogger::class)->error('Problem sync failed', [
                        'category' => 'sync',
                        'platform' => $platformSlugKey,
                        'source' => self::class,
                        'contest_id' => $contest->id,
                        'platform_contest_id' => $contest->platform_contest_id,
                        'contest_name' => $contest->name,
                        'message' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ], $e);
                } finally {
                    if ($progressBar !== null) {
                        $progressBar->advance();
                    }
                }
            }
        }

        if ($progressBar !== null) {
            $progressBar->setMessage('Contest sync finished');
            $progressBar->finish();
            $this->newLine(2);
        }

        return $stats;
    }

    private function resolveAdapter(string $platformSlug): CodeforcesAdapter|AtCoderAdapter|null
    {
        return match (strtolower(trim($platformSlug))) {
            'codeforces' => $this->codeforcesAdapter,
            'atcoder' => $this->atCoderAdapter,
            default => null,
        };
    }

    private function normalizePlatformSlug(?string $platformSlug): ?string
    {
        $platformSlug = trim((string) $platformSlug);

        return $platformSlug === '' ? null : strtolower($platformSlug);
    }
}
