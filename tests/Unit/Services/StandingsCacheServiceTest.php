<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\DTOs\ContestDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\Platforms\PlatformRegistry;
use App\Services\GoogleDrive\GoogleDriveClient;
use App\Services\StandingsCacheService;
use DateTimeImmutable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class StandingsCacheServiceTest extends TestCase
{
    public function test_platform_folder_resolves_dynamically(): void
    {
        $this->createPlatform('codeforces', 'Codeforces');
        $this->createPlatform('atcoder', 'AtCoder');

        $googleClient = Mockery::mock(GoogleDriveClient::class);
        $registry = $this->app->make(PlatformRegistry::class);

        $service = new StandingsCacheService($googleClient, $registry);

        $this->assertSame('Codeforces', $service->platformFolder('codeforces'));
        $this->assertSame('AtCoder', $service->platformFolder('atcoder'));
        $this->assertSame(['Codeforces', 'Standings'], $service->targetSubfolder('codeforces'));
    }

    public function test_cache_aside_works_with_local_disk_storage(): void
    {
        Storage::fake('local');
        Config::set('standings.disk', 'local');
        Config::set('standings.cache_enabled', true);

        $this->createPlatform('codeforces', 'Codeforces');

        $googleClient = Mockery::mock(GoogleDriveClient::class);
        $registry = $this->app->make(PlatformRegistry::class);
        $service = new StandingsCacheService($googleClient, $registry);

        $contest = new ContestDTO(
            platform: 'codeforces',
            platformContestId: '1000',
            title: 'Codeforces Round 1000',
            phase: 'FINISHED',
            type: 'CF',
            startedAt: new DateTimeImmutable('2026-01-01 10:00:00'),
            endedAt: new DateTimeImmutable('2026-01-01 12:00:00'),
            durationSeconds: 7200,
        );

        $participant = new ParticipantDTO(
            rank: 1,
            points: 100,
            penalty: 0,
            members: [['handle' => 'tourist']],
            problemResults: [],
        );

        $dto = new ContestStandingsDTO(
            contest: $contest,
            problems: [],
            rows: [$participant],
        );

        $this->assertFalse($service->has('codeforces', '1000'));
        $this->assertTrue($service->put('codeforces', '1000', $dto));
        $this->assertTrue($service->has('codeforces', '1000'));

        $cached = $service->get('codeforces', '1000');
        $this->assertNotNull($cached);
        $this->assertSame('Codeforces Round 1000', $cached->contest->title);
        $this->assertCount(1, $cached->rows);
        $this->assertSame('tourist', $cached->rows[0]->members[0]['handle']);

        $this->assertTrue($service->delete('codeforces', '1000'));
        $this->assertFalse($service->has('codeforces', '1000'));
    }
}

