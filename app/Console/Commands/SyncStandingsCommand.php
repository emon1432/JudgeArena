<?php

namespace App\Console\Commands;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\DTOs\ParticipantDTO;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Throwable;

class SyncStandingsCommand extends Command
{
    protected $signature = 'judgearena:sync-standings {platform} {contestId}';

    protected $description = 'Validate standings adapters by fetching contest standings without persisting them.';

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

        app(ApplicationLogger::class)->info('Standings sync validation started', [
            'category' => 'sync',
            'platform' => $platform,
            'contest_id' => $contestId,
            'source' => self::class,
        ]);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('Standings sync validation skipped: unsupported platform', [
                'category' => 'sync',
                'platform' => $platform,
                'contest_id' => $contestId,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platform);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        $this->info('Fetching standings...');
        $progressBar = $this->output->createProgressBar(1);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Loading standings');
        $progressBar->start();

        try {
            $standings = $adapter->getContest($contestId);

            $progressBar->setMessage('Standings loaded');
            $progressBar->advance();
            $progressBar->finish();
            $this->newLine(2);

            $firstParticipant = $standings->rows[0] ?? null;

            $this->info('Platform: ' . $platform);
            $this->info('Contest ID: ' . $contestId);
            $this->info('Contest Title: ' . $standings->contest->title);
            $this->info('Participant Count: ' . count($standings->rows));
            $this->info('Problem Count: ' . count($standings->problems));
            $this->info('First Participant Rank: ' . (
                $firstParticipant instanceof ParticipantDTO
                    ? $this->displayValue($firstParticipant->rank)
                    : 'N/A'
            ));

            app(ApplicationLogger::class)->info('Standings sync validation completed', [
                'category' => 'sync',
                'platform' => $platform,
                'contest_id' => $contestId,
                'source' => self::class,
                'contest_title' => $standings->contest->title,
                'participant_count' => count($standings->rows),
                'problem_count' => count($standings->problems),
                'first_participant_rank' => $firstParticipant instanceof ParticipantDTO ? $firstParticipant->rank : null,
            ]);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $progressBar->setMessage('Standings request failed');
            $progressBar->finish();
            $this->newLine(2);

            app(ApplicationLogger::class)->error('Standings sync validation failed', [
                'category' => 'sync',
                'platform' => $platform,
                'contest_id' => $contestId,
                'source' => self::class,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->error('Error fetching standings: ' . $e->getMessage());

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

        return (string) $value;
    }
}
