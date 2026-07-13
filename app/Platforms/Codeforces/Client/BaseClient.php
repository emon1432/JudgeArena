<?php

namespace App\Platforms\Codeforces\Client;

use App\Services\ApplicationLogger;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BaseClient
{
    private readonly string $apiBaseUrl;
    private readonly string $webBaseUrl;
    private readonly string $apiKey;
    private readonly string $apiSecret;

    protected const HTTP_TIMEOUT_SECONDS = 25;
    protected const HTTP_RETRY_ATTEMPTS = 3;
    protected const HTTP_RETRY_SLEEP_MS = 300;
    protected const USER_INFO_BATCH_CHAR_LIMIT = 2000;
    protected const API_RATE_LIMIT_SECONDS = 2;

    public function __construct()
    {
        $this->apiBaseUrl = (string) config('platforms.codeforces.api_base_url', '');
        $this->webBaseUrl = (string) config('platforms.codeforces.base_url', '');
        $this->apiKey = (string) config('platforms.codeforces.credentials.api_key', '');
        $this->apiSecret = (string) config('platforms.codeforces.credentials.api_secret', '');
    }

    //used
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

    //used
    protected function respectRateLimit(string $method, array $query): void
    {
        $cacheKey = 'codeforces:last_request_at:' . $method . ':' . md5(json_encode($query));
        $lastRequestAt = (int) (cache()->get($cacheKey) ?? 0);
        $elapsed = time() - $lastRequestAt;

        if ($lastRequestAt > 0 && $elapsed < self::API_RATE_LIMIT_SECONDS) {
            usleep((self::API_RATE_LIMIT_SECONDS - $elapsed) * 1000000);
        }

        cache()->put($cacheKey, time(), self::API_RATE_LIMIT_SECONDS + 1);
    }

    //used
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

    //used
    protected function decodeApiResponse(Response $response, string $method, array $query): array
    {
        if (! $response->ok()) {
            app(ApplicationLogger::class)->warning('Codeforces HTTP request failed', [
                'category' => 'api',
                'platform' => 'codeforces',
                'source' => self::class,
                'method' => $method,
                'status' => $response->status(),
                'query' => $query,
                'body' => $response->body(),
            ]);

            throw new RuntimeException("Codeforces {$method} request failed with HTTP {$response->status()}");
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException("Codeforces {$method} returned invalid JSON payload");
        }

        $status = strtoupper((string) ($payload['status'] ?? ''));

        if ($status !== 'OK') {
            $comment = (string) ($payload['comment'] ?? 'Codeforces API error');
            if ($comment === 'Call limit exceeded') {
                usleep(self::API_RATE_LIMIT_SECONDS * 1000000);
            }

            app(ApplicationLogger::class)->warning('Codeforces API returned non-OK status', [
                'category' => 'api',
                'platform' => 'codeforces',
                'source' => self::class,
                'method' => $method,
                'query' => $query,
                'comment' => $comment,
            ]);

            throw new RuntimeException($comment);
        }

        $result = $payload['result'] ?? [];

        return is_array($result) ? $result : [];
    }

    //used
    protected function signedQuery(string $method, array $query): array
    {
        $apiKey = $this->apiKey();
        $secret = $this->apiSecret();

        if ($apiKey === '' || $secret === '') {
            return $query;
        }

        $query['apiKey'] = $apiKey;
        $query['time'] = time();

        $sorted = $query;
        ksort($sorted);

        $paramString = http_build_query($sorted, '', '&', PHP_QUERY_RFC3986);
        $prefix = bin2hex(random_bytes(3));
        $signature = hash('sha512', $prefix . '/' . $method . '?' . $paramString . '#' . $secret);
        $query['apiSig'] = $prefix . $signature;

        return $query;
    }

    //used
    protected function apiKey(): string
    {
        return $this->apiKey;
    }

    //used
    protected function apiSecret(): string
    {
        return $this->apiSecret;
    }

    //used
    protected function hasApiCredentials(): bool
    {
        return $this->apiKey() !== '' && $this->apiSecret() !== '';
    }

    //used
    protected function requiresSignedRequest(array $options): bool
    {
        return array_key_exists('apiKey', $options)
            || array_key_exists('api_key', $options)
            || $this->hasApiCredentials();
    }

    //used
    public function requiresSignedRequestPublic(array $options): bool
    {
        return $this->requiresSignedRequest($options);
    }

    public function webBaseUrl(): string
    {
        return $this->webBaseUrl;
    }

    public function userInfoBatchCharLimit(): int
    {
        return (int) self::USER_INFO_BATCH_CHAR_LIMIT;
    }

    //used
    public function requestApi(string $method, array $query = [], bool $signed = false): array
    {
        $finalQuery = $this->sanitizeQuery($query);

        if ($signed) {
            $finalQuery = $this->sanitizeQuery($this->signedQuery($method, $finalQuery));
        }

        $this->respectRateLimit($method, $finalQuery);

        $response = $this->http()
            ->get($this->apiBaseUrl . '/' . $method, $finalQuery);

        return $this->decodeApiResponse($response, $method, $query);
    }
}
