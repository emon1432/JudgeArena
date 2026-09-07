<?php

declare(strict_types=1);

namespace App\Services\GoogleDrive;

use App\Services\ApplicationLogger;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GoogleDriveClient
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const API_BASE_URL = 'https://www.googleapis.com/drive/v3';
    private const UPLOAD_BASE_URL = 'https://www.googleapis.com/upload/drive/v3';
    private const TOKEN_CACHE_KEY = 'google_drive:access_token';
    private const HTTP_TIMEOUT_SECONDS = 30;

    private readonly string $clientId;
    private readonly string $clientSecret;
    private readonly string $refreshToken;
    private readonly string $folderId;

    public function __construct(?array $config = null)
    {
        $config ??= (array) config('filesystems.disks.google', []);

        $this->clientId = (string) ($config['clientId'] ?? env('GOOGLE_DRIVE_CLIENT_ID', ''));
        $this->clientSecret = (string) ($config['clientSecret'] ?? env('GOOGLE_DRIVE_CLIENT_SECRET', ''));
        $this->refreshToken = (string) ($config['refreshToken'] ?? env('GOOGLE_DRIVE_REFRESH_TOKEN', ''));
        $this->folderId = (string) ($config['folderId'] ?? env('GOOGLE_DRIVE_FOLDER_ID', ''));
    }

    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->clientSecret !== '' && $this->refreshToken !== '';
    }

    public function getAccessToken(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $cachedToken = Cache::get(self::TOKEN_CACHE_KEY);
        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        try {
            $response = Http::asForm()
                ->timeout(self::HTTP_TIMEOUT_SECONDS)
                ->post(self::TOKEN_URL, [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $this->refreshToken,
                    'grant_type' => 'refresh_token',
                ]);

            if (! $response->successful()) {
                app(ApplicationLogger::class)->warning('Google Drive token refresh failed', [
                    'category' => 'storage',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();
            $accessToken = (string) ($data['access_token'] ?? '');
            $expiresIn = (int) ($data['expires_in'] ?? 3600);

            if ($accessToken !== '') {
                $ttl = max(60, $expiresIn - 300);
                Cache::put(self::TOKEN_CACHE_KEY, $accessToken, $ttl);

                return $accessToken;
            }
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive token request exception', [
                'category' => 'storage',
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public function ensureSubfolder(string $folderName, ?string $parentFolderId = null): ?string
    {
        $token = $this->getAccessToken();
        if ($token === null) {
            return null;
        }

        $parent = $parentFolderId ?? $this->folderId;
        $cacheKey = 'gdrive:subfolder:' . md5($parent . ':' . $folderName);

        $cachedId = Cache::get($cacheKey);
        if (is_string($cachedId) && $cachedId !== '') {
            return $cachedId;
        }

        try {
            $query = "name = '{$folderName}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false";
            if ($parent !== '') {
                $query = "'{$parent}' in parents and " . $query;
            }

            $response = $this->http($token)->get(self::API_BASE_URL . '/files', [
                'q' => $query,
                'fields' => 'files(id, name)',
                'pageSize' => 1,
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
            ]);

            if ($response->successful()) {
                $files = $response->json('files');
                if (is_array($files) && count($files) > 0) {
                    $folderId = (string) ($files[0]['id'] ?? '');
                    if ($folderId !== '') {
                        Cache::put($cacheKey, $folderId, 86400);

                        return $folderId;
                    }
                }
            }

            $metadata = [
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
            ];
            if ($parent !== '') {
                $metadata['parents'] = [$parent];
            }

            $createResponse = $this->http($token)->asJson()->post(self::API_BASE_URL . '/files?supportsAllDrives=true', $metadata);

            if ($createResponse->successful()) {
                $newFolderId = (string) ($createResponse->json('id') ?? '');
                if ($newFolderId !== '') {
                    Cache::put($cacheKey, $newFolderId, 86400);

                    return $newFolderId;
                }
            }

            app(ApplicationLogger::class)->warning('Google Drive subfolder creation failed', [
                'category' => 'storage',
                'folder' => $folderName,
                'parent' => $parent,
                'status' => $createResponse->status(),
                'body' => $createResponse->body(),
            ]);
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive ensureSubfolder exception', [
                'category' => 'storage',
                'folder' => $folderName,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * @param array<int, string> $segments
     */
    public function ensurePath(array $segments, ?string $rootFolderId = null): ?string
    {
        $currentFolderId = $rootFolderId ?? $this->folderId;

        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment === '') {
                continue;
            }

            $currentFolderId = $this->ensureSubfolder($segment, $currentFolderId);
            if ($currentFolderId === null) {
                return null;
            }
        }

        return $currentFolderId;
    }

    /**
     * @param array<int, string>|string|null $subfolder
     */
    public function resolveTargetFolder(array|string|null $subfolder): ?string
    {
        if ($subfolder === null || $subfolder === '' || $subfolder === []) {
            return $this->folderId;
        }

        $segments = is_array($subfolder) ? $subfolder : explode('/', trim($subfolder, '/'));

        return $this->ensurePath($segments, $this->folderId);
    }

    public function exists(string $filename, array|string|null $subfolder = null): bool
    {
        return $this->findFileId($filename, $subfolder) !== null;
    }

    public function findFileId(string $filename, array|string|null $subfolder = null): ?string
    {
        $token = $this->getAccessToken();
        if ($token === null) {
            return null;
        }

        $targetFolderId = $this->resolveTargetFolder($subfolder);
        if ($targetFolderId === null) {
            return null;
        }

        try {
            $query = "name = '{$filename}' and trashed = false";
            if ($targetFolderId !== '') {
                $query = "'{$targetFolderId}' in parents and " . $query;
            }

            $response = $this->http($token)->get(self::API_BASE_URL . '/files', [
                'q' => $query,
                'fields' => 'files(id, name, size)',
                'pageSize' => 1,
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
            ]);

            if ($response->successful()) {
                $files = $response->json('files');
                if (is_array($files) && count($files) > 0) {
                    return (string) ($files[0]['id'] ?? '');
                }
            }
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive search failed', [
                'category' => 'storage',
                'filename' => $filename,
                'subfolder' => is_array($subfolder) ? implode('/', $subfolder) : $subfolder,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public function get(string $filename, array|string|null $subfolder = null): ?string
    {
        $fileId = $this->findFileId($filename, $subfolder);
        if ($fileId === null || $fileId === '') {
            return null;
        }

        $token = $this->getAccessToken();
        if ($token === null) {
            return null;
        }

        try {
            $response = $this->http($token)->get(self::API_BASE_URL . "/files/{$fileId}", [
                'alt' => 'media',
                'supportsAllDrives' => 'true',
            ]);

            if ($response->successful()) {
                return $response->body();
            }

            app(ApplicationLogger::class)->warning('Google Drive download failed', [
                'category' => 'storage',
                'filename' => $filename,
                'subfolder' => is_array($subfolder) ? implode('/', $subfolder) : $subfolder,
                'file_id' => $fileId,
                'status' => $response->status(),
            ]);
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive download exception', [
                'category' => 'storage',
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public function put(string $filename, string $content, string $mimeType = 'application/gzip', array|string|null $subfolder = null): bool
    {
        $token = $this->getAccessToken();
        if ($token === null) {
            return false;
        }

        $targetFolderId = $this->resolveTargetFolder($subfolder);
        if ($targetFolderId === null) {
            return false;
        }

        try {
            $existingFileId = $this->findFileId($filename, $subfolder);

            if ($existingFileId !== null && $existingFileId !== '') {
                $response = $this->http($token)
                    ->withHeaders(['Content-Type' => $mimeType])
                    ->withBody($content, $mimeType)
                    ->patch(self::UPLOAD_BASE_URL . "/files/{$existingFileId}?uploadType=media&supportsAllDrives=true");

                return $response->successful();
            }

            $metadata = [
                'name' => $filename,
                'mimeType' => $mimeType,
            ];

            if ($targetFolderId !== '') {
                $metadata['parents'] = [$targetFolderId];
            }

            $metaResponse = $this->http($token)
                ->asJson()
                ->post(self::API_BASE_URL . '/files?supportsAllDrives=true', $metadata);

            if (! $metaResponse->successful()) {
                app(ApplicationLogger::class)->warning('Google Drive file creation failed', [
                    'category' => 'storage',
                    'filename' => $filename,
                    'subfolder' => is_array($subfolder) ? implode('/', $subfolder) : $subfolder,
                    'status' => $metaResponse->status(),
                    'body' => $metaResponse->body(),
                ]);

                return false;
            }

            $newFileId = (string) ($metaResponse->json('id') ?? '');
            if ($newFileId === '') {
                return false;
            }

            $uploadResponse = $this->http($token)
                ->withHeaders(['Content-Type' => $mimeType])
                ->withBody($content, $mimeType)
                ->patch(self::UPLOAD_BASE_URL . "/files/{$newFileId}?uploadType=media&supportsAllDrives=true");

            return $uploadResponse->successful();
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive upload exception', [
                'category' => 'storage',
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(string $filename, array|string|null $subfolder = null): bool
    {
        $fileId = $this->findFileId($filename, $subfolder);
        if ($fileId === null || $fileId === '') {
            return true;
        }

        $token = $this->getAccessToken();
        if ($token === null) {
            return false;
        }

        try {
            $response = $this->http($token)->delete(self::API_BASE_URL . "/files/{$fileId}?supportsAllDrives=true");

            return $response->successful();
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive delete exception', [
                'category' => 'storage',
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function http(string $token): PendingRequest
    {
        return Http::withToken($token)->timeout(self::HTTP_TIMEOUT_SECONDS);
    }
}