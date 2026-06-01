<?php

namespace App\Console\Commands;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\DTOs\SubmissionDTO;
use App\Models\Contest;
use App\Models\PlatformProfile;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class SyncSubmissionsCommand extends Command
{
    protected $signature = 'judgearena:sync-submissions {platform} {handle}';

    protected $description = 'Validate user submission adapters by fetching submissions without persisting them.';

    public function __construct(
        private readonly CodeforcesAdapter $codeforcesAdapter,
        private readonly AtCoderAdapter $atCoderAdapter,
        private readonly PlatformProfile $platformProfileModel,
        private readonly Contest $contestModel,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platformSlug = strtolower(trim((string) $this->argument('platform')));
        $handle = trim((string) $this->argument('handle'));
        $adapter = $this->resolveAdapter($platformSlug);

        app(ApplicationLogger::class)->info('Submission sync validation started', [
            'category' => 'sync',
            'platform' => $platformSlug,
            'handle' => $handle,
            'source' => self::class,
        ]);

        if ($adapter === null) {
            app(ApplicationLogger::class)->warning('Submission sync validation skipped: unsupported platform', [
                'category' => 'sync',
                'platform' => $platformSlug,
                'handle' => $handle,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platformSlug);
            $this->line('Supported platforms: codeforces, atcoder');

            return self::FAILURE;
        }

        $platformProfile = $this->findPlatformProfile($platformSlug, $handle);
        if ($platformProfile === null) {
            app(ApplicationLogger::class)->warning('Submission sync validation skipped: profile not found', [
                'category' => 'sync',
                'platform' => $platformSlug,
                'handle' => $handle,
                'source' => self::class,
            ]);

            $this->error('Platform profile not found for ' . $platformSlug . ' / ' . $handle);

            return self::FAILURE;
        }

        $contests = $this->contestModel->newQuery()
            ->where('platform_id', $platformProfile->platform_id)
            ->whereNotNull('platform_contest_id')
            ->orderBy('start_time')
            ->get();

        $progressBar = null;
        if ($contests->count() > 0) {
            $progressBar = $this->output->createProgressBar($contests->count());
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            $progressBar->setMessage('Preparing submission sync validation');
            $progressBar->start();
        } else {
            $this->info('No contests found for submission sync validation.');
        }

        $submissionsFound = 0;
        $contestsFailed = 0;
        $firstSubmission = null;

        foreach ($contests as $contest) {
            $context = $this->contestLogContext($platformSlug, $handle, $contest);

            if ($progressBar !== null) {
                $progressBar->setMessage(sprintf(
                    'Syncing submissions for %s',
                    $contest->name !== '' ? $contest->name : (string) $contest->platform_contest_id
                ));
            }

            app(ApplicationLogger::class)->info('Submission sync validation contest started', $context);

            try {
                $submissions = $adapter->getSubmissions((string) $contest->platform_contest_id, $handle);

                if (! is_array($submissions)) {
                    $submissions = [];
                }

                $submissionsFound += count($submissions);
                $firstSubmission ??= $this->firstSubmissionDto($submissions);

                app(ApplicationLogger::class)->info('Submission sync validation contest completed', array_merge($context, [
                    'submissions_found' => count($submissions),
                ]));
            } catch (Throwable $e) {
                $contestsFailed++;

                app(ApplicationLogger::class)->error('Submission sync validation contest failed', array_merge($context, [
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]), $e);
            } finally {
                if ($progressBar !== null) {
                    $progressBar->advance();
                }
            }
        }

        if ($progressBar !== null) {
            $progressBar->setMessage('Submission sync validation finished');
            $progressBar->finish();
            $this->newLine(2);
        }

        $this->line('Platform: ' . $platformSlug);
        $this->line('Handle: ' . $handle);
        $this->line('Contests Checked: ' . $contests->count());
        $this->line('Submissions Found: ' . $submissionsFound);

        if ($contestsFailed > 0) {
            $this->line('Contests Failed: ' . $contestsFailed);
        }

        if ($firstSubmission instanceof SubmissionDTO) {
            $this->newLine();
            $this->line('First Submission:');
            $this->line('Submission ID: ' . $this->displayValue($firstSubmission->platformSubmissionId));
            $this->line('Problem ID: ' . $this->displayValue($firstSubmission->problemPlatformId));
            $this->line('Contest ID: ' . $this->displayValue($firstSubmission->contestPlatformId));
            $this->line('Verdict: ' . $this->displayValue($firstSubmission->verdict));
            $this->line('Language: ' . $this->displayValue($firstSubmission->language));
            $this->line('Submitted At: ' . $this->displaySubmittedAt($firstSubmission->createdAtSeconds));
        }

        app(ApplicationLogger::class)->info('Submission sync validation completed', [
            'category' => 'sync',
            'platform' => $platformSlug,
            'handle' => $handle,
            'source' => self::class,
            'contests_checked' => $contests->count(),
            'submissions_found' => $submissionsFound,
            'contests_failed' => $contestsFailed,
        ]);

        return $contestsFailed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolveAdapter(string $platformSlug): ?PlatformAdapter
    {
        return match ($platformSlug) {
            'codeforces' => $this->codeforcesAdapter,
            'atcoder' => $this->atCoderAdapter,
            default => null,
        };
    }

    private function displaySubmittedAt(?int $createdAtSeconds): string
    {
        if ($createdAtSeconds === null) {
            return 'N/A';
        }

        return Carbon::createFromTimestamp($createdAtSeconds)->toDateTimeString();
    }

    private function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        return (string) $value;
    }

    private function findPlatformProfile(string $platformSlug, string $handle): ?PlatformProfile
    {
        return $this->platformProfileModel->newQuery()
            ->with('platform')
            ->whereHas('platform', function ($platformQuery) use ($platformSlug): void {
                $platformQuery->where('slug', $platformSlug);
            })
            ->whereRaw('LOWER(handle) = ?', [mb_strtolower(trim($handle))])
            ->first();
    }

    private function contestLogContext(string $platformSlug, string $handle, Contest $contest): array
    {
        return [
            'category' => 'sync',
            'platform' => $platformSlug,
            'handle' => $handle,
            'source' => self::class,
            'contest_id' => $contest->id,
            'platform_contest_id' => $contest->platform_contest_id,
            'contest_name' => $contest->name,
        ];
    }

    private function firstSubmissionDto(array $submissions): ?SubmissionDTO
    {
        foreach ($submissions as $submission) {
            if ($submission instanceof SubmissionDTO) {
                return $submission;
            }
        }

        return null;
    }
}
