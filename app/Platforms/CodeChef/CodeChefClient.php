<?php

namespace App\Platforms\CodeChef;

use App\Support\Http\BaseHttpClient;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\CarbonImmutable;

class CodeChefClient extends BaseHttpClient
{
    private const BASE_URL = 'https://www.codechef.com';

    /**
     * Fetch user profile from CodeChef
     */
    public function fetchProfile(string $handle): array
    {
        $url = self::BASE_URL . '/users/' . urlencode($handle);

        $response = $this->get($url, [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ]);

        if (!$response->ok()) {
            throw new \RuntimeException('CodeChef user not found');
        }

        $html = $response->body();
        $crawler = new Crawler($html);

        $extended = $this->extractExtendedProfileFields($html, $crawler, $handle);

        // Extract rating
        $rating = null;
        if ($crawler->filter('.rating-number')->count()) {
            $ratingText = trim($crawler->filter('.rating-number')->first()->text());
            $rating = is_numeric($ratingText) ? (int) $ratingText : null;
        }

        // Extract max rating
        $maxRating = null;
        $ratingHeader = $crawler->filter('.rating-header');
        if ($ratingHeader->count() > 0) {
            $headerText = $ratingHeader->text();
            if (preg_match('/Highest Rating\s*(\d+)/', $headerText, $matches)) {
                $maxRating = (int) $matches[1];
            }
        }

        // Extract stars (rating tier)
        $stars = null;
        if ($crawler->filter('.rating-star')->count()) {
            $stars = $crawler->filter('.rating-star')->count();
        }

        // Extract country rank and global rank
        $countryRank = null;
        $globalRank = null;
        $crawler->filter('.rating-ranks ul li')->each(function (Crawler $node) use (&$countryRank, &$globalRank) {
            $text = $node->text();
            if (str_contains($text, 'Country Rank')) {
                preg_match('/(\d+)/', $text, $matches);
                $countryRank = isset($matches[1]) ? (int) $matches[1] : null;
            } elseif (str_contains($text, 'Global Rank')) {
                preg_match('/(\d+)/', $text, $matches);
                $globalRank = isset($matches[1]) ? (int) $matches[1] : null;
            }
        });

        // Extract total solved
        $totalSolved = 0;
        $solvedNodes = $crawler->filter('.rating-data-section.problems-solved h3');
        if ($solvedNodes->count() >= 4) {
            preg_match('/\d+/', $solvedNodes->eq(3)->text(), $m);
            $totalSolved = (int) ($m[0] ?? 0);
        }

        // Extract fully solved, partially solved
        $fullySolved = 0;
        $partiallySolved = 0;
        if ($solvedNodes->count() >= 3) {
            // Fully solved is typically the 2nd h3
            preg_match('/\d+/', $solvedNodes->eq(1)->text(), $m);
            $fullySolved = (int) ($m[0] ?? 0);

            // Partially solved is typically the 3rd h3
            preg_match('/\d+/', $solvedNodes->eq(2)->text(), $m);
            $partiallySolved = (int) ($m[0] ?? 0);
        }

        // Extract badges
        $badges = [];
        $crawler->filter('.widget.badges .badge .badge__title')->each(
            function (Crawler $node) use (&$badges) {
                $badges[] = trim($node->text());
            }
        );

        return [
            'handle' => $handle,
            'platform_user_id' => $extended['platform_user_id'],
            'name' => $extended['name'],
            'avatar_url' => $extended['avatar_url'],
            'joined_at' => $extended['joined_at'],
            'country' => $extended['country'],
            'affiliation' => $extended['affiliation'],
            'rating' => $rating,
            'max_rating' => $maxRating,
            'stars' => $stars,
            'country_rank' => $countryRank,
            'global_rank' => $globalRank,
            'total_solved' => $totalSolved,
            'fully_solved' => $fullySolved,
            'partially_solved' => $partiallySolved,
            'badges' => $badges,
        ];
    }

