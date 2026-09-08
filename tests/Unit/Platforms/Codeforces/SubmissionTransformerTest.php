<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Enums\SubmissionVerdict;
use App\Platforms\Codeforces\Mappers\CodeforcesSubmissionMapper;
use App\Platforms\Codeforces\Transformers\SubmissionTransformer;
use Tests\TestCase;

class SubmissionTransformerTest extends TestCase
{
    public function test_from_api_submission_maps_codeforces_ok_to_ac(): void
    {
        $dto = CodeforcesSubmissionMapper::fromNormalized([
            'id' => 99887766,
            'contestId' => 1000,
            'problem' => ['contestId' => 1000, 'index' => 'A'],
            'author' => ['members' => [['handle' => 'tourist']]],
            'programmingLanguage' => 'GNU C++17',
            'verdict' => 'OK',
            'testset' => 'TESTS',
            'passedTestCount' => 45,
            'timeConsumedMillis' => 30,
            'memoryConsumedBytes' => 1048576,
            'creationTimeSeconds' => 1672531500,
            'points' => 500.0,
        ]);

        $transformer = new SubmissionTransformer();
        $core = $transformer->fromApiSubmission($dto);

        $this->assertSame('codeforces', $core->platform);
        $this->assertSame('99887766', $core->platformSubmissionId);
        $this->assertSame('1000A', $core->problemPlatformId);
        $this->assertSame('tourist', $core->authorHandle);
        $this->assertSame(SubmissionVerdict::AC, $core->verdict);
        $this->assertSame('GNU C++17', $core->language);
        $this->assertSame(30, $core->timeConsumedMillis);
        $this->assertSame(1048576, $core->memoryConsumedBytes);
    }
}

