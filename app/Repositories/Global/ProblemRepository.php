<?php

namespace App\Repositories\Global;

use App\Models\Problem;
use Illuminate\Support\Collection;

class ProblemRepository
{
    public function upsertOne(int $platformId, array $attributes): Problem
    {
        return Problem::updateOrCreate(
            [
                'platform_id' => $platformId,
                'platform_problem_id' => (string) ($attributes['platform_problem_id'] ?? ''),
            ],
            array_merge($attributes, ['platform_id' => $platformId])
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
