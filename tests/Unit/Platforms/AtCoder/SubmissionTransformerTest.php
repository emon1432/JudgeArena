<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Enums\SubmissionVerdict;
use App\Platforms\AtCoder\Mappers\AtCoderSubmissionMapper;
use App\Platforms\AtCoder\Transformers\SubmissionTransformer;
use Tests\TestCase;

class SubmissionTransformerTest extends TestCase
{
    public function test_from_api_submission_maps_ac_verdict(): void
    {
        $dto = AtCoderSubmissionMapper::fromNormalized([
            'submissionId' => '12345678',
            'contestId' => 'abc300',
            'taskId' => 'abc300_a',
            'userName' => 'tourist',
            'language' => 'C++ 20 (gcc 12.2)',
            'result' => 'AC',
            'execTime' => '15 ms',
            'memory' => '2048 KB',
            'time' => '2023-04-29 21:00:00',
            'score' => 100.0,
        ]);

        $transformer = new SubmissionTransformer;
        $core = $transformer->fromApiSubmission($dto);

        $this->assertSame('atcoder', $core->platform);
        $this->assertSame('12345678', $core->platformSubmissionId);
        $this->assertSame('abc300_a', $core->problemPlatformId);
        $this->assertSame('tourist', $core->authorHandle);
        $this->assertSame(SubmissionVerdict::AC, $core->verdict);
        $this->assertSame('C++ 20 (gcc 12.2)', $core->language);
        $this->assertSame(15, $core->timeConsumedMillis);
        $this->assertSame(100.0, $core->points);
    }
}
