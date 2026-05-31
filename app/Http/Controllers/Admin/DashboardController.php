<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationLog;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\Problem;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPlatforms = Platform::count();
        $totalContests = Contest::count();
        $totalProblems = Problem::count();
        $totalUsers = User::where('role', 'user')->count();

        $errorsToday = ApplicationLog::query()
            ->whereDate('created_at', today())
            ->whereIn('level', ['error', 'critical'])
            ->count();

        $warningsToday = ApplicationLog::query()
            ->whereDate('created_at', today())
            ->where('level', 'warning')
            ->count();

        $lastSyncErrors = ApplicationLog::query()
            ->where('category', 'sync')
            ->whereIn('level', ['error', 'critical'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $recentCriticalLogs = ApplicationLog::query()
            ->where('level', 'critical')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('admin.pages.dashboard.index', compact(
            'totalPlatforms',
            'totalContests',
            'totalProblems',
            'totalUsers',
            'errorsToday',
            'warningsToday',
            'lastSyncErrors',
            'recentCriticalLogs'
        ));
    }
}
