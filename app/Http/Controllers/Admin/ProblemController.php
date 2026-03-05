<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        return view('admin.pages.problems.index');
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

        return ServerSideDatatable::make(
            $request,
            $query,
            [
                'with' => ['platform', 'contest'],
                'searchable' => [
                    'problems.name',
                    'problems.code',
                    'problems.difficulty',
                    'problems.rating',
                    'platforms.display_name',
                    'contests.name',
                ],
                'orderable' => [
                    0 => 'problems.name',
                    1 => 'platforms.display_name',
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
                $problem->platformName = optional($problem->platform)->display_name ?? '-';
                $problem->difficultyRating = ($problem->difficulty ? $problem->difficulty : '-') . ' / ' . ($problem->rating ? $problem->rating : '-');
                $problem->contestName = (new ContestInfo($problem->contest))->render()->render();

                return $problem;
            }
        );
    }
}
