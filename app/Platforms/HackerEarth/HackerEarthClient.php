<?php

namespace App\Platforms\HackerEarth;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\DomCrawler\Crawler;

class HackerEarthClient
{
    protected string $baseUrl = 'https://www.hackerearth.com';
    protected string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36';
    protected int $leaderboardPageSize = 100;

    public function profileExists(string $handle): bool
    {
        $url = "{$this->baseUrl}/@{$handle}/";

        $response = Http::withHeaders([
            'User-Agent' => $this->userAgent,
            'Accept' => 'text/html',
        ])->retry(2, 1000)->timeout(30)->get($url);

        if ($response->status() === 404) {
            return false;
        }

        if (! $response->ok()) {
            throw new \RuntimeException('HackerEarth request failed');
        }

        return str_contains($response->body(), 'HackerEarth');
    }

    public function fetchProfile(string $handle): array
    {
        if (! $this->profileExists($handle)) {
            throw new \RuntimeException('HackerEarth profile not found');
        }

        $ratingGraph = $this->fetchRatingGraph($handle);
        $profileMetrics = $this->fetchProfileMetricsWithBrowser($handle);
        $fallbackMetrics = $this->extractMetricsFromProfileHtml($handle);
        $identity = $this->extractIdentityFromProfileHtml($handle);

        $totalSolved = isset($profileMetrics['problem_solved']) && is_numeric($profileMetrics['problem_solved'])
            ? (int) $profileMetrics['problem_solved']
            : ($fallbackMetrics['problem_solved'] ?? 0);
        $contestRating = $profileMetrics['contest_rating'] ?? null;
        $rating = is_numeric($contestRating)
            ? (int) $contestRating
            : $this->extractLatestRating($ratingGraph);

        $globalRank = isset($profileMetrics['global_rank']) && is_numeric($profileMetrics['global_rank'])
            ? (int) $profileMetrics['global_rank']
            : ($fallbackMetrics['global_rank'] ?? null);
        $countryRank = isset($profileMetrics['country_rank']) && is_numeric($profileMetrics['country_rank'])
            ? (int) $profileMetrics['country_rank']
            : ($fallbackMetrics['country_rank'] ?? null);

        if ($globalRank === null) {
            $globalRank = $this->fetchGlobalRankFromLeaderboard($handle, $rating);
        }

        return [
            'handle' => $handle,
            'platform_user_id' => $identity['platform_user_id'] ?? $handle,
            'name' => $identity['name'] ?? $handle,
            'avatar_url' => $identity['avatar_url'] ?? null,
            'joined_at' => $identity['joined_at'] ?? null,
            'country' => $identity['country'] ?? null,
            'organization' => $identity['organization'] ?? null,
            'rating' => $rating,
            'ranking' => $globalRank,
            'global_rank' => $globalRank,
            'country_rank' => $countryRank,
            'total_solved' => $totalSolved,
            'rating_graph' => $ratingGraph,
            'profile_metrics' => $profileMetrics,
        ];
    }

    private function extractIdentityFromProfileHtml(string $handle): array
    {
        try {
            $url = "{$this->baseUrl}/@{$handle}/";
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html',
            ])->retry(2, 1000)->timeout(30)->get($url);

            if (! $response->ok()) {
                return [
                    'platform_user_id' => $handle,
                    'name' => $handle,
                    'avatar_url' => null,
                    'joined_at' => null,
                    'country' => null,
                    'organization' => null,
                ];
            }

            $html = $response->body();
            $crawler = new Crawler($html);

            $name = null;
            $avatarUrl = null;
            $country = null;
            $organization = null;
            $joinedAt = null;

            if ($crawler->filter('meta[property="og:title"]')->count() > 0) {
                $title = trim((string) $crawler->filter('meta[property="og:title"]')->attr('content'));
                if ($title !== '') {
                    $name = preg_replace('/\|\s*HackerEarth.*$/i', '', $title);
                    $name = trim((string) $name);
                }
            }

            if ($crawler->filter('meta[property="og:image"]')->count() > 0) {
                $avatarUrl = trim((string) $crawler->filter('meta[property="og:image"]')->attr('content'));
            }

            if (preg_match('/Country\s*[:\-]?\s*([A-Za-z][A-Za-z\s]{1,80})/i', strip_tags($html), $m)) {
                $country = trim($m[1]);
            }

            if (preg_match('/(Affiliation|Organization|Institute)\s*[:\-]?\s*([^\n\r<]{2,120})/i', strip_tags($html), $m)) {
                $organization = trim($m[2]);
            }

