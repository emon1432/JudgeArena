<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformProfileAction;
use App\Models\User;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\CodeChef\CodeChefAdapter;
use Illuminate\Console\Command;

class TestCodeChefSync extends Command
{
    protected $signature = 'test:codechef {handle} {--sync : Run full sync}';

    protected $description = 'Test CodeChef profile and rating graph fetching';

    public function handle(CodeChefAdapter $adapter, SyncPlatformProfileAction $syncAction): int
    {
        $handle = $this->argument('handle');
        $runSync = $this->option('sync');

        $this->info("Testing CodeChef for handle: {$handle}");
        $this->newLine();

        try {
            // Test profile fetch
            $this->info('1. Fetching profile...');
            $profile = $adapter->fetchProfile($handle);

            $this->line("   ✓ Handle: {$profile->handle}");
            $this->line("   ✓ Name: " . ($profile->raw['name'] ?? 'N/A'));
            $this->line("   ✓ Country: " . ($profile->raw['country'] ?? 'N/A'));
            $this->line("   ✓ Affiliation: " . ($profile->raw['affiliation'] ?? 'N/A'));
            $this->line("   ✓ Joined At: " . ($profile->raw['joined_at'] ?? 'N/A'));
            $this->line("   ✓ Avatar URL: " . (!empty($profile->raw['avatar_url']) ? 'available' : 'N/A'));
            $this->line("   ✓ Current Rating: " . ($profile->rating ?? 'Unrated'));
            $this->line("   ✓ Max Rating: " . ($profile->raw['max_rating'] ?? 'N/A'));
            $this->line("   ✓ Stars: " . ($profile->raw['stars'] ?? 'N/A'));
            $this->line("   ✓ Global Rank: " . ($profile->raw['global_rank'] ?? 'N/A'));
            $this->line("   ✓ Country Rank: " . ($profile->raw['country_rank'] ?? 'N/A'));
            $this->line("   ✓ Total Solved: {$profile->totalSolved}");
            $this->line("   ✓ Fully Solved: {$profile->raw['fully_solved']}");
            $this->line("   ✓ Partially Solved: {$profile->raw['partially_solved']}");

            if (!empty($profile->raw['badges'])) {
                $this->line("   ✓ Badges: " . implode(', ', $profile->raw['badges']));
            }

            $this->newLine();

            // Show contest participation by category
            $categories = $profile->raw['contest_categories'] ?? [];
            if (array_sum($categories) > 0) {
                $this->info('   Contest Participation:');
                $this->line("     • Long: {$categories['long']}");
                $this->line("     • Cook-off: {$categories['cookoff']}");
                $this->line("     • Lunchtime: {$categories['lunchtime']}");
                $this->line("     • Starters: {$categories['starters']}");
                $this->newLine();
            }

            // Show recent contests from each category
            $ratingGraph = $profile->raw['rating_graph'] ?? [];

            foreach (['long' => 'Long Contests', 'cookoff' => 'Cook-off', 'lunchtime' => 'Lunchtime', 'starters' => 'Starters'] as $key => $label) {
                if (!empty($ratingGraph[$key])) {
                    $this->info("   Recent {$label} (Last 3):");
                    foreach (array_slice($ratingGraph[$key], -3) as $contest) {
                        $this->line(sprintf(
                            "     • %s | Rating: %s | Rank: %s",
                            substr($contest['name'], 0, 35),
                            $contest['rating'] ?? 'N/A',
                            $contest['rank'] ?? 'N/A'
                        ));
                    }
                    $this->newLine();
                }
            }

            // Optionally run full sync
            if ($runSync) {
                $this->info('2. Running full sync...');

                // Find user and platform
                $user = User::whereHas('platformProfiles', function ($query) use ($handle) {
                    $query->where('handle', $handle)->whereHas('platform', function ($q) {
                        $q->where('name', 'codechef');
                    });
                })->first();
                if (!$user) {
                    $this->error('   ✗ No user found in database');
                    return 1;
                }

                $platform = Platform::where('name', 'codechef')->first();
                if (!$platform) {
                    $this->error('   ✗ CodeChef platform not found in database. Run platform seeder.');
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
                        'status' => 'Active',
                    ]
                );

                // Bypass cooldown for testing
                $platformProfile->last_synced_at = null;
                $platformProfile->save();

                $syncAction->execute($platformProfile, $adapter);

                $platformProfile->refresh();
                $this->line("   ✓ Sync completed!");
                $this->line("   ✓ Total Solved: {$platformProfile->total_solved}");
                $this->line("   ✓ Rating: " . ($platformProfile->rating ?? 'Unrated'));
                $this->line("   ✓ Max Rating: " . ($platformProfile->raw['max_rating'] ?? 'N/A'));
            }

            $this->newLine();
            $this->info('✓ Test completed successfully');
            return 0;

        } catch (\Exception $e) {
            $this->error('✗ Test failed: ' . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }
}
