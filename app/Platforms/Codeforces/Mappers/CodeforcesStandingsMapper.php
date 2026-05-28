<?php

namespace App\Platforms\Codeforces\Mappers;

use App\Platforms\Codeforces\DTOs\CodeforcesPartyDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesProblemResultDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesRanklistRowDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesStandingsDTO;

final class CodeforcesStandingsMapper
{
    public static function fromApiResponse(array $standings): CodeforcesStandingsDTO
    {
        $contest = isset($standings['contest']) && is_array($standings['contest'])
            ? CodeforcesContestMapper::fromNormalized($standings['contest'])
            : null;

        $problems = array_map(
            fn (array $problem): CodeforcesProblemDTO => new CodeforcesProblemDTO(
                contestId: isset($problem['contestId']) ? (string) $problem['contestId'] : null,
                problemsetName: $problem['problemsetName'] ?? null,
                index: isset($problem['index']) ? (string) $problem['index'] : null,
                name: $problem['name'] ?? null,
                type: $problem['type'] ?? null,
                points: isset($problem['points']) ? (int) $problem['points'] : null,
                rating: isset($problem['rating']) ? (int) $problem['rating'] : null,
                tags: is_array($problem['tags'] ?? null) ? $problem['tags'] : [],
                raw: $problem,
            ),
            $standings['problems'] ?? []
        );

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
        $partyDto = is_array($party) ? self::toPartyDto($party) : null;

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

    private static function toPartyDto(array $party): CodeforcesPartyDTO
    {
        return new CodeforcesPartyDTO(
            contestId: isset($party['contestId']) ? (int) $party['contestId'] : null,
            members: is_array($party['members'] ?? null) ? $party['members'] : [],
            participantType: $party['participantType'] ?? null,
            teamId: isset($party['teamId']) ? (int) $party['teamId'] : null,
            teamName: $party['teamName'] ?? null,
            ghost: array_key_exists('ghost', $party) ? (bool) $party['ghost'] : null,
            room: isset($party['room']) ? (int) $party['room'] : null,
            startTimeSeconds: isset($party['startTimeSeconds']) ? (int) $party['startTimeSeconds'] : null,
            raw: $party,
        );
    }
}
