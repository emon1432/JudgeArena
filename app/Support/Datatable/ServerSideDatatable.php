<?php

namespace App\Support\Datatable;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServerSideDatatable
{
    /**
     * Build a DataTables server-side response from an Eloquent query.
     *
     * @param  Builder  $baseQuery
     * @param  array<string, mixed>  $options
     * @param  Closure  $transform
     * @return array<string, mixed>
     */
    public static function make(Request $request, Builder $baseQuery, array $options, Closure $transform): array
    {
        $model = $baseQuery->getModel();
        $keyName = $model->getKeyName();
        $qualifiedKey = $model->getQualifiedKeyName();

        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 10);
        $maxLength = (int) ($options['maxLength'] ?? 100);
        $length = $length > 0 ? min($length, $maxLength) : 10;

        $searchableColumns = $options['searchable'] ?? [];
        $orderableColumns = $options['orderable'] ?? [];
        $defaultOrder = $options['defaultOrder'] ?? ['column' => $qualifiedKey, 'dir' => 'desc'];
        $with = $options['with'] ?? [];

        $searchValue = trim((string) $request->input('search.value', ''));

        $totalQuery = clone $baseQuery;
        $filteredQuery = clone $baseQuery;

        if ($searchValue !== '') {
            /** @var Closure|null $searchCallback */
            $searchCallback = $options['searchCallback'] ?? null;

            if ($searchCallback instanceof Closure) {
                $searchCallback($filteredQuery, $searchValue);
            } elseif (!empty($searchableColumns)) {
                $filteredQuery->where(function (Builder $query) use ($searchableColumns, $searchValue) {
                    foreach ($searchableColumns as $column) {
                        $query->orWhere($column, 'like', "%{$searchValue}%");
                    }
                });
            }
        }

        $defaultOrderColumn = (string) ($defaultOrder['column'] ?? $qualifiedKey);
        $defaultOrderDirection = strtolower((string) ($defaultOrder['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $orderColumn = $defaultOrderColumn;
        $orderDirection = $defaultOrderDirection;

        $requestedOrderColumn = $request->input('order.0.column');
        if ($requestedOrderColumn !== null) {
            $orderColumnIndex = (int) $requestedOrderColumn;

            if (array_key_exists($orderColumnIndex, $orderableColumns)) {
                $orderColumn = $orderableColumns[$orderColumnIndex];
                $orderDirection = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
            }
        }

        $filteredQuery
            ->orderBy($orderColumn, $orderDirection)
            ->orderBy($qualifiedKey, 'desc');

        $recordsTotal = self::countDistinctByKey($totalQuery, $qualifiedKey);
        $recordsFiltered = self::countDistinctByKey($filteredQuery, $qualifiedKey);

        $rowIds = (clone $filteredQuery)
            ->selectRaw("{$qualifiedKey} as datatable_row_id")
            ->distinct()
            ->skip($start)
            ->take($length)
            ->pluck('datatable_row_id')
            ->all();

        if (empty($rowIds)) {
            return [
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => [],
            ];
        }

        $rowCollection = $model->newQuery()
            ->with($with)
            ->whereIn($keyName, $rowIds)
            ->get()
            ->keyBy($keyName);

        $data = collect($rowIds)
            ->map(function ($rowId) use ($rowCollection, $transform) {
                $row = $rowCollection->get($rowId);

                if (!$row) {
                    return null;
                }

                return $transform($row);
            })
            ->filter()
            ->values()
            ->all();

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    protected static function countDistinctByKey(Builder $query, string $qualifiedKey): int
    {
        $subQuery = (clone $query)
            ->reorder()
            ->selectRaw("{$qualifiedKey} as datatable_row_id")
            ->distinct();

        return (int) DB::query()->fromSub($subQuery, 'datatable_source')->count();
    }
}
