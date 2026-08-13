<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesProblemResultDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesRanklistRowDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesStandingsDTO;
use RuntimeException;

final class CodeforcesStandingsMapper
{
    public static function fromApiResponse(array $standings): CodeforcesStandingsDTO
    {
        if (! isset($standings['contest']) || ! is_array($standings['contest'])) {
            throw new RuntimeException('Codeforces standings payload missing contest.');
        }

        $contest = CodeforcesContestMapper::fromNormalized($standings['contest']);

        $problems = CodeforcesProblemMapper::fromNormalizedList($standings['problems'] ?? []);

        $rows = array_map(
            fn (array $row): CodeforcesRanklistRowDTO => self::toRanklistRowDto($row),
            $standings['rows'] ?? []
        );

        return new CodeforcesStandingsDTO(
            contest: $contest,
            problems: $problems,
            rows: $rows,
            raw: $standings,
        );
    }

    private static function toRanklistRowDto(array $row): CodeforcesRanklistRowDTO
    {
        $party = $row['party'] ?? null;
        $partyDto = CodeforcesPartyMapper::fromNormalized(is_array($party) ? $party : null);

        $problemResults = [];
        foreach ($row['problemResults'] ?? [] as $problemResult) {
            if (! is_array($problemResult)) {
                continue;
            }

            $problemResults[] = new CodeforcesProblemResultDTO(
                points: isset($problemResult['points']) ? (float) $problemResult['points'] : null,
                penalty: isset($problemResult['penalty']) ? (int) $problemResult['penalty'] : null,
                rejectedAttemptCount: isset($problemResult['rejectedAttemptCount']) ? (int) $problemResult['rejectedAttemptCount'] : null,
                type: $problemResult['type'] ?? null,
                bestSubmissionTimeSeconds: isset($problemResult['bestSubmissionTimeSeconds']) ? (int) $problemResult['bestSubmissionTimeSeconds'] : null,
            );
        }

        return new CodeforcesRanklistRowDTO(
            party: $partyDto,
            rank: isset($row['rank']) ? (int) $row['rank'] : null,
            points: isset($row['points']) ? (int) $row['points'] : null,
            penalty: isset($row['penalty']) ? (int) $row['penalty'] : null,
            successfulHackCount: isset($row['successfulHackCount']) ? (int) $row['successfulHackCount'] : null,
            unsuccessfulHackCount: isset($row['unsuccessfulHackCount']) ? (int) $row['unsuccessfulHackCount'] : null,
            problemResults: $problemResults,
            lastSubmissionTimeSeconds: isset($row['lastSubmissionTimeSeconds']) ? (int) $row['lastSubmissionTimeSeconds'] : null,
            raw: $row,
        );
    }
}

