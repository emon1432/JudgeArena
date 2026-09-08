<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\Mappers\AtCoderContestMapper;
use App\Platforms\AtCoder\Transformers\ContestTransformer;
use Tests\TestCase;

class ContestTransformerTest extends TestCase
{
    public function test_from_api_contest_transforms_atcoder_contest_dto(): void
    {
        $atcoderContest = AtCoderContestMapper::fromNormalized([
            'id' => 'abc300',
            'start_epoch_second' => 1682769600,
            'duration_second' => 6000,
            'title' => 'AtCoder Beginner Contest 300',
            'rate_change' => ' ~ 1999',
        ]);

        $transformer = new ContestTransformer;
        $dto = $transformer->fromApiContest($atcoderContest);

        $this->assertSame('atcoder', $dto->platform);
        $this->assertSame('abc300', $dto->platformContestId);
        $this->assertSame('AtCoder Beginner Contest 300', $dto->title);
        $this->assertSame(6000, $dto->durationSeconds);
        $this->assertSame('FINISHED', $dto->phase);
        $this->assertSame('ABC', $dto->type);
        $this->assertNotNull($dto->startedAt);
        $this->assertNotNull($dto->endedAt);
        $this->assertTrue($dto->raw['is_rated']);
    }

    public function test_from_api_contest_handles_permanent_and_tutorial_contests(): void
    {
        $atcoderContest = AtCoderContestMapper::fromNormalized([
            'id' => 'abs',
            'start_epoch_second' => 0,
            'duration_second' => 3153600000,
            'title' => 'AtCoder Beginners Selection',
            'rate_change' => '-',
        ]);

        $transformer = new ContestTransformer;
        $dto = $transformer->fromApiContest($atcoderContest);

        $this->assertSame('atcoder', $dto->platform);
        $this->assertSame('abs', $dto->platformContestId);
        $this->assertSame('AtCoder Beginners Selection', $dto->title);
        $this->assertNull($dto->durationSeconds);
        $this->assertNull($dto->startedAt);
        $this->assertNull($dto->endedAt);
        $this->assertSame('CODING', $dto->phase);
        $this->assertFalse($dto->raw['is_rated']);
    }
}
