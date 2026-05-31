<?php

namespace App\Platforms\AtCoder\Client;

use App\Services\ApplicationLogger;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BaseClient
{
    private readonly string $apiBaseUrl;
    private readonly string $webBaseUrl;

    protected const HTTP_TIMEOUT_SECONDS = 25;
    protected const HTTP_RETRY_ATTEMPTS = 3;
    protected const HTTP_RETRY_SLEEP_MS = 300;
    protected const API_RATE_LIMIT_SECONDS = 1;

    public function __construct()
    {
        $this->webBaseUrl = (string) config('platforms.atcoder.base_url', '');

        $apiBaseUrl = (string) config('platforms.atcoder.api_base_url', '');
        if ($apiBaseUrl === '') {
            $apiBaseUrl = rtrim($this->webBaseUrl, '/');
            if ($apiBaseUrl !== '') {
                $apiBaseUrl .= '/api/atcoder';
            }
        }

        $this->apiBaseUrl = $apiBaseUrl;
    }

    protected function http(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->timeout(self::HTTP_TIMEOUT_SECONDS)
            ->retry(
                self::HTTP_RETRY_ATTEMPTS,
                self::HTTP_RETRY_SLEEP_MS,
                function (\Exception $exception): bool {
                    return true;
                },
                throw: false,
            );
    }

    protected function respectRateLimit(string $method, array $query): void
    {
        $cacheKey = 'atcoder:last_request_at:' . $method . ':' . md5(json_encode($query));
        $lastRequestAt = (int) (cache()->get($cacheKey) ?? 0);
        $elapsed = time() - $lastRequestAt;

        if ($lastRequestAt > 0 && $elapsed < self::API_RATE_LIMIT_SECONDS) {
            usleep((self::API_RATE_LIMIT_SECONDS - $elapsed) * 1000000);
        }

        cache()->put($cacheKey, time(), self::API_RATE_LIMIT_SECONDS + 1);
    }

    protected function sanitizeQuery(array $query): array
    {
        $sanitized = [];

        foreach ($query as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $sanitized[$key] = (string) $value;
        }

        return $sanitized;
    }

    protected function decodeApiResponse(Response $response, string $method, array $query): array
    {
        if (! $response->ok()) {
            app(ApplicationLogger::class)->warning('AtCoder HTTP request failed', [
                'category' => 'api',
                'platform' => 'atcoder',
                'source' => self::class,
                'method' => $method,
                'status' => $response->status(),
                'query' => $query,
                'body' => $response->body(),
            ]);

            throw new RuntimeException("AtCoder {$method} request failed with HTTP {$response->status()}");
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException("AtCoder {$method} returned invalid JSON payload");
        }

        return $payload;
    }

    public function webBaseUrl(): string
    {
        return $this->webBaseUrl;
    }

    public function apiBaseUrl(): string
    {
        return $this->apiBaseUrl;
    }

    public function requestApi(string $path, array $query = []): array
    {
        $finalQuery = $this->sanitizeQuery($query);
        $this->respectRateLimit($path, $finalQuery);

        $url = rtrim($this->apiBaseUrl, '/') . '/' . ltrim($path, '/');
        $response = $this->http()->get($url, $finalQuery);

        return $this->decodeApiResponse($response, $path, $query);
    }
}
