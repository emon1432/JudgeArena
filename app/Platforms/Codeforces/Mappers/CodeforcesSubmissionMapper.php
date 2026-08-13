<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO;

final class CodeforcesSubmissionMapper
{
    public static function fromNormalized(array $submission): CodeforcesSubmissionDTO
    {
        return new CodeforcesSubmissionDTO(
            id: isset($submission['id']) ? (string) $submission['id'] : null,
            contestId: isset($submission['contestId']) ? (int) $submission['contestId'] : null,
            creationTimeSeconds: isset($submission['creationTimeSeconds']) ? (int) $submission['creationTimeSeconds'] : null,
            relativeTimeSeconds: isset($submission['relativeTimeSeconds']) ? (int) $submission['relativeTimeSeconds'] : null,
            problem: CodeforcesProblemMapper::fromNormalized(is_array($submission['problem'] ?? null) ? $submission['problem'] : null),
            author: CodeforcesPartyMapper::fromNormalized(is_array($submission['author'] ?? null) ? $submission['author'] : null),
            programmingLanguage: $submission['programmingLanguage'] ?? null,
            verdict: $submission['verdict'] ?? null,
            testset: $submission['testset'] ?? null,
            passedTestCount: isset($submission['passedTestCount']) ? (int) $submission['passedTestCount'] : null,
            timeConsumedMillis: isset($submission['timeConsumedMillis']) ? (int) $submission['timeConsumedMillis'] : null,
            memoryConsumedBytes: isset($submission['memoryConsumedBytes']) ? (int) $submission['memoryConsumedBytes'] : null,
            points: isset($submission['points']) ? (float) $submission['points'] : null,
            raw: $submission,
        );
    }

    /** @return array<int, CodeforcesSubmissionDTO> */
    public static function fromNormalizedList(array $submissions): array
    {
        return array_map(fn (array $submission): CodeforcesSubmissionDTO => self::fromNormalized($submission), $submissions);
    }
}

