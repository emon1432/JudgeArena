<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformProfileAction;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\User;
use App\Platforms\Timus\TimusAdapter;
use Illuminate\Console\Command;

class TestTimusSync extends Command
{
    protected $signature = 'test:timus {handle} {--sync : Run full sync}';
    protected $description = 'Test Timus profile and submissions fetching';

    public function handle(TimusAdapter $adapter, SyncPlatformProfileAction $syncAction): int
    {
        $handle = $this->argument('handle');
        $runSync = $this->option('sync');

        $this->info("Testing Timus for handle: {$handle}");
        $this->newLine();

        try {
            $this->info('1. Fetching profile...');
            $profile = $adapter->fetchProfile($handle);

            $this->line("   ✓ Name: {$profile->raw['name']}");
            $this->line("   ✓ User ID: {$profile->raw['user_id']}");
            $this->line("   ✓ Country: " . ($profile->raw['country'] ?? 'N/A'));
            $this->line("   ✓ Joined At: " . ($profile->raw['joined_at'] ?? 'N/A'));
            $this->line("   ✓ Avatar URL: " . (!empty($profile->raw['avatar_url']) ? 'available' : 'N/A'));
            $this->line("   ✓ Handle: {$profile->handle}");
            $this->line("   ✓ Total Solved: {$profile->totalSolved}");
            if ($profile->rating) {
                $this->line("   ✓ Rating: {$profile->rating}");
            }
            $this->newLine();

            $this->info('2. Fetching submissions...');
            $submissions = $adapter->fetchSubmissions($handle);

            $this->line("   ✓ AC Submissions: {$submissions->count()}");
            $uniqueProblems = $submissions->unique('problemId')->count();
            $this->line("   ✓ Unique Problems: {$uniqueProblems}");

            if ($submissions->isNotEmpty()) {
                $this->newLine();
                $this->info('   First 5 submissions:');
                $submissions->take(5)->each(function ($sub) {
                    $this->line(sprintf(
                        "     • %s (#%s) | %s | %s",
                        substr($sub->problemName, 0, 30),
                        $sub->problemId,
                        $sub->raw['language'],
                        $sub->submittedAt->format('Y-m-d H:i:s')
                    ));
                });
            }

            if ($runSync) {
                $this->newLine();
                $this->info('3. Running full sync...');

                $user = User::whereHas('platformProfiles', function ($query) use ($handle) {
                    $query->where('handle', $handle)->whereHas('platform', function ($q) {
                        $q->where('name', 'timus');
                    });
                })->first();
                if (! $user) {
                    $this->error('   ✗ No user found in database');
                    return 1;
                }

                $platform = Platform::where('name', 'timus')->first();
                if (! $platform) {
                    $this->error('   ✗ Timus platform not found in database');
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
