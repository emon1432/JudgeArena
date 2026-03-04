<?php

namespace App\Platforms\LeetCode;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
use Illuminate\Support\Collection;

class LeetCodeAdapter implements PlatformAdapter
{
    public function __construct(
        protected LeetCodeClient $client
    ) {}

    public function platform(): string
    {
        return Platform::LEETCODE->value;
    }

    public function profileUrl(string $handle): string
    {
        $this->client->fetchUserProfile($handle);
        return "https://leetcode.com/u/{$handle}/";
    }

    public function supportsSubmissions(): bool
    {
        // LeetCode doesn't provide full submission history via API
        // Only recent 20 AC submissions are available
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $profileData = $this->client->fetchUserProfile($handle);
        $difficultyStats = $this->client->extractDifficultyStats($profileData);
        $profileMeta = $profileData['profile'] ?? [];

        // Fetch additional data
        $recentSubmissions = $this->client->fetchRecentSubmissions($handle);
        $contestInfo = $this->client->fetchContestInfo($handle);
        $calendar = $this->client->fetchSubmissionCalendar($handle);

        $totalSolved = $difficultyStats['easy'] + $difficultyStats['medium'] + $difficultyStats['hard'];

        // Build comprehensive raw data
        $rawData = [
            'platform_user_id' => $profileData['username'] ?? $handle,
            'username' => $profileData['username'],
            'name' => $profileMeta['realName'] ?: ($profileData['username'] ?? $handle),
            'avatar_url' => $profileMeta['userAvatar'] ?? null,
            'joined_at' => null,
            'country' => $profileMeta['countryName'] ?? null,
            'profile' => $profileData['profile'] ?? [],
            'submitStatsGlobal' => $profileData['submitStatsGlobal'] ?? [],
            'easy_solved' => $difficultyStats['easy'],
            'medium_solved' => $difficultyStats['medium'],
            'hard_solved' => $difficultyStats['hard'],
            'ranking' => $profileData['profile']['ranking'] ?? null,
            'badges' => $profileData['badges'] ?? [],
            'upcoming_badges' => $profileData['upcomingBadges'] ?? [],
            'recent_submissions' => $recentSubmissions,
            'contest_ranking' => $contestInfo['userContestRanking'] ?? null,
            'contest_history' => $contestInfo['userContestRankingHistory'] ?? [],
            'calendar' => $calendar,
            'contest_rating' => $contestInfo['userContestRanking']['rating'] ?? null,
            'contest_global_ranking' => $contestInfo['userContestRanking']['globalRanking'] ?? null,
            'attended_contests_count' => $contestInfo['userContestRanking']['attendedContestsCount'] ?? 0,
        ];

        // Use contest rating if available, otherwise null
        $rating = $rawData['contest_rating'] ? (int) round($rawData['contest_rating']) : null;

        return new ProfileDTO(
            platform: Platform::LEETCODE,
            handle: $profileData['username'],
            rating: $rating,
            totalSolved: $totalSolved,
            raw: $rawData
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        // LeetCode API doesn't provide comprehensive submission history
        // Only recent 20 AC submissions are available, which is too limited
        // for tracking unique problems solved over time
        return collect();
    }
}
