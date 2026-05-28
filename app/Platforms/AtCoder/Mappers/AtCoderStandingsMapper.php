<?php

namespace App\Platforms\AtCoder\Mappers;

use App\Platforms\AtCoder\DTOs\AtCoderProblemResultDTO;
use App\Platforms\AtCoder\DTOs\AtCoderRanklistRowDTO;
use App\Platforms\AtCoder\DTOs\AtCoderStandingsDTO;
use RuntimeException;

final class AtCoderStandingsMapper
{
    public static function fromApiResponse(array $standings): AtCoderStandingsDTO
    {
        if (! isset($standings['contest']) || ! is_array($standings['contest'])) {
            throw new RuntimeException('AtCoder standings payload missing contest.');
        }

        $contest = AtCoderContestMapper::fromNormalized($standings['contest']);
        $normalizedProblems = is_array($standings['problems'] ?? null) ? $standings['problems'] : [];
        $problems = AtCoderProblemMapper::fromNormalizedList($normalizedProblems);

        $rows = array_map(
            fn (array $row): AtCoderRanklistRowDTO => self::toRanklistRowDto($row, $normalizedProblems),
            $standings['rows'] ?? []
        );

        return new AtCoderStandingsDTO(
            contest: $contest,
            problems: $problems,
            rows: $rows,
            raw: $standings['raw'] ?? $standings,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $problems
     */
    private static function toRanklistRowDto(array $row, array $problems): AtCoderRanklistRowDTO
    {
        $members = [];
        $handle = $row['userScreenName'] ?? null;
        $name = $row['userName'] ?? null;

        if ($handle !== null || $name !== null) {
            $members[] = [
                'handle' => $handle,
                'name' => $name,
            ];
        }

        $party = AtCoderPartyMapper::fromNormalized([
            'contestId' => $row['contestId'] ?? null,
            'members' => $members,
            'participantType' => ($row['isTeam'] ?? false) ? 'TEAM' : 'CONTESTANT',
            'teamId' => null,
            'teamName' => ($row['isTeam'] ?? false) ? $name : null,
            'ghost' => null,
            'room' => null,
            'startTimeSeconds' => null,
        ]);

        $total = is_array($row['totalResult'] ?? null) ? $row['totalResult'] : [];
        $taskResults = is_array($row['taskResults'] ?? null) ? $row['taskResults'] : [];

        $problemResults = [];
        foreach ($problems as $problem) {
            $taskId = $problem['id'] ?? null;
            $taskResult = is_string($taskId) && isset($taskResults[$taskId]) && is_array($taskResults[$taskId])
                ? $taskResults[$taskId]
                : [];

            $problemResults[] = new AtCoderProblemResultDTO(
                points: isset($taskResult['score']) && is_numeric($taskResult['score']) ? (float) $taskResult['score'] : null,
                penalty: isset($taskResult['penalty']) ? (int) $taskResult['penalty'] : null,
                rejectedAttemptCount: isset($taskResult['failure']) ? (int) $taskResult['failure'] : null,
                type: isset($taskResult['status']) ? (string) $taskResult['status'] : null,
                bestSubmissionTimeSeconds: self::normalizeElapsedSeconds($taskResult['elapsed'] ?? null),
            );
        }

        return new AtCoderRanklistRowDTO(
            party: $party,
            rank: isset($row['rank']) ? (int) $row['rank'] : null,
            points: isset($total['score']) ? (int) $total['score'] : null,
            penalty: isset($total['penalty']) ? (int) $total['penalty'] : null,
            successfulHackCount: null,
            unsuccessfulHackCount: null,
            problemResults: $problemResults,
            lastSubmissionTimeSeconds: self::normalizeElapsedSeconds($total['elapsed'] ?? null),
            raw: $row,
        );
    }

    private static function normalizeElapsedSeconds(mixed $elapsed): ?int
    {
        if (! is_numeric($elapsed)) {
            return null;
        }

        $value = (float) $elapsed;
        if ($value <= 0) {
            return (int) $value;
        }

        if ($value > 1000000) {
            $value = $value / 1000000000;
        }

        return (int) floor($value);
    }
}

