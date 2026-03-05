<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformRequest;
use App\Models\Platform;
use App\Support\Datatable\ServerSideDatatable;
use App\View\Components\Actions;
use App\View\Components\PlatformInfo;
use App\View\Components\StatusBadge;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data($request));
        }
        return view('admin.pages.platforms.index');
    }

    public function create()
    {
        return view('admin.pages.platforms.create');
    }

    public function store(PlatformRequest $request)
    {
        try {
            Platform::create($request->except(['_token', 'image']) + [
                'image' => $request->file('image') ? imageUploadManager($request->file('image'), $request->name, 'platforms') : null,
            ]);
            return response()->json([
                'status' => 200,
                'message' => __('Platform created successfully'),
                'redirect' => route('platforms.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Platform $platform)
    {
        return view('admin.pages.platforms.edit', compact('platform'));
    }

    public function update(PlatformRequest $request, Platform $platform)
    {
        try {
            $platform->update($request->except(['_token', 'image']) + [
                'image' => $request->file('image') ? imageUpdateManager($request->file('image'), $request->name, 'platforms', $platform->image) : $platform->image,
            ]);

            return response()->json([
                'status' => 200,
                'message' => __('Platform updated successfully'),
                'redirect' => route('platforms.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    public function destroy(Platform $platform)
    {
        try {
            imageDeleteManager($platform->image);
            $platform->delete();

            return response()->json([
                'status' => 200,
                'message' => __('Platform deleted successfully'),
                'redirect' => route('platforms.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    protected function data(Request $request): array
    {
        return ServerSideDatatable::make(
            $request,
            Platform::query(),
            [
                'searchable' => ['name', 'display_name', 'base_url', 'status'],
                'orderable' => [
                    0 => 'display_name',
                    1 => 'base_url',
                    2 => 'status',
                ],
                'defaultOrder' => [
                    'column' => 'display_name',
                    'dir' => 'asc',
                ],
            ],
            function (Platform $platform) {
                $platform->actions = (new Actions([
                    'model' => $platform,
                    'resource' => 'platforms',
                    'buttons' => [
                        'basic' => [
                            'view' => true,
                            'edit' => true,
                            'delete' => true,
                        ],
                    ],
                ]))->render()->render();

                $platform->name = (new PlatformInfo($platform))->render()->render();
                $platform->base_url = '<a href="' . e($platform->base_url) . '" target="_blank">' . e($platform->base_url) . '</a>';
                $platform->status = (new StatusBadge($platform->status))->render()->render();

                return $platform;
            }
        );
    }
}
