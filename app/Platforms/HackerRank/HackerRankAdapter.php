<?php

namespace App\Platforms\HackerRank;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\Platform;
use App\Enums\Verdict;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class HackerRankAdapter implements PlatformAdapter
{
    public function __construct(
        protected HackerRankClient $client
    ) {}

    public function platform(): string
    {
        return Platform::HACKERRANK->value;
    }

    public function profileUrl(string $handle): string
    {
        $this->client->fetchProfile($handle);
        return "https://www.hackerrank.com/profile/{$handle}";
    }

    public function supportsSubmissions(): bool
    {
        // HackerRank exposes recent challenges via REST
        return true;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $data = $this->client->fetchProfile($handle);
        $ratingGraph = $this->client->fetchRatingGraph($handle);
        $rawProfile = is_array($data['raw'] ?? null) ? $data['raw'] : [];

        $latestRating = null;
        if (!empty($ratingGraph)) {
            $latestDate = null;
            foreach ($ratingGraph as $graph) {
                if (!empty($graph['data'])) {
                    foreach ($graph['data'] as $date => $event) {
                        if (is_null($latestDate) || strtotime($date) > strtotime($latestDate)) {
                            $latestDate = $date;
                            $latestRating = $event['rating'] ?? null;
                        }
                    }
                }
            }
        }

        $normalizedRating = is_numeric($latestRating)
            ? (int) round((float) $latestRating)
            : null;

        $name = $rawProfile['name']
            ?? $rawProfile['full_name']
            ?? $rawProfile['username']
            ?? $handle;

        $avatarUrl = $rawProfile['avatar']
            ?? $rawProfile['avatar_url']
            ?? $rawProfile['profile_picture']
            ?? null;

        $joinedAt = $rawProfile['created_at']
            ?? $rawProfile['createdAt']
            ?? $rawProfile['joined_at']
            ?? null;

        $country = $rawProfile['country']
            ?? $rawProfile['country_name']
            ?? null;

        return new ProfileDTO(
            platform: Platform::HACKERRANK,
            handle: $handle,
            rating: $normalizedRating,
            totalSolved: (int) ($data['total_solved'] ?? 0),
            raw: [
                'platform_user_id' => $rawProfile['id'] ?? $handle,
                'name' => $name,
                'avatar_url' => $avatarUrl,
                'joined_at' => $joinedAt,
                'country' => $country,
                'ranking' => $data['ranking'] ?? null,
                'badges' => $data['badges'] ?? null,
                'profile' => $rawProfile,
                'rating_graph' => $ratingGraph,
            ]
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        $submissions = $this->client->fetchSubmissions($handle);

        return collect($submissions)->map(function ($row) {
            $problemUrl = "https://www.hackerrank.com" . ($row['url'] ?? '');
            $problemSlug = basename(parse_url($problemUrl, PHP_URL_PATH), '/');

            $submittedAt = isset($row['created_at'])
                ? CarbonImmutable::parse($row['created_at'])
                : CarbonImmutable::now();

            return new SubmissionDTO(
                problemId: $problemSlug ?: ($row['name'] ?? 'unknown'),
                problemName: $row['name'] ?? $problemSlug ?: 'unknown',
                difficulty: null,
                verdict: Verdict::ACCEPTED,
                submittedAt: $submittedAt,
                raw: [
                    'problem_url' => $problemUrl,
                    'challenge_type' => $row['challenge_type'] ?? null,
                ]
            );
        });
    }
}
