<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WebsiteController extends Controller
{
    public function home()
    {
        $heroStats = [
            [
                'label' => 'Institutes Represented',
                'value' => number_format((int) User::query()
                    ->where('role', 'user')
                    ->whereNotNull('institute_id')
                    ->whereHas('platformProfiles', fn($query) => $query->active())
                    ->distinct('institute_id')
                    ->count('institute_id')) . '+',
            ],
            [
                'label' => 'Countries Represented',
                'value' => number_format((int) User::query()
                    ->where('role', 'user')
                    ->whereNotNull('country_id')
                    ->whereHas('platformProfiles', fn($query) => $query->active())
                    ->distinct('country_id')
                    ->count('country_id')) . '+',
            ],
            [
                'label' => 'Total Users',
                'value' => number_format((int) User::query()
                    ->where('role', 'user')
                    ->whereHas('platformProfiles', fn($query) => $query->active())
                    ->count()) . '+',
            ],
            [
                'label' => 'Platforms Tracked',
                'value' => number_format((int) Platform::query()->active()->count()),
            ],
        ];

        $profileAggregateQuery = PlatformProfile::query()
            ->active()
            ->selectRaw('user_id, COALESCE(SUM(COALESCE(total_solved, 0)), 0) as total_solved')
            ->selectRaw('COALESCE(SUM(COALESCE(rating, 0)), 0) as total_rating')
            ->selectRaw('COALESCE(MAX(COALESCE(rating, 0)), 0) as best_rating')
            ->selectRaw('COUNT(*) as platform_count')
            ->groupBy('user_id');

        $featuredCandidates = User::query()
            ->where('users.role', 'user')
            ->joinSub($profileAggregateQuery, 'profile_totals', function ($join) {
                $join->on('profile_totals.user_id', '=', 'users.id');
            })
            ->whereNotNull('users.username')
            ->whereRaw('profile_totals.total_solved > 0')
            ->whereRaw('profile_totals.platform_count >= 2')
            ->select('users.id', 'users.name', 'users.username')
            ->selectRaw('profile_totals.total_solved')
            ->selectRaw('profile_totals.total_rating')
            ->selectRaw('profile_totals.best_rating')
            ->selectRaw('profile_totals.platform_count')
            ->selectRaw(
                '(CASE WHEN users.image IS NOT NULL THEN 1 ELSE 0 END
                    + CASE WHEN users.country_id IS NOT NULL THEN 1 ELSE 0 END
                    + CASE WHEN users.institute_id IS NOT NULL THEN 1 ELSE 0 END
                    + CASE WHEN users.github IS NOT NULL AND users.github <> "" THEN 1 ELSE 0 END
                    + CASE WHEN users.linkedin IS NOT NULL AND users.linkedin <> "" THEN 1 ELSE 0 END
                    + CASE WHEN users.fav_quote IS NOT NULL AND users.fav_quote <> "" THEN 1 ELSE 0 END) as completeness_score'
            )
            ->orderByDesc('completeness_score')
            ->orderByDesc('profile_totals.platform_count')
            ->orderByDesc('profile_totals.total_solved')
            ->limit(30)
            ->get();

        $featuredUser = $featuredCandidates->isNotEmpty()
            ? $featuredCandidates->random()
            : null;

        $featuredUserRank = null;
        $featuredPlatformStats = collect();

        if ($featuredUser) {
            $featuredUserRank = User::query()
                ->where('users.role', 'user')
                ->joinSub($profileAggregateQuery, 'rank_totals', function ($join) {
                    $join->on('rank_totals.user_id', '=', 'users.id');
                })
                ->where(function ($query) use ($featuredUser) {
                    $query->where('rank_totals.total_rating', '>', $featuredUser->total_rating)
                        ->orWhere(function ($subQuery) use ($featuredUser) {
                            $subQuery->where('rank_totals.total_rating', '=', $featuredUser->total_rating)
                                ->where('users.username', '<', $featuredUser->username);
                        });
                })
                ->count() + 1;

            $featuredPlatformStats = PlatformProfile::query()
                ->active()
                ->where('user_id', $featuredUser->id)
                ->with('platform:id,name,display_name')
                ->orderByDesc('total_solved')
                ->limit(3)
                ->get();
        }

        return view('web.pages.home', [
            'heroStats' => $heroStats,
            'featuredUser' => $featuredUser,
            'featuredUserRank' => $featuredUserRank,
            'featuredPlatformStats' => $featuredPlatformStats,
        ]);
    }

    public function contactUs()
    {
        return view('web.pages.contact-us');
    }

    public function submitContact(ContactRequest $request)
    {
        $contactMessage = ContactMessage::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $recipientEmail = 'e.mon143298@gmail.com';

        if (!empty($recipientEmail)) {
            try {
                Mail::to($recipientEmail)->send(new ContactMessageMail($contactMessage));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', 'Thanks for contacting us! We have received your message.');
    }

    public function problems()
    {
        return view('web.pages.coming-soon', ['title' => 'Problems']);
    }

    public function contests()
    {
        return view('web.pages.coming-soon', ['title' => 'Contests']);
    }

    public function community()
    {
        return view('web.pages.coming-soon', ['title' => 'Community']);
    }
}
