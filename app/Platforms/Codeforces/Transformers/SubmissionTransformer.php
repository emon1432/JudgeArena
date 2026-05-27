<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\SubmissionDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;

class SubmissionTransformer
{
    public function fromApiSubmission(array $submission): SubmissionDTO
    {
        $normalized = ResponseNormalizer::submission($submission);
        $problem = $normalized['problem'] ?? [];
        $author = $normalized['author'] ?? [];

        $contestId = (string) ($problem['contestId'] ?? $normalized['contestId'] ?? '0');
        $index = strtoupper(trim((string) ($problem['index'] ?? '')));

        $members = $author['members'] ?? [];
        $handle = is_array($members) && isset($members[0]['handle']) ? (string) $members[0]['handle'] : '';

        return new SubmissionDTO(
            platform: 'codeforces',
            platformSubmissionId: (string) ($normalized['id'] ?? ''),
            problemPlatformId: $contestId . $index,
            authorHandle: $handle,
            verdict: $normalized['verdict'] ?? null,
            language: $normalized['programmingLanguage'] ?? null,
            passedTestCount: isset($normalized['passedTestCount']) ? (int) $normalized['passedTestCount'] : null,
            timeConsumedMillis: isset($normalized['timeConsumedMillis']) ? (int) $normalized['timeConsumedMillis'] : null,
            createdAtSeconds: isset($normalized['creationTimeSeconds']) ? (int) $normalized['creationTimeSeconds'] : null,
            raw: $normalized,
        );
    }

    /** @return array<int, SubmissionDTO> */
    public function fromApiSubmissions(array $submissions): array
    {
        return array_map(fn (array $submission): SubmissionDTO => $this->fromApiSubmission($submission), $submissions);
    }
}
