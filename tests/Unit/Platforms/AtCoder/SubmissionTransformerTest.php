<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Enums\SubmissionVerdict;
use App\Platforms\AtCoder\Mappers\AtCoderSubmissionMapper;
use App\Platforms\AtCoder\Transformers\SubmissionTransformer;
use Tests\TestCase;

class SubmissionTransformerTest extends TestCase
{
    public function test_submission_transformer_maps_atcoder_submission(): void
    {
        $submissionDto = AtCoderSubmissionMapper::fromNormalized([
            'submissionId' => '12345678',
            'taskId' => 'abc350_a',
            'taskTitle' => 'Past ABCs',
            'userName' => 'tourist',
            'status' => 'AC',
            'language' => 'C++ 20 (gcc 12.2)',
            'time' => '2024-04-20 21:30:00',
            'score' => 100.0,
            'execTime' => '15 ms',
            'memory' => '2048 KB',
        ]);

        $dto = (new SubmissionTransformer())->fromApiSubmission($submissionDto);

        $this->assertSame('atcoder', $dto->platform);
        $this->assertSame('12345678', $dto->platformSubmissionId);
        $this->assertSame('abc350_a', $dto->problemPlatformId);
        $this->assertSame('tourist', $dto->authorHandle);
        $this->assertSame(SubmissionVerdict::AC, $dto->verdict);
        $this->assertSame('C++ 20 (gcc 12.2)', $dto->language);
    }

    public function test_submission_transformer_maps_kenkoooo_submission(): void
    {
        $raw = [
            'id' => 6377009,
            'epoch_second' => 1563110232,
            'problem_id' => 'agc035_c',
            'contest_id' => 'agc035',
            'user_id' => 'tourist',
            'language' => 'C++14 (GCC 5.4.1)',
            'point' => 700.0,
            'length' => 2261,
            'result' => 'AC',
            'execution_time' => 37,
        ];

        $normalized = \App\Platforms\AtCoder\Support\ResponseNormalizer::submission($raw);
        $submissionDto = AtCoderSubmissionMapper::fromNormalized($normalized);
        $dto = (new SubmissionTransformer())->fromApiSubmission($submissionDto);

        $this->assertSame('atcoder', $dto->platform);
        $this->assertSame('6377009', $dto->platformSubmissionId);
        $this->assertSame('agc035_c', $dto->problemPlatformId);
        $this->assertSame('agc035', $dto->contestPlatformId);
        $this->assertSame('tourist', $dto->authorHandle);
        $this->assertSame(SubmissionVerdict::AC, $dto->verdict);
        $this->assertSame('C++14 (GCC 5.4.1)', $dto->language);
        $this->assertSame(700.0, $dto->points);
        $this->assertSame(37, $dto->timeConsumedMillis);
        $this->assertSame(1563110232, $dto->createdAtSeconds);
    }
}
