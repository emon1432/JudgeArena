<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Transformers;

use App\Core\DTOs\SubmissionDTO;
use App\Enums\SubmissionVerdict;
use App\Platforms\AtCoder\DTOs\AtCoderSubmissionDTO;

class SubmissionTransformer
{
    /** @return SubmissionDTO */
    public function fromApiSubmission(AtCoderSubmissionDTO $submission): SubmissionDTO
    {
        $problemId = (string) ($submission->problem?->id ?? $submission->raw['taskId'] ?? '');

        $members = $submission->author?->members ?? [];
        $handle = is_array($members) && isset($members[0]['handle']) ? (string) $members[0]['handle'] : '';

        return new SubmissionDTO(
            platform: 'atcoder',
            platformSubmissionId: (string) ($submission->id ?? ''),
            problemPlatformId: $problemId,
            authorHandle: $handle,
            verdict: SubmissionVerdict::fromAtCoder($submission->verdict),
            language: $submission->programmingLanguage ?? null,
            passedTestCount: $submission->passedTestCount,
            timeConsumedMillis: $submission->timeConsumedMillis,
            createdAtSeconds: $submission->creationTimeSeconds,
            raw: $submission->raw,
            contestPlatformId: $submission->contestId,
            points: $submission->points,
            testset: $submission->testset,
            memoryConsumedBytes: $submission->memoryConsumedBytes,
        );
    }

    /**
     * @param AtCoderSubmissionDTO[] $submissions
     * @return array<int, SubmissionDTO>
     */
    public function fromApiSubmissions(array $submissions): array
    {
        return array_map(fn (AtCoderSubmissionDTO $submission): SubmissionDTO => $this->fromApiSubmission($submission), $submissions);
    }
}

