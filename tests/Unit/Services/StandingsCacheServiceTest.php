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

    public function test_raw_payload_caching_and_dynamic_transformation(): void
    {
        Storage::fake('local');
        Config::set('standings.disk', 'local');
        Config::set('standings.cache_enabled', true);

        $this->createPlatform('codeforces', 'Codeforces');

        $googleClient = Mockery::mock(GoogleDriveClient::class);
        $registry = $this->app->make(PlatformRegistry::class);
        $service = new StandingsCacheService($googleClient, $registry);

        $rawCodeforcesPayload = [
            'status' => 'OK',
            'result' => [
                'contest' => [
                    'id' => 1000,
                    'name' => 'Codeforces Round 1000',
                    'type' => 'CF',
                    'phase' => 'FINISHED',
                    'durationSeconds' => 7200,
                    'startTimeSeconds' => 1672531200,
                ],
                'problems' => [
                    [
                        'contestId' => 1000,
                        'index' => 'A',
                        'name' => 'Problem A',
                        'type' => 'PROGRAMMING',
                        'rating' => 800,
                    ],
                ],
                'rows' => [
                    [
                        'party' => [
                            'contestId' => 1000,
                            'members' => [['handle' => 'tourist']],
                            'participantType' => 'CONTESTANT',
                        ],
                        'rank' => 1,
                        'points' => 500.0,
                        'penalty' => 15,
                        'problemResults' => [
                            [
                                'points' => 500.0,
                                'penalty' => 15,
                                'rejectedAttemptCount' => 0,
                                'type' => 'FINAL',
                                'bestSubmissionTimeSeconds' => 900,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertTrue($service->put('codeforces', '1000', $rawCodeforcesPayload));
        $this->assertTrue($service->has('codeforces', '1000'));

        $cached = $service->get('codeforces', '1000');
        $this->assertNotNull($cached);
        $this->assertSame('codeforces', $cached->contest->platform);
        $this->assertSame('1000', $cached->contest->platformContestId);
        $this->assertSame('Codeforces Round 1000', $cached->contest->title);
        $this->assertCount(1, $cached->problems);
        $this->assertCount(1, $cached->rows);
        $this->assertSame(1, $cached->rows[0]->rank);
        $this->assertSame('tourist', $cached->rows[0]->members[0]['handle']);
    }

    public function test_atcoder_raw_payload_caching_and_dynamic_transformation(): void
    {
        Storage::fake('local');
        Config::set('standings.disk', 'local');
        Config::set('standings.cache_enabled', true);

        $this->createPlatform('atcoder', 'AtCoder');

        $googleClient = Mockery::mock(GoogleDriveClient::class);
        $registry = $this->app->make(PlatformRegistry::class);
        $service = new StandingsCacheService($googleClient, $registry);

        $rawAtCoderPayload = [
            'Fixed' => true,
            'AdditionalColumns' => null,
            'TaskInfo' => [
                [
                    'TaskScreenName' => 'abc300_a',
                    'TaskName' => 'A. N-choice question',
                ],
            ],
            'StandingsData' => [
                [
                    'Rank' => 1,
                    'UserScreenName' => 'tourist',
                    'UserName' => 'Gennady Korotkevich',
                    'TotalResult' => [
                        'Score' => 100,
                        'Elapsed' => 60000000000,
                    ],
                    'TaskResults' => [
                        'abc300_a' => [
                            'Score' => 100,
                            'Elapsed' => 60000000000,
                            'Count' => 1,
                            'Status' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $this->assertTrue($service->put('atcoder', 'abc300', $rawAtCoderPayload));
        $this->assertTrue($service->has('atcoder', 'abc300'));

        $cached = $service->get('atcoder', 'abc300');
        $this->assertNotNull($cached);
        $this->assertSame('atcoder', $cached->contest->platform);
        $this->assertSame('abc300', $cached->contest->platformContestId);
        $this->assertCount(1, $cached->problems);
        $this->assertCount(1, $cached->rows);
        $this->assertSame(1, $cached->rows[0]->rank);
        $this->assertSame('tourist', $cached->rows[0]->members[0]['handle']);
    }
}
