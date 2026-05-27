<?php

namespace App\Platforms\Codeforces\Client;

use App\Models\Platform;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BaseClient
{
    protected $API_BASE_URL;
    protected $WEB_BASE_URL;
    protected $API_KEY;
    protected $API_SECRET;
    protected $HTTP_TIMEOUT_SECONDS = 25;
    protected $HTTP_RETRY_ATTEMPTS = 3;
    protected $HTTP_RETRY_SLEEP_MS = 300;
    protected $USER_INFO_BATCH_CHAR_LIMIT = 2000;
    protected $API_RATE_LIMIT_SECONDS = 2;

    public function __construct()
    {
        $platform = Platform::where('slug', 'codeforces')->first();
        if ($platform === null) {
            throw new RuntimeException('Codeforces platform not found in database');
        }
        $credentials = json_decode($platform->credentials, true);
        $this->API_BASE_URL = $credentials['api_base_url'] ?? 'https://codeforces.com/api';
        $this->WEB_BASE_URL = $credentials['base_url'] ?? 'https://codeforces.com';
        $this->API_KEY = $credentials['api_key'] ?? '';
        $this->API_SECRET = $credentials['api_secret'] ?? '';
    }

    protected function http(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->timeout($this->HTTP_TIMEOUT_SECONDS)
            ->retry(
                $this->HTTP_RETRY_ATTEMPTS,
                $this->HTTP_RETRY_SLEEP_MS,
                function (\Exception $exception): bool {
                    return true;
                },
                throw: false,
            );
    }

    protected function respectRateLimit(string $method, array $query): void
    {
        $cacheKey = 'codeforces:last_request_at:' . $method . ':' . md5(json_encode($query));
        $lastRequestAt = (int) (cache()->get($cacheKey) ?? 0);
        $elapsed = time() - $lastRequestAt;

        if ($lastRequestAt > 0 && $elapsed < $this->API_RATE_LIMIT_SECONDS) {
            usleep(($this->API_RATE_LIMIT_SECONDS - $elapsed) * 1000000);
        }

        cache()->put($cacheKey, time(), $this->API_RATE_LIMIT_SECONDS + 1);
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
            Log::warning('Codeforces HTTP request failed', [
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
                usleep($this->API_RATE_LIMIT_SECONDS * 1000000);
            }

            Log::notice('Codeforces API returned non-OK status', [
                'method' => $method,
                'query' => $query,
                'comment' => $comment,
            ]);

            throw new RuntimeException($comment);
        }

        $result = $payload['result'] ?? [];

        return is_array($result) ? $result : [];
    }

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

    protected function apiKey(): string
    {
        return $this->API_KEY;
    }

    protected function apiSecret(): string
    {
        return $this->API_SECRET;
    }

    protected function hasApiCredentials(): bool
    {
        return $this->apiKey() !== '' && $this->apiSecret() !== '';
    }

    protected function requiresSignedRequest(array $options): bool
    {
        return array_key_exists('apiKey', $options)
            || array_key_exists('api_key', $options)
            || $this->hasApiCredentials();
    }

    // Expose helper for services when using composition
    public function requiresSignedRequestPublic(array $options): bool
    {
        return $this->requiresSignedRequest($options);
    }

    public function webBaseUrl(): string
    {
        return (string) $this->WEB_BASE_URL;
    }

    public function userInfoBatchCharLimit(): int
    {
        return (int) $this->USER_INFO_BATCH_CHAR_LIMIT;
    }

    public function requestApi(string $method, array $query = [], bool $signed = false): array
    {
        $finalQuery = $this->sanitizeQuery($query);

        if ($signed) {
            $finalQuery = $this->sanitizeQuery($this->signedQuery($method, $finalQuery));
        }

        $this->respectRateLimit($method, $finalQuery);

        $response = $this->http()
            ->get($this->API_BASE_URL . '/' . $method, $finalQuery);

        return $this->decodeApiResponse($response, $method, $query);
    }
}
