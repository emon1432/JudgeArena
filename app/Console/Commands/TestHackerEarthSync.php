<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformProfileAction;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\User;
use App\Platforms\HackerEarth\HackerEarthAdapter;
use Illuminate\Console\Command;

class TestHackerEarthSync extends Command
{
    protected $signature = 'test:hackerearth {handle} {--sync : Run full sync}';
    protected $description = 'Test HackerEarth sync for a specific handle';

    public function handle(SyncPlatformProfileAction $syncAction, HackerEarthAdapter $adapter): int
    {
        $handle = $this->argument('handle');
        $runSync = $this->option('sync');

        $this->info("Testing HackerEarth sync for handle: {$handle}");
        $this->newLine();

        // Test fetching profile
        $this->info("1. Fetching profile data...");
        try {
            $profileDto = $adapter->fetchProfile($handle);
            $this->line("   ✓ Handle: {$profileDto->handle}");
            $this->line("   ✓ Rating: " . ($profileDto->rating ?? 'null'));
            $this->line("   ✓ Global Rank: " . ($profileDto->raw['ranking'] ?? 'N/A'));
            $this->line("   ✓ Total Solved (from DTO): {$profileDto->totalSolved}");
            $this->line("   ✓ Raw data keys: " . implode(', ', array_keys($profileDto->raw)));
        } catch (\Exception $e) {
            $this->error("   ✗ Failed to fetch profile: {$e->getMessage()}");
            return 1;
        }

        $this->newLine();

        $this->info("2. Using rendered profile metrics (Playwright fallback)");
        $metrics = $profileDto->raw['profile_metrics'] ?? [];
        $this->line("   ✓ Problem Solved: " . ((int) ($metrics['problem_solved'] ?? $profileDto->totalSolved)));
        $this->line("   ✓ Global Rank: " . (isset($metrics['global_rank']) && is_numeric($metrics['global_rank']) ? (int) $metrics['global_rank'] : 'N/A'));
        $this->line("   ✓ Country Rank: " . (isset($metrics['country_rank']) && is_numeric($metrics['country_rank']) ? (int) $metrics['country_rank'] : 'N/A'));
        $this->line("   ✓ Solutions Submitted: " . ((int) ($metrics['solutions_submitted'] ?? 0)));
        $this->line("   ✓ Contest Rating: " . ((int) ($metrics['contest_rating'] ?? 0)));
        $this->newLine();

        if ($runSync) {
            $this->info("3. Running full sync...");

            $user = User::whereHas('platformProfiles', function ($query) use ($handle) {
                $query->where('handle', $handle)->whereHas('platform', function ($q) {
                    $q->where('name', 'hackerearth');
                });
            })->first();
            if (!$user) {
                $this->error('   ✗ No user found in database');
                return 1;
            }

            $platform = Platform::where('name', 'hackerearth')->first();
            if (!$platform) {
                $this->error('   ✗ HackerEarth platform not found in database');
                return 1;
            }

            $platformProfile = PlatformProfile::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'platform_id' => $platform->id,
                    'handle' => $handle,
                ],
                [
                    'profile_url' => $adapter->profileUrl($handle),
                ]
            );

            $platformProfile->last_synced_at = null;
            $platformProfile->save();

            try {
                $syncAction->execute($platformProfile, $adapter);
                $platformProfile->refresh();

                $this->info("   ✓ Sync completed successfully!");
                $this->line("   ✓ Rating: " . ($platformProfile->rating ?? 'null'));
                $this->line("   ✓ Global Rank: " . ($platformProfile->raw['ranking'] ?? 'N/A'));
                $this->line("   ✓ Total Solved: {$platformProfile->total_solved}");
                $this->line("   ✓ Last Synced: {$platformProfile->last_synced_at}");
            } catch (\Exception $e) {
                $this->error("   ✗ Sync failed: {$e->getMessage()}");
                return 1;
            }
        }

        $this->newLine();
        $this->info("Test completed!");

        return 0;
    }
}
