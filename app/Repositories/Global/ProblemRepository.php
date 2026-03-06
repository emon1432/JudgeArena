<?php

namespace App\Repositories\Global;

use App\Models\Problem;
use Illuminate\Support\Collection;

class ProblemRepository
{
    public function upsertOne(int $platformId, array $attributes): Problem
    {
        $payload = array_merge($attributes, ['platform_id' => $platformId]);

        $slug = isset($attributes['slug']) ? trim((string) $attributes['slug']) : '';
        if ($slug !== '') {
            $model = Problem::where('platform_id', $platformId)
                ->where('slug', $slug)
                ->first();

            if ($model) {
                $incomingProblemId = (string) ($payload['platform_problem_id'] ?? '');
                if ($incomingProblemId !== '' && $incomingProblemId !== (string) $model->platform_problem_id) {
                    $alreadyUsed = Problem::where('platform_id', $platformId)
                        ->where('platform_problem_id', $incomingProblemId)
                        ->where('id', '!=', $model->id)
                        ->exists();

                    // Keep existing unique ID if the incoming one belongs to another row.
                    if ($alreadyUsed) {
                        unset($payload['platform_problem_id']);
                    }
                }

                $model->fill($payload);
                $model->save();

                return $model;
            }
        }

        return Problem::updateOrCreate(
            [
                'platform_id' => $platformId,
                'platform_problem_id' => (string) ($attributes['platform_problem_id'] ?? ''),
            ],
            $payload
        );
    }

    public function upsertMany(int $platformId, array $rows): void
    {
        foreach ($rows as $row) {
            if (! isset($row['platform_problem_id'])) {
                continue;
            }

            $this->upsertOne($platformId, $row);
        }
    }

    public function findByPlatformProblemId(int $platformId, string $platformProblemId): ?Problem
    {
        return Problem::where('platform_id', $platformId)
            ->where('platform_problem_id', $platformProblemId)
            ->first();
    }

    public function byPlatform(int $platformId, int $limit = 100): Collection
    {
        return Problem::where('platform_id', $platformId)
            ->orderByDesc('rating')
            ->limit($limit)
            ->get();
    }
}
