<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformProfileAction;
use App\Models\User;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\LeetCode\LeetCodeAdapter;
use Illuminate\Console\Command;

class TestLeetCodeSync extends Command
{
    protected $signature = 'test:leetcode {handle} {--sync : Run full sync}';

    protected $description = 'Test LeetCode profile fetching';

    public function handle(LeetCodeAdapter $adapter, SyncPlatformProfileAction $syncAction): int
    {
        $handle = $this->argument('handle');
        $runSync = $this->option('sync');

        $this->info("Testing LeetCode for handle: {$handle}");
        $this->newLine();

        try {
            // Test profile fetch
            $this->info('1. Fetching profile...');
            $profile = $adapter->fetchProfile($handle);

            $this->line("   ✓ Username: {$profile->handle}");
            $this->line("   ✓ Name: " . ($profile->raw['name'] ?? 'N/A'));
            $this->line("   ✓ Country: " . ($profile->raw['country'] ?? 'N/A'));
            $this->line("   ✓ Avatar URL: " . (!empty($profile->raw['avatar_url']) ? 'available' : 'N/A'));
            $this->line("   ✓ Total Solved: {$profile->totalSolved}");
            $this->line("   ✓ Easy: {$profile->raw['easy_solved']}");
            $this->line("   ✓ Medium: {$profile->raw['medium_solved']}");
            $this->line("   ✓ Hard: {$profile->raw['hard_solved']}");
            $this->line("   ✓ Global Ranking: " . ($profile->raw['ranking'] ?? 'N/A'));

            if (!empty($profile->raw['contest_rating'])) {
                $this->line("   ✓ Contest Rating: " . round($profile->raw['contest_rating'], 2));
                $this->line("   ✓ Contest Ranking: {$profile->raw['contest_global_ranking']} / " .
                    ($profile->raw['contest_ranking']['totalParticipants'] ?? 'N/A'));
                $this->line("   ✓ Contests Attended: {$profile->raw['attended_contests_count']}");
            }

            $this->newLine();

            // Show badges
            if (!empty($profile->raw['badges'])) {
                $this->info('   Badges Earned: ' . count($profile->raw['badges']));
                foreach (array_slice($profile->raw['badges'], 0, 5) as $badge) {
                    $this->line("     • {$badge['displayName']}");
                }
                $this->newLine();
            }

            // Show recent submissions
            if (!empty($profile->raw['recent_submissions'])) {
                $this->info('   Recent AC Submissions (Last 20):');
                foreach (array_slice($profile->raw['recent_submissions'], 0, 5) as $sub) {
                    $date = date('Y-m-d H:i', $sub['timestamp']);
                    $this->line("     • {$sub['title']} | {$date}");
                }
                $this->newLine();
            }

            // Show calendar stats
            if (!empty($profile->raw['calendar'])) {
                $calendar = $profile->raw['calendar'];
                $this->info('   Activity:');
                $this->line("     • Current Streak: {$calendar['streak']} days");
                $this->line("     • Total Active Days: {$calendar['totalActiveDays']}");
                $this->line("     • Active Years: " . implode(', ', $calendar['activeYears'] ?? []));
                $this->newLine();
            }

            // Show recent contest history
            if (!empty($profile->raw['contest_history'])) {
                $this->info('   Recent Contests (Last 5):');
                foreach (array_slice($profile->raw['contest_history'], -5) as $contest) {
                    if ($contest['attended']) {
                        $this->line(sprintf(
                            "     • %s | Rating: %.0f | Rank: %d | Solved: %d/%d",
                            substr($contest['contest']['title'], 0, 30),
                            $contest['rating'],
                            $contest['ranking'],
                            $contest['problemsSolved'],
                            $contest['totalProblems']
                        ));
                    }
                }
                $this->newLine();
            }

            // Optionally run full sync
            if ($runSync) {
                $this->info('2. Running full sync...');

                // Find user and platform
                $user = User::whereHas('platformProfiles', function ($query) use ($handle) {
                    $query->where('handle', $handle)->whereHas('platform', function ($q) {
                        $q->where('name', 'leetcode');
                    });
                })->first();
                if (!$user) {
                    $this->error('   ✗ No user found in database');
                    return 1;
                }

                $platform = Platform::where('name', 'leetcode')->first();
                if (!$platform) {
                    $this->error('   ✗ LeetCode platform not found in database. Run platform seeder.');
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
                $this->line("   ✓ Contest Rating: " . ($platformProfile->rating ?? 'N/A'));
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
