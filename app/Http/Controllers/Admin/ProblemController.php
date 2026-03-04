<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Problem;
use App\View\Components\Actions;
use App\View\Components\ContestInfo;
use App\View\Components\ProblemInfo;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data());
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

    protected function data()
    {
        return Problem::with('platform','contest')->get()->map(function ($problem) {
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
            $problem->difficultyRating = ($problem->difficulty ? $problem->difficulty : '-') . '/' . ($problem->rating ? $problem->rating : '-');
            $problem->contestName = (new ContestInfo($problem->contest))->render()->render();
            return $problem;
        })->toArray();
    }
}
