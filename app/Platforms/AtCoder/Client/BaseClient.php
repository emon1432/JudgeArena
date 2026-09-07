<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Client;

use App\Services\ApplicationLogger;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BaseClient
{
    private readonly string $webBaseUrl;
    private readonly string $resourcesUrl;
    private readonly string $apiUrl;

    protected const HTTP_TIMEOUT_SECONDS = 30;
    protected const HTTP_RETRY_ATTEMPTS = 3;
    protected const HTTP_RETRY_SLEEP_MS = 300;
    protected const API_RATE_LIMIT_SECONDS = 1;

    public function __construct()
    {
        $this->webBaseUrl = rtrim((string) config('platforms.atcoder.base_url', 'https://atcoder.jp'), '/');
        $this->resourcesUrl = rtrim((string) config('platforms.atcoder.resources_url', 'https://kenkoooo.com/atcoder/resources'), '/');
        $this->apiUrl = rtrim((string) config('platforms.atcoder.api_url', 'https://kenkoooo.com/atcoder/atcoder-api'), '/');
    }

    protected function http(): PendingRequest
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Accept-Encoding' => 'gzip, deflate',
        ];

        $sessionCookie = config('platforms.atcoder.credentials.atcoder_session_cookies')
            ?? env('ATCODER_SESSION_COOKIES');

        if ($sessionCookie !== null && trim((string) $sessionCookie) !== '') {
            $cookieStr = trim((string) $sessionCookie);
            if (! str_contains($cookieStr, '=')) {
                $cookieStr = 'REVEL_SESSION=' . $cookieStr;
            }
            $headers['Cookie'] = $cookieStr;
        }

        return Http::acceptJson()
            ->withHeaders($headers)
            ->timeout(self::HTTP_TIMEOUT_SECONDS)
            ->retry(
                self::HTTP_RETRY_ATTEMPTS,
                self::HTTP_RETRY_SLEEP_MS,
                fn (\Exception $exception): bool => true,
                throw: false
            );
    }

    protected function respectRateLimit(string $endpoint): void
    {
        $cacheKey = 'atcoder:last_request_at:' . md5($endpoint);
        $lastRequestAt = (int) (cache()->get($cacheKey) ?? 0);
        $elapsed = time() - $lastRequestAt;

        if ($lastRequestAt > 0 && $elapsed < self::API_RATE_LIMIT_SECONDS) {
            usleep((self::API_RATE_LIMIT_SECONDS - $elapsed) * 1000000);
        }

        cache()->put($cacheKey, time(), self::API_RATE_LIMIT_SECONDS + 1);
    }

    /**
     * Request Kenkoooo static resources (e.g. contests.json, problems.json, merged-problems.json, contest-problem.json, problem-models.json)
     *
     * @return array<mixed>
     */
    public function requestResource(string $resource): array
    {
        $url = $this->resourcesUrl . '/' . ltrim($resource, '/');

        return $this->fetchJson($url, $resource);
    }

    /**
     * Request AtCoder internal web JSON endpoints (e.g. /contests/{id}/standings/json, /users/{handle}/history/json)
     *
     * @param array<string, mixed> $query
     * @return array<mixed>
     */
    public function requestWebJson(string $path, array $query = []): array
    {
        $this->respectRateLimit($path);
        $url = $this->webBaseUrl . '/' . ltrim($path, '/');

        return $this->fetchJson($url, $path, $query);
    }

    /**
     * Request Kenkoooo API endpoints (e.g. /v3/user/submissions, /v3/user_info)
     *
     * @param array<string, mixed> $query
     * @return array<mixed>
     */
    public function requestApi(string $path, array $query = []): array
    {
        $this->respectRateLimit($path);
        $url = $this->apiUrl . '/' . ltrim($path, '/');

        return $this->fetchJson($url, $path, $query);
    }

    /**
     * @param array<string, mixed> $query
     * @return array<mixed>
     */
    private function fetchJson(string $url, string $identifier, array $query = []): array
    {
        $sanitizedQuery = [];
        foreach ($query as $key => $value) {
            if ($value !== null && $value !== '') {
                $sanitizedQuery[$key] = (string) $value;
            }
        }

        $response = empty($sanitizedQuery)
            ? $this->http()->get($url)
            : $this->http()->get($url, $sanitizedQuery);

        if ($response->status() === 404 && str_contains($url, '/standings/json')) {
            $fallbackUrl = str_replace('/standings/json', '/standings/team/json', $url);
            $response = empty($sanitizedQuery)
                ? $this->http()->get($fallbackUrl)
                : $this->http()->get($fallbackUrl, $sanitizedQuery);
        }

        return $this->decodeApiResponse($response, $identifier, $sanitizedQuery);
    }

    /**
     * @param array<string, string> $query
     * @return array<mixed>
     */
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

    public function resourcesUrl(): string
    {
        return $this->resourcesUrl;
    }

    public function apiUrl(): string
    {
        return $this->apiUrl;
    }
}
