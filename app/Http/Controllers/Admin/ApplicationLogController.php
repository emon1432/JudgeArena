<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationLog;
use App\Support\Datatable\ServerSideDatatable;
use App\View\Components\Actions;
use Illuminate\Http\Request;

class ApplicationLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data($request));
        }

        $categories = ApplicationLog::query()->select('category')->distinct()->orderBy('category')->pluck('category');
        $platforms = ApplicationLog::query()->whereNotNull('platform')->distinct()->orderBy('platform')->pluck('platform');
        $entityTypes = ApplicationLog::query()->whereNotNull('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type');

        return view('admin.pages.logs.index', compact('categories', 'platforms', 'entityTypes'));
    }

    public function show(ApplicationLog $log)
    {
        $log->load('user');

        return view('admin.pages.logs.show', compact('log'));
    }

    protected function data(Request $request): array
    {
        $query = ApplicationLog::query()->with('user')->latest('created_at');

        $search = $this->filterInput($request, 'search');
        $level = $this->filterInput($request, 'level');
        $category = $this->filterInput($request, 'category');
        $platform = $this->filterInput($request, 'platform');
        $entityType = $this->filterInput($request, 'entity_type');
        $entityId = $this->filterInput($request, 'entity_id');
        $startDate = $this->filterInput($request, 'start_date');
        $endDate = $this->filterInput($request, 'end_date');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('message', 'like', '%' . $search . '%')
                    ->orWhere('source', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('platform', 'like', '%' . $search . '%')
                    ->orWhere('entity_type', 'like', '%' . $search . '%')
                    ->orWhere('entity_id', 'like', '%' . $search . '%')
                    ->orWhere('ip_address', 'like', '%' . $search . '%');
            });
        }

        if ($level !== '') {
            $query->where('level', $level);
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($platform !== '') {
            $query->where('platform', $platform);
        }

        if ($entityType !== '') {
            $query->where('entity_type', $entityType);
        }

        if ($entityId !== '') {
            $query->where('entity_id', $entityId);
        }

        if ($startDate !== '') {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate !== '') {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return ServerSideDatatable::make(
            $request,
            $query,
            [
                'with' => ['user'],
                'searchable' => [
                    'application_logs.message',
                    'application_logs.source',
                    'application_logs.category',
                    'application_logs.platform',
                    'application_logs.entity_type',
                    'application_logs.entity_id',
                    'application_logs.ip_address',
                ],
                'orderable' => [
                    0 => 'application_logs.created_at',
                    1 => 'application_logs.level',
                    2 => 'application_logs.category',
                    3 => 'application_logs.platform',
                    4 => 'application_logs.entity_type',
                    5 => 'application_logs.entity_id',
                    6 => 'application_logs.source',
                ],
                'defaultOrder' => [
                    'column' => 'application_logs.created_at',
                    'dir' => 'desc',
                ],
            ],
            function (ApplicationLog $log) {
                $log->createdAt = $log->created_at?->format('d M, Y h:i A');
                $log->level = $this->levelBadge((string) $log->level);
                $log->category = e($log->category);
                $log->platform = e($log->platform ?? '-');
                $log->entity = e(trim(($log->entity_type ?? '-') . ($log->entity_id ? ' : ' . $log->entity_id : '')));
                $log->source = e($log->source);
                $log->message = e(str($log->message)->limit(100));
                $log->userName = e($log->user?->name ?? 'System');
                $log->actions = (new Actions([
                    'model' => $log,
                    'resource' => 'logs',
                    'buttons' => [
                        'basic' => [
                            'view' => true,
                            'edit' => false,
                            'delete' => false,
                        ],
                    ],
                ]))->render()->render();
                return $log;
            }
        );
    }

    protected function levelBadge(string $level): string
    {
        $color = match ($level) {
            'critical' => 'dark',
            'error' => 'danger',
            'warning' => 'warning',
            default => 'info',
        };

        return '<span class="badge bg-label-' . $color . ' text-uppercase">' . e($level) . '</span>';
    }

    private function filterInput(Request $request, string $key): string
    {
        $value = $request->input($key, '');

        if (is_array($value)) {
            $value = $value[0] ?? '';
        }

        return trim((string) $value);
    }
}
