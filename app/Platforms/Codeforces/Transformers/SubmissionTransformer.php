<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\SubmissionDTO;
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
            verdict: $submission->verdict ?? null,
            language: $submission->programmingLanguage ?? null,
            passedTestCount: $submission->passedTestCount,
            timeConsumedMillis: $submission->timeConsumedMillis,
            createdAtSeconds: $submission->creationTimeSeconds,
            raw: $submission->raw,
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
