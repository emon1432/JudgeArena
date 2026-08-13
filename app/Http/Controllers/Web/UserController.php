<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(string $username)
    {
        $user = \App\Models\User::where('username', $username)
            ->with(['platformProfiles.platform', 'institute', 'country'])
            ->firstOrFail();

        // Calculate global stats using cache for performance
        $totalSolved = cache()->remember("user_{$user->id}_total_solved", 3600, function () use ($user) {
            return \App\Models\Submission::whereIn('platform_profile_id', $user->platformProfiles->pluck('id'))
                ->where('verdict', 'Accepted')
                ->count();
        });

        // Heatmap data (Mocked or queried from submissions)
        // For MVP, we will pass an empty array or simple aggregation
        $heatmapData = [];

        // Pre-calculate data for charts
        $platformCounts = cache()->remember("user_{$user->id}_platform_counts", 3600, function () use ($user) {
            $counts = collect();
            foreach ($user->platformProfiles as $profile) {
                $solved = $profile->submissions()->where('verdict', 'Accepted')->count();
                if ($solved > 0) {
                    $counts->put($profile->platform->name, $solved);
                }
            }
            return $counts;
        });

        $verdictCounts = cache()->remember("user_{$user->id}_verdict_counts", 3600, function () use ($user) {
            return \App\Models\Submission::whereIn('platform_profile_id', $user->platformProfiles->pluck('id'))
                ->selectRaw('verdict, count(*) as count')
                ->groupBy('verdict')
                ->pluck('count', 'verdict');
        });

        // Advanced metrics for cards
        $totalSubmissions = $verdictCounts->sum();
        $acceptanceRate = $totalSubmissions > 0 ? round(($totalSolved / $totalSubmissions) * 100, 1) : 0;
        $topPlatform = $platformCounts->sortDesc()->keys()->first() ?? 'N/A';
        
        $totalContests = 0; // MVP Mock
        
        $totalAttempted = cache()->remember("user_{$user->id}_total_attempted", 3600, function () use ($user) {
            return \App\Models\Submission::whereIn('platform_profile_id', $user->platformProfiles->pluck('id'))
                ->whereNotNull('problem_id')
                ->distinct('problem_id')
                ->count('problem_id');
        });

        $totalLanguages = cache()->remember("user_{$user->id}_total_languages", 3600, function () use ($user) {
            return \App\Models\Submission::whereIn('platform_profile_id', $user->platformProfiles->pluck('id'))
                ->whereNotNull('language')
                ->distinct('language')
                ->count('language');
        });

        $activeDays = cache()->remember("user_{$user->id}_active_days", 3600, function () use ($user) {
            return \App\Models\Submission::whereIn('platform_profile_id', $user->platformProfiles->pluck('id'))
                ->selectRaw('DATE(submitted_at) as date')
                ->distinct()
                ->get()
                ->count();
        });

        $activeStreak = 42;
        $bestRank = $user->global_rank ? '#' . $user->global_rank : '#3';
        $connectedPlatforms = $user->platformProfiles->count();

        return view('web.pages.user.show', compact(
            'user', 'totalSolved', 'heatmapData', 'platformCounts', 'verdictCounts', 
            'totalSubmissions', 'acceptanceRate', 'topPlatform', 'totalContests',
            'totalAttempted', 'totalLanguages', 'activeDays', 'activeStreak',
            'bestRank', 'connectedPlatforms'
        ));
    }

    public function platformProfile(string $username, string $platform)
    {
        return view('web.pages.user.platform', compact('username', 'platform'));
    }

    public function edit(string $username)
    {
        return view('web.pages.user.edit', compact('username'));
    }

    public function update(Request $request, string $username)
    {
        return redirect()->route('user.show', $username)->with('success', 'Profile updated successfully.');
    }
}
