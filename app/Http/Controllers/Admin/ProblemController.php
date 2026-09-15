<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\Problem;
use App\Support\Datatable\ServerSideDatatable;
use App\View\Components\Actions;
use App\View\Components\ContestInfo;
use App\View\Components\ProblemInfo;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data($request));
        }

        $platforms = Platform::query()->orderBy('name')->get();

        return view('admin.pages.problems.index', compact('platforms'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Problem $all_problem)
    {
        return response()->json($all_problem->load(['platform', 'contest']));
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }

    protected function data(Request $request): array
    {
        $query = Problem::query()
            ->leftJoin('platforms', 'platforms.id', '=', 'problems.platform_id')
            ->leftJoin('contests', 'contests.id', '=', 'problems.contest_id')
            ->select('problems.*');

        if ($request->filled('platform')) {
            $platformVal = $request->input('platform');
            if (is_numeric($platformVal)) {
                $query->where('problems.platform_id', (int) $platformVal);
            } else {
                $query->where('platforms.slug', $platformVal);
            }
        }

        if ($request->filled('rating_range')) {
            $ratingRange = (string) $request->input('rating_range');
            match ($ratingRange) {
                'unrated' => $query->whereNull('problems.rating'),
                '800-1199' => $query->whereBetween('problems.rating', [800, 1199]),
                '1200-1599' => $query->whereBetween('problems.rating', [1200, 1599]),
                '1600-1999' => $query->whereBetween('problems.rating', [1600, 1999]),
                '2000-2399' => $query->whereBetween('problems.rating', [2000, 2399]),
                '2400+' => $query->where('problems.rating', '>=', 2400),
                default => null,
            };
        }

        if ($request->filled('status')) {
            $query->where('problems.status', $request->input('status'));
        }

        return ServerSideDatatable::make(
            $request,
            $query,
            [
                'with' => ['platform', 'contest'],
                'searchable' => [
                    'problems.name',
                    'problems.code',
                    'problems.platform_problem_id',
                    'problems.rating',
                    'platforms.name',
                    'contests.name',
                ],
                'orderable' => [
                    0 => 'problems.name',
                    1 => 'platforms.name',
                    2 => 'problems.rating',
                    3 => 'contests.name',
                ],
                'defaultOrder' => [
                    'column' => 'problems.platform_problem_id',
                    'dir' => 'asc',
                ],
            ],
            function (Problem $problem) {
                $problem->actions = (new Actions([
                    'model' => $problem,
                    'resource' => 'all-problems',
                    'buttons' => [
                        'basic' => [
                            'view' => true,
                            'edit' => false,
                            'delete' => false,
                        ],
                    ],
                ]))->render()->render();

                $problem->name = (new ProblemInfo($problem))->render()->render();
                $problem->platformName = optional($problem->platform)->name ?? '-';
                $problem->difficultyRating = ($problem->difficulty ? $problem->difficulty : '-').' / '.($problem->rating ? $problem->rating : '-');
                $problem->contestName = (new ContestInfo($problem->contest))->render()->render();

                return $problem;
            }
        );
    }
}
