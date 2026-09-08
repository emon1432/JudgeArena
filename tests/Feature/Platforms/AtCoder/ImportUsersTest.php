<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Platforms\AtCoder\Importers\UserImporter;
use App\Platforms\AtCoder\Services\AtCoderHtmlScraper;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportUsersTest extends TestCase
{
    public function test_import_users_updates_atcoder_user_profile(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $scraper = $this->mock(AtCoderHtmlScraper::class);
        $scraper->shouldReceive('getUserProfile')
            ->with('tourist')
            ->once()
            ->andReturn([
                'username' => 'tourist',
                'avatarUrl' => 'https://img.atcoder.jp/assets/icon/avatar.png',
                'country' => 'BY',
                'birthYear' => 1994,
                'twitterId' => null,
                'topcoderId' => null,
                'codeforcesId' => 'tourist',
                'affiliation' => 'ITMO University',
                'contestStatus' => [
                    'algo' => [
                        'rating' => 4229,
                        'highestRating' => 4229,
                        'rank' => 1,
                        'ratedMatches' => 50,
                    ],
                    'heuristic' => null,
                ],
            ]);

        Http::fake([
            '*v3/user_info*' => Http::response([
                'user_id' => 'tourist',
                'accepted_count' => 1500,
                'accepted_count_rank' => 1,
                'rated_point_sum' => 500000.0,
                'rated_point_sum_rank' => 1,
            ], 200),
        ]);

        $importer = app(UserImporter::class);
        $result = $importer->import('tourist');

        $this->assertSame(1, $result->checked);
        $this->assertSame(1, $result->updated);

        $profile->refresh();
        $this->assertNotNull($profile->raw);
        $this->assertNotNull($profile->last_synced_at);
        $this->assertSame('tourist', $profile->raw['username'] ?? null);
    }
}

