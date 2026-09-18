<?php

declare(strict_types=1);

namespace App\Services\GoogleDrive;

use App\Services\ApplicationLogger;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GoogleDriveClient
{
    /**
     * In-memory cache of folder files mapping: [subfolderKey => [filename => file_id]]
     *
     * @var array<string, array<string, string>>
     */
    private static array $folderFilesMemory = [];
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const API_BASE_URL = 'https://www.googleapis.com/drive/v3';

    private const UPLOAD_BASE_URL = 'https://www.googleapis.com/upload/drive/v3';

    private const TOKEN_CACHE_KEY = 'google_drive:access_token';

    private const TOKEN_FAILURE_KEY = 'google_drive:token_failure';

    private const HTTP_TIMEOUT_SECONDS = 15;

    private const TOKEN_HTTP_TIMEOUT_SECONDS = 5;

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

        if (Cache::has(self::TOKEN_FAILURE_KEY)) {
            return null;
        }

        try {
            $response = Http::asForm()
                ->timeout(self::TOKEN_HTTP_TIMEOUT_SECONDS)
                ->post(self::TOKEN_URL, [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $this->refreshToken,
                    'grant_type' => 'refresh_token',
                ]);

            if (! $response->successful()) {
                Cache::put(self::TOKEN_FAILURE_KEY, true, 120);

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
                Cache::forget(self::TOKEN_FAILURE_KEY);
                $ttl = max(60, $expiresIn - 300);
                Cache::put(self::TOKEN_CACHE_KEY, $accessToken, $ttl);

                return $accessToken;
            }
        } catch (Throwable $e) {
            Cache::put(self::TOKEN_FAILURE_KEY, true, 60);

            app(ApplicationLogger::class)->warning('Google Drive token request exception', [
                'category' => 'storage',
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public function warmupAllFolders(?string $rootFolderId = null): array
    {
        $token = $this->getAccessToken();
        if ($token === null) {
            return [];
        }

        $root = $rootFolderId ?? $this->folderId;
        $cacheKey = 'gdrive:all_folders:'.md5((string) $root);
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        try {
            $query = "mimeType = 'application/vnd.google-apps.folder' and trashed = false";
            $response = $this->http($token)->get(self::API_BASE_URL.'/files', [
                'q' => $query,
                'fields' => 'files(id, name, parents)',
                'pageSize' => 500,
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
            ]);

            if ($response->successful()) {
                $folders = $response->json('files') ?? [];
                $map = [];
                foreach ($folders as $folder) {
                    $id = (string) ($folder['id'] ?? '');
                    $name = (string) ($folder['name'] ?? '');
                    $parents = (array) ($folder['parents'] ?? []);
                    if ($id !== '' && $name !== '') {
                        foreach ($parents as $parent) {
                            $subfolderCacheKey = 'gdrive:subfolder:'.md5($parent.':'.$name);
                            Cache::put($subfolderCacheKey, $id, 86400);
                            $map[$parent.':'.$name] = $id;
                        }
                    }
                }

                Cache::put($cacheKey, $map, 86400);

                return $map;
            }
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive warmupAllFolders exception', [
                'category' => 'storage',
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    public function ensureSubfolder(string $folderName, ?string $parentFolderId = null): ?string
    {
        $token = $this->getAccessToken();
        if ($token === null) {
            return null;
        }

        $parent = $parentFolderId ?? $this->folderId;
        $cacheKey = 'gdrive:subfolder:'.md5($parent.':'.$folderName);

        $cachedId = Cache::get($cacheKey);
        if (is_string($cachedId) && $cachedId !== '') {
            return $cachedId;
        }

        try {
            $query = "name = '{$folderName}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false";
            if ($parent !== '') {
                $query = "'{$parent}' in parents and ".$query;
            }

            $response = $this->http($token)->get(self::API_BASE_URL.'/files', [
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

            $createResponse = $this->http($token)->asJson()->post(self::API_BASE_URL.'/files?supportsAllDrives=true', $metadata);

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
     * @param  array<int, string>  $segments
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
     * @param  array<int, string>|string|null  $subfolder
     */
    public function resolveTargetFolder(array|string|null $subfolder): ?string
    {
        if ($subfolder === null || $subfolder === '' || $subfolder === []) {
            return $this->folderId;
        }

        $segments = is_array($subfolder) ? $subfolder : explode('/', trim($subfolder, '/'));

        return $this->ensurePath($segments, $this->folderId);
    }

    public function clearSubfolderCache(array|string $subfolder): void
    {
        $segments = is_array($subfolder) ? $subfolder : explode('/', trim($subfolder, '/'));
        $currentFolderId = $this->folderId;

        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment === '') {
                continue;
            }

            $cacheKey = 'gdrive:subfolder:'.md5($currentFolderId.':'.$segment);
            $nextFolderId = Cache::get($cacheKey);
            Cache::forget($cacheKey);

            if (is_string($nextFolderId) && $nextFolderId !== '') {
                $currentFolderId = $nextFolderId;
            }
        }
    }

    public function exists(string $filename, array|string|null $subfolder = null): bool
    {
        return $this->findFileId($filename, $subfolder) !== null;
    }

    /**
     * Warm up all file IDs inside a target subfolder in 1 paginated API call.
     *
     * @return array<string, string> [filename => file_id]
     */
    public function warmupFolderFiles(array|string|null $subfolder = null): array
    {
        $subfolderKey = is_array($subfolder) ? implode('/', $subfolder) : (string) ($subfolder ?? '');
        if (isset(self::$folderFilesMemory[$subfolderKey])) {
            return self::$folderFilesMemory[$subfolderKey];
        }

        $cacheKey = 'gdrive:folder_files:'.md5($subfolderKey);
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return self::$folderFilesMemory[$subfolderKey] = $cached;
        }

        $token = $this->getAccessToken();
        if ($token === null) {
            return [];
        }

        $targetFolderId = $this->resolveTargetFolder($subfolder);
        if ($targetFolderId === null) {
            return [];
        }

        $map = [];
        $pageToken = null;

        try {
            do {
                $query = 'trashed = false';
                if ($targetFolderId !== '') {
                    $query = "'{$targetFolderId}' in parents and ".$query;
                }

                $params = [
                    'q' => $query,
                    'fields' => 'nextPageToken, files(id, name)',
                    'pageSize' => 1000,
                    'supportsAllDrives' => 'true',
                    'includeItemsFromAllDrives' => 'true',
                ];
                if ($pageToken !== null && $pageToken !== '') {
                    $params['pageToken'] = $pageToken;
                }

                $response = $this->http($token)->get(self::API_BASE_URL.'/files', $params);
                if (! $response->successful()) {
                    break;
                }

                $data = $response->json();
                $files = (array) ($data['files'] ?? []);
                foreach ($files as $file) {
                    $name = (string) ($file['name'] ?? '');
                    $id = (string) ($file['id'] ?? '');
                    if ($name !== '' && $id !== '') {
                        $map[$name] = $id;
                        $fileCacheKey = 'gdrive:file:'.md5($subfolderKey.':'.$name);
                        Cache::put($fileCacheKey, $id, 86400);
                    }
                }

                $pageToken = $data['nextPageToken'] ?? null;
            } while ($pageToken !== null && $pageToken !== '');

            Cache::put($cacheKey, $map, 86400);

            return self::$folderFilesMemory[$subfolderKey] = $map;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive warmupFolderFiles exception', [
                'category' => 'storage',
                'subfolder' => $subfolderKey,
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    public function findFileId(string $filename, array|string|null $subfolder = null): ?string
    {
        $subfolderKey = is_array($subfolder) ? implode('/', $subfolder) : (string) ($subfolder ?? '');

        // 1. In-memory check first (instant 0 ms)
        if (isset(self::$folderFilesMemory[$subfolderKey][$filename])) {
            return self::$folderFilesMemory[$subfolderKey][$filename];
        }

        // 2. Check cached folder map from previous warmup
        if (! isset(self::$folderFilesMemory[$subfolderKey])) {
            $cachedFolderMap = Cache::get('gdrive:folder_files:'.$subfolderKey);
            if (is_array($cachedFolderMap)) {
                self::$folderFilesMemory[$subfolderKey] = $cachedFolderMap;
                if (isset($cachedFolderMap[$filename])) {
                    return $cachedFolderMap[$filename];
                }
            }
        }

        $fileCacheKey = 'gdrive:file:'.md5($subfolderKey.':'.$filename);

        $cachedFileId = Cache::get($fileCacheKey);
        if ($cachedFileId !== null) {
            return $cachedFileId !== '' ? (string) $cachedFileId : null;
        }

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
                $query = "'{$targetFolderId}' in parents and ".$query;
            }

            $response = $this->http($token)->get(self::API_BASE_URL.'/files', [
                'q' => $query,
                'fields' => 'files(id, name, size)',
                'pageSize' => 1,
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
            ]);

            if ($response->successful()) {
                $files = $response->json('files');
                if (is_array($files) && count($files) > 0) {
                    $fileId = (string) ($files[0]['id'] ?? '');
                    if ($fileId !== '') {
                        Cache::put($fileCacheKey, $fileId, 3600);

                        return $fileId;
                    }
                }

                Cache::put($fileCacheKey, '', 300);
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

        return $this->getById($fileId, $filename, $subfolder);
    }

    public function getById(string $fileId, ?string $filename = null, array|string|null $subfolder = null): ?string
    {
        $token = $this->getAccessToken();
        if ($token === null) {
            return null;
        }

        try {
            $response = $this->http($token)->get(self::API_BASE_URL."/files/{$fileId}", [
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

    /**
     * Download multiple files concurrently via Http::pool().
     *
     * @param  array<string>  $filenames
     * @return array<string, ?string> [filename => binaryContent]
     */
    public function getMultiple(array $filenames, array|string|null $subfolder = null): array
    {
        if (empty($filenames)) {
            return [];
        }

        $token = $this->getAccessToken();
        if ($token === null) {
            return array_fill_keys($filenames, null);
        }

        $fileIds = [];
        foreach ($filenames as $filename) {
            $fileId = $this->findFileId($filename, $subfolder);
            if ($fileId !== null && $fileId !== '') {
                $fileIds[$filename] = $fileId;
            }
        }

        if (empty($fileIds)) {
            return array_fill_keys($filenames, null);
        }

        try {
            $responses = Http::pool(function (Pool $pool) use ($fileIds, $token) {
                foreach ($fileIds as $filename => $fileId) {
                    $pool->as($filename)
                        ->withToken($token)
                        ->timeout(self::HTTP_TIMEOUT_SECONDS)
                        ->get(self::API_BASE_URL."/files/{$fileId}", [
                            'alt' => 'media',
                            'supportsAllDrives' => 'true',
                        ]);
                }
            });

            $results = [];
            foreach ($filenames as $filename) {
                if (isset($responses[$filename]) && $responses[$filename]->successful()) {
                    $results[$filename] = $responses[$filename]->body();
                } else {
                    $results[$filename] = null;
                }
            }

            return $results;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive getMultiple pool exception', [
                'category' => 'storage',
                'error' => $e->getMessage(),
            ]);

            $results = [];
            foreach ($filenames as $filename) {
                $results[$filename] = $this->get($filename, $subfolder);
            }

            return $results;
        }
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
                    ->patch(self::UPLOAD_BASE_URL."/files/{$existingFileId}?uploadType=media&supportsAllDrives=true");

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
                ->post(self::API_BASE_URL.'/files?supportsAllDrives=true', $metadata);

            if (! $metaResponse->successful()) {
                if ($metaResponse->status() === 404 && $subfolder !== null) {
                    $this->clearSubfolderCache($subfolder);
                    $newTargetFolderId = $this->resolveTargetFolder($subfolder);
                    if ($newTargetFolderId !== null && $newTargetFolderId !== $targetFolderId) {
                        $metadata['parents'] = $newTargetFolderId !== '' ? [$newTargetFolderId] : [];
                        $retryMeta = $this->http($token)->asJson()->post(self::API_BASE_URL.'/files?supportsAllDrives=true', $metadata);
                        if ($retryMeta->successful()) {
                            $retryFileId = (string) ($retryMeta->json('id') ?? '');
                            if ($retryFileId !== '') {
                                $uploadResponse = $this->http($token)
                                    ->withHeaders(['Content-Type' => $mimeType])
                                    ->withBody($content, $mimeType)
                                    ->patch(self::UPLOAD_BASE_URL."/files/{$retryFileId}?uploadType=media&supportsAllDrives=true");

                                return $uploadResponse->successful();
                            }
                        }
                    }
                }

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
                ->patch(self::UPLOAD_BASE_URL."/files/{$newFileId}?uploadType=media&supportsAllDrives=true");

            if ($uploadResponse->successful()) {
                $subfolderKey = is_array($subfolder) ? implode('/', $subfolder) : (string) ($subfolder ?? '');
                Cache::put('gdrive:file:'.md5($subfolderKey.':'.$filename), $newFileId, 3600);
                if ($targetFolderId !== '') {
                    $listCacheKey = 'gdrive:list_files:'.md5($targetFolderId);
                    $cachedList = Cache::get($listCacheKey);
                    if (is_array($cachedList)) {
                        if (! in_array($filename, $cachedList, true)) {
                            $cachedList[] = $filename;
                            Cache::put($listCacheKey, array_values($cachedList), 3600);
                        }
                    }
                }

                return true;
            }

            return false;
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
            $response = $this->http($token)->delete(self::API_BASE_URL."/files/{$fileId}?supportsAllDrives=true");

            if ($response->successful()) {
                $subfolderKey = is_array($subfolder) ? implode('/', $subfolder) : (string) ($subfolder ?? '');
                Cache::forget('gdrive:file:'.md5($subfolderKey.':'.$filename));
                $targetFolderId = $this->resolveTargetFolder($subfolder);
                if ($targetFolderId !== null && $targetFolderId !== '') {
                    $listCacheKey = 'gdrive:list_files:'.md5($targetFolderId);
                    $cachedList = Cache::get($listCacheKey);
                    if (is_array($cachedList)) {
                        $cachedList = array_values(array_filter($cachedList, fn ($f) => $f !== $filename));
                        Cache::put($listCacheKey, $cachedList, 3600);
                    }
                }

                return true;
            }

            return false;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive delete exception', [
                'category' => 'storage',
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param  array<int, string>  $folderIds
     * @return array<string, array<int, string>> Map of folderId => filenames
     */
    public function batchListFiles(array $folderIds): array
    {
        $folderIds = array_values(array_filter(array_unique($folderIds)));
        if (empty($folderIds)) {
            return [];
        }

        $token = $this->getAccessToken();
        if ($token === null) {
            return [];
        }

        $results = [];
        $uncachedFolderIds = [];

        foreach ($folderIds as $folderId) {
            $cacheKey = 'gdrive:list_files:'.md5($folderId);
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                $results[$folderId] = $cached;
            } else {
                $uncachedFolderIds[] = $folderId;
                $results[$folderId] = [];
            }
        }

        if (empty($uncachedFolderIds)) {
            return $results;
        }

        try {
            $parentClauses = array_map(fn ($id) => "'{$id}' in parents", $uncachedFolderIds);
            $parentQuery = count($parentClauses) === 1 ? $parentClauses[0] : '('.implode(' or ', $parentClauses).')';
            $query = "{$parentQuery} and trashed = false and mimeType != 'application/vnd.google-apps.folder'";

            $pageToken = null;
            do {
                $params = [
                    'q' => $query,
                    'fields' => 'nextPageToken, files(id, name, parents)',
                    'pageSize' => 1000,
                    'supportsAllDrives' => 'true',
                    'includeItemsFromAllDrives' => 'true',
                ];
                if ($pageToken !== null && $pageToken !== '') {
                    $params['pageToken'] = $pageToken;
                }

                $response = $this->http($token)->get(self::API_BASE_URL.'/files', $params);
                if (! $response->successful()) {
                    break;
                }

                $data = $response->json();
                $items = $data['files'] ?? [];

                foreach ($items as $item) {
                    $name = (string) ($item['name'] ?? '');
                    $parents = (array) ($item['parents'] ?? []);
                    if ($name === '') {
                        continue;
                    }

                    foreach ($parents as $parentId) {
                        if (isset($results[$parentId])) {
                            $results[$parentId][] = $name;
                        }
                    }
                }

                $pageToken = $data['nextPageToken'] ?? null;
            } while ($pageToken !== null && $pageToken !== '');

            foreach ($uncachedFolderIds as $folderId) {
                $list = array_values(array_unique($results[$folderId] ?? []));
                $results[$folderId] = $list;
                Cache::put('gdrive:list_files:'.md5($folderId), $list, 3600);
            }
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive batchListFiles exception', [
                'category' => 'storage',
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * @param  array<int, string>|string|null  $subfolder
     * @return array<int, string>
     */
    public function listFiles(array|string|null $subfolder = null): array
    {
        $token = $this->getAccessToken();
        if ($token === null) {
            return [];
        }

        $targetFolderId = $this->resolveTargetFolder($subfolder);
        if ($targetFolderId === null || $targetFolderId === '') {
            return [];
        }

        $cacheKey = 'gdrive:list_files:'.md5((string) $targetFolderId);
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $files = [];
        $pageToken = null;

        try {
            do {
                $params = [
                    'q' => "'{$targetFolderId}' in parents and trashed = false and mimeType != 'application/vnd.google-apps.folder'",
                    'fields' => 'nextPageToken, files(id, name, size)',
                    'pageSize' => 1000,
                    'supportsAllDrives' => 'true',
                    'includeItemsFromAllDrives' => 'true',
                ];
                if ($pageToken !== null && $pageToken !== '') {
                    $params['pageToken'] = $pageToken;
                }

                $response = $this->http($token)->get(self::API_BASE_URL.'/files', $params);
                if (! $response->successful()) {
                    break;
                }

                $data = $response->json();
                $items = $data['files'] ?? [];
                $subfolderKey = is_array($subfolder) ? implode('/', $subfolder) : (string) ($subfolder ?? '');

                foreach ($items as $item) {
                    if (isset($item['name']) && is_string($item['name'])) {
                        $files[] = $item['name'];
                        if (isset($item['id']) && is_string($item['id'])) {
                            Cache::put('gdrive:file:'.md5($subfolderKey.':'.$item['name']), (string) $item['id'], 3600);
                        }
                    }
                }

                $pageToken = $data['nextPageToken'] ?? null;
            } while ($pageToken !== null && $pageToken !== '');

            Cache::put($cacheKey, $files, 3600);
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('Google Drive listFiles exception', [
                'category' => 'storage',
                'subfolder' => is_array($subfolder) ? implode('/', $subfolder) : $subfolder,
                'error' => $e->getMessage(),
            ]);
        }

        return $files;
    }

    /**
     * @return array{driver: string, configured: bool, connected: bool, status: string, badge_class: string, message: string}
     */
    public function checkConnection(): array
    {
        $disk = (string) config('standings.disk', env('STANDINGS_DISK', 'local'));

        if ($disk === 'local') {
            return [
                'driver' => 'local',
                'configured' => true,
                'connected' => true,
                'status' => 'Local Disk',
                'badge_class' => 'secondary',
                'message' => 'Standings cache is running on local disk storage.',
            ];
        }

        if (! $this->isConfigured()) {
            return [
                'driver' => 'google',
                'configured' => false,
                'connected' => false,
                'status' => 'Not Configured',
                'badge_class' => 'warning',
                'message' => 'Google Drive credentials are missing in your environment configuration.',
            ];
        }

        $token = $this->getAccessToken();
        if ($token === null) {
            return [
                'driver' => 'google',
                'configured' => true,
                'connected' => false,
                'status' => 'Token Expired',
                'badge_class' => 'danger',
                'message' => 'Google Drive OAuth token has expired or is invalid. Standings cache uploads are suspended.',
            ];
        }

        return [
            'driver' => 'google',
            'configured' => true,
            'connected' => true,
            'status' => 'Connected',
            'badge_class' => 'success',
            'message' => 'Google Drive cloud storage is active and authorized.',
        ];
    }

    private function http(string $token): PendingRequest
    {
        return Http::withToken($token)->timeout(self::HTTP_TIMEOUT_SECONDS);
    }
}
