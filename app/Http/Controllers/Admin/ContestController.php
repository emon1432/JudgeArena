<?php

namespace App\Http\Controllers\Admin;

use App\Core\Platforms\PlatformRegistry;
use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Services\StandingsCacheService;
use App\Support\Datatable\ServerSideDatatable;
use App\View\Components\Actions;
use App\View\Components\ContestInfo;
use App\View\Components\StatusBadge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ContestController extends Controller
{
    public function __construct(
        private readonly StandingsCacheService $standingsCacheService,
        private readonly PlatformRegistry $platformRegistry,
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data($request));
        }

        return view('admin.pages.contests.index');
    }

    public function show(Contest $all_contest)
    {
        return response()->json($all_contest->load(['platform']));
    }

    public function syncStandings(Contest $all_contest): JsonResponse
    {
        $platformSlug = strtolower($all_contest->platform?->slug ?? '');
        if (! $platformSlug) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => __('Platform not found for this contest.'),
            ], 400);
        }

        try {
            $adapter = $this->platformRegistry->resolve($platformSlug);
            if ($adapter === null) {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => __('Adapter not found for platform :platform', ['platform' => $platformSlug]),
                ], 400);
            }

            $standings = $adapter->getUserStandings((string) $all_contest->platform_contest_id);

            $participantCount = count($standings->rows ?? []);
            if ($participantCount > 0 && empty($all_contest->participant_count)) {
                $all_contest->update(['participant_count' => $participantCount]);
            }

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => __("Standings for contest ':name' synced and uploaded successfully (:count participants).", [
                    'name' => $all_contest->name ?? $all_contest->platform_contest_id,
                    'count' => $participantCount,
                ]),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => __('Failed to sync standings: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    protected function data(Request $request): array
    {
        $query = Contest::query()
            ->leftJoin('platforms', 'platforms.id', '=', 'contests.platform_id')
            ->select('contests.*');

        return ServerSideDatatable::make(
            $request,
            $query,
            [
                'with' => ['platform'],
                'searchable' => [
                    'contests.name',
                    'contests.platform_contest_id',
                    'contests.phase',
                    'contests.type',
                    'contests.status',
                    'platforms.name',
                ],
                'orderable' => [
                    0 => 'contests.name',
                    1 => 'platforms.name',
                    2 => 'contests.phase',
                    3 => 'contests.start_time',
                    5 => 'contests.status',
                ],
                'defaultOrder' => [
                    'column' => 'contests.start_time',
                    'dir' => 'desc',
                ],
            ],
            function (Contest $contest) {
                $contest->actions = (new Actions([
                    'model' => $contest,
                    'resource' => 'all-contests',
                    'buttons' => [
                        'basic' => [
                            'view' => true,
                            'edit' => false,
                            'delete' => false,
                        ],
                    ],
                ]))->render()->render();

                $contest->name = (new ContestInfo($contest))->render()->render();
                $contest->platformName = optional($contest->platform)->name ?? '-';
                $contest->phase = ucfirst($contest->phase ?? 'Unknown');
                $contest->startAt = $contest->start_time?->format('d M, Y h:i A') ?? '-';
                $contest->status = (new StatusBadge((string) ($contest->status ?? 'Unknown')))->render()->render();

                $platformSlug = strtolower($contest->platform?->slug ?? '');
                $contestId = (string) ($contest->platform_contest_id ?? '');

                if ($platformSlug !== '' && $contestId !== '' && $this->standingsCacheService->has($platformSlug, $contestId)) {
                    $contest->standingsCache = '<span class="badge bg-label-success"><i class="icon-base ti tabler-check icon-xs me-1"></i> '.__('Uploaded').'</span>';
                } elseif (strtoupper((string) $contest->phase) === 'BEFORE') {
                    $contest->standingsCache = '<span class="badge bg-label-secondary">'.__('Upcoming').'</span>';
                } else {
                    $syncUrl = route('admin.all-contests.sync-standings', $contest);
                    $contest->standingsCache = '<button type="button" class="btn btn-sm btn-primary sync-standings-btn py-1 px-2" data-url="'.$syncUrl.'" data-id="'.$contest->id.'"><i class="icon-base ti tabler-cloud-upload icon-xs me-1"></i> '.__('Upload').'</button>';
                }

                return $contest;
            }
        );
    }
}
