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


    //used
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
