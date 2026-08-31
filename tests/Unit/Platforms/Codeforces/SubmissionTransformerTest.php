<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Enums\SubmissionVerdict;
use App\Platforms\Codeforces\Mappers\CodeforcesSubmissionMapper;
use App\Platforms\Codeforces\Transformers\SubmissionTransformer;
use Tests\TestCase;

class SubmissionTransformerTest extends TestCase
{
    public function test_submission_transformer_maps_submission_payload(): void
    {
        $submission = [
            'id' => 300123456,
            'contestId' => 2225,
            'creationTimeSeconds' => 1776785000,
            'relativeTimeSeconds' => 2900,
            'problem' => [
                'contestId' => 2225,
                'index' => 'A',
                'name' => 'A Number Between Two Others',
                'type' => 'PROGRAMMING',
                'points' => 500,
                'rating' => 800,
                'tags' => ['greedy', 'math'],
            ],
            'author' => [
                'contestId' => 2225,
                'members' => [
                    ['handle' => 'tourist'],
                ],
                'participantType' => 'CONTESTANT',
                'ghost' => false,
                'startTimeSeconds' => 1776782100,
            ],
            'programmingLanguage' => 'C++23 (GCC 14-64, msys2)',
            'verdict' => 'OK',
            'testset' => 'TESTS',
            'passedTestCount' => 30,
            'timeConsumedMillis' => 15,
            'memoryConsumedBytes' => 2097152,
        ];

        $submissionDto = CodeforcesSubmissionMapper::fromNormalized($submission);
        $dto = (new SubmissionTransformer())->fromApiSubmission($submissionDto);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame('300123456', $dto->platformSubmissionId);
        $this->assertSame(SubmissionVerdict::AC, $dto->verdict);
        $this->assertSame('C++23 (GCC 14-64, msys2)', $dto->language);
        $this->assertSame($submission, $submissionDto->raw);
    }
}
