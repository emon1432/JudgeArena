<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\Services\AtCoderHtmlScraper;
use Tests\TestCase;

class AtCoderHtmlScraperTest extends TestCase
{
    public function test_scraper_parses_user_profile_and_both_contest_statuses_from_html_fixtures(): void
    {
        $algoHtmlPath = base_path('docs/platforms/atcoder.jp/sample-responses/tourist - contestType=algo - AtCoder.html');
        $heuristicHtmlPath = base_path('docs/platforms/atcoder.jp/sample-responses/tourist - contestType=heuristic - AtCoder.html');
        $algoHtmlPath = file_exists(base_path('tests/Fixtures/Platforms/AtCoder/tourist - contestType=algo - AtCoder.html'))
            ? base_path('tests/Fixtures/Platforms/AtCoder/tourist - contestType=algo - AtCoder.html')
            : base_path('docs/platforms/atcoder.jp/sample-responses/tourist - contestType=algo - AtCoder.html');
        $heuristicHtmlPath = file_exists(base_path('tests/Fixtures/Platforms/AtCoder/tourist - contestType=heuristic - AtCoder.html'))
            ? base_path('tests/Fixtures/Platforms/AtCoder/tourist - contestType=heuristic - AtCoder.html')
            : base_path('docs/platforms/atcoder.jp/sample-responses/tourist - contestType=heuristic - AtCoder.html');

        $this->assertFileExists($algoHtmlPath);
        $this->assertFileExists($heuristicHtmlPath);

        $htmlAlgo = (string) file_get_contents($algoHtmlPath);
        $htmlHeuristic = (string) file_get_contents($heuristicHtmlPath);

        $scraper = new AtCoderHtmlScraper();
        $parsed = $scraper->parseUserProfileHtml($htmlAlgo, $htmlHeuristic, 'tourist');

        // Profile identity fields
        $this->assertSame('tourist', $parsed['username']);
        $this->assertSame('https://img.atcoder.jp/icons/267f5de4d8768543b1570f07e47b5316.jpg', $parsed['avatarUrl']);
        $this->assertSame('Belarus', $parsed['country']);
        $this->assertSame('1994', $parsed['birthYear']);
        $this->assertSame('@que_tourist', $parsed['twitterId']);
        $this->assertSame('tourist', $parsed['topcoderId']);
        $this->assertSame('tourist', $parsed['codeforcesId']);
        $this->assertSame('ITMO University', $parsed['affiliation']);

        // Algorithm contest status
        $algo = $parsed['contestStatus']['algo'];
        $this->assertIsArray($algo);
        $this->assertSame(1, $algo['rank']);
        $this->assertSame('Top <0.01%', $algo['percentile']);
        $this->assertSame(3797, $algo['rating']);
        $this->assertFalse($algo['is_provisional']);
        $this->assertSame(4229, $algo['highest_rating']);
        $this->assertSame('King', $algo['user_title']);
        $this->assertSame(71, $algo['rated_matches']);
        $this->assertSame('2026-03-29', $algo['last_competed']);
        $this->assertSame('4229', $algo['raw_highest_rating']);

        // Heuristic contest status
        $heuristic = $parsed['contestStatus']['heuristic'];
        $this->assertIsArray($heuristic);
        $this->assertNull($heuristic['rank']);
        $this->assertNull($heuristic['percentile']);
        $this->assertSame(2066, $heuristic['rating']);
        $this->assertTrue($heuristic['is_provisional']);
        $this->assertSame(2383, $heuristic['highest_rating']);
        $this->assertNull($heuristic['user_title']);
        $this->assertSame(5, $heuristic['rated_matches']);
        $this->assertSame('2024-07-21', $heuristic['last_competed']);
        $this->assertSame('2383', $heuristic['raw_highest_rating']);
    }

    public function test_scraper_falls_back_to_default_avatar_when_no_avatar_is_found(): void
    {
        $htmlWithoutAvatar = '<html><body><div class="col-md-3"><a class="username">novice</a></div></body></html>';
        $scraper = new AtCoderHtmlScraper();
        $parsed = $scraper->parseUserProfileHtml($htmlWithoutAvatar, null, 'novice');

        $this->assertSame('https://img.atcoder.jp/assets/icon/avatar.png', $parsed['avatarUrl']);
    }
}

