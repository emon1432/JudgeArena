<?php

namespace App\Platforms\AtCoder;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
use Illuminate\Support\Collection;

class AtCoderAdapter implements PlatformAdapter
{
    public function __construct(
        protected AtCoderClient $client
    ) {}

    public function platform(): string
    {
        return Platform::ATCODER->value;
    }

    public function profileUrl(string $handle): string
    {
        $this->client->fetchProfile($handle);
        return "https://atcoder.jp/users/{$handle}";
    }

    public function supportsSubmissions(): bool
    {
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $profileData = $this->client->fetchProfile($handle);
        $contestHistory = $this->client->fetchContestHistory($handle);

        // Build comprehensive raw data
        $rawData = [
            'platform_user_id' => $profileData['handle'],
            'handle' => $profileData['handle'],
            'name' => $profileData['handle'],
            'avatar_url' => $profileData['avatar_url'] ?? null,
            'joined_at' => $profileData['joined_at'] ?? null,
            'country' => $profileData['country'] ?? null,
            'organization' => $profileData['affiliation'] ?? null,
            'affiliation' => $profileData['affiliation'] ?? null,
            'rating' => $profileData['rating'],
            'highest_rating' => $profileData['highest_rating'],
            'rank' => $profileData['rank'],
            'rated_matches' => $profileData['rated_matches'],
            'contest_history' => $contestHistory,
            'rating_graph_data' => $this->buildRatingGraphData($contestHistory),
        ];

        return new ProfileDTO(
            platform: Platform::ATCODER,
            handle: $profileData['handle'],
            rating: $profileData['rating'],
            totalSolved: $profileData['total_solved'],
            raw: $rawData
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        return collect();
    }

    /**
     * Build rating graph data from contest history
     */
    private function buildRatingGraphData(array $contestHistory): array
    {
        $graphData = [];

        foreach ($contestHistory as $contest) {
            $graphData[] = [
                'timestamp' => $contest['timestamp'],
                'contest_name' => $contest['contest_name'],
                'contest_url' => $contest['contest_url'],
                'rating' => $contest['new_rating'],
                'rating_change' => $contest['rating_change'],
                'rank' => $contest['rank'],
                'performance' => $contest['performance'],
            ];
        }

        // Reverse to get chronological order (scraping returns newest first)
        return array_reverse($graphData);
    }
}
