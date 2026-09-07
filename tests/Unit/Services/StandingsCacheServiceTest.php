<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\DTOs\ContestDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemDTO;
use App\Core\DTOs\ProblemResultDTO;
use App\Services\StandingsCacheService;
use DateTimeImmutable;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StandingsCacheServiceTest extends TestCase
{
    private StandingsCacheService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['standings.disk' => 'local']);
        config(['standings.cache_enabled' => true]);

        $this->service = app(StandingsCacheService::class);
    }

    public function test_put_stores_compressed_standings_and_get_retrieves_exact_data(): void
    {
        $dto = new ContestStandingsDTO(
            contest: new ContestDTO(
                platform: 'codeforces',
                platformContestId: '2225',
                title: 'Educational Codeforces Round 189',
                slug: 'codeforces-2225',
                type: 'CF',
                phase: 'FINISHED',
                startedAt: new DateTimeImmutable('2026-04-20 17:00:00'),
                durationSeconds: 7200,
                endedAt: new DateTimeImmutable('2026-04-20 19:00:00'),
                url: 'https://codeforces.com/contest/2225',
                raw: ['id' => 2225],
            ),
            problems: [
                new ProblemDTO(
                    platform: 'codeforces',
                    platformProblemId: '2225A',
                    title: 'A Number Between Two Others',
                    contestPlatformId: '2225',
                    code: 'A',
                    points: 500.0,
                    rating: 800,
                    timeLimit: 1000,
                    memoryLimit: 256,
                    tags: ['math', 'greedy'],
                    url: 'https://codeforces.com/contest/2225/problem/A',
                    raw: ['index' => 'A'],
                    solvedCount: 15000,
                ),
            ],
            rows: [
                new ParticipantDTO(
                    rank: 1,
                    points: 500,
                    penalty: 0,
                    members: ['tourist'],
                    problemResults: [
                        new ProblemResultDTO(
                            points: 500.0,
                            penalty: 0,
                            rejectedAttemptCount: 0,
                            type: 'FINAL',
                            bestSubmissionTimeSeconds: 120,
                        ),
                    ],
                    raw: ['party' => ['members' => [['handle' => 'tourist']]]],
                ),
            ],
            raw: ['status' => 'OK'],
        );

        $saved = $this->service->put('codeforces', '2225', $dto);
        $this->assertTrue($saved);

        $filePath = $this->service->diskPath('codeforces', '2225');
        $this->assertSame('Codeforces/Standings/2225.json.gz', $filePath);
        $this->assertTrue(Storage::disk('local')->exists($filePath));

        // Verify Gzip magic bytes "\x1f\x8b"
        $rawBytes = Storage::disk('local')->get($filePath);
        $this->assertStringStartsWith("\x1f\x8b", $rawBytes);

        $this->assertTrue($this->service->has('codeforces', '2225'));

        $retrieved = $this->service->get('codeforces', '2225');
        $this->assertInstanceOf(ContestStandingsDTO::class, $retrieved);
        $this->assertSame('codeforces', $retrieved->contest->platform);
        $this->assertSame('2225', $retrieved->contest->platformContestId);
        $this->assertSame('Educational Codeforces Round 189', $retrieved->contest->title);
        $this->assertSame('FINISHED', $retrieved->contest->phase);

        $this->assertCount(1, $retrieved->problems);
        $this->assertSame('2225A', $retrieved->problems[0]->platformProblemId);
        $this->assertSame('A Number Between Two Others', $retrieved->problems[0]->title);
        $this->assertSame(500.0, $retrieved->problems[0]->points);

        $this->assertCount(1, $retrieved->rows);
        $this->assertSame(1, $retrieved->rows[0]->rank);
        $this->assertSame(500, $retrieved->rows[0]->points);
        $this->assertSame(['tourist'], $retrieved->rows[0]->members);

        $this->assertCount(1, $retrieved->rows[0]->problemResults);
        $this->assertSame(500.0, $retrieved->rows[0]->problemResults[0]->points);
        $this->assertSame(120, $retrieved->rows[0]->problemResults[0]->bestSubmissionTimeSeconds);
    }

    public function test_get_returns_null_when_file_does_not_exist(): void
    {
        $this->assertFalse($this->service->has('codeforces', '999999'));
        $this->assertNull($this->service->get('codeforces', '999999'));
    }

    public function test_cache_disabled_returns_false_and_null(): void
    {
        config(['standings.cache_enabled' => false]);

        $dto = new ContestStandingsDTO(
            contest: new ContestDTO('codeforces', '100', 'Title'),
        );

        $this->assertFalse($this->service->put('codeforces', '100', $dto));
        $this->assertFalse($this->service->has('codeforces', '100'));
        $this->assertNull($this->service->get('codeforces', '100'));
    }

    public function test_delete_removes_cached_file(): void
    {
        $dto = new ContestStandingsDTO(
            contest: new ContestDTO('codeforces', '100', 'Title'),
        );

        $this->service->put('codeforces', '100', $dto);
        $this->assertTrue($this->service->has('codeforces', '100'));

        $deleted = $this->service->delete('codeforces', '100');
        $this->assertTrue($deleted);
        $this->assertFalse($this->service->has('codeforces', '100'));
    }
}