    private function extractExtendedProfileFields(string $html, Crawler $crawler, string $handle): array
    {
        $name = null;
        $country = null;
        $affiliation = null;
        $joinedAt = null;
        $avatarUrl = null;
        $platformUserId = $handle;

        if ($crawler->filter('meta[property="og:image"]')->count() > 0) {
            $avatarUrl = $crawler->filter('meta[property="og:image"]')->attr('content');
        }

        if ($crawler->filter('meta[property="og:title"]')->count() > 0) {
            $title = trim((string) $crawler->filter('meta[property="og:title"]')->attr('content'));
            if ($title !== '') {
                $name = str_ireplace('| CodeChef', '', $title);
                $name = trim((string) $name);
            }
        }

        if ($crawler->filter('.user-name, .profile-details .name')->count() > 0) {
            $heading = trim((string) $crawler->filter('.user-name, .profile-details .name')->first()->text(''));
            if ($heading !== '' && ! str_contains(strtolower($heading), 'user profile')) {
                $name = $heading;
            }
        }

        $crawler->filter('tr')->each(function (Crawler $row) use (&$country, &$affiliation, &$joinedAt) {
            if ($row->filter('th')->count() === 0 || $row->filter('td')->count() === 0) {
                return;
            }

            $label = strtolower(trim((string) $row->filter('th')->first()->text('')));
            $value = trim((string) $row->filter('td')->first()->text(''));

            if ($value === '') {
                return;
            }

            if (str_contains($label, 'country') && $country === null) {
                $country = $value;
            } elseif ((str_contains($label, 'institution') || str_contains($label, 'organization') || str_contains($label, 'affiliation')) && $affiliation === null) {
                $affiliation = $value;
            } elseif ((str_contains($label, 'joined') || str_contains($label, 'registered')) && $joinedAt === null) {
                try {
                    $joinedAt = CarbonImmutable::parse($value)->toIso8601String();
                } catch (\Throwable) {
                    $joinedAt = null;
                }
            }
        });

        if (preg_match('/window\.__INITIAL_STATE__\s*=\s*(\{.*?\});/s', $html, $matches)) {
            $state = json_decode($matches[1], true);
            if (is_array($state)) {
                $platformUserId = (string) (
                    data_get($state, 'userDetails.userId')
                    ?? data_get($state, 'profile.userId')
                    ?? data_get($state, 'user.id')
                    ?? $platformUserId
                );

                $name = $name
                    ?? data_get($state, 'userDetails.name')
                    ?? data_get($state, 'profile.name')
                    ?? data_get($state, 'user.name');

                $country = $country
                    ?? data_get($state, 'userDetails.country')
                    ?? data_get($state, 'profile.country')
                    ?? data_get($state, 'user.country');

                $affiliation = $affiliation
                    ?? data_get($state, 'userDetails.institution')
                    ?? data_get($state, 'userDetails.organization')
                    ?? data_get($state, 'profile.organization');

                $avatarUrl = $avatarUrl
                    ?? data_get($state, 'userDetails.profileImage')
                    ?? data_get($state, 'profile.avatar');

                if ($joinedAt === null) {
                    $joinedRaw = data_get($state, 'userDetails.joinedOn')
                        ?? data_get($state, 'profile.createdAt')
                        ?? data_get($state, 'user.createdAt');

                    if (is_string($joinedRaw) && trim($joinedRaw) !== '') {
                        try {
                            $joinedAt = CarbonImmutable::parse($joinedRaw)->toIso8601String();
                        } catch (\Throwable) {
                            $joinedAt = null;
                        }
                    }
                }
            }
        }

        if (! is_string($name) || trim($name) === '') {
            $name = $handle;
        }

        $name = preg_replace('/\buser\s+profile\s+for\b.*$/i', '', $name ?? '') ?? '';
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? '');

        $lowerName = strtolower($name);
        if (
            str_contains($lowerName, 'programming')
            || str_contains($lowerName, 'competitive')
            || str_contains($lowerName, 'dsa')
            || str_contains($lowerName, 'codechef')
        ) {
            $name = $handle;
        }

        if ($name === '' || strcasecmp($name, 'profile') === 0) {
            $name = $handle;
        }

        if (is_string($avatarUrl)) {
            $normalizedAvatar = strtolower($avatarUrl);
            if (
                str_contains($normalizedAvatar, '/cc-logo')
                || str_contains($normalizedAvatar, '/codechef-logo')
                || str_contains($normalizedAvatar, '/themes/abessive/')
            ) {
                $avatarUrl = null;
            }
        }

