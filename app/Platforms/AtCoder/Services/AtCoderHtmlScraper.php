<?php

namespace App\Platforms\AtCoder\Services;

use App\Services\ApplicationLogger;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AtCoderHtmlScraper
{
    // ১. ডিলে বাড়িয়ে ১৫০০-২৫০০ms এর মধ্যে র‍্যান্ডমাইজ করা হয়েছে (Anti-Bot Bypass)
    private const MIN_DELAY_MS = 1200;
    private const MAX_DELAY_MS = 2500;
    private static int $lastRequestTime = 0;

    private function baseUrl(): string
    {
        return rtrim((string) config('platforms.atcoder.base_url', 'https://atcoder.jp'), '/');
    }

    public function getContests(?callable $pageProcessor = null, bool $fullSync = false): array
    {
        $contests = [];

        $contests = array_merge($contests, $this->getNormalContests($pageProcessor, $fullSync));
        $contests = array_merge($contests, $this->getWeekDayContests($pageProcessor, $fullSync));
        $contests = array_merge($contests, $this->getDailyTrainingContests($pageProcessor, $fullSync));
        $contests = array_merge($contests, $this->getPermanentContests());
        $contests = array_merge($contests, $this->getHiddenContests());

        return $contests;
    }

    public function getStandings(string $contestId): array
    {
        return $this->fetchJson($this->baseUrl() . '/contests/' . $contestId . '/standings/json');
    }

    public function getStandingsVirtual(string $contestId): array
    {
        return $this->fetchJson($this->baseUrl() . '/contests/' . $contestId . '/standings/virtual/json');
    }

    public function getResults(string $contestId): array
    {
        return $this->fetchJson($this->baseUrl() . '/contests/' . $contestId . '/results/json');
    }

    public function getSubmissions(
        string $contestId,
        ?string $user = null,
        ?string $stopSubmissionId = null,
    ): array {
        $submissions = [];
        $page = 1;
        $reachedStop = false;

        while (true) {
            $url = $user !== null && $user !== ''
                ? $this->baseUrl() . '/contests/' . $contestId . '/submissions?f.User=' . urlencode($user) . '&page=' . $page
                : $this->baseUrl() . '/contests/' . $contestId . '/submissions?page=' . $page;

            $html = $this->fetchPage($url);

            if ($html === '') {
                break;
            }

            $doc = new DOMDocument();
            @$doc->loadHTML($html);

            $xpath = new DOMXPath($doc);
            $rows = $xpath->query('//table//tbody//tr');

            if ($rows->length === 0) {
                break;
            }

            foreach ($rows as $row) {
                $cells = $xpath->query('.//td', $row);

                if ($cells->length < 2) {
                    continue;
                }

                $time = trim($cells->item(0)?->textContent ?? '');

                $taskLink = $xpath->query('.//a', $cells->item(1))->item(0);
                $taskId = null;
                $taskTitle = null;
                $taskUrl = null;

                if ($taskLink instanceof DOMElement) {
                    $taskHref = $taskLink->getAttribute('href');
                    $taskId = basename($taskHref);
                    $taskTitle = trim($taskLink->textContent);
                    $taskUrl = $this->baseUrl() . $taskHref;
                } else {
                    $taskTitle = trim($cells->item(1)?->textContent ?? '');
                }

                $userLink = $xpath->query('.//a', $cells->item(2))->item(0);
                $username = $userLink instanceof DOMElement
                    ? trim(basename($userLink->getAttribute('href')))
                    : trim($cells->item(2)?->textContent ?? '');

                $langLink = $xpath->query('.//a', $cells->item(3))->item(0);
                $language = $langLink instanceof DOMElement
                    ? trim($langLink->textContent)
                    : trim($cells->item(3)?->textContent ?? '');

                $scoreTd = $cells->item(4);
                $score = null;
                $submissionId = null;

                if ($scoreTd instanceof DOMElement) {
                    $scoreText = trim($scoreTd->textContent ?? '');
                    if (is_numeric($scoreText)) {
                        $score = (int) $scoreText;
                    } elseif ($scoreText !== '') {
                        $score = $scoreText;
                    }

                    $dataId = $scoreTd->getAttribute('data-id');
                    if ($dataId !== '') {
                        $submissionId = $dataId;
                    }
                }

                $codeSize = trim($cells->item(5)?->textContent ?? '');
                $statusSpan = $xpath->query('.//span', $cells->item(6))->item(0);
                $status = $statusSpan instanceof DOMElement
                    ? trim($statusSpan->textContent)
                    : trim($cells->item(6)?->textContent ?? '');

                $execTime = trim($cells->item(7)?->textContent ?? '');
                $memory = trim($cells->item(8)?->textContent ?? '');
                $detailUrl = null;

                if ($submissionId !== null) {
                    $detailUrl = $this->baseUrl() . '/contests/' . $contestId . '/submissions/' . $submissionId;
                } else {
                    $detailLink = $xpath->query('.//a[contains(@class,"submission-details-link")]', $cells->item(9))->item(0);
                    if ($detailLink instanceof DOMElement) {
                        $detailHref = $detailLink->getAttribute('href');
                        $detailUrl = $this->baseUrl() . $detailHref;
                    }

                    if ($submissionId === null && $detailUrl !== null) {
                        $submissionId = basename($detailUrl);
                    }
                }

                if ($stopSubmissionId !== null && $submissionId === $stopSubmissionId) {
                    $reachedStop = true;
                    break;
                }

                $submissions[] = [
                    'submission_id' => $submissionId,
                    'time' => $time,
                    'task_id' => $taskId,
                    'task_title' => $taskTitle,
                    'task_url' => $taskUrl,
                    'username' => $username,
                    'language' => $language,
                    'score' => $score,
                    'code_size' => $codeSize,
                    'result' => $status,
                    'status' => $status,
                    'exec_time' => $execTime,
                    'memory' => $memory,
                    'detail_url' => $detailUrl,
                ];
            }

            if ($reachedStop) {
                break;
            }

            $page++;
            $this->respectRateLimit();
        }

        return [
            'result' => $submissions,
            'reached_stop' => $reachedStop,
        ];
    }

    public function getTasks(string $contestId): array
    {
        $html = $this->fetchPage($this->baseUrl() . '/contests/' . $contestId . '/tasks?lang=en');
        if (empty($html)) {
            return ['result' => []];
        }

        $doc = new DOMDocument;
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);

        $tasks = [];
        $rows = $xpath->query('//table[contains(@class, "table")]//tbody//tr | //table//tbody//tr | //table//tr');

        foreach ($rows as $row) {
            $cells = $xpath->query('.//td', $row);
            if ($cells->length < 2) {
                continue;
            }

            $position = trim($cells->item(0)?->nodeValue ?? '');
            $link = $xpath->query('.//a[contains(@href, "/tasks/")]', $cells->item(1))->item(0)
                ?? $xpath->query('.//a', $cells->item(0))->item(0);

            if (!$link instanceof DOMElement) {
                continue;
            }

            $href = $link->getAttribute('href');
            $taskId = basename($href);

            if (empty($taskId) || str_contains($href, '/tasks/archive')) {
                continue;
            }

            $title = trim($link->nodeValue);
            $timeLimit = $cells->length > 2 ? trim($cells->item(2)?->nodeValue ?? '') : '';
            $memoryLimit = $cells->length > 3 ? trim($cells->item(3)?->nodeValue ?? '') : '';
            $taskUrl = $this->baseUrl() . $href;
            $score = $this->getTaskScore($contestId, $taskId);

            $tasks[] = [
                'id' => $taskId,
                'contest_id' => $contestId,
                'title' => $title,
                'position' => $position,
                'score' => $score,
                'time_limit' => $timeLimit,
                'memory_limit' => $memoryLimit,
                'url' => $taskUrl,
            ];
        }

        return ['result' => $tasks];
    }

    public function getTaskScore(string $contestId, string $taskId): ?float
    {
        try {
            $html = $this->fetchPage($this->baseUrl() . '/contests/' . $contestId . '/tasks/' . $taskId . '?lang=en');
            if ($html === '') {
                return null;
            }

            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            $nodes = $xpath->query('//p[contains(text(), "Score") or contains(text(), "配点") or contains(., "Score")]');
            foreach ($nodes as $node) {
                $text = trim($node->textContent);
                if (preg_match('/(?:Score|配点)\s*[:：]?\s*(\d+)/i', $text, $matches)) {
                    return (float) $matches[1];
                }
                $varNode = $xpath->query('.//var', $node)->item(0);
                if ($varNode) {
                    $varText = trim($varNode->textContent);
                    if (is_numeric($varText)) {
                        return (float) $varText;
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    public function getUserProfile(string $username): array
    {
        $types = ['algo', 'heuristic'];
        $result = [];

        foreach ($types as $index => $type) {
            $html = $this->fetchPage($this->baseUrl() . '/users/' . $username . '?contestType=' . $type);

            $doc = new DOMDocument;
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            if ($index === 0) {
                $avatarNode = $xpath->query('//div[contains(@class, "col-md-3")]//img[contains(@class, "avatar")]')->item(0);
                $avatarUrl = $avatarNode instanceof DOMElement ? $avatarNode->getAttribute('src') : null;

                $countryNode = $xpath->query('//div[contains(@class, "col-md-3")]//table//tr[th[contains(text(), "Country/Region")]]/td')->item(0);
                $country = $countryNode ? trim($countryNode->nodeValue) : null;

                $birthYearNode = $xpath->query('//div[contains(@class, "col-md-3")]//table//tr[th[contains(text(), "Birth Year")]]/td')->item(0);
                $birthYear = $birthYearNode ? trim($birthYearNode->nodeValue) : null;

                $twitterNode = $xpath->query('//div[contains(@class, "col-md-3")]//table//tr[th[contains(text(), "X(Twitter) ID")]]/td/a')->item(0);
                $twitterId = $twitterNode ? trim($twitterNode->textContent) : null;

                $topcoderNode = $xpath->query('//div[contains(@class, "col-md-3")]//table//tr[th[contains(text(), "TopCoder ID")]]/td/a')->item(0);
                $topcoderId = $topcoderNode ? trim($topcoderNode->textContent) : null;

                $codeforcesNode = $xpath->query('//div[contains(@class, "col-md-3")]//table//tr[th[contains(text(), "Codeforces ID")]]/td/a')->item(0);
                $codeforcesId = $codeforcesNode ? trim($codeforcesNode->textContent) : null;

                $affiliationNode = $xpath->query('//div[contains(@class, "col-md-3")]//table//tr[th[contains(text(), "Affiliation")]]/td')->item(0);
                $affiliation = $affiliationNode ? trim($affiliationNode->textContent) : null;

                $result = [
                    'username' => $username,
                    'avatar_url' => $avatarUrl,
                    'country' => $country,
                    'birth_year' => $birthYear,
                    'twitter_id' => $twitterId,
                    'topcoder_id' => $topcoderId,
                    'codeforces_id' => $codeforcesId,
                    'affiliation' => $affiliation,
                ];
            }

            $rankNode = $xpath->query('//div[contains(@class, "col-md-9")]//table//tr[th[contains(text(), "Rank")]]/td')->item(0);
            $rawRank = $rankNode ? trim($rankNode->nodeValue) : null;

            $ratingNode = $xpath->query('//div[contains(@class, "col-md-9")]//table//tr[th[contains(text(), "Rating")]]/td')->item(0);
            $rawRating = $ratingNode ? trim($ratingNode->nodeValue) : null;

            $highestRatingNode = $xpath->query('//div[contains(@class, "col-md-9")]//table//tr[th[contains(text(), "Highest Rating")]]/td')->item(0);
            $rawHighestRating = $highestRatingNode ? trim($highestRatingNode->nodeValue) : null;

            $ratedMatchesNode = $xpath->query('//div[contains(@class, "col-md-9")]//table//tr[th[contains(text(), "Rated Matches")]]/td')->item(0);
            $rawRatedMatches = $ratedMatchesNode ? trim($ratedMatchesNode->nodeValue) : null;

            $lastCompetedNode = $xpath->query('//div[contains(@class, "col-md-9")]//table//tr[th[contains(text(), "Last Competed")]]/td')->item(0);
            $rawLastCompeted = $lastCompetedNode ? trim($lastCompetedNode->nodeValue) : null;

            $parsedRating = null;
            $isProvisional = false;
            if ($rawRating !== null) {
                if (preg_match('/^(\d+)/', $rawRating, $m)) {
                    $parsedRating = (int) $m[1];
                }
                $isProvisional = str_contains($rawRating, '(Provisional)');
            }

            $parsedHighestRating = null;
            $userTitle = null;
            if ($rawHighestRating !== null) {
                if (preg_match('/^(\d+)/', $rawHighestRating, $m)) {
                    $parsedHighestRating = (int) $m[1];
                }
                $lines = array_values(array_filter(array_map('trim', explode("\n", $rawHighestRating))));
                foreach ($lines as $line) {
                    if ($line !== '' && $line !== '―' && !is_numeric($line) && !str_contains($line, 'to promote') && !str_contains($line, 'Provisional')) {
                        $userTitle = $line;
                        break;
                    }
                }
            }

            $parsedRank = null;
            $percentile = null;
            if ($rawRank !== null) {
                if (preg_match('/^(\d+)/', $rawRank, $m)) {
                    $parsedRank = (int) $m[1];
                }
                if (preg_match('/\(([^)]+)\)/', $rawRank, $m)) {
                    $percentile = $m[1];
                }
            }

            $parsedRatedMatches = null;
            if ($rawRatedMatches !== null && preg_match('/^(\d+)/', $rawRatedMatches, $m)) {
                $parsedRatedMatches = (int) $m[1];
            }

            $cleanLastCompeted = $rawLastCompeted !== null ? str_replace('/', '-', trim($rawLastCompeted)) : null;

            $result['contest_status'][$type] = [
                'rank' => $parsedRank,
                'rank_text' => $rawRank,
                'percentile' => $percentile,
                'rating' => $parsedRating,
                'is_provisional' => $isProvisional,
                'highest_rating' => $parsedHighestRating,
                'user_title' => $userTitle,
                'rated_matches' => $parsedRatedMatches,
                'last_competed' => $cleanLastCompeted,
                'raw_highest_rating' => $rawHighestRating,
            ];
        }

        return ['result' => $result];
    }

    public function getUserRatingHistory(string $username): array
    {
        $types = ['algo', 'heuristic'];
        $history = [];

        foreach ($types as $type) {
            $data = $this->fetchJson($this->baseUrl() . '/users/' . $username . '/history/json?contestType=' . $type);
            foreach ($data as $entry) {
                if (is_array($entry)) {
                    $entry['contest_type'] = $type;
                    $history[] = $entry;
                }
            }
        }

        return ['result' => $history];
    }

    private function scrapeCategoryArchive(string $categoryParam, string $type, ?callable $pageProcessor, bool $fullSync): array
    {
        $contests = [];
        $page = 1;
        $maxPages = null;

        while (true) {
            $query = $categoryParam !== '' ? 'category=' . $categoryParam . '&' : '';
            // lang=en দিয়ে সরাসরি একটি রিকোয়েস্টেই ইংলিশ ডেটা টানা হচ্ছে
            $enUrl = $this->baseUrl() . '/contests/archive?' . $query . 'lang=en&page=' . $page;

            $html = $this->fetchPage($enUrl);
            if (empty($html)) {
                break;
            }

            if ($maxPages === null) {
                $maxPages = $this->extractMaxPages($html);
            }

            $doc = new DOMDocument;
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            $rows = $xpath->query('//table//tbody//tr');
            $pageHasContests = false;
            $pageContests = [];

            foreach ($rows as $row) {
                $cells = $xpath->query('.//td', $row);
                if ($cells->length < 4) {
                    continue;
                }

                $startText = trim($cells->item(0)?->nodeValue ?? '');
                $link = $xpath->query('.//a', $cells->item(1))->item(0);
                if (!$link instanceof DOMElement) {
                    continue;
                }

                $href = $link->getAttribute('href');
                $contestId = basename($href);
                $title = trim($link->nodeValue);
                $duration = trim($cells->item(2)?->nodeValue ?? '');
                $rateChange = trim($cells->item(3)?->nodeValue ?? '');

                $item = [
                    'id' => $contestId,
                    'title' => $title,
                    'url' => $this->baseUrl() . $href,
                    'date' => $startText,
                    'duration' => $duration,
                    'rate_change' => $rateChange,
                    'type' => $type,
                ];

                $contests[] = $item;
                $pageContests[] = $item;
                $pageHasContests = true;
            }

            if (!$pageHasContests) {
                break;
            }

            if ($pageProcessor !== null && !empty($pageContests)) {
                $shouldContinue = $pageProcessor($pageContests, $type);
                if ($shouldContinue === false && !$fullSync) {
                    break;
                }
            }

            if ($maxPages !== null && $page >= $maxPages) {
                break;
            }

            $page++;
            $this->respectRateLimit();
        }
        return $contests;
    }

    private function getNormalContests(?callable $pageProcessor = null, bool $fullSync = false): array
    {
        return $this->scrapeCategoryArchive('', 'normal', $pageProcessor, $fullSync);
    }

    private function getWeekDayContests(?callable $pageProcessor = null, bool $fullSync = false): array
    {
        return $this->scrapeCategoryArchive('20', 'weekday', $pageProcessor, $fullSync);
    }

    private function getPermanentContests(): array
    {
        $contests = [];

        try {
            $htmlEn = $this->fetchPage($this->baseUrl() . '/contests/?lang=en');
            if (empty($htmlEn)) {
                return [];
            }

            $doc = new DOMDocument;
            @$doc->loadHTML($htmlEn);
            $xpath = new DOMXPath($doc);

            $tables = $xpath->query('//table');
            foreach ($tables as $table) {
                $tbody = $xpath->query('.//tbody', $table);
                if ($tbody->length === 0) {
                    continue;
                }

                $rows = $xpath->query('.//tr', $tbody->item(0));

                foreach ($rows as $row) {
                    $cells = $xpath->query('.//td', $row);
                    if ($cells->length < 2) {
                        continue;
                    }

                    $link = $xpath->query('.//a[contains(@href, "/contests/")]', $row)->item(0);
                    if (!$link instanceof DOMElement) {
                        continue;
                    }

                    $href = $link->getAttribute('href');
                    $contestId = basename($href);

                    if (empty($contestId) || str_contains($href, '/contests/archive')) {
                        continue;
                    }

                    $col0Text = trim($cells->item(0)?->nodeValue ?? '');
                    $col1Text = trim($cells->item(1)?->nodeValue ?? '');
                    $col2Text = $cells->length > 2 ? trim($cells->item(2)?->nodeValue ?? '') : '';
                    $col3Text = $cells->length > 3 ? trim($cells->item(3)?->nodeValue ?? '') : '';

                    $title = trim($link->nodeValue);
                    $date = '';
                    $duration = 'Permanent';
                    $rateChange = '';
                    $type = 'permanent';

                    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $col0Text)) {
                        $date = $col0Text;
                        $duration = $col2Text;
                        $rateChange = $col3Text;
                        $type = str_contains(strtolower($contestId), 'adt_') ? 'daily_training' : 'normal';
                    } else {
                        $rateChange = $col1Text;
                        $type = 'permanent';
                        $times = $this->getContestTimesFromDetail($contestId);
                        if (!empty($times['start_time'])) {
                            $date = $times['start_time'];
                        }
                    }

                    $contests[] = [
                        'id' => $contestId,
                        'title' => $title,
                        'url' => $this->baseUrl() . $href,
                        'date' => $date,
                        'duration' => $duration,
                        'rate_change' => $rateChange,
                        'type' => $type,
                    ];
                }
            }

            $this->respectRateLimit();
        } catch (\Exception $exception) {
            app(ApplicationLogger::class)->warning('AtCoder scraper failed while reading home page contests', [
                'category' => 'scraper',
                'platform' => 'atcoder',
                'source' => self::class,
                'operation' => 'getPermanentContests',
            ], $exception);
        }

        return $contests;
    }

    private function getHiddenContests(): array
    {
        $contests = [];

        try {
            $filePath = storage_path('app/atcoder_hidden_contests.json');
            if (!file_exists($filePath)) {
                return $contests;
            }

            $json = file_get_contents($filePath);
            $hiddenContests = json_decode($json ?: '[]', true) ?? [];

            foreach ($hiddenContests as $contest) {
                if (!isset($contest['id'])) {
                    continue;
                }

                $startTime = '';
                if (isset($contest['start_epoch_second']) && $contest['start_epoch_second'] > 0) {
                    $startTime = date('Y-m-d H:i:s', $contest['start_epoch_second']);
                }

                $duration = '';
                if (isset($contest['duration_second'])) {
                    $hours = (int) ($contest['duration_second'] / 3600);
                    $minutes = (int) (($contest['duration_second'] % 3600) / 60);
                    $duration = sprintf('%02d:%02d', $hours, $minutes);
                }

                $contests[] = [
                    'id' => $contest['id'],
                    'title' => $contest['title'] ?? $contest['id'],
                    'url' => $this->baseUrl() . '/contests/' . $contest['id'],
                    'date' => $startTime,
                    'duration' => $duration ?: 'Archived',
                    'rate_change' => $contest['rate_change'] ?? '-',
                    'type' => 'hidden',
                ];
            }
        } catch (\Exception $exception) {
            app(ApplicationLogger::class)->warning('AtCoder scraper failed while reading hidden contests', [
                'category' => 'scraper',
                'platform' => 'atcoder',
                'source' => self::class,
                'operation' => 'getHiddenContests',
            ], $exception);
        }

        return $contests;
    }

    private function getDailyTrainingContests(?callable $pageProcessor = null, bool $fullSync = false): array
    {
        return $this->scrapeCategoryArchive('60', 'daily_training', $pageProcessor, $fullSync);
    }

    public function getContestTimesFromDetail(string $contestId): array
    {
        try {
            $html = $this->fetchPage($this->baseUrl() . '/contests/' . $contestId . '?lang=en');
            if ($html === '') {
                return ['start_time' => null, 'end_time' => null];
            }

            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            $timeNodes = $xpath->query('//small[contains(@class, "contest-duration")]//time');
            $startTime = $timeNodes->length >= 1 ? trim($timeNodes->item(0)->nodeValue) : null;
            $endTime = $timeNodes->length >= 2 ? trim($timeNodes->item(1)->nodeValue) : null;

            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        } catch (\Throwable $e) {
            return ['start_time' => null, 'end_time' => null];
        }
    }

    private function fetchPage(string $url): string
    {
        $this->respectRateLimit();

        try {
            $response = $this->httpRequest()->get($url);

            // ৪০৩ আসলে ক্যাশ কুকি ডিলিট করে রিলগিন ট্রাই করবে
            if ($response->status() === 403) {
                Cache::forget('atcoder_auto_session_cookie');
            }

            if (!$response->successful()) {
                app(ApplicationLogger::class)->warning('AtCoder HTTP request failed', [
                    'category' => 'scraper',
                    'platform' => 'atcoder',
                    'source' => self::class,
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return '';
            }

            return $response->body();
        } catch (\Throwable $e) {
            app(ApplicationLogger::class)->warning('AtCoder HTTP request exception', [
                'category' => 'scraper',
                'platform' => 'atcoder',
                'source' => self::class,
                'url' => $url,
                'message' => $e->getMessage(),
            ], $e);

            return '';
        }
    }

    private function fetchJson(string $url): array
    {
        $this->respectRateLimit();

        $request = $this->httpRequest();
        $response = $request->get($url);

        if ($response->status() === 404 && str_contains($url, '/standings/json')) {
            $fallbackUrl = str_replace('/standings/json', '/standings/team/json', $url);
            $response = $request->get($fallbackUrl);
        }

        if ($response->status() === 403) {
            Cache::forget('atcoder_auto_session_cookie');
        }

        if (!$response->successful()) {
            throw new RuntimeException('AtCoder JSON request failed with HTTP ' . $response->status());
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            throw new RuntimeException('AtCoder JSON request returned invalid payload');
        }

        return $payload;
    }

    private function extractMaxPages(string $html): int
    {
        if (empty($html) || trim($html) === '') {
            return 100;
        }

        try {
            $doc = new DOMDocument;
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            $paginationLinks = $xpath->query('//ul[contains(@class, "pagination")]//li//a');
            if ($paginationLinks->length === 0) {
                return 100;
            }

            $maxPage = 1;
            foreach ($paginationLinks as $link) {
                $href = $link instanceof DOMElement ? $link->getAttribute('href') : '';
                if (preg_match('/page=(\d+)/', $href, $matches)) {
                    $pageNum = (int) $matches[1];
                    if ($pageNum > $maxPage) {
                        $maxPage = $pageNum;
                    }
                }
            }

            return $maxPage;
        } catch (\Exception $exception) {
            return 100;
        }
    }

    // ২. ফুল ব্রাউজার হেডার ও HTTP/2 কনফিগারেশন
    private function httpRequest()
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Sec-Ch-Ua' => '"Chromium";v="128", "Not;A=Brand";v="24", "Google Chrome";v="128"',
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => '"Linux"',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
            'Upgrade-Insecure-Requests' => '1',
            'Connection' => 'keep-alive',
        ];

        $cookies = $this->getAuthenticatedCookie();
        if ($cookies !== '') {
            $headers['Cookie'] = $cookies;
        }

        $curlOptions = [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0, // HTTP/2
            CURLOPT_ENCODING => 'gzip, deflate, br',
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        return Http::timeout(20)
            ->withOptions(['curl' => $curlOptions])
            ->withHeaders($headers);
    }

    private function getAuthenticatedCookie(): string
    {
        $envCookie = config('platforms.atcoder.credentials.atcoder_session_cookies')
            ?? env('ATCODER_SESSION_COOKIES');

        if ($envCookie !== null && trim((string) $envCookie) !== '') {
            $cookieStr = trim((string) $envCookie);
            if (!str_contains($cookieStr, '=')) {
                $cookieStr = 'REVEL_SESSION=' . $cookieStr;
            }
            if (str_contains($cookieStr, 'REVEL_SESSION=') && !str_contains($cookieStr, 'RE_session=')) {
                $val = str_replace('REVEL_SESSION=', '', $cookieStr);
                $cookieStr .= '; RE_session=' . $val;
            }
            return $cookieStr;
        }

        $cachedCookie = Cache::get('atcoder_auto_session_cookie');
        if ($cachedCookie !== null && trim((string) $cachedCookie) !== '') {
            return (string) $cachedCookie;
        }

        $username = config('platforms.atcoder.credentials.atcoder_username') ?? env('ATCODER_USERNAME');
        $password = config('platforms.atcoder.credentials.atcoder_password') ?? env('ATCODER_PASSWORD');

        if (empty($username) || empty($password)) {
            return '';
        }

        try {
            $loginPageResponse = Http::withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                ],
            ])->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'Accept-Language' => 'en-US,en;q=0.9',
                    ])->get($this->baseUrl() . '/login');

            if (!$loginPageResponse->successful()) {
                return '';
            }

            $html = $loginPageResponse->body();
            $doc = new DOMDocument;
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            $csrfInput = $xpath->query('//input[@name="csrf_token"]')->item(0);
            $csrfToken = $csrfInput instanceof DOMElement ? $csrfInput->getAttribute('value') : null;

            if (empty($csrfToken)) {
                return '';
            }

            $cookieHeader = $loginPageResponse->header('Set-Cookie');
            $initialCookie = '';
            if (is_array($cookieHeader)) {
                foreach ($cookieHeader as $c) {
                    if (preg_match('/REVEL_SESSION=([^;]+)/', $c, $m)) {
                        $initialCookie = 'REVEL_SESSION=' . $m[1];
                        break;
                    }
                }
            } elseif (is_string($cookieHeader) && preg_match('/REVEL_SESSION=([^;]+)/', $cookieHeader, $m)) {
                $initialCookie = 'REVEL_SESSION=' . $m[1];
            }

            $postResponse = Http::asForm()->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                ],
            ])->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                        'Cookie' => $initialCookie,
                        'Referer' => $this->baseUrl() . '/login',
                    ])->post($this->baseUrl() . '/login', [
                        'username' => $username,
                        'password' => $password,
                        'csrf_token' => $csrfToken,
                    ]);

            $postCookieHeader = $postResponse->header('Set-Cookie');
            $loggedSession = '';
            if (is_array($postCookieHeader)) {
                foreach ($postCookieHeader as $c) {
                    if (preg_match('/REVEL_SESSION=([^;]+)/', $c, $m)) {
                        $val = $m[1];
                        $loggedSession = 'REVEL_SESSION=' . $val . '; RE_session=' . $val;
                        break;
                    }
                }
            } elseif (is_string($postCookieHeader) && preg_match('/REVEL_SESSION=([^;]+)/', $postCookieHeader, $m)) {
                $val = $m[1];
                $loggedSession = 'REVEL_SESSION=' . $val . '; RE_session=' . $val;
            }

            if (!empty($loggedSession)) {
                // সেশন মেয়াদ ৩ দিন রাখা নিরাপদ
                Cache::put('atcoder_auto_session_cookie', $loggedSession, 86400 * 3);
                return $loggedSession;
            }
        } catch (\Throwable $e) {
            app(ApplicationLogger::class)->warning('Automated AtCoder login exception', [
                'category' => 'scraper',
                'platform' => 'atcoder',
                'source' => self::class,
                'message' => $e->getMessage(),
            ], $e);
        }

        return '';
    }

    // ৩. রিকোয়েস্টের মাঝে হিউম্যান-লাইক জ্যামিতিক র‍্যান্ডম ডিলে
    private function respectRateLimit(): void
    {
        if (self::$lastRequestTime > 0) {
            $elapsed = (int) ((microtime(true) * 1000) - self::$lastRequestTime);
            $requiredDelay = rand(self::MIN_DELAY_MS, self::MAX_DELAY_MS);

            if ($elapsed < $requiredDelay) {
                usleep(($requiredDelay - $elapsed) * 1000);
            }
        }

        self::$lastRequestTime = (int) (microtime(true) * 1000);
    }
}