<?php

namespace App\Platforms\AtCoder\Services;

use App\Services\ApplicationLogger;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AtCoderHtmlScraper
{
    private const MIN_DELAY_MS = 1200;
    private const MAX_DELAY_MS = 2500;
    private static int $lastRequestTime = 0;

    private function baseUrl(): string
    {
        return rtrim((string) config('platforms.atcoder.base_url', 'https://atcoder.jp'), '/');
    }

    public function getSubmissions(
        string $contestId,
        ?string $user = null,
        ?string $stopSubmissionId = null,
    ): array {
        $submissions = [];
        $page = 1;
        $reachedStop = false;

        while (true) {
            $url = $user !== null && $user !== ''
                ? $this->baseUrl() . '/contests/' . $contestId . '/submissions?f.User=' . urlencode($user) . '&page=' . $page
                : $this->baseUrl() . '/contests/' . $contestId . '/submissions?page=' . $page;

            $html = $this->fetchPage($url);

            if ($html === '') {
                break;
            }

            $doc = new DOMDocument();
            @$doc->loadHTML($html);

            $xpath = new DOMXPath($doc);
            $rows = $xpath->query('//table//tbody//tr');

            if ($rows->length === 0) {
                break;
            }

            foreach ($rows as $row) {
                $cells = $xpath->query('.//td', $row);

                if ($cells->length < 2) {
                    continue;
                }

                $time = trim($cells->item(0)?->textContent ?? '');

                $taskLink = $xpath->query('.//a', $cells->item(1))->item(0);
                $taskId = null;
                $taskTitle = null;
                $taskUrl = null;

                if ($taskLink instanceof DOMElement) {
                    $taskHref = $taskLink->getAttribute('href');
                    $taskId = basename($taskHref);
                    $taskTitle = trim($taskLink->textContent);
                    $taskUrl = $this->baseUrl() . $taskHref;
                } else {
                    $taskTitle = trim($cells->item(1)?->textContent ?? '');
                }

                $userLink = $xpath->query('.//a', $cells->item(2))->item(0);
                $username = $userLink instanceof DOMElement
                    ? trim(basename($userLink->getAttribute('href')))
                    : trim($cells->item(2)?->textContent ?? '');

                $langLink = $xpath->query('.//a', $cells->item(3))->item(0);
                $language = $langLink instanceof DOMElement
                    ? trim($langLink->textContent)
                    : trim($cells->item(3)?->textContent ?? '');

                $scoreTd = $cells->item(4);
                $score = null;
                $submissionId = null;

                if ($scoreTd instanceof DOMElement) {
                    $scoreText = trim($scoreTd->textContent ?? '');
                    if (is_numeric($scoreText)) {
                        $score = (int) $scoreText;
                    } elseif ($scoreText !== '') {
                        $score = $scoreText;
                    }

                    $dataId = $scoreTd->getAttribute('data-id');
                    if ($dataId !== '') {
                        $submissionId = $dataId;
                    }
                }

                $codeSize = trim($cells->item(5)?->textContent ?? '');
                $statusSpan = $xpath->query('.//span', $cells->item(6))->item(0);
                $status = $statusSpan instanceof DOMElement
                    ? trim($statusSpan->textContent)
                    : trim($cells->item(6)?->textContent ?? '');

                $execTime = trim($cells->item(7)?->textContent ?? '');
                $memory = trim($cells->item(8)?->textContent ?? '');
                $detailUrl = null;

                if ($submissionId !== null) {
                    $detailUrl = $this->baseUrl() . '/contests/' . $contestId . '/submissions/' . $submissionId;
                } else {
                    $detailLink = $xpath->query('.//a[contains(@class,"submission-details-link")]', $cells->item(9))->item(0);
                    if ($detailLink instanceof DOMElement) {
                        $detailHref = $detailLink->getAttribute('href');
                        $detailUrl = $this->baseUrl() . $detailHref;
                    }

                    if ($submissionId === null && $detailUrl !== null) {
                        $submissionId = basename($detailUrl);
                    }
                }

                if ($stopSubmissionId !== null && $submissionId === $stopSubmissionId) {
                    $reachedStop = true;
                    break;
                }

                $submissions[] = [
                    'id' => $submissionId,
                    'contest_id' => $contestId,
                    'problem_id' => $taskId,
                    'problem_title' => $taskTitle,
                    'problem_url' => $taskUrl,
                    'user' => $username,
                    'language' => $language,
                    'verdict' => $status,
                    'score' => $score,
                    'time' => $time,
                    'code_size' => $codeSize,
                    'exec_time' => $execTime,
                    'memory' => $memory,
                    'url' => $detailUrl,
                ];
            }

            if ($reachedStop) {
                break;
            }

            $page++;
        }

        return [
            'result' => $submissions,
            'reached_stop' => $reachedStop,
        ];
    }

    public function getTasks(string $contestId): array
    {
        $html = $this->fetchPage($this->baseUrl() . '/contests/' . $contestId . '/tasks?lang=en');
        if (empty($html)) {
            return ['result' => []];
        }

        $doc = new DOMDocument;
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);

        $tasks = [];
        $rows = $xpath->query('//table[contains(@class, "table")]//tbody//tr | //table//tbody//tr | //table//tr');

        foreach ($rows as $row) {
            $cells = $xpath->query('.//td', $row);
            if ($cells->length < 2) {
                continue;
            }

            $position = trim($cells->item(0)?->nodeValue ?? '');
            $link = $xpath->query('.//a[contains(@href, "/tasks/")]', $cells->item(1))->item(0)
                ?? $xpath->query('.//a', $cells->item(0))->item(0);

            if (!$link instanceof DOMElement) {
                continue;
            }

            $href = $link->getAttribute('href');
            $taskId = basename($href);

            if (empty($taskId) || str_contains($href, '/tasks/archive')) {
                continue;
            }

            $title = trim($link->nodeValue);
            $timeLimit = $cells->length > 2 ? trim($cells->item(2)?->nodeValue ?? '') : '';
            $memoryLimit = $cells->length > 3 ? trim($cells->item(3)?->nodeValue ?? '') : '';
            $taskUrl = $this->baseUrl() . $href;
            $score = $this->getTaskScore($contestId, $taskId);

            $tasks[] = [
                'id' => $taskId,
                'contest_id' => $contestId,
                'title' => $title,
                'position' => $position,
                'score' => $score,
                'time_limit' => $timeLimit,
                'memory_limit' => $memoryLimit,
                'url' => $taskUrl,
            ];
        }

        return ['result' => $tasks];
    }

    public function getTaskScore(string $contestId, string $taskId): ?float
    {
        try {
            $html = $this->fetchPage($this->baseUrl() . '/contests/' . $contestId . '/tasks/' . $taskId . '?lang=en');
            if ($html === '') {
                return null;
            }

            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            $nodes = $xpath->query('//p[contains(text(), "Score") or contains(text(), "配点") or contains(., "Score")]');
            foreach ($nodes as $node) {
                $text = trim($node->textContent);
                if (preg_match('/(?:Score|配点)\s*[:：]?\s*(\d+)/i', $text, $matches)) {
                    return (float) $matches[1];
                }
                $varNode = $xpath->query('.//var', $node)->item(0);
                if ($varNode) {
                    $varText = trim($varNode->textContent);
                    if (is_numeric($varText)) {
                        return (float) $varText;
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    public function getUserProfile(string $username): array
    {
        $types = ['algo', 'heuristic'];
        $result = [];

        foreach ($types as $index => $type) {
            $html = $this->fetchPage($this->baseUrl() . '/users/' . $username . '?contestType=' . $type);

            $doc = new DOMDocument;
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            if ($index === 0) {
                $rawCountry = null;
                $rawAffiliation = null;
                $rawBirthYear = null;
                $rawTwitter = null;

                $profileRows = $xpath->query("//table[contains(@class, 'table-bordered')]//tr");
                foreach ($profileRows as $row) {
                    $th = $xpath->query('.//th', $row)->item(0);
                    $td = $xpath->query('.//td', $row)->item(0);

                    if ($th && $td) {
                        $label = trim($th->nodeValue);
                        $value = trim($td->nodeValue);

                        if (str_contains($label, 'Country/Region')) {
                            $rawCountry = $value;
                        } elseif (str_contains($label, 'Affiliation')) {
                            $rawAffiliation = $value;
                        } elseif (str_contains($label, 'Birth Year')) {
                            $rawBirthYear = is_numeric($value) ? (int) $value : null;
                        } elseif (str_contains($label, 'Twitter')) {
                            $rawTwitter = ltrim($value, '@');
                        }
                    }
                }

                $avatarNode = $xpath->query("//img[contains(@class, 'avatar')]")->item(0);
                $rawAvatar = $avatarNode instanceof DOMElement ? $avatarNode->getAttribute('src') : null;

                $result['profile'] = [
                    'username' => $username,
                    'country' => $rawCountry,
                    'affiliation' => $rawAffiliation,
                    'birth_year' => $rawBirthYear,
                    'twitter_id' => $rawTwitter,
                    'avatar_url' => $rawAvatar,
                ];
            }

            $rawRank = null;
            $rawRating = null;
            $rawHighest = null;
            $rawRatedMatches = null;
            $rawLastCompeted = null;

            $rows = $xpath->query("//table[contains(@class, 'dl-table')]//tr");

            foreach ($rows as $row) {
                $th = $xpath->query('.//th', $row)->item(0);
                $td = $xpath->query('.//td', $row)->item(0);

                if ($th && $td) {
                    $label = trim($th->nodeValue);
                    $value = trim($td->nodeValue);

                    if (str_contains($label, 'Rank')) {
                        $rawRank = $value;
                    } elseif (str_contains($label, 'Rating')) {
                        $rawRating = $value;
                    } elseif (str_contains($label, 'Highest Rating')) {
                        $rawHighest = $value;
                    } elseif (str_contains($label, 'Rated Matches')) {
                        $rawRatedMatches = $value;
                    } elseif (str_contains($label, 'Last Competed')) {
                        $rawLastCompeted = $value;
                    }
                }
            }

            $parsedRank = null;
            if ($rawRank !== null && preg_match('/(\d+)/', str_replace(',', '', $rawRank), $matches)) {
                $parsedRank = (int) $matches[1];
            }

            $parsedRating = null;
            $isProvisional = false;
            if ($rawRating !== null && preg_match('/(\d+)/', $rawRating, $matches)) {
                $parsedRating = (int) $matches[1];
                if (str_contains($rawRating, 'Provisional') || str_contains($rawRating, '①') || str_contains($rawRating, '②')) {
                    $isProvisional = true;
                }
            }

            $parsedHighest = null;
            if ($rawHighest !== null && preg_match('/(\d+)/', $rawHighest, $matches)) {
                $parsedHighest = (int) $matches[1];
            }

            $parsedRatedMatches = null;
            if ($rawRatedMatches !== null && preg_match('/(\d+)/', $rawRatedMatches, $matches)) {
                $parsedRatedMatches = (int) $matches[1];
            }

            $percentile = null;
            if ($rawRank !== null && preg_match('/Top\s*([\d\.]+)%/i', $rawRank, $matches)) {
                $percentile = (float) $matches[1];
            }

            $cleanLastCompeted = $rawLastCompeted !== null ? str_replace('/', '-', trim($rawLastCompeted)) : null;

            $result['contest_status'][$type] = [
                'rank' => $parsedRank,
                'rank_text' => $rawRank,
                'percentile' => $percentile,
                'rating' => $parsedRating,
                'is_provisional' => $isProvisional,
                'highest_rating' => $parsedHighest,
                'rated_matches' => $parsedRatedMatches,
                'last_competed' => $cleanLastCompeted,
            ];
        }

        return $result;
    }

    public function getUserRatingHistory(string $username): array
    {
        $types = ['algo', 'heuristic'];
        $history = [];

        foreach ($types as $type) {
            $data = $this->fetchJson($this->baseUrl() . '/users/' . $username . '/history/json?contestType=' . $type);
            foreach ($data as $entry) {
                if (is_array($entry)) {
                    $entry['contest_type'] = $type;
                    $history[] = $entry;
                }
            }
        }

        return $history;
    }

    private function fetchPage(string $url): string
    {
        $this->respectRateLimit();

        try {
            $response = $this->httpRequest()->get($url);

            if ($response->status() === 403) {
                Cache::forget('atcoder_auto_session_cookie');
            }

            if (!$response->successful()) {
                app(ApplicationLogger::class)->warning('AtCoder HTTP request failed', [
                    'category' => 'scraper',
                    'platform' => 'atcoder',
                    'source' => self::class,
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return '';
            }

            return $response->body();
        } catch (\Throwable $e) {
            app(ApplicationLogger::class)->warning('AtCoder HTTP request exception', [
                'category' => 'scraper',
                'platform' => 'atcoder',
                'source' => self::class,
                'url' => $url,
                'message' => $e->getMessage(),
            ], $e);

            return '';
        }
    }

    private function fetchJson(string $url): array
    {
        $this->respectRateLimit();

        $request = $this->httpRequest();
        $response = $request->get($url);

        if ($response->status() === 404 && str_contains($url, '/standings/json')) {
            $fallbackUrl = str_replace('/standings/json', '/standings/team/json', $url);
            $response = $request->get($fallbackUrl);
        }

        if ($response->status() === 403) {
            Cache::forget('atcoder_auto_session_cookie');
        }

        if (!$response->successful()) {
            throw new RuntimeException('AtCoder JSON request failed with HTTP ' . $response->status());
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            throw new RuntimeException('AtCoder JSON request returned invalid payload');
        }

        return $payload;
    }

    private function httpRequest()
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Sec-Ch-Ua' => '"Chromium";v="128", "Not;A=Brand";v="24", "Google Chrome";v="128"',
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => '"Linux"',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
            'Upgrade-Insecure-Requests' => '1',
            'Connection' => 'keep-alive',
        ];

        $cookies = $this->getAuthenticatedCookie();
        if ($cookies !== '') {
            $headers['Cookie'] = $cookies;
        }

        $curlOptions = [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0, // HTTP/2
            CURLOPT_ENCODING => 'gzip, deflate, br',
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        return Http::timeout(20)
            ->withOptions(['curl' => $curlOptions])
            ->withHeaders($headers);
    }

    private function getAuthenticatedCookie(): string
    {
        $envCookie = config('platforms.atcoder.credentials.atcoder_session_cookies')
            ?? env('ATCODER_SESSION_COOKIES');

        if ($envCookie !== null && trim((string) $envCookie) !== '') {
            $cookieStr = trim((string) $envCookie);
            if (!str_contains($cookieStr, '=')) {
                $cookieStr = 'REVEL_SESSION=' . $cookieStr;
            }

            return $cookieStr;
        }

        return '';
    }

    private function respectRateLimit(): void
    {
        $now = (int) (microtime(true) * 1000);
        $elapsed = $now - self::$lastRequestTime;
        $requiredDelay = random_int(self::MIN_DELAY_MS, self::MAX_DELAY_MS);

        if ($elapsed < $requiredDelay) {
            usleep(($requiredDelay - $elapsed) * 1000);
        }

        self::$lastRequestTime = (int) (microtime(true) * 1000);
    }
}
