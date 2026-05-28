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
            $data = $request->except(['_token', 'icon', 'credential_keys', 'credential_values']);

            $credentialKeys = $request->input('credential_keys', []);
            $credentialValues = $request->input('credential_values', []);
            $settings = [];
            foreach ($credentialKeys as $i => $key) {
                if (is_null($key) || $key === '') {
                    continue;
                }
                $settings[$key] = $credentialValues[$i] ?? null;
            }
            if (!empty($settings)) {
                $data['settings'] = $settings;
            }

            $data['icon'] = $request->file('icon') ? imageUploadManager($request->file('icon'), $request->name, 'platforms') : null;

            Platform::create($data);
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
        $platform = Platform::findOrFail($id);
        return view('admin.pages.platforms.show', compact('platform'));
    }

    public function edit(Platform $platform)
    {
        return view('admin.pages.platforms.edit', compact('platform'));
    }

    public function update(PlatformRequest $request, Platform $platform)
    {
        try {
            $data = $request->except(['_token', 'icon', 'credential_keys', 'credential_values']);

            $credentialKeys = $request->input('credential_keys', []);
            $credentialValues = $request->input('credential_values', []);
            $settings = [];
            foreach ($credentialKeys as $i => $key) {
                if (is_null($key) || $key === '') {
                    continue;
                }
                $settings[$key] = $credentialValues[$i] ?? null;
            }
            $data['settings'] = $settings;

            $data['icon'] = $request->file('icon') ? imageUpdateManager($request->file('icon'), $request->name, 'platforms', $platform->icon) : $platform->icon;

            $platform->update($data);

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
            imageDeleteManager($platform->icon);
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
                'searchable' => ['name', 'name', 'base_url', 'status'],
                'orderable' => [
                    0 => 'name',
                    1 => 'base_url',
                    2 => 'status',
                ],
                'defaultOrder' => [
                    'column' => 'name',
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

                $platform->info = (new PlatformInfo($platform))->render()->render();
                $platform->base_url = '<a href="' . e($platform->base_url) . '" target="_blank">' . e($platform->base_url) . '</a>';
                $platform->status = (new StatusBadge($platform->status))->render()->render();

                return $platform;
            }
        );
    }
}
