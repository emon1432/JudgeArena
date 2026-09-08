<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Core\DTOs\UserDTO;
use App\Platforms\AtCoder\AtCoderAdapter;
use Tests\TestCase;

class ImportUsersTest extends TestCase
{
    public function test_import_atcoder_users_command_updates_registered_profiles(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder', 'https://atcoder.jp');
        $profile = $this->createUserWithProfile($platform, 'chokudai');

        $adapter = $this->app->make(AtCoderAdapter::class);
        $mock = \Mockery::mock($adapter)->makePartial();
        $mock->shouldReceive('getUser')
            ->once()
            ->with('chokudai')
            ->andReturn(
                new UserDTO(
                    platform: 'atcoder',
                    platformHandle: 'chokudai',
                    rating: 3000,
                    country: 'Japan',
                    raw: ['handle' => 'chokudai', 'rating' => 3000],
                )
            );

        $this->app->instance(AtCoderAdapter::class, $mock);

        $this->artisan('judgearena:import-users', ['platform' => 'atcoder'])
            ->expectsOutputToContain('Platform: atcoder')
            ->expectsOutputToContain('Updated: 1')
            ->assertExitCode(0);

        $profile->refresh();
        $this->assertNotNull($profile->last_synced_at);
        $this->assertSame(3000, $profile->raw['rating']);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'user',
            'entity_platform_id' => 'chokudai',
            'sync_status' => 'synced',
        ]);
    }

    public function test_import_atcoder_users_command_integrates_html_scraper_and_kenkoooo_api(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder', 'https://atcoder.jp');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $algoHtml = (string) file_get_contents(base_path('docs/platforms/atcoder.jp/sample-responses/tourist - contestType=algo - AtCoder.html'));
        $heuristicHtml = (string) file_get_contents(base_path('docs/platforms/atcoder.jp/sample-responses/tourist - contestType=heuristic - AtCoder.html'));
        $kenkooooJson = (array) json_decode((string) file_get_contents(base_path('docs/platforms/atcoder.jp/sample-responses/user_info.json')), true);
        $algoHtmlPath = file_exists(base_path('tests/Fixtures/Platforms/AtCoder/tourist - contestType=algo - AtCoder.html'))
            ? base_path('tests/Fixtures/Platforms/AtCoder/tourist - contestType=algo - AtCoder.html')
            : base_path('docs/platforms/atcoder.jp/sample-responses/tourist - contestType=algo - AtCoder.html');
        $heuristicHtmlPath = file_exists(base_path('tests/Fixtures/Platforms/AtCoder/tourist - contestType=heuristic - AtCoder.html'))
            ? base_path('tests/Fixtures/Platforms/AtCoder/tourist - contestType=heuristic - AtCoder.html')
            : base_path('docs/platforms/atcoder.jp/sample-responses/tourist - contestType=heuristic - AtCoder.html');
        $userInfoPath = file_exists(base_path('tests/Fixtures/Platforms/AtCoder/user_info.json'))
            ? base_path('tests/Fixtures/Platforms/AtCoder/user_info.json')
            : base_path('docs/platforms/atcoder.jp/sample-responses/user_info.json');

        $algoHtml = (string) file_get_contents($algoHtmlPath);
        $heuristicHtml = (string) file_get_contents($heuristicHtmlPath);
        $kenkooooJson = (array) json_decode((string) file_get_contents($userInfoPath), true);

        \Illuminate\Support\Facades\Http::fake([
            'https://atcoder.jp/users/tourist?contestType=algo' => \Illuminate\Support\Facades\Http::response($algoHtml, 200),
            'https://atcoder.jp/users/tourist?contestType=heuristic' => \Illuminate\Support\Facades\Http::response($heuristicHtml, 200),
            'https://kenkoooo.com/atcoder/atcoder-api/v3/user_info?user=tourist' => \Illuminate\Support\Facades\Http::response($kenkooooJson, 200),
            '*' => \Illuminate\Support\Facades\Http::response('', 200),
        ]);

        $this->artisan('judgearena:import-users', ['platform' => 'atcoder', 'handle' => 'tourist'])
            ->expectsOutputToContain('Platform: atcoder')
            ->expectsOutputToContain('Updated: 1')
            ->assertExitCode(0);

        $profile->refresh();
        $this->assertNotNull($profile->last_synced_at);
        $this->assertSame(3797, $profile->raw['rating']);
        $this->assertSame('Belarus', $profile->raw['country']);
        $this->assertSame(1057, $profile->raw['accepted_count']);
        $this->assertSame(688543, $profile->raw['rated_point_sum']);
        $this->assertSame('King', $profile->raw['contestStatus']['algo']['user_title']);

        $this->assertDatabaseHas('platform_sync_states', [
            'platform_id' => $platform->id,
            'entity_type' => 'user',
            'entity_platform_id' => 'tourist',
            'sync_status' => 'synced',
        ]);
    }
}
