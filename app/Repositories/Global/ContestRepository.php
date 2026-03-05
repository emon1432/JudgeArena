<?php

namespace App\Repositories\Global;

use App\Models\Contest;
use Illuminate\Support\Collection;

class ContestRepository
{
    public function upsertOne(int $platformId, array $attributes): Contest
    {
        return Contest::updateOrCreate(
            [
                'platform_id' => $platformId,
                'platform_contest_id' => (string) ($attributes['platform_contest_id'] ?? ''),
            ],
            array_merge($attributes, ['platform_id' => $platformId])
        );
    }

    public function upsertMany(int $platformId, array $rows): void
    {
        foreach ($rows as $row) {
            if (! isset($row['platform_contest_id'])) {
                continue;
            }

            $this->upsertOne($platformId, $row);
        }
    }

    public function findByPlatformContestId(int $platformId, string $platformContestId): ?Contest
    {
        return Contest::where('platform_id', $platformId)
            ->where('platform_contest_id', $platformContestId)
            ->first();
    }

    public function upcomingByPlatform(int $platformId, int $limit = 50): Collection
    {
        return Contest::where('platform_id', $platformId)
            ->whereIn('phase', ['before', 'upcoming'])
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }
}
