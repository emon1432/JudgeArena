<?php

namespace App\Console\Commands;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;

class SyncUserCommand extends Command
{
    protected $signature = 'judgearena:sync-user {platform} {handle}';

    protected $description = 'Validate a user profile lookup for a supported platform.';

    public function __construct(
        private readonly CodeforcesAdapter $codeforcesAdapter,
        private readonly AtCoderAdapter $atCoderAdapter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));
        $handle = trim((string) $this->argument('handle'));
        $adapter = $this->resolveAdapter($platformSlug);

        app(ApplicationLogger::class)->info('User sync validation started', [
            'category' => 'sync',
            'platform' => $platformSlug,
            'handle' => $handle,
            'source' => self::class,
        ]);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('User sync validation skipped: unsupported platform', [
                'category' => 'sync',
                'platform' => $platformSlug,
                'handle' => $handle,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platformSlug);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        $this->info('Fetching user profile...');
        $progressBar = $this->output->createProgressBar(1);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Loading user profile');
        $progressBar->start();

        try {
            $user = $adapter->getUser($handle);
            $progressBar->setMessage('User profile loaded');
            $progressBar->advance();
            $progressBar->finish();
            $this->newLine(2);

            $this->line('Platform: ' . $user->platform);
            $this->line('Handle: ' . $user->platformHandle);
            $this->line('Country: ' . ($user->country ?? 'N/A'));
            $this->line('Rating: ' . ($user->rating !== null ? (string) $user->rating : 'N/A'));

            app(ApplicationLogger::class)->info('User sync validation completed', [
                'category' => 'sync',
                'platform' => $platformSlug,
                'handle' => $handle,
                'source' => self::class,
                'country' => $user->country,
                'rating' => $user->rating,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $progressBar->setMessage('User profile request failed');
            $progressBar->finish();
            $this->newLine(2);

            app(ApplicationLogger::class)->error('User sync validation failed', [
                'category' => 'sync',
                'platform' => $platformSlug,
                'handle' => $handle,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Error fetching user profile: ' . $e->getMessage());

            return self::FAILURE;
        }
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
