<?php

namespace App\Console\Commands;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;

class SyncUserRatingHistoryCommand extends Command
{
    protected $signature = 'judgearena:sync-user-rating-history {platform} {handle}';

    protected $description = 'Validate user rating history adapters by fetching a user history timeline.';

    public function __construct(
        private readonly CodeforcesAdapter $codeforcesAdapter,
        private readonly AtCoderAdapter $atCoderAdapter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platform = strtolower(trim((string) $this->argument('platform')));
        $handle = trim((string) $this->argument('handle'));
        $adapter = $this->resolveAdapter($platform);

        app(ApplicationLogger::class)->info('User rating history sync validation started', [
            'category' => 'sync',
            'platform' => $platform,
            'handle' => $handle,
            'source' => self::class,
        ]);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('User rating history sync validation skipped: unsupported platform', [
                'category' => 'sync',
                'platform' => $platform,
                'handle' => $handle,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platform);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        $this->info('Fetching rating history...');
        $progressBar = $this->output->createProgressBar(1);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Loading rating history');
        $progressBar->start();

        try {
            $ratingHistory = $adapter->getUserRatingHistory($handle);
            $progressBar->setMessage('Rating history loaded');
            $progressBar->advance();
            $progressBar->finish();
            $this->newLine(2);

            $firstRatingChange = $ratingHistory[0] ?? null;

            $this->info('Platform: ' . $platform);
            $this->info('Handle: ' . $handle);
            $this->info('Total Rating History Rows: ' . count($ratingHistory));

            if ($firstRatingChange === null) {
                $this->warn('No rating history rows found.');

                app(ApplicationLogger::class)->warning('User rating history sync validation returned no rows', [
                    'category' => 'sync',
                    'platform' => $platform,
                    'handle' => $handle,
                    'source' => self::class,
                ]);

                return self::SUCCESS;
            }

            $this->info('First rating history row:');
            $this->line('Contest Platform ID: ' . $this->displayValue($firstRatingChange->contestPlatformId));
            $this->line('Handle: ' . $this->displayValue($firstRatingChange->handle));
            $this->line('Rank: ' . $this->displayValue($firstRatingChange->rank));
            $this->line('Old Rating: ' . $this->displayValue($firstRatingChange->oldRating));
            $this->line('New Rating: ' . $this->displayValue($firstRatingChange->newRating));
            $this->line('Rating Change: ' . $this->displayValue($firstRatingChange->ratingChange));
            $this->line('Is Rated: ' . $this->displayValue($firstRatingChange->isRated));

            app(ApplicationLogger::class)->info('User rating history sync validation completed', [
                'category' => 'sync',
                'platform' => $platform,
                'handle' => $handle,
                'source' => self::class,
                'rating_history_count' => count($ratingHistory),
                'first_rating_history_contest_platform_id' => $firstRatingChange->contestPlatformId,
                'first_rating_history_handle' => $firstRatingChange->handle,
                'first_rating_history_rank' => $firstRatingChange->rank,
                'first_rating_history_old_rating' => $firstRatingChange->oldRating,
                'first_rating_history_new_rating' => $firstRatingChange->newRating,
                'first_rating_history_rating_change' => $firstRatingChange->ratingChange,
                'first_rating_history_is_rated' => $firstRatingChange->isRated,
            ]);

            return self::SUCCESS;
        } catch (\TypeError $e) {
            $progressBar->setMessage('Rating history request failed');
            $progressBar->finish();
            $this->newLine(2);

            app(ApplicationLogger::class)->error('User rating history sync validation failed: handle type mismatch', [
                'category' => 'sync',
                'platform' => $platform,
                'handle' => $handle,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Handle format is not supported by the current adapter signature: ' . $e->getMessage());

            return self::FAILURE;
        } catch (\Throwable $e) {
            $progressBar->setMessage('Rating history request failed');
            $progressBar->finish();
            $this->newLine(2);

            app(ApplicationLogger::class)->error('User rating history sync validation failed', [
                'category' => 'sync',
                'platform' => $platform,
                'handle' => $handle,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Error fetching rating history: ' . $e->getMessage());

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
