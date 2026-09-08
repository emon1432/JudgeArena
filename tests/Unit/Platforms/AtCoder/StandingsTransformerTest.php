<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\Mappers\AtCoderStandingsMapper;
use App\Platforms\AtCoder\Transformers\StandingsTransformer;
use Tests\TestCase;

class StandingsTransformerTest extends TestCase
{
    public function test_from_api_standings_transforms_atcoder_standings(): void
    {
        $dto = AtCoderStandingsMapper::fromApiResponse([
            'contest' => [
                'id' => 'abc300',
                'start_epoch_second' => 1682769600,
                'duration_second' => 6000,
                'title' => 'AtCoder Beginner Contest 300',
                'rate_change' => 'All',
            ],
            'problems' => [
                [
                    'id' => 'abc300_a',
                    'contest_id' => 'abc300',
                    'position' => 'A',
                    'title' => 'Problem A',
                ],
            ],
            'rows' => [
                [
                    'userScreenName' => 'tourist',
                    'rank' => 1,
                    'totalResult' => [
                        'score' => 100,
                        'penalty' => 120,
                    ],
                    'taskResults' => [
                        'abc300_a' => [
                            'score' => 100,
                            'penalty' => 0,
                            'failure' => 0,
                            'elapsed' => 120,
                        ],
                    ],
                ],
            ],
        ]);

        $transformer = new StandingsTransformer();
        $result = $transformer->fromApiStandings($dto);

        $this->assertSame('atcoder', $result->contest->platform);
        $this->assertSame('abc300', $result->contest->platformContestId);
        $this->assertCount(1, $result->problems);
        $this->assertCount(1, $result->rows);
        $this->assertSame(1, $result->rows[0]->rank);
        $this->assertSame(100.0, (float) $result->rows[0]->points);
        $this->assertCount(1, $result->rows[0]->problemResults);
    }
}
