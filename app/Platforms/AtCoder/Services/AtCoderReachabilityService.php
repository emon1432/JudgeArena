<?php

namespace App\Platforms\AtCoder\Services;

use App\Services\ApplicationLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AtCoderReachabilityService
{
    private const CACHE_KEY = 'atcoder_reachability_status';
    private const CACHE_TTL_SECONDS = 1800; // 30 minutes

    /**
     * Check if atcoder.jp is directly reachable without CloudFront WAF blocks.
     */
    public function isReachable(): bool
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            try {
                $curlOptions = [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    CURLOPT_ENCODING => 'gzip, deflate, br',
                ];

                $response = Http::timeout(5)
                    ->withOptions(['curl' => $curlOptions])
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    ])
                    ->get('https://atcoder.jp/contests/archive?lang=ja&page=1');

                $isOk = $response->successful() && $response->status() === 200;

                app(ApplicationLogger::class)->info('AtCoder reachability circuit breaker checked', [
                    'category' => 'scraper',
                    'platform' => 'atcoder',
                    'source' => self::class,
                    'status' => $response->status(),
                    'is_reachable' => $isOk,
                ]);

                return $isOk;
            } catch (\Throwable $e) {
                app(ApplicationLogger::class)->warning('AtCoder reachability check failed with exception', [
                    'category' => 'scraper',
                    'platform' => 'atcoder',
                    'source' => self::class,
                    'message' => $e->getMessage(),
                ]);

                return false;
            }
        });
    }

    /**
     * Force reset the reachability status (e.g. after IP or proxy change).
     */
    public function resetCheck(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
