<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use App\Models\Country;
use App\Models\Institute;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WebsiteController extends Controller
{
    public function home()
    {
        return view('web.pages.home');
    }

    public function leaderboard(Request $request)
    {
        $sort = $request->string('sort')->toString() ?: 'rating_desc';

        $ratingAggregateQuery = PlatformProfile::query()
            ->selectRaw('user_id, COALESCE(SUM(COALESCE(rating, 0)), 0) as total_rating, COALESCE(SUM(COALESCE(total_solved, 0)), 0) as total_solved')
            ->groupBy('user_id');

        $leaderboardQuery = User::query()
            ->with(['country:id,name,code,flag', 'institute:id,name'])
            ->where('role', 'user')
            ->leftJoinSub($ratingAggregateQuery, 'leaderboard_profiles', function ($join) {
                $join->on('leaderboard_profiles.user_id', '=', 'users.id');
            })
            ->select('users.*')
            ->selectRaw('COALESCE(leaderboard_profiles.total_rating, 0) as total_rating')
            ->selectRaw('COALESCE(leaderboard_profiles.total_solved, 0) as total_solved');

        if ($search = trim($request->input('search', ''))) {
            $leaderboardQuery->where(function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%");
            });
        }

        if ($countryId = $request->input('country_id')) {
            $leaderboardQuery->where('users.country_id', $countryId);
        }

        if ($instituteId = $request->input('institute_id')) {
            $leaderboardQuery->where('users.institute_id', $instituteId);
        }

        if ($platformId = $request->input('platform_id')) {
            $leaderboardQuery->whereExists(function ($query) use ($platformId) {
                $query->selectRaw('1')
                    ->from('platform_profiles')
                    ->whereColumn('platform_profiles.user_id', 'users.id')
                    ->where('platform_profiles.platform_id', $platformId);
            });
        }

        switch ($sort) {
            case 'rating_asc':
                $leaderboardQuery->orderByRaw('COALESCE(leaderboard_profiles.total_rating, 0) asc')
                    ->orderBy('users.username');
                break;
            case 'rating_desc':
            default:
                $leaderboardQuery->orderByRaw('COALESCE(leaderboard_profiles.total_rating, 0) desc')
                    ->orderBy('users.username');
                break;
        }

        $users = $leaderboardQuery->paginate(20)->withQueryString();

        $startRank = ($users->currentPage() - 1) * $users->perPage() + 1;
        $users->getCollection()->transform(function ($user, $index) use ($startRank) {
            $user->leaderboard_rank = $startRank + $index;

            return $user;
        });

        $topUsers = (clone $leaderboardQuery)
            ->limit(3)
            ->get();

        $countries = Country::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'flag']);

        $platforms = Platform::query()
            ->active()
            ->orderBy('display_name')
            ->get(['id', 'name', 'display_name']);

        $institutes = Institute::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('web.pages.leaderboard', [
            'users' => $users,
            'topUsers' => $topUsers,
            'countries' => $countries,
            'institutes' => $institutes,
            'platforms' => $platforms,
            'sort' => $sort,
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
}
