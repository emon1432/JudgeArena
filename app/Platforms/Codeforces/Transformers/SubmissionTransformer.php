<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\SubmissionDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO;

class SubmissionTransformer
{
    /**
     * @param CodeforcesSubmissionDTO|array<string,mixed> $submission
     */
    public function fromApiSubmission(CodeforcesSubmissionDTO|array $submission): SubmissionDTO
    {
        $dto = $submission instanceof CodeforcesSubmissionDTO
            ? $submission
            : CodeforcesSubmissionDTO::fromApiResponse($submission);

        $problem = $dto->problem ?? [];
        $author = $dto->author ?? [];

        $contestId = (string) ($problem['contestId'] ?? $dto->contestId ?? '0');
        $index = strtoupper(trim((string) ($problem['index'] ?? '')));

        $members = $author['members'] ?? [];
        $handle = is_array($members) && isset($members[0]['handle']) ? (string) $members[0]['handle'] : '';

        return new SubmissionDTO(
            platform: 'codeforces',
            platformSubmissionId: (string) ($dto->id ?? ''),
            problemPlatformId: $contestId . $index,
            authorHandle: $handle,
            verdict: $dto->verdict ?? null,
            language: $dto->programmingLanguage ?? null,
            passedTestCount: $dto->passedTestCount,
            timeConsumedMillis: $dto->timeConsumedMillis,
            createdAtSeconds: $dto->creationTimeSeconds,
            raw: $dto->raw,
        );
    }

    /** @return array<int, SubmissionDTO> */
    public function fromApiSubmissions(array $submissions): array
    {
        return array_map(fn (CodeforcesSubmissionDTO|array $submission): SubmissionDTO => $this->fromApiSubmission($submission), $submissions);
    }
}