        return [
            'platform_user_id' => $platformUserId !== '' ? $platformUserId : $handle,
            'name' => trim($name),
            'avatar_url' => is_string($avatarUrl) && trim($avatarUrl) !== '' ? trim($avatarUrl) : null,
            'joined_at' => $joinedAt,
            'country' => is_string($country) && trim($country) !== '' ? trim($country) : null,
            'affiliation' => is_string($affiliation) && trim($affiliation) !== '' ? trim($affiliation) : null,
        ];
    }

    /**
     * Fetch rating graph data (contest history)
     * CodeChef has 4 categories: Long, Cook-off, Lunchtime, Starters
     */
    public function fetchRatingGraph(string $handle): array
    {
        try {
            $url = self::BASE_URL . '/users/' . urlencode($handle);

            $response = $this->get($url, [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ]);

            if (!$response->ok()) {
                return [];
            }

            $html = $response->body();

            // Extract the JavaScript variable that contains rating data
            // Pattern: var all_rating = [...];
            if (!preg_match('/var all_rating = (.*?);/s', $html, $matches)) {
                return [];
            }

            $ratingJson = $matches[1];
            // Replace 'null' with '"null"' for valid JSON
            $ratingJson = str_replace('null', '"null"', $ratingJson);

            $ratings = json_decode($ratingJson, true);
            if (!is_array($ratings)) {
                return [];
            }

            $longContests = [];
            $cookoffContests = [];
            $lunchtimeContests = [];
            $startersContests = [];

            foreach ($ratings as $contest) {
                $timestamp = null;
                if (!empty($contest['end_date'])) {
                    try {
                        $timestamp = CarbonImmutable::parse($contest['end_date'])->toDateTimeString();
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                $contestData = [
                    'name' => $contest['name'] ?? '',
                    'code' => $contest['code'] ?? '',
                    'url' => self::BASE_URL . '/' . ($contest['code'] ?? ''),
                    'rating' => $contest['rating'] !== 'null' ? (int) $contest['rating'] : null,
                    'rank' => $contest['rank'] ?? null,
                    'timestamp' => $timestamp,
                ];

                $code = $contest['code'] ?? '';

                if (str_contains($code, 'COOK')) {
                    $cookoffContests[] = $contestData;
                } elseif (str_contains($code, 'LTIME')) {
                    $lunchtimeContests[] = $contestData;
                } elseif (str_contains($code, 'START')) {
                    $startersContests[] = $contestData;
                } else {
                    $longContests[] = $contestData;
                }
            }

            return [
                'long' => $longContests,
                'cookoff' => $cookoffContests,
                'lunchtime' => $lunchtimeContests,
                'starters' => $startersContests,
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to fetch CodeChef rating graph for {$handle}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Check if user profile exists
     */
    public function profileExists(string $handle): bool
    {
        try {
            $this->fetchProfile($handle);
            return true;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Get problem details via API (tags, editorial)
     */
    public function fetchProblemDetails(string $problemLink): array
    {
        try {
            // Convert problem link to API endpoint
            // https://www.codechef.com/CONTEST/problems/PROBLEMCODE
            // to https://www.codechef.com/api/contests/CONTEST/problems/PROBLEMCODE
            $apiLink = str_replace(
                'https://www.codechef.com/',
                'https://www.codechef.com/api/contests/',
                $problemLink
            );

            $response = $this->get($apiLink . '?v=' . time(), [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ]);

            if (!$response->ok()) {
                return [];
            }

            $data = $response->json();

            $tags = [];
            $tags = array_merge($tags, $data['user_tags'] ?? []);
            $tags = array_merge($tags, $data['computed_tags'] ?? []);

            $editorialLink = $data['editorial_url'] ?? null;
            if ($editorialLink === '') {
                $editorialLink = null;
            }

            $problemAuthor = $data['problem_author'] ?? null;

            return [
                'tags' => array_unique($tags),
                'editorial_link' => $editorialLink,
                'problem_author' => $problemAuthor,
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to fetch CodeChef problem details: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Get problem URL
     */
    public function getProblemUrl(string $contestCode, string $problemCode): string
    {
        return self::BASE_URL . '/' . $contestCode . '/problems/' . $problemCode;
    }

    /**
     * Get submission URL
     */
    public function getSubmissionUrl(int $submissionId): string
    {
        return self::BASE_URL . '/viewsolution/' . $submissionId;
    }
}
