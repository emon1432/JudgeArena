<?php

namespace App\Console\Commands;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;

class SyncRatingChangesCommand extends Command
{
    protected $signature = 'judgearena:sync-rating-changes {platform} {contestId}';

    protected $description = 'Validate rating change adapters by fetching contest rating changes.';

    public function __construct(
        private readonly CodeforcesAdapter $codeforcesAdapter,
        private readonly AtCoderAdapter $atCoderAdapter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platform = strtolower(trim((string) $this->argument('platform')));
        $contestId = trim((string) $this->argument('contestId'));
        $adapter = $this->resolveAdapter($platform);

        app(ApplicationLogger::class)->info('Rating change sync validation started', [
            'category' => 'sync',
            'platform' => $platform,
            'contest_id' => $contestId,
            'source' => self::class,
        ]);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('Rating change sync validation skipped: unsupported platform', [
                'category' => 'sync',
                'platform' => $platform,
                'contest_id' => $contestId,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platform);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        $this->info('Fetching rating changes...');
        $progressBar = $this->output->createProgressBar(1);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Loading rating changes');
        $progressBar->start();

        try {
            $ratingChanges = $adapter->getRatingChanges($contestId);
            $progressBar->setMessage('Rating changes loaded');
            $progressBar->advance();
            $progressBar->finish();
            $this->newLine(2);

            $firstRatingChange = $ratingChanges[0] ?? null;

            $this->info('Platform: ' . $platform);
            $this->info('Contest ID: ' . $contestId);
            $this->info('Total Rating Changes: ' . count($ratingChanges));

            if ($firstRatingChange === null) {
                $this->warn('No rating changes found.');

                app(ApplicationLogger::class)->warning('Rating change sync validation returned no rating changes', [
                    'category' => 'sync',
                    'platform' => $platform,
                    'contest_id' => $contestId,
                    'source' => self::class,
                ]);

                return self::SUCCESS;
            }

            $this->info('First rating change:');
            $this->line('Handle: ' . $this->displayValue($firstRatingChange->handle));
            $this->line('Rank: ' . $this->displayValue($firstRatingChange->rank));
            $this->line('Old Rating: ' . $this->displayValue($firstRatingChange->oldRating));
            $this->line('New Rating: ' . $this->displayValue($firstRatingChange->newRating));

            app(ApplicationLogger::class)->info('Rating change sync validation completed', [
                'category' => 'sync',
                'platform' => $platform,
                'contest_id' => $contestId,
                'source' => self::class,
                'rating_change_count' => count($ratingChanges),
                'first_rating_change_handle' => $firstRatingChange->handle,
                'first_rating_change_rank' => $firstRatingChange->rank,
                'first_rating_change_old_rating' => $firstRatingChange->oldRating,
                'first_rating_change_new_rating' => $firstRatingChange->newRating,
            ]);

            return self::SUCCESS;
        } catch (\TypeError $e) {
            $progressBar->setMessage('Rating changes request failed');
            $progressBar->finish();
            $this->newLine(2);

            app(ApplicationLogger::class)->error('Rating change sync validation failed: contest ID type mismatch', [
                'category' => 'sync',
                'platform' => $platform,
                'contest_id' => $contestId,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Contest ID format is not supported by the current adapter signature: ' . $e->getMessage());

            return self::FAILURE;
        } catch (\Throwable $e) {
            $progressBar->setMessage('Rating changes request failed');
            $progressBar->finish();
            $this->newLine(2);

            app(ApplicationLogger::class)->error('Rating change sync validation failed', [
                'category' => 'sync',
                'platform' => $platform,
                'contest_id' => $contestId,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Error fetching rating changes: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function resolveAdapter(string $platform): ?PlatformAdapter
    {
        return match ($platform) {
            'codeforces' => $this->codeforcesAdapter,
            'atcoder' => $this->atCoderAdapter,
            default => null,
        };
    }

    private function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value) ?: 'N/A';
        }

        return (string) $value;
    }
}

