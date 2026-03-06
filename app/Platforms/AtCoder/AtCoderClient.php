<?php

namespace App\Platforms\AtCoder;

use App\Support\Http\BaseHttpClient;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\CarbonImmutable;
use HeadlessChromium\BrowserFactory;

class AtCoderClient extends BaseHttpClient
{
    private const BASE_URL = 'https://atcoder.jp';
    private const DEFAULT_API_URL = 'https://kenkoooo.com/atcoder/atcoder-api';

    /**
     * Fetch user profile from AtCoder
     */
    public function fetchProfile(string $handle): array
    {
        $url = self::BASE_URL . '/users/' . urlencode($handle);

        $response = $this->get($url, [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
        ]);

        if (!$response->ok()) {
            throw new \RuntimeException('AtCoder user not found');
        }

        $crawler = new Crawler($response->body());

        // Verify handle matches
        $usernameElement = $crawler->filter('.username');
        if ($usernameElement->count() === 0 || $usernameElement->text() !== $handle) {
            throw new \RuntimeException('AtCoder user not found or handle mismatch');
        }

        $rating = null;
        $highestRating = null;
        $rank = null;
        $solved = 0;
        $rated = 0;
        $country = null;
        $affiliation = null;
        $joinedAt = null;
        $avatarUrl = $this->extractAvatarUrl($crawler);

        $crawler->filter('table.dl-table tr')->each(function ($row) use (&$rating, &$highestRating, &$rank, &$solved, &$rated) {
            $label = trim($row->filter('th')->text(''));
            $value = trim($row->filter('td')->text(''));

            if ($label === 'Rating') {
                $rating = is_numeric($value) ? (int) $value : null;
            } elseif ($label === 'Highest Rating') {
                $highestRating = is_numeric($value) ? (int) $value : null;
            } elseif ($label === 'Rank') {
                $rank = $value;
            } elseif ($label === 'Accepted Count') {
                // Extract number before " / " if present
                if (preg_match('/^(\d+)/', $value, $matches)) {
                    $solved = (int) $matches[1];
                }
            } elseif ($label === 'Rated Matches Count') {
                $rated = is_numeric($value) ? (int) $value : 0;
            }
        });

        $crawler->filter('table.dl-table tr')->each(function ($row) use (&$country, &$affiliation, &$joinedAt) {
            $label = trim($row->filter('th')->text(''));
            $value = trim($row->filter('td')->text(''));
            $normalizedLabel = strtolower(rtrim($label, ':'));

            if (in_array($normalizedLabel, ['country/region', 'country', 'region'], true)) {
                $country = $value !== '' ? $value : null;
            } elseif ($normalizedLabel === 'affiliation') {
                $affiliation = $value !== '' ? $value : null;
            } elseif (in_array($normalizedLabel, ['registered', 'registration date'], true)) {
                try {
                    $joinedAt = CarbonImmutable::parse($value)->toIso8601String();
                } catch (\Throwable) {
                    $joinedAt = null;
                }
            }
        });

        if ($solved === 0) {
            $fallbackSolved = $this->fetchAcceptedCount($handle);
            if ($fallbackSolved !== null) {
                $solved = $fallbackSolved;
            }
        }

        return [
            'handle' => $handle,
            'rating' => $rating,
            'highest_rating' => $highestRating,
            'rank' => $rank,
            'total_solved' => $solved,
            'rated_matches' => $rated,
            'country' => $country,
            'affiliation' => $affiliation,
            'joined_at' => $joinedAt,
            'avatar_url' => $avatarUrl,
        ];
    }

