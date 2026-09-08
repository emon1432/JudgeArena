<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\Services\AtCoderHtmlScraper;
use Tests\TestCase;

class AtCoderHtmlScraperTest extends TestCase
{
    public function test_scraper_parses_user_profile_and_both_contest_statuses_from_synthetic_html(): void
    {
        $algoHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<div class="col-md-3 col-sm-12">
<div class="col-md-3">
    <img class="avatar" src="https://img.atcoder.jp/icons/sample_avatar.jpg">
    <a class="username" href="/users/tourist"><span class="user-red">tourist</span></a>
    <table class="dl-table">
        <tr><th>Country/Region</th><td>Belarus</td></tr>
        <tr><th>Birth Year</th><td>1994</td></tr>
        <tr><th>Twitter ID</th><td><a href="https://twitter.com/que_tourist">@que_tourist</a></td></tr>
        <tr><th>TopCoder ID</th><td><a href="https://topcoder.com">tourist</a></td></tr>
        <tr><th>Codeforces ID</th><td><a href="https://codeforces.com">tourist</a></td></tr>
        <tr><th>Affiliation</th><td>ITMO University</td></tr>
    </table>
</div>
<table class="dl-table">
    <tr><th>Country / Region</th><td>Belarus</td></tr>
    <tr><th>Birth Year</th><td>1994</td></tr>
    <tr><th>Twitter ID</th><td><a href="https://twitter.com/que_tourist">@que_tourist</a></td></tr>
    <tr><th>Topcoder ID</th><td><a href="https://topcoder.com">tourist</a></td></tr>
    <tr><th>Codeforces ID</th><td><a href="https://codeforces.com">tourist</a></td></tr>
    <tr><th>Affiliation</th><td>ITMO University</td></tr>
    <tr><th>Rank</th><td>1th</td></tr>
    <tr><th>Rating</th><td><span class="user-red">3797</span></td></tr>
    <tr><th>Highest Rating</th><td><span class="user-red">4229</span> &#x2015; King (+171 to promote)</td></tr>
    <tr><th>Rated Matches</th><td>71</td></tr>
    <tr><th>Last Competed</th><td>2026/03/29</td></tr>
</table>
<div class="col-md-9">
    <table class="dl-table">
        <tr><th>Rank</th><td>1th</td></tr>
        <tr><th>Rating</th><td><span class="user-red">3797</span></td></tr>
        <tr><th>Highest Rating</th><td><span class="user-red">4229</span> &#x2015; <span class="bold">King</span> (+171 to promote)</td></tr>
        <tr><th>Rated Matches</th><td>71</td></tr>
        <tr><th>Last Competed</th><td>2026/03/29</td></tr>
    </table>
</div>
</body>
</html>
HTML;

        $heuristicHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<table class="dl-table">
    <tr><th>Rating</th><td><span class="user-yellow">2066</span> (Provisional)</td></tr>
    <tr><th>Highest Rating</th><td><span class="user-yellow">2383</span></td></tr>
    <tr><th>Rated Matches</th><td>5</td></tr>
    <tr><th>Last Competed</th><td>2024/07/21</td></tr>
</table>
<div class="col-md-9">
    <table class="dl-table">
        <tr><th>Rating</th><td><span class="user-yellow">2066</span> (Provisional)</td></tr>
        <tr><th>Highest Rating</th><td><span class="user-yellow">2383</span></td></tr>
        <tr><th>Rated Matches</th><td>5</td></tr>
        <tr><th>Last Competed</th><td>2024/07/21</td></tr>
    </table>
</div>
</body>
</html>
HTML;

        $scraper = new AtCoderHtmlScraper;
        $parsed = $scraper->parseUserProfileHtml($algoHtml, $heuristicHtml, 'tourist');

        // Profile identity fields
        $this->assertSame('tourist', $parsed['username']);
        $this->assertSame('https://img.atcoder.jp/icons/sample_avatar.jpg', $parsed['avatarUrl']);
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
        $scraper = new AtCoderHtmlScraper;
        $parsed = $scraper->parseUserProfileHtml($htmlWithoutAvatar, null, 'novice');

        $this->assertSame('https://img.atcoder.jp/assets/icon/avatar.png', $parsed['avatarUrl']);
    }
}
