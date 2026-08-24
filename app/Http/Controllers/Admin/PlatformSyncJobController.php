<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformSyncJobRequest;
use App\Models\PlatformSyncJob;
use App\Services\ApplicationLogger;
use App\Support\Datatable\ServerSideDatatable;
use App\View\Components\Actions;
use App\View\Components\PlatformInfo;
use Illuminate\Http\Request;

class PlatformSyncJobController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data($request));
        }
        return view('admin.pages.platform-sync-jobs.index');
    }

    public function show(PlatformSyncJob $platformSyncJob)
    {
        return view('admin.pages.platform-sync-jobs.show', compact('platformSyncJob'));
    }

    public function edit(PlatformSyncJob $platformSyncJob)
    {
        return view('admin.pages.platform-sync-jobs.edit', compact('platformSyncJob'));
    }

    public function update(PlatformSyncJobRequest $request, PlatformSyncJob $platformSyncJob)
    {
        try {
            $platformSyncJob->update($request->validated());

            return response()->json([
                'status' => 200,
                'message' => __('Platform sync job updated successfully'),
                'redirect' => route('admin.platform-sync-jobs.index'),
            ]);
        } catch (\Exception $e) {
            app(ApplicationLogger::class)->error('Platform sync job update failed', [
                'category' => 'admin',
                'source' => self::class,
                'resource' => 'platform-sync-jobs',
                'job_id' => $platformSyncJob->id,
            ], $e);

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
            PlatformSyncJob::query()->with('platform'),
            [
                'searchable' => ['entity'],
                'orderable' => [
                    0 => 'platform_id',
                    1 => 'entity',
                    2 => 'priority',
                    3 => 'interval_minutes',
                    4 => 'enabled',
                    5 => 'last_started_at',
                    6 => 'last_success_at',
                    7 => 'last_failed_at',
                ],
                'defaultOrder' => [
                    'column' => 'priority',
                    'dir' => 'desc',
                ],
            ],
            function (PlatformSyncJob $job) {
                $job->actions = (new Actions([
                    'model' => $job,
                    'resource' => 'platform-sync-jobs',
                    'buttons' => [
                        'basic' => [
                            'view' => true,
                            'edit' => true,
                            'delete' => false,
                        ],
                    ],
                ]))->render()->render();

                $job->platform_info = (new PlatformInfo($job->platform))->render()->render();
                
                $job->entity_formatted = ucfirst(str_replace('_', ' ', $job->entity->value));
                
                $enabledBadge = $job->enabled 
                    ? '<span class="badge bg-label-success">' . __('Enabled') . '</span>' 
                    : '<span class="badge bg-label-secondary">' . __('Disabled') . '</span>';
                $job->enabled_status = $enabledBadge;
                
                $job->last_started = $job->last_started_at ? $job->last_started_at->diffForHumans() : '<span class="text-muted">Never</span>';
                $job->last_success = $job->last_success_at ? '<span class="text-success">' . $job->last_success_at->diffForHumans() . '</span>' : '<span class="text-muted">Never</span>';
                $job->last_failed = $job->last_failed_at ? '<span class="text-danger">' . $job->last_failed_at->diffForHumans() . '</span>' : '<span class="text-muted">Never</span>';
                
                $nextRunAt = $job->nextRunAt();
                $job->next_run = $nextRunAt ? $nextRunAt->diffForHumans() : __('Immediate');

                return $job;
            }
        );
    }
}
