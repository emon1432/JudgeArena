<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformProfileAction;
use App\Models\User;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\AtCoder\AtCoderAdapter;
use Illuminate\Console\Command;

class TestAtCoderSync extends Command
{
    protected $signature = 'test:atcoder {handle} {--sync : Run full sync}';

    protected $description = 'Test AtCoder profile and submissions fetching';

    public function handle(AtCoderAdapter $adapter, SyncPlatformProfileAction $syncAction): int
    {
        $handle = $this->argument('handle');
        $runSync = $this->option('sync');

        $this->info("Testing AtCoder for handle: {$handle}");
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
            $this->line("   ✓ Highest Rating: " . ($profile->raw['highest_rating'] ?? 'N/A'));
            $this->line("   ✓ Rank: " . ($profile->raw['rank'] ?? 'N/A'));
            $this->line("   ✓ Total Solved: {$profile->totalSolved}");
            $this->line("   ✓ Rated Matches: {$profile->raw['rated_matches']}");
            $this->line("   ✓ Contest History: " . count($profile->raw['contest_history'] ?? []));
            $this->newLine();

            // Show last 5 contests
            if (!empty($profile->raw['contest_history'])) {
                $this->info('   Last 5 Contests:');
                $contests = array_slice($profile->raw['contest_history'], 0, 5);
                foreach ($contests as $contest) {
                    $this->line(sprintf(
                        "     • %s | Rank: %s | Rating: %+d → %d | Performance: %s",
                        substr($contest['contest_name'], 0, 35),
                        $contest['rank'],
                        $contest['rating_change'],
                        $contest['new_rating'],
                        $contest['performance']
                    ));
                }
                $this->newLine();
            }

            // Test submissions fetch
            $this->info('2. Fetching submissions...');
            $submissions = $adapter->fetchSubmissions($handle);

            $this->line("   ✓ Total AC Submissions: {$submissions->count()}");
            $uniqueProblems = $submissions->unique('problemId')->count();
            $this->line("   ✓ Unique Problems Solved: {$uniqueProblems}");
            $this->newLine();

            // Show first 5 submissions
            if ($submissions->isNotEmpty()) {
                $this->info('   First 5 Accepted Submissions:');
                $submissions->take(5)->each(function ($submission) {
                    $this->line(sprintf(
                        "     • %s (%s) | %d points | %s",
                        substr($submission->problemName, 0, 30),
                        $submission->problemId,
                        $submission->raw['point'],
                        $submission->submittedAt->format('Y-m-d H:i:s')
                    ));
                });
                $this->newLine();
            }

            // Optionally run full sync
            if ($runSync) {
                $this->info('3. Running full sync...');

                // Find user and platform
                $user = User::whereHas('platformProfiles', function ($query) use ($handle) {
                    $query->where('handle', $handle)->whereHas('platform', function ($q) {
                        $q->where('name', 'atcoder');
                    });
                })->first();
                if (!$user) {
                    $this->error('   ✗ No user found in database');
                    return 1;
                }

                $platform = Platform::where('name', 'atcoder')->first();
                if (!$platform) {
                    $this->error('   ✗ AtCoder platform not found in database. Run platform seeder.');
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
                $this->line("   ✓ Highest Rating: " . ($platformProfile->raw['highest_rating'] ?? 'N/A'));
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
