<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesContestMapper;
use App\Platforms\Codeforces\Transformers\ContestTransformer;
use Tests\TestCase;

class ContestTransformerTest extends TestCase
{
    public function test_from_api_contest_transforms_codeforces_contest(): void
    {
        $cfContest = CodeforcesContestMapper::fromNormalized([
            'id' => 1000,
            'name' => 'Codeforces Round 1000 (Div. 2)',
            'type' => 'CF',
            'phase' => 'FINISHED',
            'frozen' => false,
            'durationSeconds' => 7200,
            'startTimeSeconds' => 1672531200,
            'relativeTimeSeconds' => 7200,
            'url' => 'https://codeforces.com/contest/1000',
        ]);

        $transformer = new ContestTransformer();
        $dto = $transformer->fromApiContest($cfContest);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame('1000', $dto->platformContestId);
        $this->assertSame('Codeforces Round 1000 (Div. 2)', $dto->title);
        $this->assertSame('CF', $dto->type);
        $this->assertSame('FINISHED', $dto->phase);
        $this->assertSame(7200, $dto->durationSeconds);
        $this->assertNotNull($dto->startedAt);
        $this->assertNotNull($dto->endedAt);
    }
}
