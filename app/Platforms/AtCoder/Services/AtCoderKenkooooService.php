<?php

namespace App\Platforms\AtCoder\Services;

use App\Services\ApplicationLogger;
use Illuminate\Support\Facades\Http;

class AtCoderKenkooooService
{
    private const BASE_URL = 'https://kenkoooo.com/atcoder';

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

                        // $title = $this->formatEnglishTitle($contestId, $rawTitle);

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
        } catch (\Throwable $e) {
            app(ApplicationLogger::class)->error('Kenkoooo API contests fetch failed', [
                'category' => 'scraper',
                'platform' => 'atcoder',
                'source' => self::class,
                'message' => $e->getMessage(),
            ], $e);
        }

        return $contests;
    }

    private function formatEnglishTitle(string $contestId, string $rawTitle): string
    {
        if (preg_match('/[（\(]\s*(AtCoder\s+[^）\)]+)[）\)]/u', $rawTitle, $m)) {
            return trim($m[1]);
        }

        $lowerId = strtolower($contestId);

        if (preg_match('/^abc(\d+)/', $lowerId, $m)) {
            return 'AtCoder Beginner Contest ' . (int) $m[1];
        }
        if (preg_match('/^arc(\d+)/', $lowerId, $m)) {
            return 'AtCoder Regular Contest ' . (int) $m[1];
        }
        if (preg_match('/^agc(\d+)/', $lowerId, $m)) {
            return 'AtCoder Grand Contest ' . (int) $m[1];
        }
        if (preg_match('/^ahc(\d+)/', $lowerId, $m)) {
            return 'AtCoder Heuristic Contest ' . (int) $m[1];
        }

        $dictionary = [
            'プログラミングコンテスト' => ' Programming Contest ',
            'プログラミング' => ' Programming ',
            'コンテスト' => ' Contest ',
            'ハーフマラソン' => ' Half Marathon ',
            'マラソン' => ' Marathon ',
            '決勝' => ' Finals ',
            '予選' => ' Qualifier ',
            '本戦' => ' Main Round ',
            '夏' => ' Summer ',
            '秋' => ' Autumn ',
            '冬' => ' Winter ',
            '春' => ' Spring ',
            '第' => ' Round ',
            '回' => ' ',
            '学生' => ' Student ',
            '選手権' => ' Championship ',
            '日本橋' => ' Nihonbashi ',
            'ユニークビジョン' => ' Unique Vision ',
            '日本最強' => ' Japan Strongest ',
            '入門' => ' Introduction ',
        ];

        $translated = strtr($rawTitle, $dictionary);
        $clean = preg_replace('/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', '', $translated);
        $clean = trim((string) preg_replace('/\s+/', ' ', $clean));

        return $clean !== '' ? $clean : $contestId;
    }
}
