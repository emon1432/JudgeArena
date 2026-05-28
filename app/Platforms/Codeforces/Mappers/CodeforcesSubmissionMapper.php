<?php

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;

final class CodeforcesSubmissionMapper
{
    public static function fromNormalized(array $submission): CodeforcesSubmissionDTO
    {
        $normalized = ResponseNormalizer::submission($submission);

        return new CodeforcesSubmissionDTO(
            id: isset($normalized['id']) ? (string) $normalized['id'] : null,
            contestId: isset($normalized['contestId']) ? (int) $normalized['contestId'] : null,
            creationTimeSeconds: isset($normalized['creationTimeSeconds']) ? (int) $normalized['creationTimeSeconds'] : null,
            relativeTimeSeconds: isset($normalized['relativeTimeSeconds']) ? (int) $normalized['relativeTimeSeconds'] : null,
            problem: CodeforcesProblemMapper::fromNormalized(is_array($normalized['problem'] ?? null) ? $normalized['problem'] : null),
            author: CodeforcesPartyMapper::fromNormalized(is_array($normalized['author'] ?? null) ? $normalized['author'] : null),
            programmingLanguage: $normalized['programmingLanguage'] ?? null,
            verdict: $normalized['verdict'] ?? null,
            testset: $normalized['testset'] ?? null,
            passedTestCount: isset($normalized['passedTestCount']) ? (int) $normalized['passedTestCount'] : null,
            timeConsumedMillis: isset($normalized['timeConsumedMillis']) ? (int) $normalized['timeConsumedMillis'] : null,
            memoryConsumedBytes: isset($normalized['memoryConsumedBytes']) ? (int) $normalized['memoryConsumedBytes'] : null,
            points: isset($normalized['points']) ? (float) $normalized['points'] : null,
            raw: $submission,
        );
    }

    /** @return array<int, CodeforcesSubmissionDTO> */
    public static function fromNormalizedList(array $submissions): array
    {
        return array_map(fn (array $submission): CodeforcesSubmissionDTO => self::fromNormalized($submission), $submissions);
    }
}
