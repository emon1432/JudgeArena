<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Models\Country;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
class OthersController extends Controller
{
    public function select2Options(Request $request)
    {
        $type = strtolower(trim((string) $request->input('type', '')));
        $search = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        if (in_array($type, ['country', 'countries'], true)) {
            $query = Country::query()->orderBy('name');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $items = $query->paginate($perPage, ['id', 'name', 'code', 'flag'], 'page', $page);

            return response()->json([
                'results' => $items->getCollection()->map(function ($country) {
                    return [
                        'id' => $country->id,
                        'text' => trim(($country->flag ? $country->flag . ' ' : '') . $country->name . ($country->code ? ' (' . $country->code . ')' : '')),
                    ];
                })->values(),
                'pagination' => [
                    'more' => $items->hasMorePages(),
                ],
            ]);
        }

        if (in_array($type, ['institute', 'institutes'], true)) {
            $query = Institute::query()
                ->with('country:id,name')
                ->orderBy('name');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('website', 'like', "%{$search}%")
                        ->orWhereHas('country', function ($countryQuery) use ($search) {
                            $countryQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $items = $query->paginate($perPage, ['id', 'name', 'country_id', 'website'], 'page', $page);

            return response()->json([
                'results' => $items->getCollection()->map(function ($institute) {
                    $suffix = [];

                    if ($institute->country?->name) {
                        $suffix[] = $institute->country->name;
                    }

                    if (! empty($institute->website)) {
                        $suffix[] = $institute->website;
                    }

                    return [
                        'id' => $institute->id,
                        'text' => $suffix ? $institute->name . ' (' . implode(' • ', $suffix) . ')' : $institute->name,
                    ];
                })->values(),
                'pagination' => [
                    'more' => $items->hasMorePages(),
                ],
            ]);
        }

        return response()->json([
            'results' => [],
            'pagination' => [
                'more' => false,
            ],
        ]);
    }

    public function sitemap()
    {
        $leaderboardLastMod = User::query()->where('role', 'user')->max('updated_at');

        $staticUrls = collect([
            [
                'loc' => route('home'),
                'changefreq' => 'daily',
                'priority' => '1.0',
                'lastmod' => now()->toAtomString(),
            ],
            [
                'loc' => route('leaderboard'),
                'changefreq' => 'hourly',
                'priority' => '0.9',
                'lastmod' => $leaderboardLastMod ? Carbon::parse($leaderboardLastMod)->toAtomString() : now()->toAtomString(),
            ],
            [
                'loc' => route('contact.us'),
                'changefreq' => 'monthly',
                'priority' => '0.6',
                'lastmod' => now()->toAtomString(),
            ],
        ]);

        $profileUrls = User::query()
            ->where('role', 'user')
            ->whereNotNull('username')
            ->orderByDesc('updated_at')
            ->get(['username', 'updated_at'])
            ->map(function (User $user) {
                return [
                    'loc' => route('user.profile.show', $user->username),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                    'lastmod' => optional($user->updated_at)->toAtomString() ?? now()->toAtomString(),
                ];
            });

        $urls = $staticUrls->merge($profileUrls);

        return response()
            ->view('web.pages.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    public function login()
    {
        return view('auth.admin-login');
    }

    public function testMail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            $data = [
                'subject' => 'Test Email',
                'body' => 'This is a test email to verify the mail configuration.',
            ];

            Mail::to($request->test_email)->send(new TestMail($data));

            return response()->json([
                'status' => 200,
                'message' => __('Test email sent successfully to ') . $request->test_email,
                'redirect' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Failed to send test email: ') . $e->getMessage(),
                'redirect' => null,
            ]);
        }
    }

    public function migrate()
    {
        $userId = Auth::user()->id ?? 1;
        Artisan::call('migrate:fresh --seed');
        Auth::loginUsingId($userId);
        notify()->success('Database migration has been completed successfully.');
        return redirect('/dashboard');
    }

    public function clear()
    {
        Artisan::call('optimize:clear');
        notify()->success('Cache has been cleared successfully.');
        return redirect('/dashboard');
    }

    public function composer()
    {
        exec('composer update');
        exec('composer dump-autoload');
        notify()->success('Composer update has been completed successfully.');
        return redirect('/dashboard');
    }

    public function iseed()
    {
        $tables = DB::select('SHOW TABLES');
        $prevent_tables = ['failed_jobs', 'migrations', 'password_reset_tokens', 'personal_access_tokens', 'sessions', 'cache', 'cache_locks', 'failed_jobs', 'job_batches', 'jobs'];
        foreach ($tables as $table) {
            $table_name = 'Tables_in_' . env('DB_DATABASE');
            $table_name = $table->$table_name;
            if (!in_array($table_name, $prevent_tables))
                Artisan::call('iseed ' . $table_name . ' --force');
        }
        notify()->success('Database seed has been created successfully.');
        return redirect('/dashboard');
    }
}
