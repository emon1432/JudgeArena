<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\SubmissionDTO;
use App\Enums\SubmissionVerdict;
use App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO;

class SubmissionTransformer
{
    /** @return SubmissionDTO */
    public function fromApiSubmission(CodeforcesSubmissionDTO $submission): SubmissionDTO
    {
        $contestId = (string) ($submission->problem?->contestId ?? $submission->contestId ?? '0');
        $index = strtoupper(trim((string) ($submission->problem?->index ?? '')));

        $members = $submission->author?->members ?? [];
        $handle = is_array($members) && isset($members[0]['handle']) ? (string) $members[0]['handle'] : '';

        return new SubmissionDTO(
            platform: 'codeforces',
            platformSubmissionId: (string) ($submission->id ?? ''),
            problemPlatformId: $contestId . $index,
            authorHandle: $handle,
            verdict: SubmissionVerdict::fromCodeforces($submission->verdict),
            language: $submission->programmingLanguage ?? null,
            passedTestCount: $submission->passedTestCount,
            timeConsumedMillis: $submission->timeConsumedMillis,
            createdAtSeconds: $submission->creationTimeSeconds,
            raw: $submission->raw,
            contestPlatformId: $contestId !== '0' ? $contestId : null,
            points: $submission->points,
            testset: $submission->testset,
            memoryConsumedBytes: $submission->memoryConsumedBytes,
        );
    }

    /**
     * @param CodeforcesSubmissionDTO[] $submissions
     * @return array<int, SubmissionDTO>
     */
    public function fromApiSubmissions(array $submissions): array
    {
        return array_map(fn (CodeforcesSubmissionDTO $submission): SubmissionDTO => $this->fromApiSubmission($submission), $submissions);
    }
}


