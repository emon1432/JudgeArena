<?php

namespace App\Platforms\AtCoder\Mappers;

use App\Platforms\AtCoder\DTOs\AtCoderSubmissionDTO;

final class AtCoderSubmissionMapper
{
    public static function fromNormalized(array $submission): AtCoderSubmissionDTO
    {
        $taskId = isset($submission['taskId']) ? (string) $submission['taskId'] : null;
        $contestId = self::extractContestId($taskId);
        $contestId = ! empty($submission['contestId']) ? (string) $submission['contestId'] : self::extractContestId($taskId);
        $problemPosition = self::extractProblemPosition($taskId);

        $problem = AtCoderProblemMapper::fromNormalized([
            'id' => $taskId,
            'contestId' => $contestId,
            'title' => $submission['taskTitle'] ?? null,
            'position' => $problemPosition,
            'fullTitle' => $submission['taskTitle'] ?? null,
            'score' => $submission['score'] ?? null,
            'timeLimit' => null,
            'memoryLimit' => null,
            'url' => $submission['taskUrl'] ?? null,
        ]);

        $author = AtCoderPartyMapper::fromNormalized([
            'contestId' => $contestId,
            'members' => [
                [
                    'handle' => $submission['userName'] ?? null,
                    'name' => $submission['userName'] ?? null,
                ],
            ],
            'participantType' => null,
            'teamId' => null,
            'teamName' => null,
            'ghost' => null,
            'room' => null,
            'startTimeSeconds' => null,
        ]);

        return new AtCoderSubmissionDTO(
            id: isset($submission['submissionId']) ? (string) $submission['submissionId'] : null,
            contestId: $contestId,
            creationTimeSeconds: self::parseTimestamp($submission['time'] ?? null),
            relativeTimeSeconds: null,
            problem: $problem,
            author: $author,
            programmingLanguage: $submission['language'] ?? null,
            verdict: $submission['status'] ?? $submission['result'] ?? null,
            testset: null,
            passedTestCount: null,
            timeConsumedMillis: self::parseExecTime($submission['execTime'] ?? null),
            memoryConsumedBytes: self::parseMemoryBytes($submission['memory'] ?? null),
            points: isset($submission['score']) && is_numeric($submission['score']) ? (float) $submission['score'] : null,
            raw: $submission,
        );
    }

    /** @return array<int, AtCoderSubmissionDTO> */
    public static function fromNormalizedList(array $submissions): array
    {
        return array_map(fn (array $submission): AtCoderSubmissionDTO => self::fromNormalized($submission), $submissions);
    }

    private static function extractContestId(?string $taskId): ?string
    {
        if ($taskId === null || $taskId === '') {
            return null;
        }

        $pos = strrpos($taskId, '_');
        if ($pos === false) {
            return $taskId;
        }

        return substr($taskId, 0, $pos);
    }

    private static function extractProblemPosition(?string $taskId): ?string
    {
        if ($taskId === null || $taskId === '') {
            return null;
        }

        $pos = strrpos($taskId, '_');
        if ($pos === false || $pos === (strlen($taskId) - 1)) {
            return null;
        }

        return strtoupper(substr($taskId, $pos + 1));
    }

    private static function parseTimestamp(mixed $time): ?int
    {
        if ($time === null || $time === '') {
            return null;
        }

        if (is_numeric($time)) {
            return (int) $time;
        }

        $timestamp = strtotime((string) $time);

        return $timestamp === false ? null : (int) $timestamp;
    }

    private static function parseExecTime(mixed $execTime): ?int
    {
        if ($execTime === null || $execTime === '') {
            return null;
        }

        if (is_numeric($execTime)) {
            return (int) $execTime;
        }

        if (preg_match('/\d+/', (string) $execTime, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    private static function parseMemoryBytes(mixed $memory): ?int
    {
        if ($memory === null || $memory === '') {
            return null;
        }

        if (is_numeric($memory)) {
            return (int) $memory;
        }

        if (! preg_match('/([\d.]+)\s*([a-zA-Z]+)?/', (string) $memory, $matches)) {
            return null;
        }

        $value = (float) $matches[1];
        $unit = strtoupper($matches[2] ?? '');

        switch ($unit) {
            case 'KIB':
            case 'KB':
                $value *= 1024;
                break;
            case 'MIB':
            case 'MB':
                $value *= 1024 * 1024;
                break;
            case 'GIB':
            case 'GB':
                $value *= 1024 * 1024 * 1024;
                break;
            default:
                break;
        }

        return (int) round($value);
    }
}

