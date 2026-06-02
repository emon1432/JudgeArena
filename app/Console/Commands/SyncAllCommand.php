<?php

namespace App\Console\Commands;

use App\Models\PlatformProfile;
use App\Services\ApplicationLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class SyncAllCommand extends Command
{
    protected $signature = 'judgearena:sync-all {platform?}';

    protected $description = 'Run the full JudgeArena synchronization pipeline.';

    private const SUPPORTED_PLATFORMS = [
        'codeforces',
        'atcoder',
    ];

    public function __construct(
        private readonly PlatformProfile $platformProfileModel,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $platform = $this->normalizePlatform($this->argument('platform'));
        $platformLabel = $platform ?? 'all';
        $startedAt = microtime(true);

        app(ApplicationLogger::class)->info('Full synchronization started', [
            'category' => 'sync',
            'platform' => $platformLabel,
            'source' => self::class,
        ]);

        if ($platform !== null && ! in_array($platform, self::SUPPORTED_PLATFORMS, true)) {
            app(ApplicationLogger::class)->warning('Full synchronization skipped: unsupported platform', [
                'category' => 'sync',
                'platform' => $platform,
                'source' => self::class,
            ]);

            $this->error('Unsupported platform: ' . $platform);
            $this->line('Supported platforms: ' . implode(', ', self::SUPPORTED_PLATFORMS));

            return self::FAILURE;
        }

        $steps = $this->steps();
        $totalSteps = count($steps);

        foreach ($steps as $index => $step) {
            $stepNumber = $index + 1;
            $stepStartedAt = microtime(true);

            $this->newLine($stepNumber === 1 ? 0 : 1);
            $this->line(sprintf('[%d/%d] %s...', $stepNumber, $totalSteps, $step['label']));

            app(ApplicationLogger::class)->info('Full synchronization step started', [
                'category' => 'sync',
                'platform' => $platformLabel,
                'source' => self::class,
                'step' => $stepNumber,
                'total_steps' => $totalSteps,
                'command' => $step['command'],
                'label' => $step['label'],
            ]);

            try {
                $result = $this->runStep($step, $platform);
            } catch (Throwable $e) {
                $duration = microtime(true) - $stepStartedAt;

                app(ApplicationLogger::class)->error('Full synchronization step failed', [
                    'category' => 'sync',
                    'platform' => $platformLabel,
                    'source' => self::class,
                    'step' => $stepNumber,
                    'total_steps' => $totalSteps,
                    'command' => $step['command'],
                    'label' => $step['label'],
                    'duration_seconds' => round($duration, 2),
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], $e);

                $this->error('Failed in ' . $this->formatDuration($duration));
                $this->line($e->getMessage());

                return self::FAILURE;
            }

            $duration = microtime(true) - $stepStartedAt;

            if ($result['exit_code'] !== self::SUCCESS) {
                app(ApplicationLogger::class)->error('Full synchronization step failed', [
                    'category' => 'sync',
                    'platform' => $platformLabel,
                    'source' => self::class,
                    'step' => $stepNumber,
                    'total_steps' => $totalSteps,
                    'command' => $step['command'],
                    'label' => $step['label'],
                    'duration_seconds' => round($duration, 2),
                    'exit_code' => $result['exit_code'],
                    'output' => $result['output'],
                ]);

                $this->error('Failed in ' . $this->formatDuration($duration));

                if (trim($result['output']) !== '') {
                    $this->line(trim($result['output']));
                }

                return self::FAILURE;
            }

            app(ApplicationLogger::class)->info('Full synchronization step completed', [
                'category' => 'sync',
                'platform' => $platformLabel,
                'source' => self::class,
                'step' => $stepNumber,
                'total_steps' => $totalSteps,
                'command' => $step['command'],
                'label' => $step['label'],
                'duration_seconds' => round($duration, 2),
                'child_call_count' => $result['child_call_count'],
            ]);

            $this->line('Completed in ' . $this->formatDuration($duration));
        }

        $totalDuration = microtime(true) - $startedAt;

        app(ApplicationLogger::class)->info('Full synchronization completed', [
            'category' => 'sync',
            'platform' => $platformLabel,
            'source' => self::class,
            'duration_seconds' => round($totalDuration, 2),
        ]);

        $this->newLine();
        $this->info('Synchronization completed.');
        $this->line('Total runtime: ' . $this->formatDuration($totalDuration));

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{label: string, command: string, scope: string}>
     */
    private function steps(): array
    {
        return [
            ['label' => 'Importing contests', 'command' => 'judgearena:import-contests', 'scope' => 'supported-platforms'],
            ['label' => 'Importing problems', 'command' => 'judgearena:import-problems', 'scope' => 'optional-platform'],
            ['label' => 'Importing users', 'command' => 'judgearena:import-users', 'scope' => 'optional-platform'],
            // ['label' => 'Importing user rating history', 'command' => 'judgearena:import-user-rating-history', 'scope' => 'platform-profiles'],
            ['label' => 'Importing rating changes', 'command' => 'judgearena:import-rating-changes', 'scope' => 'optional-platform'],
            ['label' => 'Importing standings', 'command' => 'judgearena:import-standings', 'scope' => 'optional-platform'],
            ['label' => 'Importing submissions', 'command' => 'judgearena:import-submissions', 'scope' => 'platform-profiles'],
        ];
    }

    /**
     * @param array{label: string, command: string, scope: string} $step
     * @return array{exit_code: int, output: string, child_call_count: int}
     */
    private function runStep(array $step, ?string $platform): array
    {
        return match ($step['scope']) {
            'supported-platforms' => $this->runForSupportedPlatforms($step['command'], $platform),
            'optional-platform' => $this->runOptionalPlatformCommand($step['command'], $platform),
            'platform-profiles' => $this->runForPlatformProfiles($step['command'], $platform),
            default => throw new \InvalidArgumentException('Unsupported sync-all step scope: ' . $step['scope']),
        };
    }

    /**
     * @return array{exit_code: int, output: string, child_call_count: int}
     */
    private function runForSupportedPlatforms(string $command, ?string $platform): array
    {
        $platforms = $platform !== null ? [$platform] : self::SUPPORTED_PLATFORMS;

        return $this->runChildCommands(array_map(
            fn (string $platformSlug): array => [$command, ['platform' => $platformSlug]],
            $platforms
        ));
    }

    /**
     * @return array{exit_code: int, output: string, child_call_count: int}
     */
    private function runOptionalPlatformCommand(string $command, ?string $platform): array
    {
        $arguments = $platform !== null ? ['platform' => $platform] : [];

        return $this->runChildCommands([[$command, $arguments]]);
    }

    /**
     * @return array{exit_code: int, output: string, child_call_count: int}
     */
    private function runForPlatformProfiles(string $command, ?string $platform): array
    {
        $profiles = $this->platformProfiles($platform);

        if ($profiles->isEmpty()) {
            app(ApplicationLogger::class)->info('Full synchronization profile-scoped step skipped: no active profiles found', [
                'category' => 'sync',
                'platform' => $platform ?? 'all',
                'source' => self::class,
                'command' => $command,
            ]);

            return [
                'exit_code' => self::SUCCESS,
                'output' => '',
                'child_call_count' => 0,
            ];
        }

        $calls = [];

        foreach ($profiles as $profile) {
            $platformSlug = trim((string) ($profile->platform?->slug ?? ''));
            $handle = trim((string) $profile->handle);

            if ($platformSlug === '' || $handle === '') {
                continue;
            }

            $calls[] = [$command, [
                'platform' => $platformSlug,
                'handle' => $handle,
            ]];
        }

        if ($calls === []) {
            return [
                'exit_code' => self::SUCCESS,
                'output' => '',
                'child_call_count' => 0,
            ];
        }

        return $this->runChildCommands($calls);
    }

    /**
     * @param array<int, array{0: string, 1: array<string, mixed>}> $calls
     * @return array{exit_code: int, output: string, child_call_count: int}
     */
    private function runChildCommands(array $calls): array
    {
        $combinedOutput = '';
        $callCount = 0;

        foreach ($calls as [$command, $arguments]) {
            $exitCode = Artisan::call($command, $arguments);
            $output = Artisan::output();
            $callCount++;

            if (trim($output) !== '') {
                $combinedOutput .= trim($output) . PHP_EOL;
            }

            if ($exitCode !== self::SUCCESS) {
                return [
                    'exit_code' => $exitCode,
                    'output' => $combinedOutput,
                    'child_call_count' => $callCount,
                ];
            }
        }

        return [
            'exit_code' => self::SUCCESS,
            'output' => $combinedOutput,
            'child_call_count' => $callCount,
        ];
    }

    private function platformProfiles(?string $platform): Collection
    {
        $query = $this->platformProfileModel->newQuery()
            ->active()
            ->with('platform')
            ->whereNotNull('handle');

        if ($platform !== null) {
            $query->whereHas('platform', function ($platformQuery) use ($platform): void {
                $platformQuery->where('slug', $platform);
            });
        }

        return $query->get()->filter(function (PlatformProfile $profile): bool {
            return trim((string) $profile->handle) !== '';
        });
    }

    private function normalizePlatform(mixed $platform): ?string
    {
        if (! is_string($platform)) {
            return null;
        }

        $platform = strtolower(trim($platform));

        return $platform === '' ? null : $platform;
    }

    private function formatDuration(float $seconds): string
    {
        return number_format($seconds, 1) . 's';
    }
}
