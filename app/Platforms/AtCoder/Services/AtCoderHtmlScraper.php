<?php

namespace App\Platforms\AtCoder\Services;

use App\Services\ApplicationLogger;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AtCoderHtmlScraper
{
    private const MIN_DELAY_MS = 1200;

    private const MAX_DELAY_MS = 2500;

    private static int $lastRequestTime = 0;

    private function baseUrl(): string
    {
        return rtrim((string) config('platforms.atcoder.base_url', 'https://atcoder.jp'), '/');
    }

    // used
    public function getUserProfile(string $username): array
    {
        $htmlAlgo = $this->fetchPage($this->baseUrl().'/users/'.$username.'?contestType=algo');
        $htmlHeuristic = $this->fetchPage($this->baseUrl().'/users/'.$username.'?contestType=heuristic');

        return $this->parseUserProfileHtml($htmlAlgo, $htmlHeuristic, $username);
    }

    public function parseUserProfileHtml(string $htmlAlgo, ?string $htmlHeuristic = null, string $username = ''): array
    {
        $result = [
            'username' => $username,
            'avatarUrl' => null,
            'country' => null,
            'birthYear' => null,
            'twitterId' => null,
            'topcoderId' => null,
            'codeforcesId' => null,
            'affiliation' => null,
            'contestStatus' => [
                'algo' => null,
                'heuristic' => null,
            ],
        ];

        if (trim($htmlAlgo) === '') {
            return $result;
        }

        $doc = new DOMDocument;
        @$doc->loadHTML($htmlAlgo);
        $xpath = new DOMXPath($doc);

        // Avatar
        $avatarNode = $xpath->query("//div[contains(@class, 'col-md-3')]//img[contains(@class, 'avatar')]")->item(0)
            ?? $xpath->query("//img[contains(@class, 'avatar')]")->item(0);
        if ($avatarNode instanceof DOMElement) {
            $src = $avatarNode->getAttribute('src');
            $result['avatarUrl'] = str_starts_with($src, '//') ? 'https:'.$src : $src;
        }

        if (empty($result['avatarUrl'])) {
            $result['avatarUrl'] = 'https://img.atcoder.jp/assets/icon/avatar.png';
        }

        // Username
        $userNode = $xpath->query("//a[contains(@class, 'username')]")->item(0);
        if ($userNode) {
            $parsedUsername = trim($userNode->textContent);
            if ($parsedUsername !== '') {
                $result['username'] = $parsedUsername;
            }
        }

        // Profile Table (Left Column)
        $leftRows = $xpath->query("//div[contains(@class, 'col-md-3')]//table[contains(@class, 'dl-table')]//tr");
        if ($leftRows->length === 0) {
            $leftRows = $xpath->query("//table[contains(@class, 'dl-table')][1]//tr");
        }

        foreach ($leftRows as $row) {
            $th = $xpath->query('.//th', $row)->item(0);
            $td = $xpath->query('.//td', $row)->item(0);
            if (! $th || ! $td) {
                continue;
            }

            $label = trim($th->textContent);
            $val = trim(preg_replace('/\s+/', ' ', $td->textContent));

            if (str_contains($label, 'Country/Region')) {
                $result['country'] = $val;
            } elseif (str_contains($label, 'Birth Year')) {
                $result['birthYear'] = $val;
            } elseif (str_contains($label, 'Twitter')) {
                $result['twitterId'] = $val;
            } elseif (str_contains($label, 'TopCoder')) {
                $result['topcoderId'] = $val;
            } elseif (str_contains($label, 'Codeforces')) {
                $result['codeforcesId'] = $val;
            } elseif (str_contains($label, 'Affiliation')) {
                $result['affiliation'] = $val;
            }
        }

        // Algo contest status
        $result['contestStatus']['algo'] = $this->parseStatusTable($xpath);

        // Heuristic contest status
        if ($htmlHeuristic !== null && trim($htmlHeuristic) !== '') {
            $docH = new DOMDocument;
            @$docH->loadHTML($htmlHeuristic);
            $xpathH = new DOMXPath($docH);
            $result['contestStatus']['heuristic'] = $this->parseStatusTable($xpathH);
        }

        return $result;
    }

    private function parseStatusTable(DOMXPath $xpath): ?array
    {
        $rows = $xpath->query("//div[contains(@class, 'col-md-9')]//table[contains(@class, 'dl-table')]//tr");
        if ($rows->length === 0) {
            return null;
        }

        $rawRank = null;
        $rawRating = null;
        $rawHighest = null;
        $userTitle = null;
        $rawRatedMatches = null;
        $rawLastCompeted = null;

        foreach ($rows as $row) {
            $th = $xpath->query('.//th', $row)->item(0);
            $td = $xpath->query('.//td', $row)->item(0);
            if (! $th || ! $td) {
                continue;
            }

            $label = trim($th->textContent);
            $val = trim($td->textContent);

            if (str_contains($label, 'Rank')) {
                $rawRank = $val;
            } elseif (str_contains($label, 'Rating') && ! str_contains($label, 'Highest')) {
                $rawRating = $val;
            } elseif (str_contains($label, 'Highest Rating')) {
                $rawHighest = $val;
                $bold = $xpath->query(".//span[contains(@class, 'bold')]", $td)->item(0);
                if ($bold) {
                    $userTitle = trim($bold->textContent);
                }
            } elseif (str_contains($label, 'Rated Matches')) {
                $rawRatedMatches = $val;
            } elseif (str_contains($label, 'Last Competed')) {
                $rawLastCompeted = $val;
            }
        }

        $parsedRank = null;
        $percentile = null;
        if ($rawRank !== null) {
            if (preg_match('/(\d+)/', str_replace(',', '', $rawRank), $m)) {
                $parsedRank = (int) $m[1];
            }
            if (preg_match('/\((Top\s*[^\)]+)\)/i', $rawRank, $m)) {
                $percentile = trim($m[1]);
            }
        }

        $parsedRating = null;
        $isProvisional = false;
        if ($rawRating !== null) {
            if (preg_match('/-?\d+/', $rawRating, $m)) {
                $parsedRating = (int) $m[0];
            }
            if (str_contains($rawRating, 'Provisional') || str_contains($rawRating, '①') || str_contains($rawRating, '②')) {
                $isProvisional = true;
            }
        }

        $parsedHighest = null;
        if ($rawHighest !== null && preg_match('/-?\d+/', $rawHighest, $m)) {
            $parsedHighest = (int) $m[0];
        }

        $parsedMatches = null;
        if ($rawRatedMatches !== null && preg_match('/(\d+)/', $rawRatedMatches, $m)) {
            $parsedMatches = (int) $m[1];
        }

        $lastCompeted = $rawLastCompeted !== null ? str_replace('/', '-', trim($rawLastCompeted)) : null;

        return [
            'rank' => $parsedRank,
            'rank_text' => $rawRank !== null ? trim(preg_replace('/\s+/', ' ', $rawRank)) : null,
            'percentile' => $percentile,
            'rating' => $parsedRating,
            'is_provisional' => $isProvisional,
            'highest_rating' => $parsedHighest,
            'user_title' => $userTitle,
            'rated_matches' => $parsedMatches,
            'last_competed' => $lastCompeted,
            'raw_highest_rating' => $parsedHighest !== null ? (string) $parsedHighest : null,
        ];
    }

    private function fetchPage(string $url): string
    {
        $this->respectRateLimit();

        try {
            $response = $this->httpRequest()->get($url);

            if ($response->status() === 403) {
                Cache::forget('atcoder_auto_session_cookie');
            }

            if (! $response->successful()) {
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

    private function httpRequest()
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept-Encoding' => 'gzip, deflate',
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
            CURLOPT_ENCODING => '', // automatically use all supported encodings by curl
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
            if (! str_contains($cookieStr, '=')) {
                $cookieStr = 'REVEL_SESSION='.$cookieStr;
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
