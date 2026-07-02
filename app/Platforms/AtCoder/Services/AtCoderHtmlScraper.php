<?php

namespace App\Platforms\AtCoder\Services;

use App\Services\ApplicationLogger;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AtCoderHtmlScraper
{
    private const ATCODER_BASE_URL = 'https://atcoder.jp';

    private const LOGIN_URL = 'https://atcoder.jp/login';

    private const REQUEST_DELAY_MS = 500;

    private static int $lastRequestTime = 0;

    private ?string $sessionCookies = null;

    public function __construct()
    {
        $this->authenticate();
    }

    //used
    public function getContests(): array
    {
        $contests = [];

        $contests = array_merge($contests, $this->getNormalContests());
        $contests = array_merge($contests, $this->getWeekDayContests());
        $contests = array_merge($contests, $this->getPermanentContests());
        $contests = array_merge($contests, $this->getHiddenContests());
        $contests = array_merge($contests, $this->getHistoricalContests());

        return $contests;
    }

    /** @return array<string, mixed> */
    public function getStandings(string $contestId): array
    {
        return $this->fetchJson(self::ATCODER_BASE_URL . '/contests/' . $contestId . '/standings/json');
    }

    /** @return array<string, mixed> */
    public function getStandingsVirtual(string $contestId): array
    {
        return $this->fetchJson(self::ATCODER_BASE_URL . '/contests/' . $contestId . '/standings/virtual/json');
    }

    /** @return array<string, mixed> */
    public function getResults(string $contestId): array
    {
        return $this->fetchJson(self::ATCODER_BASE_URL . '/contests/' . $contestId . '/results/json');
    }

    /** @return array<string, mixed> */
    public function getSubmissions(string $contestId, ?string $user = null): array
    {
        $submissions = [];
        $page = 1;

        while (true) {
            $url = self::ATCODER_BASE_URL . '/contests/' . $contestId . '/submissions?page=' . $page;
            if ($user !== null && $user !== '') {
                $url .= '&f.User=' . urlencode($user);
            }

            $html = $this->fetchPage($url);
            if($html === "") {
                break;
            }

            $doc = new DOMDocument;
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            $rows = $xpath->query('//table//tbody//tr');
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
                if ($taskLink instanceof \DOMElement) {
                    $taskHref = $taskLink->getAttribute('href');
                    $taskId = basename($taskHref);
                    $taskTitle = trim($taskLink->textContent);
                    $taskUrl = self::ATCODER_BASE_URL . $taskHref;
                } else {
                    $taskTitle = trim($cells->item(1)?->textContent ?? '');
                }

                $userLink = $xpath->query('.//a', $cells->item(2))->item(0);
                $username = $userLink instanceof \DOMElement ? trim(basename($userLink->getAttribute('href'))) : trim($cells->item(2)?->textContent ?? '');

                $langLink = $xpath->query('.//a', $cells->item(3))->item(0);
                $language = $langLink instanceof \DOMElement ? trim($langLink->textContent) : trim($cells->item(3)?->textContent ?? '');

                $scoreTd = $cells->item(4);
                $score = null;
                $submissionId = null;
                if ($scoreTd instanceof \DOMElement) {
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
                $status = $statusSpan instanceof \DOMElement ? trim($statusSpan->textContent) : trim($cells->item(6)?->textContent ?? '');

                $execTime = trim($cells->item(7)?->textContent ?? '');
                $memory = trim($cells->item(8)?->textContent ?? '');

                $detailUrl = null;
                if ($submissionId !== null) {
                    $detailUrl = self::ATCODER_BASE_URL . '/contests/' . $contestId . '/submissions/' . $submissionId;
                } else {
                    $detailLink = $xpath->query('.//a[contains(@class, "submission-details-link")]', $cells->item(9))->item(0);
                    if ($detailLink instanceof \DOMElement) {
                        $detailHref = $detailLink->getAttribute('href');
                        $detailUrl = self::ATCODER_BASE_URL . $detailHref;
                    }

                    if ($submissionId === null && $detailUrl !== null) {
                        $submissionId = basename($detailUrl);
                    }
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

            if ($rows->length === 0) {
                break;
            }

            $page++;
            $this->respectRateLimit();
        }

        return ['result' => $submissions];
    }

    //used
    public function getTasks(string $contestId): array
    {
        $response = $this->httpRequest()->get(self::ATCODER_BASE_URL . '/contests/' . $contestId . '/tasks');
        if (! $response->successful()) {
            throw new RuntimeException('AtCoder tasks request failed with HTTP ' . $response->status());
        }

        $doc = new DOMDocument;
        @$doc->loadHTML($response->body());
        $xpath = new DOMXPath($doc);

        $tasks = [];
        $rows = $xpath->query('//table//tr');

        foreach ($rows as $row) {
            $cells = $xpath->query('.//td', $row);
            if ($cells->length < 3) {
                continue;
            }

            $link = $xpath->query('.//a', $cells->item(0))->item(0);
            if (! $link instanceof \DOMElement) {
                continue;
            }

            $taskId = basename($link->getAttribute('href'));
            $title = trim($cells->item(1)?->nodeValue ?? '');
            $position = trim($cells->item(0)?->nodeValue ?? '');
            $fullTitle = $position . ' - ' . $title;
            $timeLimit = trim($cells->item(2)?->nodeValue ?? '');
            $memoryLimit = trim($cells->item(3)?->nodeValue ?? '');
            $taskUrl = self::ATCODER_BASE_URL . $link->getAttribute('href');
            $score = null;

            $taskResponse = $this->httpRequest()->get(self::ATCODER_BASE_URL . '/contests/' . $contestId . '/tasks/' . $taskId);
            if ($taskResponse->successful()) {
                $taskDoc = new DOMDocument;
                @$taskDoc->loadHTML($taskResponse->body());
                $taskXpath = new DOMXPath($taskDoc);

                $scoreNode = $taskXpath->query('//p[contains(text(), "Score") or contains(text(), "配点")]')->item(0);
                if ($scoreNode) {
                    $scoreVar = $taskXpath->query('.//var', $scoreNode)->item(0);
                    if ($scoreVar) {
                        $scoreText = $scoreVar->textContent;
                        preg_match('/\d+/', $scoreText, $matches);
                        if (isset($matches[0])) {
                            $score = (int) $matches[0];
                        }
                    }
                }
            }

            $tasks[] = [
                'id' => $taskId,
                'contest_id' => $contestId,
                'title' => $title,
                'position' => $position,
                'full_title' => $fullTitle,
                'score' => $score,
                'time_limit' => $timeLimit,
                'memory_limit' => $memoryLimit,
                'url' => $taskUrl,
            ];
        }

        return ['result' => $tasks];
    }

    /** @return array<string, mixed> */
    public function getUserProfile(string $username): array
    {
        $types = ['algo', 'heuristic'];
        $result = [];

        foreach ($types as $index => $type) {
            $html = $this->fetchPage(self::ATCODER_BASE_URL . '/users/' . $username . '?contestType=' . $type);

            $doc = new DOMDocument;
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            if ($index === 0) {
                $avatarNode = $xpath->query('//div[contains(@class, "col-md-3")]//img[contains(@class, "avatar")]')->item(0);
                $avatarUrl = $avatarNode instanceof \DOMElement ? $avatarNode->getAttribute('src') : null;

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
            $rank = $rankNode ? trim($rankNode->nodeValue) : null;

            $ratingNode = $xpath->query('//div[contains(@class, "col-md-9")]//table//tr[th[contains(text(), "Rating")]]/td')->item(0);
            $rating = $ratingNode ? trim($ratingNode->nodeValue) : null;

            $highestRatingNode = $xpath->query('//div[contains(@class, "col-md-9")]//table//tr[th[contains(text(), "Highest Rating")]]/td')->item(0);
            $highestRating = $highestRatingNode ? trim($highestRatingNode->nodeValue) : null;

            $ratedMatchesNode = $xpath->query('//div[contains(@class, "col-md-9")]//table//tr[th[contains(text(), "Rated Matches")]]/td')->item(0);
            $ratedMatches = $ratedMatchesNode ? trim($ratedMatchesNode->nodeValue) : null;

            $lastCompetedNode = $xpath->query('//div[contains(@class, "col-md-9")]//table//tr[th[contains(text(), "Last Competed")]]/td')->item(0);
            $lastCompeted = $lastCompetedNode ? trim($lastCompetedNode->nodeValue) : null;

            $result['contest_status'][$type] = [
                'rank' => $rank,
                'rating' => $rating,
                'highest_rating' => $highestRating,
                'rated_matches' => $ratedMatches,
                'last_competed' => $lastCompeted,
            ];
        }

        return ['result' => $result];
    }

    /** @return array<string, mixed> */
    public function getUserRatingHistory(string $username): array
    {
        $types = ['algo', 'heuristic'];
        $history = [];

        foreach ($types as $type) {
            $data = $this->fetchJson(self::ATCODER_BASE_URL . '/users/' . $username . '/history/json?contestType=' . $type);
            foreach ($data as $entry) {
                if (is_array($entry)) {
                    $entry['contest_type'] = $type;
                    $history[] = $entry;
                }
            }
        }

        return ['result' => $history];
    }

    private function authenticate(): void
    {
        $sessionCookieEnv = env('ATCODER_SESSION_COOKIES') ?? (string) config('platforms.atcoder.credentials.atcoder_session_cookies', '');
        if ($sessionCookieEnv !== '') {
            $this->sessionCookies = $sessionCookieEnv;

            return;
        }

        $username = (string) config('platforms.atcoder.credentials.atcoder_username', '');
        $password = (string) config('platforms.atcoder.credentials.atcoder_password', '');

        if ($username === '' || $password === '') {
            app(ApplicationLogger::class)->info('AtCoder authentication skipped', [
                'category' => 'authentication',
                'platform' => 'atcoder',
                'source' => self::class,
                'reason' => 'no credentials or session cookie configured',
            ]);

            return;
        }

        try {
            $getResp = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                ->get(self::LOGIN_URL);

            if (! $getResp->successful()) {
                app(ApplicationLogger::class)->warning('AtCoder login page request failed', [
                    'category' => 'scraper',
                    'platform' => 'atcoder',
                    'source' => self::class,
                    'status' => $getResp->status(),
                ]);

                return;
            }

            $csrf = $this->extractCsrfToken($getResp->body());
            if ($csrf === null) {
                app(ApplicationLogger::class)->warning('AtCoder login page missing csrf token', [
                    'category' => 'scraper',
                    'platform' => 'atcoder',
                    'source' => self::class,
                ]);

                return;
            }

            $cookiePairs = [];
            $getHeaders = $getResp->headers();
            $setCookieHeaders = $getHeaders['Set-Cookie'] ?? $getHeaders['set-cookie'] ?? [];
            if (! is_array($setCookieHeaders) && $setCookieHeaders !== []) {
                $setCookieHeaders = [$setCookieHeaders];
            }
            foreach ($setCookieHeaders as $setCookieHeader) {
                $cookiePairs[] = trim(explode(';', $setCookieHeader)[0]);
            }

            $cookieHeader = count($cookiePairs) > 0 ? implode('; ', $cookiePairs) : null;

            $postReq = Http::asForm()->timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'Referer' => self::LOGIN_URL]);

            if ($cookieHeader !== null) {
                $postReq = $postReq->withHeaders(['Cookie' => $cookieHeader]);
            }

            $postResp = $postReq->post(self::LOGIN_URL, [
                'username' => $username,
                'password' => $password,
                'csrf_token' => $csrf,
            ]);

            $postHeaders = $postResp->headers();
            $postSetCookieHeaders = $postHeaders['Set-Cookie'] ?? $postHeaders['set-cookie'] ?? [];
            if (! is_array($postSetCookieHeaders) && $postSetCookieHeaders !== []) {
                $postSetCookieHeaders = [$postSetCookieHeaders];
            }
            foreach ($postSetCookieHeaders as $setCookieHeader) {
                $cookiePairs[] = trim(explode(';', $setCookieHeader)[0]);
            }

            $cookiePairs = array_values(array_unique($cookiePairs));
            $this->sessionCookies = implode('; ', $cookiePairs);
        } catch (\Exception $exception) {
            app(ApplicationLogger::class)->error('AtCoder authentication failed', [
                'category' => 'authentication',
                'platform' => 'atcoder',
                'source' => self::class,
            ], $exception);
        }
    }

    private function extractCsrfToken(string $html): ?string
    {
        $doc = new DOMDocument;
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);
        $scriptNodes = $xpath->query('//script[contains(text(), "var csrfToken")]');

        foreach ($scriptNodes as $node) {
            $scriptContent = $node->textContent;
            if (preg_match('/var csrfToken\s*=\s*"([^"]+)"/', $scriptContent, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    //used
    private function getNormalContests(): array
    {
        $contests = [];
        $page = 1;
        $maxPages = null;

        while (true) {
            $html = $this->fetchPage(self::ATCODER_BASE_URL . '/contests/archive?lang=ja&page=' . $page);

            if ($maxPages === null) {
                $maxPages = $this->extractMaxPages($html);
            }

            $doc = new DOMDocument;
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            $rows = $xpath->query('//table//tbody//tr');
            $pageHasContests = false;

            foreach ($rows as $row) {
                $cells = $xpath->query('.//td', $row);
                if ($cells->length < 4) {
                    continue;
                }

                $startText = trim($cells->item(0)?->nodeValue ?? '');
                $link = $xpath->query('.//a', $cells->item(1))->item(0);
                if (! $link instanceof \DOMElement) {
                    continue;
                }

                $href = $link->getAttribute('href');
                $contestId = basename($href);
                $title = $link->nodeValue;
                $duration = trim($cells->item(2)?->nodeValue ?? '');
                $rateChange = trim($cells->item(3)?->nodeValue ?? '');

                $contests[] = [
                    'id' => $contestId,
                    'title' => $title,
                    'url' => self::ATCODER_BASE_URL . $href,
                    'date' => $startText,
                    'duration' => $duration,
                    'rate_change' => $rateChange,
                    'type' => 'normal',
                ];

                $pageHasContests = true;
            }

            if (! $pageHasContests) {
                break;
            }

            if ($maxPages !== null && $page >= $maxPages) {
                break;
            }

            $page++;
            $this->respectRateLimit();
        }

        return $contests;
    }

    //used
    private function getWeekDayContests(): array
    {
        $contests = [];
        $page = 1;
        $maxPages = null;

        while (true) {
            $html = $this->fetchPage(self::ATCODER_BASE_URL . '/contests/archive?category=20&page=' . $page);

            if ($maxPages === null) {
                $maxPages = $this->extractMaxPages($html);
            }

            $doc = new DOMDocument;
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            $rows = $xpath->query('//table//tbody//tr');
            $pageHasContests = false;

            foreach ($rows as $row) {
                $cells = $xpath->query('.//td', $row);
                if ($cells->length < 4) {
                    continue;
                }

                $startText = trim($cells->item(0)?->nodeValue ?? '');
                $link = $xpath->query('.//a', $cells->item(1))->item(0);
                if (! $link instanceof \DOMElement) {
                    continue;
                }

                $href = $link->getAttribute('href');
                $contestId = basename($href);
                $title = $link->nodeValue;
                $duration = trim($cells->item(2)?->nodeValue ?? '');
                $rateChange = trim($cells->item(3)?->nodeValue ?? '');

                $contests[] = [
                    'id' => $contestId,
                    'title' => $title,
                    'url' => self::ATCODER_BASE_URL . $href,
                    'date' => $startText,
                    'duration' => $duration,
                    'rate_change' => $rateChange,
                    'type' => 'weekday',
                ];

                $pageHasContests = true;
            }

            if (! $pageHasContests) {
                break;
            }

            if ($maxPages !== null && $page >= $maxPages) {
                break;
            }

            $page++;
            $this->respectRateLimit();
        }

        return $contests;
    }

    //used
    private function extractMaxPages(string $html): int
    {
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
                $href = $link instanceof \DOMElement ? $link->getAttribute('href') : '';
                if (preg_match('/page=(\d+)/', $href, $matches)) {
                    $pageNum = (int) $matches[1];
                    if ($pageNum > $maxPage) {
                        $maxPage = $pageNum;
                    }
                }
            }

            return $maxPage;
        } catch (\Exception $exception) {
            app(ApplicationLogger::class)->warning('AtCoder scraper failed to detect max pages', [
                'category' => 'scraper',
                'platform' => 'atcoder',
                'source' => self::class,
                'operation' => 'extractMaxPages',
            ], $exception);

            return 100;
        }
    }

    //used
    private function getPermanentContests(): array
    {
        $contests = [];

        try {
            $html = $this->fetchPage(self::ATCODER_BASE_URL . '/contests/?lang=ja');

            $doc = new DOMDocument;
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            $tables = $xpath->query('//table');
            foreach ($tables as $table) {
                $tbody = $xpath->query('.//tbody', $table);
                if ($tbody->length === 0) {
                    continue;
                }

                $rows = $xpath->query('.//tr', $tbody->item(0));
                $foundPermanent = false;

                foreach ($rows as $row) {
                    $cells = $xpath->query('.//td', $row);
                    if ($cells->length < 2) {
                        continue;
                    }

                    $firstCellText = trim($cells->item(0)?->nodeValue ?? '');
                    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $firstCellText)) {
                        break;
                    }

                    $link = $xpath->query('.//a', $cells->item(0))->item(0);
                    if (! $link instanceof \DOMElement) {
                        continue;
                    }

                    $href = $link->getAttribute('href');
                    $contestId = basename($href);
                    $title = trim($link->nodeValue);
                    $rateChange = $cells->length > 1 ? trim($cells->item(1)?->nodeValue ?? '') : '';

                    $contests[] = [
                        'id' => $contestId,
                        'title' => $title,
                        'url' => self::ATCODER_BASE_URL . $href,
                        'date' => '',
                        'duration' => 'Permanent',
                        'rate_change' => $rateChange,
                        'type' => 'permanent',
                    ];

                    $foundPermanent = true;
                }

                if ($foundPermanent) {
                    break;
                }
            }

            $this->respectRateLimit();
        } catch (\Exception $exception) {
            app(ApplicationLogger::class)->warning('AtCoder scraper failed while reading permanent contests', [
                'category' => 'scraper',
                'platform' => 'atcoder',
                'source' => self::class,
                'operation' => 'getPermanentContests',
            ], $exception);
        }

        return $contests;
    }

    //used
    private function getHiddenContests(): array
    {
        $contests = [];

        try {
            $filePath = storage_path('app/atcoder_hidden_contests.json');
            if (! file_exists($filePath)) {
                return $contests;
            }

            $json = file_get_contents($filePath);
            $hiddenContests = json_decode($json ?: '[]', true) ?? [];

            foreach ($hiddenContests as $contest) {
                if (! isset($contest['id'])) {
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
                    'url' => self::ATCODER_BASE_URL . '/contests/' . $contest['id'],
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

    //used
    private function getHistoricalContests(): array
    {
        $contests = [];

        try {
            $filePath = storage_path('app/atcoder_historical_contests.json');
            if (! file_exists($filePath)) {
                return $contests;
            }

            $json = file_get_contents($filePath);
            $historicalContests = json_decode($json ?: '[]', true) ?? [];

            foreach ($historicalContests as $contest) {
                if (! isset($contest['id'])) {
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
                    'url' => self::ATCODER_BASE_URL . '/contests/' . $contest['id'],
                    'date' => $startTime,
                    'duration' => $duration ?: 'Archived',
                    'rate_change' => $contest['rate_change'] ?? '-',
                    'type' => 'historical',
                ];
            }
        } catch (\Exception $exception) {
            app(ApplicationLogger::class)->warning('AtCoder scraper failed while reading historical contests', [
                'category' => 'scraper',
                'platform' => 'atcoder',
                'source' => self::class,
                'operation' => 'getHistoricalContests',
            ], $exception);
        }

        return $contests;
    }

    //used
    private function fetchPage(string $url): string
    {
        $this->respectRateLimit();

        $request = $this->httpRequest();
        $response = $this->sessionCookies !== null
            ? $request->withHeaders(['Cookie' => $this->sessionCookies])->get($url)
            : $request->get($url);

        if (! $response->successful()) {
            return '';
            // throw new RuntimeException('AtCoder page request failed with HTTP ' . $response->status());
        }

        return $response->body();
    }

    private function fetchJson(string $url): array
    {
        $this->respectRateLimit();

        $request = $this->httpRequest();
        $response = $this->sessionCookies !== null
            ? $request->withHeaders(['Cookie' => $this->sessionCookies])->get($url)
            : $request->get($url);

        if ($response->status() === 404 && str_contains($url, '/standings/json')) {
            $fallbackUrl = str_replace('/standings/json', '/standings/team/json', $url);
            $response = $this->sessionCookies !== null
                ? $request->withHeaders(['Cookie' => $this->sessionCookies])->get($fallbackUrl)
                : $request->get($fallbackUrl);
        }

        if (! $response->successful()) {
            throw new RuntimeException('AtCoder JSON request failed with HTTP ' . $response->status());
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('AtCoder JSON request returned invalid payload');
        }

        return $payload;
    }

    //used
    private function httpRequest()
    {
        return Http::timeout(15)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36']);
    }

    //used
    private function respectRateLimit(): void
    {
        if (self::$lastRequestTime > 0) {
            $elapsed = (int) ((microtime(true) * 1000) - self::$lastRequestTime);
            if ($elapsed < self::REQUEST_DELAY_MS) {
                usleep((self::REQUEST_DELAY_MS - $elapsed) * 1000);
            }
        }

        self::$lastRequestTime = (int) (microtime(true) * 1000);
    }
}
