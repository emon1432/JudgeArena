<?php

namespace App\Platforms\AtCoder\Services;

use App\Services\ApplicationLogger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Throwable;

class AtCoderKenkooooService
{
    private const BASE_URL = 'https://kenkoooo.com/atcoder';
    private const CACHE_TTL_IN_SECONDS = 60 * 60 * 24 * 7;

    /**
     * Fetch all AtCoder contests from Kenkoooo API resource.
     */
    public function getContests(): array
    {
        $contests = [];

        try {
            $response = Http::timeout(20)->get(self::BASE_URL . '/resources/contests.json');

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    foreach ($data as $item) {
                        if (!isset($item['id'])) {
                            continue;
                        }

                        $contestId = (string) $item['id'];
                        $rawTitle = (string) ($item['title'] ?? $contestId);

                        $startEpoch = (int) ($item['start_epoch_second'] ?? 0);
                        $date = $startEpoch > 0 ? date('Y-m-d H:i:s', $startEpoch) : '';

                        $durationSec = (int) ($item['duration_second'] ?? 0);
                        $hours = (int) ($durationSec / 3600);
                        $minutes = (int) (($durationSec % 3600) / 60);
                        $duration = sprintf('%02d:%02d', $hours, $minutes);

                        $rateChange = (string) ($item['rate_change'] ?? '-');

                        $type = 'normal';
                        $lowerId = strtolower($contestId);
                        if (str_starts_with($lowerId, 'abc')) {
                            $type = 'ABC';
                        } elseif (str_starts_with($lowerId, 'arc')) {
                            $type = 'ARC';
                        } elseif (str_starts_with($lowerId, 'agc')) {
                            $type = 'AGC';
                        } elseif (str_starts_with($lowerId, 'ahc')) {
                            $type = 'AHC';
                        } elseif (str_contains($lowerId, 'adt_')) {
                            $type = 'daily_training';
                        }

                        $contests[] = [
                            'id' => $contestId,
                            'title' => $rawTitle,
                            'url' => 'https://atcoder.jp/contests/' . $contestId,
                            'date' => $date,
                            'duration' => $duration,
                            'rate_change' => $rateChange,
                            'type' => $type,
                        ];
                    }
                }
            }
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('Kenkoooo API contests fetch failed', [
                'category' => 'scraper',
                'platform' => 'atcoder',
                'source' => self::class,
                'message' => $e->getMessage(),
            ], $e);
        }

        return $contests;
    }

    /**
     * Fetch problems for a specific contest from Kenkoooo API resources.
     */
    public function getContestProblems(string $contestId): array
    {
        try {
            $contestProblemMap = Cache::remember('kenkoooo_contest_problem_map', self::CACHE_TTL_IN_SECONDS, function () {
                $response = Http::timeout(20)->get(self::BASE_URL . '/resources/contest-problem.json');
                return $response->successful() && is_array($response->json()) ? $response->json() : [];
            });

            $mergedProblems = Cache::remember('kenkoooo_merged_problems', self::CACHE_TTL_IN_SECONDS, function () {
                $response = Http::timeout(20)->get(self::BASE_URL . '/resources/merged-problems.json');
                $data = $response->successful() && is_array($response->json()) ? $response->json() : [];
                $indexed = [];
                foreach ($data as $p) {
                    if (isset($p['id'])) {
                        $indexed[$p['id']] = $p;
                    }
                }
                return $indexed;
            });

            $problemModels = Cache::remember('kenkoooo_problem_models', self::CACHE_TTL_IN_SECONDS, function () {
                $response = Http::timeout(20)->get(self::BASE_URL . '/resources/problem-models.json');
                return $response->successful() && is_array($response->json()) ? $response->json() : [];
            });

            $problems = [];

            foreach ($contestProblemMap as $cp) {
                if (($cp['contest_id'] ?? '') === $contestId) {
                    $problemId = (string) ($cp['problem_id'] ?? '');
                    $position = (string) ($cp['problem_index'] ?? '');

                    $meta = $mergedProblems[$problemId] ?? null;
                    $model = $problemModels[$problemId] ?? null;

                    $rawTitle = (string) ($meta['title'] ?? ($meta['name'] ?? $problemId));
                    $cleanTitle = preg_replace('/^[A-Z1-9]\.\s*/', '', $rawTitle);

                    $score = $meta['point'] ?? ($model['raw_point'] ?? null);
                    $execTimeMs = isset($meta['execution_time']) ? (int) $meta['execution_time'] : null;

                    $problems[] = [
                        'id' => $problemId,
                        'contest_id' => $contestId,
                        'title' => $cleanTitle,
                        'position' => $position,
                        'score' => $score,
                        'rating' => $model['difficulty'] ?? null,
                        'time_limit' => $execTimeMs !== null ? $execTimeMs . ' ms' : null,
                        'memory_limit' => null,
                        'url' => 'https://atcoder.jp/contests/' . $contestId . '/tasks/' . $problemId,
                    ];
                }
            }

            return $problems;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->error('Kenkoooo API contest problems fetch failed', [
                'category' => 'scraper',
                'platform' => 'atcoder',
                'source' => self::class,
                'contest_id' => $contestId,
                'message' => $e->getMessage(),
            ], $e);

            return [];
        }
    }
}