            if (preg_match('/(Joined|Registered)\s*[:\-]?\s*([A-Za-z0-9,\-\/\s]+)/i', strip_tags($html), $m)) {
                try {
                    $joinedAt = CarbonImmutable::parse(trim($m[2]))->toIso8601String();
                } catch (\Throwable) {
                    $joinedAt = null;
                }
            }

            if (! is_string($name) || trim($name) === '') {
                $name = $handle;
            }

            if (! is_string($avatarUrl) || trim($avatarUrl) === '') {
                $avatarUrl = null;
            }

            return [
                'platform_user_id' => $handle,
                'name' => $name,
                'avatar_url' => $avatarUrl,
                'joined_at' => $joinedAt,
                'country' => $country,
                'organization' => $organization,
            ];
        } catch (\Throwable $e) {
            Log::warning("HackerEarth identity extraction failed for {$handle}: {$e->getMessage()}");

            return [
                'platform_user_id' => $handle,
                'name' => $handle,
                'avatar_url' => null,
                'joined_at' => null,
                'country' => null,
                'organization' => null,
            ];
        }
    }

    private function fetchGlobalRankFromLeaderboard(string $handle, ?int $rating): ?int
    {
        $cacheKey = 'hackerearth_global_rank_' . strtolower($handle);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($handle, $rating) {
            $firstPage = $this->fetchLeaderboardPage(1);
            if ($firstPage === null) {
                return null;
            }

            $firstPageRank = $this->extractRankFromItems($firstPage['items'] ?? [], $handle);
            if ($firstPageRank !== null) {
                return $firstPageRank;
            }

            $total = (int) data_get($firstPage, 'meta.total', 0);
            if ($total <= 0) {
                return null;
            }

            $totalPages = (int) ceil($total / $this->leaderboardPageSize);
            if ($totalPages <= 1) {
                return null;
            }

            if ($rating !== null) {
                $candidatePage = $this->findCandidatePageByRating($rating, $totalPages);
                if ($candidatePage !== null) {
                    for ($page = max(1, $candidatePage - 3); $page <= min($totalPages, $candidatePage + 3); $page++) {
                        $data = $this->fetchLeaderboardPage($page);
                        if ($data === null) {
                            continue;
                        }

                        $rank = $this->extractRankFromItems($data['items'] ?? [], $handle);
                        if ($rank !== null) {
                            return $rank;
                        }
                    }
                }
            }

            $maxLinearPages = min($totalPages, 120);
            for ($page = 2; $page <= $maxLinearPages; $page++) {
                $data = $this->fetchLeaderboardPage($page);
                if ($data === null) {
                    continue;
                }

                $rank = $this->extractRankFromItems($data['items'] ?? [], $handle);
                if ($rank !== null) {
                    return $rank;
                }
            }

            return null;
        });
    }

    private function findCandidatePageByRating(int $rating, int $totalPages): ?int
    {
        $low = 1;
        $high = $totalPages;

        while ($low <= $high) {
            $mid = intdiv($low + $high, 2);
            $data = $this->fetchLeaderboardPage($mid);
            if ($data === null || empty($data['items'])) {
                return null;
            }

            $items = $data['items'];
            $topPoints = isset($items[0]['points']) ? (float) $items[0]['points'] : null;
            $lastItem = end($items);
            $bottomPoints = isset($lastItem['points']) ? (float) $lastItem['points'] : null;

            if ($topPoints === null || $bottomPoints === null) {
                return null;
            }

            if ($rating > $topPoints) {
                $high = $mid - 1;
                continue;
            }

            if ($rating < $bottomPoints) {
                $low = $mid + 1;
                continue;
            }

            return $mid;
        }

        return null;
    }

    private function fetchLeaderboardPage(int $page): ?array
    {
        $cacheKey = "hackerearth_leaderboard_rated_page_{$page}_size_{$this->leaderboardPageSize}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($page) {
            try {
                $url = "{$this->baseUrl}/api/leaderboard/?page={$page}&size={$this->leaderboardPageSize}&type=rated";

                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'application/json',
                    'Referer' => "{$this->baseUrl}/leaderboard/contests/rated/",
                ])->timeout(20)->get($url);

                if (! $response->ok()) {
                    return null;
                }

                $payload = $response->json();

                return is_array($payload) ? $payload : null;
            } catch (\Exception $e) {
                Log::warning("HackerEarth leaderboard page fetch failed (page {$page}): {$e->getMessage()}");
                return null;
            }
        });
    }

    private function extractRankFromItems(array $items, string $handle): ?int
    {
        $target = strtolower($handle);

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $username = strtolower((string) ($item['username'] ?? ''));
            if ($username !== $target) {
                continue;
            }

            $rank = $item['rank'] ?? null;
            if (is_numeric($rank)) {
                return (int) $rank;
            }
        }

        return null;
    }

    private function extractMetricsFromProfileHtml(string $handle): array
    {
        try {
            $url = "{$this->baseUrl}/@{$handle}/";
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html',
            ])->retry(2, 1000)->timeout(30)->get($url);

            if (! $response->ok()) {
                return [
                    'problem_solved' => 0,
                    'global_rank' => null,
                    'country_rank' => null,
                ];
            }

            $html = $response->body();
            $plainText = preg_replace('/\s+/', ' ', strip_tags($html));

            if (! is_string($plainText)) {
                return [
                    'problem_solved' => 0,
                    'global_rank' => null,
                    'country_rank' => null,
                ];
            }

            return [
                'problem_solved' => $this->extractSolvedValue($plainText),
                'global_rank' => $this->extractRankValue($plainText, 'Global\s+Rank'),
                'country_rank' => $this->extractRankValue($plainText, 'Country\s+Rank'),
            ];
        } catch (\Exception $e) {
            Log::warning("HackerEarth profile HTML metrics fallback failed for {$handle}: {$e->getMessage()}");
            return [
                'problem_solved' => 0,
                'global_rank' => null,
                'country_rank' => null,
            ];
        }
    }

    private function extractSolvedValue(string $plainText): int
    {
        $patterns = [
            '/([0-9][0-9,]*)\s+(?:Problems?|Problem)\s+Solved/i',
            '/(?:Problems?|Problem)\s+Solved\s*[:\-]?\s*([0-9][0-9,]*)/i',
            '/Solved\s+Problems?\s*[:\-]?\s*([0-9][0-9,]*)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $plainText, $matches)) {
                $value = (int) str_replace(',', '', $matches[1]);
                if ($value >= 0) {
                    return $value;
                }
            }
        }

        return 0;
    }

    private function extractRankValue(string $plainText, string $labelPattern): ?int
    {
        $patterns = [
            '/([0-9][0-9,]*)\s+' . $labelPattern . '/i',
            '/' . $labelPattern . '\s*[:\-]?\s*([0-9][0-9,]*)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $plainText, $matches)) {
                $rank = (int) str_replace(',', '', $matches[1]);
                if ($rank > 0) {
                    return $rank;
                }
            }
        }

        return null;
    }

    protected function fetchProfileMetricsWithBrowser(string $handle): array
    {
        $enabled = (bool) config('platforms.hackerearth.playwright.enabled', true);
        if (! $enabled) {
            return [];
        }

        $scriptPath = base_path('public/web/js/hackerearth-profile-metrics.mjs');
        if (! file_exists($scriptPath)) {
            Log::warning("HackerEarth Playwright script not found at {$scriptPath}");
            return [];
        }

        $timeoutMs = (int) config('platforms.hackerearth.playwright.timeout_ms', 25000);
        $browser = (string) config('platforms.hackerearth.playwright.browser', 'chromium');

        try {
            $process = new Process([
                'node',
                $scriptPath,
                $handle,
                (string) $timeoutMs,
                $browser,
            ], base_path());

            $process->setTimeout(max(30, (int) ceil($timeoutMs / 1000) + 15));
            $process->run();

            if (! $process->isSuccessful()) {
                $stderr = trim($process->getErrorOutput());
                $stdout = trim($process->getOutput());
                $details = $stderr !== '' ? $stderr : ($stdout !== '' ? $stdout : 'No process output');
                Log::warning("HackerEarth Playwright process failed for {$handle}: {$details}");
                return [];
            }

            $rawOutput = trim($process->getOutput());
            if ($rawOutput === '') {
                return [];
            }

            $payload = json_decode($rawOutput, true);
            if (! is_array($payload)) {
                Log::warning("HackerEarth Playwright output is not valid JSON for {$handle}");
                return [];
            }

            if (($payload['ok'] ?? false) !== true) {
                Log::warning("HackerEarth Playwright returned not-ok for {$handle}: " . ($payload['error'] ?? 'unknown error'));
                return [];
            }

            $metrics = $payload['metrics'] ?? [];

            return is_array($metrics) ? $metrics : [];
        } catch (\Throwable $e) {
            Log::warning("HackerEarth Playwright fetch failed for {$handle}: {$e->getMessage()}");
            return [];
        }
    }

    public function fetchRatingGraph(string $handle): array
    {
        try {
            $url = "{$this->baseUrl}/ratings/AJAX/rating-graph/{$handle}/";

            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html',
                'Referer' => "{$this->baseUrl}/@{$handle}/",
            ])->timeout(20)->retry(2, 500)->get($url);

            if (! $response->ok()) {
                return [];
            }

            $body = trim($response->body());
            if ($body === '') {
                return [];
            }

            if (! preg_match('/var dataset = (\[.*?\]);/s', $body, $matches)) {
                return [];
            }

            $data = json_decode($matches[1], true);
            if (! is_array($data)) {
                return [];
            }

            return $data;
        } catch (\Throwable $e) {
            Log::warning("HackerEarth rating graph fetch failed for {$handle}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch submissions from HackerEarth
     *
     * ⚠️ KNOWN ISSUE: This endpoint frequently times out
     * Possible solutions to try in the future:
     * 1. Use a proxy service
     * 2. Implement exponential backoff
     * 3. Parse the main submissions page HTML directly (not AJAX)
     * 4. Use browser automation (Selenium/Puppeteer)
     * 5. Contact HackerEarth for official API access
     *
     * Current status: Returns empty array on timeout
     */
    public function fetchSubmissions(string $handle, int $maxPages = 10): array
    {
        // Try with retry and longer timeout
        $initialUrl = "{$this->baseUrl}/submissions/{$handle}/";

        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ])
                ->timeout(30) // Increased timeout
                ->retry(2, 1000) // Retry 2 times with 1 second delay
                ->get($initialUrl);

            if ($response->status() === 404) {
                throw new \RuntimeException('HackerEarth profile not found');
            }

            if (! $response->ok()) {
                throw new \RuntimeException('HackerEarth submissions request failed');
            }

            $headers = $this->buildAjaxHeaders($response, $initialUrl);
        } catch (\Exception $e) {
            // ⚠️ Timeout or connection error - log for future debugging
            \Log::warning("HackerEarth initial page failed for {$handle}: " . $e->getMessage());
            throw new \RuntimeException('HackerEarth submissions page unavailable: ' . $e->getMessage());
        }

        $submissions = [];
        $consecutiveFailures = 0;

        for ($page = 1; $page <= $maxPages; $page++) {
            $url = "{$this->baseUrl}/AJAX/feed/newsfeed/submission/user/{$handle}/?page={$page}";

            try {
                $pageResponse = Http::withHeaders($headers)
                    ->timeout(30) // Increased timeout
                    ->retry(2, 1000) // Retry with delay
                    ->get($url);

                if (! $pageResponse->ok()) {
                    $consecutiveFailures++;
                    if ($consecutiveFailures >= 3) {
                        \Log::warning("HackerEarth: Too many failures for {$handle}, stopping at page {$page}");
                        break;
                    }
                    continue;
                }

                // Reset failure counter on success
                $consecutiveFailures = 0;

                $payload = $pageResponse->json();
                if (! is_array($payload)) {
                    break;
                }

                if (($payload['status'] ?? '') === 'ERROR') {
                    break;
                }

                $html = (string) ($payload['data'] ?? '');
                if (trim($html) === '') {
                    break;
                }

                $crawler = new Crawler($html);
                $rows = $crawler->filter('tbody tr');

                if ($rows->count() === 0) {
                    break;
                }

                $rows->each(function (Crawler $row) use (&$submissions) {
                    try {
                        $problemAnchor = $this->findProblemAnchor($row);
                        if (! $problemAnchor) {
                            return;
                        }

                        $href = $problemAnchor->getAttribute('href');
                        if (! $href) {
                            return;
                        }

                        $problemLink = str_starts_with($href, 'http')
                            ? $href
                            : "{$this->baseUrl}{$href}";

                        $problemName = trim($problemAnchor->textContent ?? '');

                        $statusText = $this->extractStatusText($row);
                        $verdict = $this->normalizeStatus($statusText);

                        $timestamp = $this->extractTimestamp($row);
                        if (! $timestamp) {
                            return;
                        }

                        $submittedAt = CarbonImmutable::createFromFormat(
                            'Y-m-d H:i:sP',
                            $timestamp,
                            'UTC'
                        );

                        if (! $submittedAt) {
                            return;
                        }

                        $submissionId = $this->extractSubmissionId($row);
                        $submissionUrl = $submissionId
                            ? "{$this->baseUrl}/submission/{$submissionId}"
                            : null;

                        $submissions[] = [
                            'problem_id' => $problemLink,
                            'problem_name' => $problemName,
                            'verdict' => $verdict,
                            'submitted_at' => $submittedAt,
                            'submission_url' => $submissionUrl,
                        ];
                    } catch (\Exception $e) {
                        // Skip problematic rows
                        \Log::debug("HackerEarth: Failed to parse row: " . $e->getMessage());
                    }
                });

                // Add small delay between pages to avoid rate limiting
                usleep(500000); // 0.5 second delay

            } catch (\Exception $e) {
                $consecutiveFailures++;
                \Log::warning("HackerEarth page {$page} failed for {$handle}: " . $e->getMessage());

                if ($consecutiveFailures >= 3) {
                    break;
                }

                // Continue to next page
                continue;
            }
        }

        return $submissions;
    }

    protected function buildAjaxHeaders(Response $response, string $referer): array
    {
        $setCookies = $response->headers()['Set-Cookie'] ?? [];
        $cookieHeader = $this->buildCookieHeader($setCookies);
        $csrfToken = $this->extractCsrfToken($setCookies);

        $headers = [
            'User-Agent' => $this->userAgent,
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate',
            'X-Requested-With' => 'XMLHttpRequest',
            'Connection' => 'keep-alive',
            'Referer' => $referer,
        ];

        if ($cookieHeader !== '') {
            $headers['Cookie'] = $cookieHeader;
        }

        if ($csrfToken) {
            $headers['X-CSRFToken'] = $csrfToken;
        }

        return $headers;
    }

    protected function extractCsrfToken(array $setCookies): ?string
    {
        $cookieString = implode('; ', $setCookies);

        if (preg_match('/csrfToken=([^;]+)/', $cookieString, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function buildCookieHeader(array $setCookies): string
    {
        return collect($setCookies)
            ->map(fn($cookie) => explode(';', $cookie)[0])
            ->filter()
            ->implode('; ');
    }

    protected function extractLatestRating(array $ratingGraph): ?int
    {
        if (! $ratingGraph) {
            return null;
        }

        $last = end($ratingGraph);
        if (! is_array($last)) {
            return null;
        }

        $rating = $last['rating'] ?? null;

        return is_numeric($rating) ? (int) $rating : null;
    }

    protected function extractTimestamp(Crawler $row): ?string
    {
        $html = $row->html() ?? '';

        if (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\+00:00/', $html, $matches)) {
            return $matches[0];
        }

        return null;
    }

    protected function extractStatusText(Crawler $row): string
    {
        if ($row->filter('td i[title]')->count()) {
            return (string) $row->filter('td i[title]')->first()->attr('title');
        }

        if ($row->filter('td span[title]')->count()) {
            return (string) $row->filter('td span[title]')->first()->attr('title');
        }

        if ($row->filter('td[title]')->count()) {
            return (string) $row->filter('td[title]')->first()->attr('title');
        }

        if ($row->filter('td')->count() >= 3) {
            return trim($row->filter('td')->eq(2)->text());
        }

        return '';
    }

    protected function normalizeStatus(string $statusText): string
    {
        $statusText = strtolower($statusText);

        if (str_contains($statusText, 'accepted')) {
            return 'AC';
        }

        if (str_contains($statusText, 'partial')) {
            return 'PS';
        }

        if (str_contains($statusText, 'wrong')) {
            return 'WA';
        }

        if (str_contains($statusText, 'compilation')) {
            return 'CE';
        }

        if (str_contains($statusText, 'runtime')) {
            return 'RE';
        }

        if (str_contains($statusText, 'memory')) {
            return 'MLE';
        }

        if (str_contains($statusText, 'time')) {
            return 'TLE';
        }

        return 'OTH';
    }

    protected function extractSubmissionId(Crawler $row): ?string
    {
        $id = $row->attr('id');

        if (! $id) {
            return null;
        }

        if (preg_match('/(\d+)/', $id, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function findProblemAnchor(Crawler $row): ?\DOMElement
    {
        foreach ($row->filter('a') as $anchor) {
            $href = $anchor->getAttribute('href');

            if (! $href) {
                continue;
            }

            if ($this->looksLikeProblemLink($href)) {
                return $anchor;
            }
        }

        return $row->filter('a')->count()
            ? $row->filter('a')->first()->getNode(0)
            : null;
    }

    protected function looksLikeProblemLink(string $href): bool
    {
        return str_contains($href, '/practice/')
            || str_contains($href, '/problem/')
            || str_contains($href, '/challenge/')
            || str_contains($href, '/challenges/');
    }
}
