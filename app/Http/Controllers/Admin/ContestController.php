<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Support\Datatable\ServerSideDatatable;
use App\View\Components\Actions;
use App\View\Components\ContestInfo;
use App\View\Components\StatusBadge;
use Illuminate\Http\Request;

class ContestController extends Controller
{
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
                    'platforms.display_name',
                ],
                'orderable' => [
                    0 => 'contests.name',
                    1 => 'platforms.display_name',
                    2 => 'contests.phase',
                    3 => 'contests.start_time',
                    4 => 'contests.status',
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
                $contest->platformName = optional($contest->platform)->display_name ?? '-';
                $contest->phase = ucfirst($contest->phase ?? 'Unknown');
                $contest->startAt = $contest->start_time?->format('d M, Y h:i A') ?? '-';
                $contest->status = (new StatusBadge((string) ($contest->status ?? 'Unknown')))->render()->render();

                return $contest;
            }
        );
    }
}
