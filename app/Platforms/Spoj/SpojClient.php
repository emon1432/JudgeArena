<?php

namespace App\Platforms\Spoj;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\CarbonImmutable;

class SpojClient
{
    private const BASE_URL = 'https://www.spoj.com';

    /**
     * Fetch user profile from SPOJ
     * Handles Cloudflare challenge by retrying with longer timeout
     *
     * ⚠️ Note: SPOJ uses aggressive Cloudflare protection that may block requests.
     * This implementation attempts to bypass it but success is not guaranteed.
     */
    public function fetchProfile(string $handle): array
    {
        $url = self::BASE_URL . '/users/' . urlencode($handle) . '/';

        $html = $this->getWithCloudflareHandling($url);

        // Check for user not found
        if (
            stripos($html, 'User does not exist') !== false ||
            stripos($html, 'Page not found') !== false ||
            stripos($html, 'History of submissions') === false
        ) {
            throw new \RuntimeException('SPOJ user not found');
        }

        // Parse HTML with Crawler for reliable data extraction
        $crawler = new Crawler($html);

        // Extract key metrics from legacy <dt>/<dd> profile layout
        $totalSolved = 0;
        $points = null;
        try {
            $crawler->filter('dt')->each(function (Crawler $dt) use (&$totalSolved, &$points) {
                $text = trim(strip_tags($dt->html())); // Remove HTML tags and &nbsp;
                if (stripos($text, 'Problems solved') !== false) {
                    $dd = $dt->nextAll()->filter('dd')->first();
                    if ($dd->count() > 0) {
                        $totalSolved = (int) trim($dd->text());
                    }
                } elseif (stripos($text, 'Points') !== false) {
                    $dd = $dt->nextAll()->filter('dd')->first();
                    if ($dd->count() > 0) {
                        $pointsText = str_replace(',', '', trim($dd->text()));
                        if (preg_match('/-?\d+(?:\.\d+)?/', $pointsText, $pointsMatch)) {
                            $points = (int) round((float) $pointsMatch[0]);
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            Log::warning("SPOJ: Failed to parse problems solved: {$e->getMessage()}");
        }

        $rank = null;

        // Extract rank and points from current profile layout, e.g.
        // "World Rank: #8 (851.6 points)"
        try {
            $leftProfileText = $crawler->filter('#user-profile-left')->count() > 0
                ? preg_replace('/\s+/', ' ', trim($crawler->filter('#user-profile-left')->text()))
                : '';

            if (
                ! empty($leftProfileText) &&
                preg_match('/World\s*Rank:\s*#\s*([\d,]+)\s*\(([-\d.,]+)\s*points?\)/i', $leftProfileText, $rankPointsMatch)
            ) {
                $rank = (int) str_replace(',', '', $rankPointsMatch[1]);
                $points = (int) round((float) str_replace(',', '', $rankPointsMatch[2]));
            } elseif (
                ! empty($leftProfileText) &&
                preg_match('/World\s*Leader\s*\(([-\d.,]+)\s*points?\)/i', $leftProfileText, $leaderPointsMatch)
            ) {
                $rank = 1;
                $points = (int) round((float) str_replace(',', '', $leaderPointsMatch[1]));
            }
        } catch (\Exception $e) {
            Log::warning("SPOJ: Failed to parse world rank and points from profile-left: {$e->getMessage()}");
        }

        // Rank fallback for older formats
        if ($rank === null && preg_match('/Rank:\s*(\d+)/i', $html, $rankMatch)) {
            $rank = (int) $rankMatch[1];
        } elseif ($rank === null && preg_match('/World\s*Rank:\s*#\s*([\d,]+)/i', $html, $worldRankMatch)) {
            $rank = (int) str_replace(',', '', $worldRankMatch[1]);
        } elseif ($rank === null && preg_match('/World\s*Leader/i', $html)) {
            $rank = 1;
        }

        // Points fallback for older formats
        if ($points === null && preg_match('/\(([-\d.,]+)\s*points?\)/i', $html, $pointsMatch)) {
            $points = (int) round((float) str_replace(',', '', $pointsMatch[1]));
        }

        // Extract join date
        $joinDate = null;
        if (preg_match('/Member\s+since:\s*(\d{4}-\d{2}-\d{2})/i', $html, $joinMatch)) {
            $joinDate = $joinMatch[1];
        }

        // Extract problem list (for later use in submissions)
        $problemSlugs = $this->extractProblemSlugs($html, $handle);

        return [
            'handle' => $handle,
            'total_solved' => $totalSolved,
            'points' => $points,
            'rank' => $rank,
            'join_date' => $joinDate,
            'problem_slugs' => $problemSlugs,
        ];
    }

    /**
     * Get content with Cloudflare bypass support
     *
     * Tries FlareSolverr first (if available), falls back to direct HTTP
     */
    private function getWithCloudflareHandling(string $url, int $maxRetries = 1): string
    {
        // Try FlareSolverr first if configured
        $flareSolverrUrl = config('platforms.flaresolverr_url');

        if ($flareSolverrUrl) {
            try {
                Log::info("SPOJ: Attempting FlareSolverr bypass for {$url}");
                return $this->getViaFlareSolverr($url, $flareSolverrUrl);
            } catch (\Exception $e) {
                Log::warning("SPOJ: FlareSolverr failed ({$e->getMessage()}), falling back to direct HTTP");
            }
        }

        // Fallback: Direct HTTP request (will likely be blocked)
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->timeout(15)
                ->get($url);

                if (!$response->successful()) {
                    throw new \RuntimeException('HTTP ' . $response->status() . ' - Cloudflare protection blocking access');
                }

                $html = $response->body();

                // Check if Cloudflare challenge page
                if (str_contains($html, 'Just a moment') || str_contains($html, '_cf_chl_opt')) {
                    throw new \RuntimeException('Cloudflare challenge page detected - automated access blocked');
                }

                return $html;

            } catch (\Exception $e) {
                if ($attempt >= $maxRetries) {
                    throw new \RuntimeException(
                        'SPOJ profile unavailable: ' . $e->getMessage() . '. ' .
                        'SPOJ uses Cloudflare protection that blocks automated requests. ' .
                        ($flareSolverrUrl ? 'FlareSolverr also failed. ' : 'Install FlareSolverr to bypass. ') .
                        'This is a known limitation.'
                    );
                }
                sleep(2);
            }
        }

        throw new \RuntimeException('Failed to fetch SPOJ content');
    }

    /**
     * Fetch content via FlareSolverr proxy
     */
    private function getViaFlareSolverr(string $url, string $flareSolverrUrl): string
    {
        $response = Http::timeout(60)->post($flareSolverrUrl . '/v1', [
            'cmd' => 'request.get',
            'url' => $url,
            'maxTimeout' => 60000,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('FlareSolverr request failed: HTTP ' . $response->status());
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'ok') {
            throw new \RuntimeException('FlareSolverr error: ' . ($data['message'] ?? 'Unknown error'));
        }

        $html = $data['solution']['response'] ?? '';

        if (empty($html)) {
            throw new \RuntimeException('FlareSolverr returned empty response');
        }

        // Verify we got actual content, not Cloudflare page
        if (str_contains($html, 'Just a moment') || str_contains($html, '_cf_chl_opt')) {
            throw new \RuntimeException('FlareSolverr still returned Cloudflare challenge page');
        }

        Log::info("SPOJ: Successfully fetched via FlareSolverr");
        return $html;
    }

    /**
     * Extract problem slugs that user has submissions for
     */
    private function extractProblemSlugs(string $html, string $handle): array
    {
        try {
            $crawler = new Crawler($html);
            $problemSlugs = [];

            $crawler->filter('td a')->each(function (Crawler $node) use ($handle, &$problemSlugs) {
                $href = $node->attr('href');
                if ($href && preg_match('/\/status\/.*,' . preg_quote($handle, '/') . '\//', $href)) {
                    $text = trim($node->text());
                    if (!empty($text)) {
                        $problemSlugs[] = $text;
                    }
                }
            });

            return array_unique($problemSlugs);
        } catch (\Exception $e) {
            Log::warning("Failed to extract SPOJ problem slugs: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch submissions for a user (paginated)
     */
    public function fetchSubmissions(string $handle, int $limit = 500): array
    {
        try {
            $submissions = [];
            $start = 0;
            $perPage = 20;
            $prevId = -1;
            $maxPages = (int) ceil($limit / $perPage);

            for ($page = 0; $page < $maxPages; $page++) {
                $url = self::BASE_URL . '/status/' . urlencode($handle) . '/all/start=' . $start;
                $start += $perPage;

                try {
                    $html = $this->getWithCloudflareHandling($url, 2); // Fewer retries for submission pages
                } catch (\Exception $e) {
                    Log::warning("SPOJ submissions page {$page} failed for {$handle}: {$e->getMessage()}");
                    break;
                }

                $crawler = new Crawler($html);
                $tbody = $crawler->filter('tbody');

                if ($tbody->count() === 0 || $tbody->filter('tr')->count() <= 1) {
                    // No more submissions
                    break;
                }

                $pageSubmissions = [];
                $currentId = null;

                $tbody->filter('tr')->each(function (Crawler $row, $index) use (&$pageSubmissions, &$currentId, &$prevId, $handle) {
                    try {
                        $cells = $row->filter('td');
                        if ($cells->count() < 13) {
                            return;
                        }

                        // Submission ID
                        $subId = trim($cells->eq(1)->text());
                        if ($index === 0) {
                            $currentId = $subId;
                            if ($currentId === $prevId) {
                                // Duplicate page, stop
                                return;
                            }
                        }

                        // Time of submission
                        $timeText = trim($cells->eq(3)->filter('span')->first()->text());
                        $submittedAt = CarbonImmutable::parse($timeText);

                        // Problem
                        $problemLink = $cells->eq(5)->filter('a');
                        if ($problemLink->count() === 0) {
                            return;
                        }

                        $problemName = trim($problemLink->text());
                        $problemHref = $problemLink->attr('href');
                        $problemUrl = self::BASE_URL . $problemHref;

                        // Status
                        $statusHtml = $cells->eq(6)->html();
                        $status = $this->normalizeStatus($statusHtml);

                        // Language
                        $language = trim($cells->eq(12)->filter('span')->first()->text(''));

                        $pageSubmissions[] = [
                            'submission_id' => $subId,
                            'submitted_at' => $submittedAt,
                            'problem_name' => $problemName,
                            'problem_url' => $problemUrl,
                            'status' => $status,
                            'language' => $language,
                        ];
                    } catch (\Exception $e) {
                        // Skip problematic rows
                    }
                });

                if (!empty($pageSubmissions)) {
                    $submissions = array_merge($submissions, $pageSubmissions);
                    $prevId = $currentId;
                } else {
                    break;
                }

                // Small delay to avoid triggering Cloudflare
                usleep(1500000); // 1.5 second delay
            }

            return $submissions;
        } catch (\Exception $e) {
            Log::error("SPOJ fetchSubmissions failed for {$handle}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Fetch problem details (tags, author)
     */
    public function fetchProblemDetails(string $problemUrl): array
    {
        try {
            $html = $this->getWithCloudflareHandling($problemUrl, 2);
            $crawler = new Crawler($html);

            // Extract tags
            $tags = [];
            $crawler->filter('#problem-tags span')->each(function (Crawler $node) use (&$tags) {
                $tagText = trim($node->text());
                if (!empty($tagText) && str_starts_with($tagText, '#')) {
                    $tags[] = substr($tagText, 1); // Remove #
                }
            });

            // Extract problem author
            $author = null;
            $metaTable = $crawler->filter('table#problem-meta');
            if ($metaTable->count() > 0) {
                $authorLink = $metaTable->filter('a')->first();
                if ($authorLink->count() > 0) {
                    $authorHref = $authorLink->attr('href');
                    if (str_starts_with($authorHref, '/users/')) {
                        $author = str_replace('/users/', '', $authorHref);
                    }
                }
            }

            return [
                'tags' => $tags,
                'author' => $author,
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to fetch SPOJ problem details: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Normalize SPOJ status
     */
    private function normalizeStatus(string $statusHtml): string
    {
        if (stripos($statusHtml, 'accepted') !== false) {
            return 'AC';
        } elseif (stripos($statusHtml, 'wrong') !== false) {
            return 'WA';
        } elseif (stripos($statusHtml, 'compilation') !== false) {
            return 'CE';
        } elseif (stripos($statusHtml, 'runtime') !== false) {
            return 'RE';
        } elseif (stripos($statusHtml, 'time limit') !== false) {
            return 'TLE';
        }
        return 'OTH';
    }

    /**
     * Check if user profile exists
     */
    public function profileExists(string $handle): bool
    {
        try {
            $this->fetchProfile($handle);
            return true;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return false;
            }
            throw $e;
        }
    }
}