    private function extractAvatarUrl(Crawler $crawler): ?string
    {
        $avatar = null;

        if ($crawler->filter('img[src*="img.atcoder.jp/icons/"]')->count() > 0) {
            $avatar = $crawler->filter('img[src*="img.atcoder.jp/icons/"]')->first()->attr('src');
        } elseif ($crawler->filter('.avatar img')->count() > 0) {
            $avatar = $crawler->filter('.avatar img')->first()->attr('src');
        }

        if (! is_string($avatar) || trim($avatar) === '') {
            return null;
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        if (str_starts_with($avatar, '//')) {
            return 'https:' . $avatar;
        }

        return rtrim(self::BASE_URL, '/') . '/' . ltrim($avatar, '/');
    }

    /**
     * Fetch contest history and rating graph data
     */
    public function fetchContestHistory(string $handle): array
    {
        try {
            $url = self::BASE_URL . '/users/' . urlencode($handle) . '/history';

            $response = $this->get($url, [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ]);

            if (!$response->ok()) {
                Log::warning("Failed to fetch AtCoder contest history for {$handle}: " . $response->status());
                return [];
            }

            $crawler = new Crawler($response->body());
            $historyTable = $crawler->filter('table#history tbody');

            if ($historyTable->count() === 0) {
                return [];
            }

            $contests = [];
            $historyTable->filter('tr')->each(function (Crawler $row) use (&$contests) {
                try {
                    $cells = $row->filter('td');
                    if ($cells->count() < 6) {
                        return;
                    }

                    $timeText = $cells->eq(0)->text();
                    // Parse datetime and adjust for timezone (JST +9:00)
                    $timestamp = CarbonImmutable::parse($timeText, 'Asia/Tokyo')->setTimezone('UTC');

                    $contestLink = $cells->eq(1)->filter('a');
                    $contestName = $cells->eq(1)->text();
                    $contestUrl = null;
                    if ($contestLink->count() > 0) {
                        $href = $contestLink->attr('href');
                        $contestUrl = self::BASE_URL . substr($href, 1); // Remove leading /
                    }

                    $rank = $cells->eq(2)->text();
                    $performance = $cells->eq(3)->text();
                    $newRating = (int) $cells->eq(4)->text();

                    $ratingChangeText = $cells->eq(5)->text();
                    $ratingChange = 0;
                    if ($ratingChangeText !== '-' && is_numeric($ratingChangeText)) {
                        $ratingChange = (int) $ratingChangeText;
                    }

                    $contests[] = [
                        'timestamp' => $timestamp->toDateTimeString(),
                        'contest_name' => $contestName,
                        'contest_url' => $contestUrl,
                        'rank' => $rank,
                        'performance' => $performance,
                        'new_rating' => $newRating,
                        'rating_change' => $ratingChange,
                    ];
                } catch (\Exception $e) {
                    // Skip problematic rows
                }
            });

            return $contests;
        } catch (\Exception $e) {
            Log::warning("Failed to fetch AtCoder contest history for {$handle}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Not used for AtCoder sync.
     * Total solved comes only from v3/user/ac_rank and v2/user_info.
     */
    public function fetchSubmissions(string $handle): array
    {
        return [];
    }

    /**
     * Not used for AtCoder sync.
     */
    public function fetchProblemMapping(): array
    {
        return [];
    }

    /**
     * Fetch accepted count from Kenkoooo API.
     * Primary: v3/user/ac_rank, Fallback: v2/user_info.
     */
    public function fetchAcceptedCount(string $handle): ?int
    {
        try {
            $v3Url = $this->atcoderApiUrl() . '/v3/user/ac_rank?user=' . urlencode($handle);
            $payload = $this->fetchKenkooooJson($v3Url);
            if (is_array($payload)) {
                $count = $payload['count'] ?? null;
                if (is_numeric($count)) {
                    return (int) $count;
                }
            }
        } catch (\Exception $e) {
            Log::warning("AtCoder ac_rank lookup failed for {$handle}: {$e->getMessage()}");
        }

        try {
            $v3InfoUrl = $this->atcoderApiUrl() . '/v3/user_info?user=' . urlencode($handle);
            $payload = $this->fetchKenkooooJson($v3InfoUrl);
            if (is_array($payload)) {
                $count = $payload['accepted_count'] ?? null;
                if (is_numeric($count)) {
                    return (int) $count;
                }
            }
        } catch (\Exception $e) {
            Log::warning("AtCoder v3 user_info fallback failed for {$handle}: {$e->getMessage()}");
        }

        try {
            $v2Url = $this->atcoderApiUrl() . '/v2/user_info?user=' . urlencode($handle);
            $payload = $this->fetchKenkooooJson($v2Url);
            if (is_array($payload)) {
                $count = $payload['accepted_count'] ?? null;
                if (is_numeric($count)) {
                    return (int) $count;
                }
            }
        } catch (\Exception $e) {
            Log::warning("AtCoder v2 user_info fallback failed for {$handle}: {$e->getMessage()}");
        }

        return null;
    }

    private function kenkooooJsonHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'application/json',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Origin' => 'https://kenkoooo.com',
            'Referer' => 'https://kenkoooo.com/atcoder/',
        ];
    }

    private function fetchKenkooooJson(string $url): ?array
    {
        try {
            $response = $this->get($url, $this->kenkooooJsonHeaders());
            $json = $response->json();
            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            if (!str_contains($e->getMessage(), '403')) {
                throw $e;
            }

            return $this->fetchKenkooooJsonViaBrowser($url);
        }
    }

    private function fetchKenkooooJsonViaBrowser(string $url): ?array
    {
        try {
            $factory = new BrowserFactory('/usr/bin/google-chrome');
            $browser = $factory->createBrowser();
            $page = $browser->createPage();

            try {
                $page->navigate($url)->waitForNavigation();
                usleep(500000);
                $html = $page->getHtml();
            } finally {
                $browser->close();
            }

            if (preg_match('/<pre[^>]*>(.*?)<\/pre>/is', $html, $matches)) {
                $payload = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $decoded = json_decode($payload, true);
                return is_array($decoded) ? $decoded : null;
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning("AtCoder browser fallback failed for URL {$url}: {$e->getMessage()}");
            return null;
        }
    }

    private function atcoderApiUrl(): string
    {
        return rtrim((string) config('platforms.atcoder.api_url', self::DEFAULT_API_URL), '/');
    }

    /**
     * Fetch contests from Kenkoooo resources API.
     */
    public function fetchResourceContests(): array
    {
        $url = 'https://kenkoooo.com/atcoder/resources/contests.json';
        $json = $this->fetchKenkooooJson($url);

        return is_array($json) ? $json : [];
    }

    /**
     * Fetch problems from Kenkoooo resources API.
     */
    public function fetchResourceProblems(): array
    {
        $url = 'https://kenkoooo.com/atcoder/resources/problems.json';
        $json = $this->fetchKenkooooJson($url);

        return is_array($json) ? $json : [];
    }

    /**
     * Fetch merged problem details from Kenkoooo resources API.
     */
    public function fetchResourceMergedProblems(): array
    {
        $url = 'https://kenkoooo.com/atcoder/resources/merged-problems.json';
        $json = $this->fetchKenkooooJson($url);

        return is_array($json) ? $json : [];
    }

    /**
     * Fetch contest-problem pairs from Kenkoooo resources API.
     */
    public function fetchResourceContestProblemPairs(): array
    {
        $url = 'https://kenkoooo.com/atcoder/resources/contest-problem.json';
        $json = $this->fetchKenkooooJson($url);

        return is_array($json) ? $json : [];
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
     * Get editorial link for a problem
     */
    public function getEditorialLink(string $problemLink): ?string
    {
        try {
            if (preg_match('/contests\/(.*)\/tasks/', $problemLink, $matches)) {
                $contestId = $matches[1];
                return "https://img.atcoder.jp/{$contestId}/editorial.pdf";
            }
        } catch (\Exception $e) {
            // Ignore
        }
        return null;
    }

    /**
     * Normalize AtCoder verdict
     */
    public function normalizeVerdict(string $verdict): string
    {
        return match ($verdict) {
            'AC' => 'AC',
            'WA' => 'WA',
            'TLE' => 'TLE',
            'MLE' => 'MLE',
            'CE' => 'CE',
            'RE' => 'RE',
            default => 'OTH',
        };
    }

    /**
     * Get problem URL
     */
    public function getProblemUrl(string $contestId, string $problemId): string
    {
        return self::BASE_URL . '/contests/' . $contestId . '/tasks/' . $problemId;
    }

    /**
     * Get submission URL
     */
    public function getSubmissionUrl(string $contestId, int $submissionId): string
    {
        return self::BASE_URL . '/contests/' . $contestId . '/submissions/' . $submissionId;
    }

    /**
     * Fetch contests from AtCoder archive pages.
     */
    public function fetchContestArchive(int $maxPages = 2): array
    {
        $all = [];

        for ($page = 1; $page <= $maxPages; $page++) {
            try {
                $url = self::BASE_URL . '/contests/archive?page=' . $page . '&lang=en';
                $response = $this->get($url, [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ]);

                if (! $response->ok()) {
                    break;
                }

                $crawler = new Crawler($response->body());
                $rows = $crawler->filter('table.table tbody tr');

                if ($rows->count() === 0) {
                    break;
                }

                $countBefore = count($all);

                $rows->each(function (Crawler $row) use (&$all) {
                    try {
                        $cells = $row->filter('td');
                        if ($cells->count() < 2) {
                            return;
                        }

                        $timeText = trim($cells->eq(0)->text(''));
                        $link = $cells->eq(1)->filter('a')->first();
                        if ($link->count() === 0) {
                            return;
                        }

                        $href = (string) $link->attr('href');
                        if (! preg_match('#/contests/([A-Za-z0-9_-]+)$#', $href, $matches)) {
                            return;
                        }

                        $contestId = $matches[1];
                        $name = trim($link->text(''));
                        $durationText = $cells->count() > 2 ? trim($cells->eq(2)->text('')) : '';
                        $durationSeconds = $this->parseDurationToSeconds($durationText);

                        $startTime = null;
                        if ($timeText !== '') {
                            try {
                                $startTime = CarbonImmutable::parse($timeText, 'Asia/Tokyo')->setTimezone('UTC');
                            } catch (\Throwable) {
                                $startTime = null;
                            }
                        }

                        $endTime = ($startTime && $durationSeconds !== null)
                            ? $startTime->addSeconds($durationSeconds)
                            : null;

                        $typeLabel = null;
                        if ($cells->eq(1)->filter('span[title]')->count() > 0) {
                            $typeLabel = strtolower((string) $cells->eq(1)->filter('span[title]')->first()->attr('title'));
                        }

                        $all[] = [
                            'contest_id' => $contestId,
                            'name' => $name,
                            'start_time' => $startTime?->toDateTimeString(),
                            'end_time' => $endTime?->toDateTimeString(),
                            'duration_seconds' => $durationSeconds,
                            'type' => $typeLabel ?: 'contest',
                            'url' => self::BASE_URL . '/contests/' . $contestId,
                        ];
                    } catch (\Throwable) {
                        // Skip malformed row.
                    }
                });

                if (count($all) === $countBefore) {
                    break;
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to fetch AtCoder contest archive page {$page}: {$e->getMessage()}");
                break;
            }
        }

        return $all;
    }

    /**
     * Fetch tasks (problems) from a contest tasks page.
     */
    public function fetchContestTasks(string $contestId): array
    {
        try {
            $url = self::BASE_URL . '/contests/' . $contestId . '/tasks?lang=en';
            $response = $this->get($url, [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ]);

            if (! $response->ok()) {
                return [];
            }

            $crawler = new Crawler($response->body());
            $rows = $crawler->filter('table.table tbody tr');
            if ($rows->count() === 0) {
                return [];
            }

            $tasks = [];
            $rows->each(function (Crawler $row) use ($contestId, &$tasks) {
                try {
                    $cells = $row->filter('td');
                    if ($cells->count() < 2) {
                        return;
                    }

                    $code = trim($cells->eq(0)->text(''));
                    $problemLink = $cells->eq(1)->filter('a')->first();
                    if ($problemLink->count() === 0) {
                        return;
                    }

                    $href = (string) $problemLink->attr('href');
                    if (! preg_match('#/contests/' . preg_quote($contestId, '#') . '/tasks/([A-Za-z0-9_-]+)$#', $href, $matches)) {
                        return;
                    }

                    $taskId = $matches[1];
                    $name = trim($problemLink->text(''));
                    $problemUrl = self::BASE_URL . '/contests/' . $contestId . '/tasks/' . $taskId;

                    $tasks[] = [
                        'task_id' => $taskId,
                        'name' => $name,
                        'code' => $code !== '' ? $code : null,
                        'url' => $problemUrl,
                        'editorial_url' => self::BASE_URL . '/contests/' . $contestId . '/editorial',
                    ];
                } catch (\Throwable) {
                    // Skip malformed task row.
                }
            });

            return $tasks;
        } catch (\Throwable $e) {
            Log::warning("Failed to fetch AtCoder tasks for {$contestId}: {$e->getMessage()}");
            return [];
        }
    }

    private function parseDurationToSeconds(string $durationText): ?int
    {
        if ($durationText === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $durationText, $matches)) {
            return ((int) $matches[1] * 3600) + ((int) $matches[2] * 60);
        }

        return null;
    }
}
