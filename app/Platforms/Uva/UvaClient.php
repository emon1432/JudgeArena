<?php

namespace App\Platforms\Uva;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UvaClient
{
    private const UHUNT_API = 'http://uhunt.onlinejudge.org/api';
    private const UVA_URL = 'https://uva.onlinejudge.org';
    private const TIMEOUT = 60;

    /**
     * Fetch user profile from UHunt
     */
    public function fetchProfile(string $handle): array
    {
        try {
            $uid = $this->resolveUserId($handle);
            if ($uid === null) {
                return $this->emptyProfile($handle);
            }

            $rankEntry = $this->fetchRankEntry($uid);

            $totalSolved = is_array($rankEntry) && is_numeric($rankEntry['ac'] ?? null)
                ? (int) $rankEntry['ac']
                : 0;
            $totalSubmissions = is_array($rankEntry) && is_numeric($rankEntry['nos'] ?? null)
                ? (int) $rankEntry['nos']
                : 0;

            if ($totalSolved === 0 || $totalSubmissions === 0) {
                $subsData = $this->fetchSubsUserPayload($uid);
                $subs = $subsData['subs'] ?? [];

                if (is_array($subs)) {
                    $totalSubmissions = count($subs);
                    $uniqueAcProblems = [];

                    foreach ($subs as $sub) {
                        if (! is_array($sub) || count($sub) < 3) {
                            continue;
                        }

                        $problemId = $sub[1] ?? null;
                        $verdict = $sub[2] ?? null;

                        if ($problemId !== null && (int) $verdict === 90) {
                            $uniqueAcProblems[(string) $problemId] = true;
                        }
                    }

                    $totalSolved = count($uniqueAcProblems);
                }
            }

            return [
                'handle' => $handle,
                'user_id' => (string) $uid,
                'name' => $rankEntry['name'] ?? null,
                'uname' => $rankEntry['username'] ?? null,
                'total_solved' => $totalSolved,
                'submissions' => $totalSubmissions,
                'rank' => is_numeric($rankEntry['rank'] ?? null) ? (int) $rankEntry['rank'] : null,
                'ranking' => is_numeric($rankEntry['rank'] ?? null) ? (int) $rankEntry['rank'] : null,
                'raw' => [
                    'rank_entry' => $rankEntry,
                ],
            ];
        } catch (\Exception $e) {
            Log::warning("UVa profile fetch failed for {$handle}: {$e->getMessage()}");
            return $this->emptyProfile($handle);
        }
    }

    /**
     * Fetch all submissions for a user
     */
    public function fetchSubmissions(string $handle): array
    {
        try {
            $uid = $this->resolveUserId($handle);
            if ($uid === null) {
                throw new \RuntimeException('UVa user not found');
            }

            $data = $this->fetchSubsUserPayload($uid);
            return $data['subs'] ?? [];
        } catch (\Exception $e) {
            Log::error("UVa submissions fetch failed for {$handle}: {$e->getMessage()}");
            throw $e;
        }
    }

    private function resolveUserId(string $handle): ?int
    {
        if (ctype_digit($handle)) {
            return (int) $handle;
        }

        try {
            $response = Http::withoutVerifying()
                ->retry(2, 1000)
                ->timeout(self::TIMEOUT)
                ->get(self::UHUNT_API . '/uname2uid/' . urlencode($handle));

            if (! $response->ok()) {
                return null;
            }

            $uid = trim((string) $response->body());
            if (! ctype_digit($uid) || $uid === '0') {
                return null;
            }

            return (int) $uid;
        } catch (\Exception $e) {
            Log::warning("UVa uname2uid failed for {$handle}: {$e->getMessage()}");
            return null;
        }
    }

    private function fetchRankEntry(int $uid): ?array
    {
        try {
            $response = Http::withoutVerifying()
                ->retry(2, 1000)
                ->timeout(self::TIMEOUT)
                ->get(self::UHUNT_API . '/ranklist/' . $uid . '/0/0');

            if (! $response->ok()) {
                return null;
            }

            $data = $response->json();
            if (! is_array($data) || empty($data) || ! is_array($data[0])) {
                return null;
            }

            return $data[0];
        } catch (\Exception $e) {
            Log::warning("UVa ranklist fetch failed for uid {$uid}: {$e->getMessage()}");
            return null;
        }
    }

    private function fetchSubsUserPayload(int $uid): array
    {
        $response = Http::withoutVerifying()
            ->retry(2, 1000)
            ->timeout(self::TIMEOUT)
            ->get(self::UHUNT_API . '/subs-user/' . $uid);

        if (! $response->ok()) {
            throw new \RuntimeException('Failed to fetch UVa subs-user payload');
        }

        $data = $response->json();
        return is_array($data) ? $data : [];
    }

    private function emptyProfile(string $handle): array
    {
        return [
            'handle' => $handle,
            'total_solved' => 0,
            'submissions' => 0,
            'rank' => null,
            'ranking' => null,
            'user_id' => $handle,
        ];
    }

    /**
     * Fetch problem mapping (cached)
     */
    public function fetchProblems(): array
    {
        try {
            $response = Http::withoutVerifying()->timeout(30)->get(self::UHUNT_API . '/p');

            if (! $response->ok()) {
                return [];
            }

            $problems = $response->json();
            $mapping = [];

            foreach ($problems as $problem) {
                $mapping[$problem[0]] = [
                    'name' => $problem[1],
                    'number' => $problem[0],
                ];
            }

            return $mapping;
        } catch (\Exception $e) {
            Log::warning("UVa problem mapping fetch failed: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Normalize submission verdict
     */
    public static function normalizeVerdict(int $statusCode): string
    {
        return match ($statusCode) {
            90 => 'AC',
            70 => 'WA',
            30 => 'CE',
            40 => 'RE',
            50 => 'TLE',
            60 => 'MLE',
            default => 'OTH',
        };
    }

    /**
     * Map language ID to name
     */
    public static function getLanguage(int $langId): string
    {
        return match ($langId) {
            1 => 'ANSI C',
            2 => 'Java',
            3 => 'C++',
            4 => 'Pascal',
            5 => 'C++11',
            6 => 'Python',
            default => 'Unknown',
        };
    }

    /**
     * Get problem URL
     */
    public static function getProblemUrl(int $problemId): string
    {
        return self::UVA_URL . '/index.php?option=com_onlinejudge&Itemid=8&page=show_problem&problem=' . $problemId;
    }
}
