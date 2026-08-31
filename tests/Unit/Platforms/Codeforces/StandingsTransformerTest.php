<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesStandingsMapper;
use App\Platforms\Codeforces\Transformers\StandingsTransformer;
use Tests\TestCase;

class StandingsTransformerTest extends TestCase
{
    public function test_standings_transformer_maps_rows_and_problems(): void
    {
        $standings = [
            'contest' => [
                'id' => 2225,
                'name' => 'Educational Codeforces Round 189 (Rated for Div. 2)',
                'type' => 'CF',
                'phase' => 'FINISHED',
                'frozen' => false,
                'durationSeconds' => 7200,
                'startTimeSeconds' => 1776782100,
                'relativeTimeSeconds' => 7200,
            ],
            'problems' => [
                [
                    'contestId' => 2225,
                    'index' => 'A',
                    'name' => 'A Number Between Two Others',
                    'type' => 'PROGRAMMING',
                    'points' => 500,
                    'rating' => 800,
                    'tags' => ['greedy', 'math'],
                ],
            ],
            'rows' => [
                [
                    'party' => [
                        'contestId' => 2225,
                        'members' => [
                            ['handle' => 'tourist'],
                        ],
                        'participantType' => 'CONTESTANT',
                        'ghost' => false,
                        'startTimeSeconds' => 1776782100,
                    ],
                    'rank' => 1,
                    'points' => 500,
                    'penalty' => 0,
                    'successfulHackCount' => 0,
                    'unsuccessfulHackCount' => 0,
                    'problemResults' => [
                        [
                            'points' => 500.0,
                            'penalty' => 0,
                            'rejectedAttemptCount' => 0,
                            'type' => 'FINAL',
                            'bestSubmissionTimeSeconds' => 300,
                        ],
                    ],
                ],
            ],
        ];

        $dto = CodeforcesStandingsMapper::fromApiResponse($standings);
        $core = (new StandingsTransformer())->fromApiStandings($dto);

        $this->assertSame('codeforces', $core->contest->platform);
        $this->assertSame('2225', $core->contest->platformContestId);
        $this->assertNotEmpty($core->problems);
        $this->assertNotEmpty($core->rows);
        $this->assertSame(1, $core->rows[0]->rank);
    }
}
