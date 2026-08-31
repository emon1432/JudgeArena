<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\Mappers\AtCoderStandingsMapper;
use App\Platforms\AtCoder\Transformers\StandingsTransformer;
use Tests\TestCase;

class StandingsTransformerTest extends TestCase
{
    public function test_standings_transformer_maps_atcoder_standings(): void
    {
        $payload = [
            'contest' => [
                'id' => 'abc350',
                'title' => 'AtCoder Beginner Contest 350',
                'type' => 'Algorithm',
                'date' => '2024-04-20 21:00:00+0900',
                'duration' => '01:40',
                'rateChange' => ' ~ 1999',
                'url' => 'https://atcoder.jp/contests/abc350',
            ],
            'problems' => [
                [
                    'id' => 'abc350_a',
                    'contestId' => 'abc350',
                    'title' => 'Past ABCs',
                    'position' => 'A',
                    'points' => 100.0,
                ],
            ],
            'rows' => [
                [
                    'contestId' => 'abc350',
                    'userScreenName' => 'chokudai',
                    'userName' => 'chokudai',
                    'rank' => 1,
                    'isTeam' => false,
                    'totalResult' => [
                        'score' => 10000,
                        'penalty' => 0,
                        'elapsed' => 120,
                    ],
                    'taskResults' => [
                        'abc350_a' => [
                            'score' => 10000,
                            'penalty' => 0,
                            'failure' => 0,
                            'status' => '1',
                            'elapsed' => 120,
                        ],
                    ],
                ],
            ],
        ];

        $dto = AtCoderStandingsMapper::fromApiResponse($payload);
        $core = (new StandingsTransformer())->fromApiStandings($dto);

        $this->assertSame('atcoder', $core->contest->platform);
        $this->assertSame('abc350', $core->contest->platformContestId);
        $this->assertNotEmpty($core->problems);
        $this->assertNotEmpty($core->rows);
        $this->assertSame(1, $core->rows[0]->rank);
    }
}
