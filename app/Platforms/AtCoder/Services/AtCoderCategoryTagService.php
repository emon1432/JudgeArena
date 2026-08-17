<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Services;

use App\Services\ApplicationLogger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AtCoderCategoryTagService
{
    private const GITHUB_RAW_BASE = 'https://raw.githubusercontent.com/atcoder-categories/atcoder-categories.github.io/main';
    private const CACHE_FILE = 'atcoder_category_map.json';
    private const CACHE_TTL_SECONDS = 86400; // 24 hours

    private static ?array $memoryMap = null;

    public function __construct(
        private readonly ApplicationLogger $logger
    ) {}

    /**
     * Get the full problem category tag & difficulty map [problem_id => ['rating' => ?int, 'tags' => array]].
     *
     * @return array<string, array{rating: ?int, tags: array<string>}>
     */
    public function getCategoryMap(): array
    {
        if (self::$memoryMap !== null) {
            return self::$memoryMap;
        }

        // 1. Try loading from cache storage
        if (Storage::disk('local')->exists(self::CACHE_FILE)) {
            $lastModified = Storage::disk('local')->lastModified(self::CACHE_FILE);
            if ((time() - $lastModified) < self::CACHE_TTL_SECONDS) {
                $cachedJson = Storage::disk('local')->get(self::CACHE_FILE);
                $map = json_decode((string) $cachedJson, true);
                if (is_array($map) && !empty($map)) {
                    self::$memoryMap = $map;
                    return self::$memoryMap;
                }
            }
        }

        // 2. Fetch remotely from GitHub repository
        $map = $this->buildMapFromGitHub();
        if (!empty($map)) {
            $this->saveMapToCache($map);
        }

        self::$memoryMap = $map;
        return self::$memoryMap;
    }

    /**
     * Enrich a problem with category tags & difficulty rating.
     *
     * @param string $problemId Platform problem ID (e.g. abc399_a)
     * @param int|null $scrapedRating Rating from scraper
     * @param array $scrapedTags Tags from scraper
     * @return array{rating: ?int, tags: array<string>}
     */
    public function enrichProblem(string $problemId, ?int $scrapedRating = null, array $scrapedTags = []): array
    {
        $map = $this->getCategoryMap();

        $info = $map[$problemId] ?? null;

        $finalRating = $scrapedRating;
        if ($finalRating === null && isset($info['rating']) && $info['rating'] !== null) {
            $finalRating = (int) $info['rating'];
        }

        $mappedTags = isset($info['tags']) && is_array($info['tags']) ? $info['tags'] : [];
        $finalTags = array_values(array_unique(array_merge($scrapedTags, $mappedTags)));

        return [
            'rating' => $finalRating,
            'tags' => $finalTags,
        ];
    }

    private function saveMapToCache(array $map): void
    {
        try {
            Storage::disk('local')->put(self::CACHE_FILE, (string) json_encode($map, JSON_PRETTY_PRINT));
        } catch (Throwable $e) {
            $this->logger->warning('Failed to save atcoder category map to storage cache', [
                'category' => 'cache',
                'platform' => 'atcoder',
                'source' => self::class,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function buildMapFromGitHub(): array
    {
        try {
            $response = Http::timeout(10)->get(self::GITHUB_RAW_BASE . '/index.html');
            if (!$response->successful()) {
                return [];
            }

            $indexHtml = $response->body();
            preg_match_all('/href:\s*"([^"]+\.html)"/', $indexHtml, $matches);

            $htmlFiles = array_unique($matches[1] ?? []);
            $problemMap = [];

            foreach ($htmlFiles as $file) {
                if ($file === 'index.html' || $file === 'practice.html') {
                    continue;
                }

                $pageResponse = Http::timeout(10)->get(self::GITHUB_RAW_BASE . '/' . $file);
                if ($pageResponse->successful()) {
                    $this->parseCategoryHtml($pageResponse->body(), $file, $problemMap);
                }
            }

            return $problemMap;
        } catch (Throwable $e) {
            $this->logger->error('Failed to fetch atcoder categories from GitHub', [
                'category' => 'import',
                'platform' => 'atcoder',
                'source' => self::class,
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function parseCategoryHtml(string $content, string $filename, array &$problemMap): void
    {
        $tagName = '';
        if (preg_match('/<title>(.*?) - AtCoder Categories<\/title>/i', $content, $m)) {
            $tagName = trim($m[1]);
        } elseif (preg_match('/<h1>(.*?)<\/h1>/i', $content, $m)) {
            $tagName = trim($m[1]);
        }

        if (empty($tagName)) {
            $tagName = ucfirst(str_replace('_', ' ', str_replace('.html', '', $filename)));
        }

        if (preg_match('/const PROBLEMS = (\[.*?\]);/s', $content, $m)) {
            $problems = json_decode($m[1], true);
            if (is_array($problems)) {
                foreach ($problems as $p) {
                    $probId = (string) ($p['id'] ?? '');
                    if (empty($probId)) {
                        continue;
                    }

                    $difficulty = isset($p['difficulty']) ? (int) $p['difficulty'] : null;

                    if (!isset($problemMap[$probId])) {
                        $problemMap[$probId] = [
                            'rating' => $difficulty,
                            'tags' => [],
                        ];
                    }

                    if ($difficulty !== null && $problemMap[$probId]['rating'] === null) {
                        $problemMap[$probId]['rating'] = $difficulty;
                    }

                    if (!in_array($tagName, $problemMap[$probId]['tags'], true)) {
                        $problemMap[$probId]['tags'][] = $tagName;
                    }
                }
            }
        }
    }
}
