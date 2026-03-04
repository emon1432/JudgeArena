<?php
// filepath: /app/Console/Commands/TestHackerRankSync.php
namespace App\Console\Commands;

use App\Actions\SyncPlatformProfileAction;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\User;
use App\Platforms\HackerRank\HackerRankAdapter;
use Illuminate\Console\Command;

class TestHackerRankSync extends Command
{
    protected $signature = 'test:hackerrank {handle} {--sync : Run full sync}';
    protected $description = 'Test HackerRank profile, rating graph, and recent submissions';

    public function handle(HackerRankAdapter $adapter, SyncPlatformProfileAction $syncAction): int
    {
        $handle = $this->argument('handle');
        $runSync = $this->option('sync');

        $this->info("Testing HackerRank for handle: {$handle}");
        $this->newLine();

        try {
            // Profile
            $this->info('1. Fetching profile...');
            $profile = $adapter->fetchProfile($handle);

            $this->line("   ✓ Name: " . ($profile->raw['name'] ?? 'N/A'));
            $this->line("   ✓ Country: " . ($profile->raw['country'] ?? 'N/A'));
            $this->line("   ✓ Joined At: " . (($profile->raw['joined_at'] ?? null) ? (string) $profile->raw['joined_at'] : 'N/A'));
            $this->line("   ✓ Avatar URL: " . (!empty($profile->raw['avatar_url']) ? 'available' : 'N/A'));
            $this->line("   ✓ Total Solved: {$profile->totalSolved}");
            $this->line("   ✓ Ranking: " . ($profile->raw['ranking'] ?? 'N/A'));
            $this->line("   ✓ Badges: " . ($profile->raw['badges'] ?? 'N/A'));
            $this->line("   ✓ Rating Graph Entries: " . count($profile->raw['rating_graph'] ?? []));
            $this->newLine();

            // Submissions
            $this->info('2. Fetching recent submissions...');
            $submissions = $adapter->fetchSubmissions($handle);

            $this->line("   ✓ Recent submissions: {$submissions->count()}");
            $this->line("   ✓ Unique problems: " . $submissions->unique('problemId')->count());

            if ($submissions->isNotEmpty()) {
                $this->newLine();
                $this->info('   First 5 submissions:');
                $submissions->take(5)->each(function ($sub) {
                    $this->line("     • {$sub->problemName} ({$sub->problemId})");
                });
            }

            $this->newLine();

            // Optional sync
            if ($runSync) {
                $this->info('3. Running full sync...');

                $user = User::whereHas('platformProfiles', function ($query) use ($handle) {
                    $query->where('handle', $handle)->whereHas('platform', function ($q) {
                        $q->where('name', 'hackerrank');
                    });
                })->first();
                if (!$user) {
                    $this->error('   ✗ No user found in database');
                    return 1;
                }

                $platform = Platform::where('name', 'hackerrank')->first();
                if (! $platform) {
                    $this->error('   ✗ HackerRank platform not found in database');
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

                $platformProfile->last_synced_at = null;
                $platformProfile->save();

                $syncAction->execute($platformProfile, $adapter);

                $platformProfile->refresh();
                $this->line("   ✓ Sync completed!");
                $this->line("   ✓ Total Solved: {$platformProfile->total_solved}");
                $this->line("   ✓ Ranking: " . ($platformProfile->ranking ?? 'N/A'));
            }

            $this->newLine();
            $this->info('✓ Test completed successfully');
            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Test failed: ' . $e->getMessage());
            return 1;
        }
    }
}
