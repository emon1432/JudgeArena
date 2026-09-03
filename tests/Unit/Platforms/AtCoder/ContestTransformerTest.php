<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\Mappers\AtCoderContestMapper;
use App\Platforms\AtCoder\Transformers\ContestTransformer;
use Tests\TestCase;

class ContestTransformerTest extends TestCase
{
    public function test_contest_transformer_maps_atcoder_contest_with_epoch_and_rate_change(): void
    {
        $raw = [
            'id' => 'abc350',
            'title' => 'AtCoder Beginner Contest 350',
            'start_epoch_second' => 1713614400,
            'duration_second' => 6000,
            'rate_change' => '~ 1999',
        ];

        $contestDto = AtCoderContestMapper::fromNormalized($raw);
        $dto = (new ContestTransformer())->fromApiContest($contestDto);

        $this->assertSame('atcoder', $dto->platform);
        $this->assertSame('abc350', $dto->platformContestId);
        $this->assertSame('AtCoder Beginner Contest 350', $dto->title);
        $this->assertSame('ABC', $dto->type);
        $this->assertSame(6000, $dto->durationSeconds);
        $this->assertSame(1713614400, $dto->startedAt?->getTimestamp());
        $this->assertSame('FINISHED', $dto->phase);
        $this->assertSame('https://atcoder.jp/contests/abc350', $dto->url);

        // Rate change spec verification
        $this->assertTrue($dto->raw['is_rated']);
        $this->assertSame(1999, $dto->raw['rate_change_spec']['max_rating']);
        $this->assertSame(0, $dto->raw['rate_change_spec']['min_rating']);
        $this->assertSame('Rated for ≤ 1999', $dto->raw['rate_change_spec']['label']);
    }

    public function test_contest_transformer_parses_all_rate_change_variations(): void
    {
        $transformer = new ContestTransformer();

        // 1. Unrated variations
        foreach (['-', '', null, '~ 0', '0 ~ 0', '~0'] as $unrated) {
            $raw = ['id' => 'test1', 'title' => 'Test', 'rate_change' => $unrated];
            $dto = $transformer->fromApiContest(AtCoderContestMapper::fromNormalized($raw));
            $this->assertFalse($dto->raw['is_rated']);
            $this->assertNull($dto->raw['rate_change_spec']['min_rating']);
            $this->assertNull($dto->raw['rate_change_spec']['max_rating']);
            $this->assertSame('Unrated', $dto->raw['rate_change_spec']['label']);
        }

        // 2. Rated for All
        $dtoAll = $transformer->fromApiContest(AtCoderContestMapper::fromNormalized([
            'id' => 'ahc030', 'title' => 'AHC 030', 'rate_change' => 'All',
        ]));
        $this->assertTrue($dtoAll->raw['is_rated']);
        $this->assertSame(0, $dtoAll->raw['rate_change_spec']['min_rating']);
        $this->assertNull($dtoAll->raw['rate_change_spec']['max_rating']);
        $this->assertSame('Rated for All', $dtoAll->raw['rate_change_spec']['label']);

        // 3. Upper bound only
        $dtoUpper = $transformer->fromApiContest(AtCoderContestMapper::fromNormalized([
            'id' => 'abc350', 'title' => 'ABC 350', 'rate_change' => '~ 1999',
        ]));
        $this->assertTrue($dtoUpper->raw['is_rated']);
        $this->assertSame(0, $dtoUpper->raw['rate_change_spec']['min_rating']);
        $this->assertSame(1999, $dtoUpper->raw['rate_change_spec']['max_rating']);

        // 4. Range bound
        $dtoRange = $transformer->fromApiContest(AtCoderContestMapper::fromNormalized([
            'id' => 'arc180', 'title' => 'ARC 180', 'rate_change' => '1200 ~ 2799',
        ]));
        $this->assertTrue($dtoRange->raw['is_rated']);
        $this->assertSame(1200, $dtoRange->raw['rate_change_spec']['min_rating']);
        $this->assertSame(2799, $dtoRange->raw['rate_change_spec']['max_rating']);
        $this->assertSame('Rated for 1200 ~ 2799', $dtoRange->raw['rate_change_spec']['label']);

        // 5. Lower bound open-ended
        $dtoOpen = $transformer->fromApiContest(AtCoderContestMapper::fromNormalized([
            'id' => 'agc060', 'title' => 'AGC 060', 'rate_change' => '2000 ~',
        ]));
        $this->assertTrue($dtoOpen->raw['is_rated']);
        $this->assertSame(2000, $dtoOpen->raw['rate_change_spec']['min_rating']);
        $this->assertNull($dtoOpen->raw['rate_change_spec']['max_rating']);
        $this->assertSame('Rated for ≥ 2000', $dtoOpen->raw['rate_change_spec']['label']);
    }

    public function test_contest_transformer_classifies_contest_types_accurately(): void
    {
        $cases = [
            ['id' => 'abc350', 'title' => 'AtCoder Beginner Contest 350', 'expected' => 'ABC'],
            ['id' => 'arc180', 'title' => 'AtCoder Regular Contest 180', 'expected' => 'ARC'],
            ['id' => 'agc060', 'title' => 'AtCoder Grand Contest 060', 'expected' => 'AGC'],
            ['id' => 'ahc030', 'title' => 'AtCoder Heuristic Contest 030', 'expected' => 'AHC'],
            ['id' => 'adt_all_20240101', 'title' => 'AtCoder Daily Training ALL', 'expected' => 'ADT'],
            ['id' => 'awc001', 'title' => 'AtCoder Weekday Contest 001', 'expected' => 'AWC'],
            ['id' => 'past202005', 'title' => 'Practical Algorithm Skill Test', 'expected' => 'PAST'],
            ['id' => 'joi2024yo1a', 'title' => 'JOI 2023/2024 First Qualifying Round', 'expected' => 'JOI'],
            ['id' => 'masters2024', 'title' => 'Toyota Programming Contest (Masters)', 'expected' => 'Heuristic'],
            ['id' => 'practice2', 'title' => 'AtCoder Library Practice Contest', 'expected' => 'Algorithm'],
        ];

        $transformer = new ContestTransformer();

        foreach ($cases as $case) {
            $raw = [
                'id' => $case['id'],
                'title' => $case['title'],
                'start_epoch_second' => 1700000000,
                'duration_second' => 7200,
                'rate_change' => '-',
            ];

            $dto = $transformer->fromApiContest(AtCoderContestMapper::fromNormalized($raw));
            $this->assertSame($case['expected'], $dto->type, "Failed for contest ID: {$case['id']}");
        }
    }
}
