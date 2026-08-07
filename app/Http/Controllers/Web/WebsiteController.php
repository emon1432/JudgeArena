<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\Problem;
use App\Models\Standing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index(): View
    {
        return view('web.pages.home.index');
    }

    public function platforms(Request $request): View
    {
        $perPage = min(max((int) $request->input('per_page', 10), 5), 100);
        $search = trim((string) $request->input('search', ''));
        $sort = $request->input('sort', 'popular');

        $query = Platform::query()
            ->select(['id', 'name', 'short_name', 'slug', 'base_url', 'icon', 'status', 'created_at'])
            ->withCount(['platformProfiles', 'problems', 'contests']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('base_url', 'like', "%{$search}%");
            });
        }

        match ($sort) {
            'name-asc' => $query->orderBy('name', 'asc'),
            'name-desc' => $query->orderBy('name', 'desc'),
            'contests-desc' => $query->orderBy('contests_count', 'desc'),
            'problems-desc' => $query->orderBy('problems_count', 'desc'),
            'users-desc', 'popular' => $query->orderBy('platform_profiles_count', 'desc'),
            default => $query->orderBy('platform_profiles_count', 'desc'),
        };

        $platforms = $query->simplePaginate($perPage)->withQueryString();

        $totalPlatforms = Platform::query()->count('*');
        $totalContests = Contest::query()->count('*');
        $totalProblems = Problem::query()->count('*');
        $totalProfiles = PlatformProfile::query()->count('*');

        return view('web.pages.platforms.index', compact('platforms', 'totalPlatforms', 'totalContests', 'totalProblems', 'totalProfiles'));
    }

    public function platformDetail(string $slug): View
    {
        $platform = Platform::query()
            ->where('slug', $slug)
            ->withCount(['contests', 'problems', 'platformProfiles'])
            ->firstOrFail();

        $recentContests = $platform->contests()
            ->orderByDesc('start_time')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $recentProblems = $platform->problems()
            ->with('contest:id,name')
            ->orderBy('created_at', 'asc')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('web.pages.platforms.show', compact('platform', 'recentContests', 'recentProblems'));
    }

    public function contests(): View
    {
        return view('web.pages.contests.index');
    }

    public function problems(): View
    {
        return view('web.pages.problems.index');
    }

    public function rankings(): View
    {
        return view('web.pages.rankings.index');
    }

    public function community(): View
    {
        return view('web.pages.community.index');
    }
}

