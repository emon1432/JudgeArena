<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\Problem;
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

    public function contests(Request $request): View
    {
        $perPage = min(max((int) $request->input('per_page', 10), 5), 100);
        $search = trim((string) $request->input('search', ''));
        $sort = $request->input('sort', 'soonest');
        $status = $request->input('status', 'all');
        $platformSlug = $request->input('platform', 'all');

        $now = now();

        $query = Contest::query()->with('platform');

        if ($platformSlug !== 'all') {
            $query->whereHas('platform', function ($q) use ($platformSlug) {
                $q->where('slug', $platformSlug)
                    ->orWhere('short_name', $platformSlug);
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('platform', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $date = $request->input('date', '');
        if ($date !== '') {
            $query->whereDate('start_time', $date);
        }

        if ($status === 'live') {
            $query->where(function ($q) use ($now) {
                $q->where('phase', 'CODING')->orWhere(function ($q2) use ($now) {
                    $q2->where('start_time', '<=', $now)
                        ->where('end_time', '>=', $now);
                });
            });
        } elseif ($status === 'upcoming') {
            $query->where('start_time', '>', $now)->where('end_time', '>', $now);
        } elseif ($status === 'past') {
            $query->where('end_time', '<', $now);
        }

        match ($sort) {
            'soonest' => $query->orderBy('start_time', 'asc'),
            'duration' => $query->orderBy('duration_seconds', 'asc'),
            'popular' => $query->orderBy('participant_count', 'desc'),
            'platform' => $query->join('platforms', 'contests.platform_id', '=', 'platforms.id')
                ->orderBy('platforms.name', 'asc')
                ->select('contests.*'),
            default => $query->orderBy('start_time', 'asc'),
        };

        $contests = $query->simplePaginate($perPage)->withQueryString();

        $liveCount = Contest::where(function ($q) use ($now) {
            $q->where('phase', 'CODING')->orWhere(function ($q2) use ($now) {
                $q2->where('start_time', '<=', $now)->where('end_time', '>=', $now);
            });
        })->count();
        $upcomingCount = Contest::where('start_time', '>', $now)->count();
        $totalContests = Contest::count();
        $platformsCount = Platform::where('status', 'Active')->count();

        $featuredContests = Contest::with('platform')
            ->where(function ($q) use ($now) {
                $q->where('phase', 'CODING')->orWhere(function ($q2) use ($now) {
                    $q2->where('start_time', '<=', $now)->where('end_time', '>=', $now);
                });
            })
            ->orWhere('start_time', '>', $now)
            ->orderBy('start_time', 'asc')
            ->limit(4)
            ->get();

        $platforms = Platform::select('id', 'name', 'slug', 'short_name')->orderBy('name', 'asc')->get();

        return view('web.pages.contests.index', compact(
            'contests',
            'liveCount',
            'upcomingCount',
            'totalContests',
            'platformsCount',
            'featuredContests',
            'platforms'
        ));
    }

    public function problems(Request $request): View
    {
        $perPage = min(max((int) $request->input('per_page', 25), 10), 100);
        $search = trim((string) $request->input('search', ''));
        $sort = $request->input('sort', 'name-asc');
        $platformSlug = $request->input('platform', 'all');
        $difficulty = $request->input('difficulty', 'all');
        $tagsParam = $request->input('tags', ''); // comma separated

        $query = Problem::query()->with('platform');

        // Filter by platform
        if ($platformSlug !== 'all') {
            $query->whereHas('platform', function ($q) use ($platformSlug) {
                $q->where('slug', $platformSlug)
                    ->orWhere('short_name', $platformSlug)
                    ->orWhere('name', $platformSlug);
            });
        }

        // Filter by search (name or code)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by difficulty (using rating approximation)
        if ($difficulty === 'Easy') {
            $query->where('rating', '<', 1200);
        } elseif ($difficulty === 'Medium') {
            $query->whereBetween('rating', [1200, 1899]);
        } elseif ($difficulty === 'Hard') {
            $query->where('rating', '>=', 1900);
        }

        // Filter by tags
        if ($tagsParam !== '') {
            $tags = array_map('trim', explode(',', $tagsParam));
            foreach ($tags as $tag) {
                if (!empty($tag)) {
                    $query->whereJsonContains('tags', $tag);
                }
            }
        }

        // Sorting
        match ($sort) {
            'name-asc' => $query->orderBy('name', 'asc'),
            'diff-asc' => $query->orderBy('rating', 'asc'),
            'diff-desc' => $query->orderBy('rating', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
            default => $query->orderBy('name', 'asc'),
        };

        $problems = $query->simplePaginate($perPage)->withQueryString();

        // Calculate metrics
        $totalProblems = Problem::query()->count();
        $activePlatforms = Platform::query()->where('status', 'Active')->get();
        $platformsCount = $activePlatforms->count();
        $platformShortNames = $activePlatforms->pluck('short_name')->filter()->implode(', ');

        $availableTags = cache()->remember('problems_available_tags', 3600, function () {
            return Problem::whereNotNull('tags')->pluck('tags')->map(function ($tags) {
                return is_string($tags) ? json_decode($tags, true) : $tags;
            })->flatten()->filter()->unique()->sort()->values();
        });

        $totalTags = $availableTags->count();

        return view('web.pages.problems.index', compact(
            'problems',
            'totalProblems',
            'platformsCount',
            'activePlatforms',
            'platformShortNames',
            'availableTags',
            'totalTags'
        ));
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
