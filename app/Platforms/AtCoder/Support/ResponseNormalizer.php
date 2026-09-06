<?php

namespace App\Platforms\AtCoder\Support;

final class ResponseNormalizer
{
    public static function contests(array $payload): array
    {
        return array_map([self::class, 'contest'], self::unwrapList($payload));
    }

    public static function contest(array $contest): array
    {
        return array_merge([
            'id' => null,
            'title' => null,
            'url' => null,
            'date' => null,
            'duration' => null,
            'rateChange' => null,
            'type' => null,
            'start_epoch_second' => null,
            'duration_second' => null,
        ], [
            'id' => $contest['id'] ?? null,
            'title' => $contest['title'] ?? null,
            'url' => $contest['url'] ?? null,
            'date' => $contest['date'] ?? null,
            'duration' => $contest['duration'] ?? null,
            'rateChange' => $contest['rateChange'] ?? $contest['rate_change'] ?? null,
            'type' => $contest['type'] ?? null,
            'start_epoch_second' => $contest['start_epoch_second'] ?? $contest['startEpochSecond'] ?? null,
            'duration_second' => $contest['duration_second'] ?? $contest['durationSecond'] ?? null,
        ]);
    }

    public static function problems(array $payload): array
    {
        return array_map([self::class, 'problem'], self::unwrapList($payload));
    }

    public static function problem(array $problem): array
    {
        return array_merge([
            'id' => null,
            'contestId' => null,
            'title' => null,
            'position' => null,
            'fullTitle' => null,
            'score' => null,
            'rating' => null,
            'timeLimit' => null,
            'memoryLimit' => null,
            'solverCount' => null,
            'url' => null,
        ], [
            'id' => $problem['id'] ?? null,
            'contestId' => $problem['contestId'] ?? $problem['contest_id'] ?? null,
            'title' => $problem['title'] ?? $problem['name'] ?? null,
            'position' => $problem['position'] ?? $problem['problem_index'] ?? null,
            'fullTitle' => $problem['fullTitle'] ?? $problem['full_title'] ?? null,
            'score' => $problem['score'] ?? $problem['point'] ?? $problem['points'] ?? null,
            'rating' => $problem['rating'] ?? $problem['difficulty'] ?? null,
            'timeLimit' => $problem['timeLimit'] ?? $problem['time_limit'] ?? (isset($problem['execution_time']) ? $problem['execution_time'] . ' ms' : null),
            'memoryLimit' => $problem['memoryLimit'] ?? $problem['memory_limit'] ?? null,
            'solverCount' => $problem['solverCount'] ?? $problem['solver_count'] ?? null,
            'url' => $problem['url'] ?? null,
        ]);
    }

    public static function submissions(array $payload): array
    {
        return array_map([self::class, 'submission'], self::unwrapList($payload));
    }

    public static function submission(array $submission): array
    {
        $submissionId = $submission['id'] ?? $submission['submissionId'] ?? $submission['submission_id'] ?? null;
        $contestId = $submission['contest_id'] ?? $submission['contestId'] ?? null;
        $problemId = $submission['problem_id'] ?? $submission['taskId'] ?? $submission['task_id'] ?? null;
        $userName = $submission['user_id'] ?? $submission['userName'] ?? $submission['username'] ?? $submission['user'] ?? null;
        $time = $submission['epoch_second'] ?? $submission['epochSecond'] ?? $submission['time'] ?? null;
        $score = $submission['point'] ?? $submission['score'] ?? null;
        $codeSize = $submission['length'] ?? $submission['codeSize'] ?? $submission['code_size'] ?? null;
        $result = $submission['result'] ?? $submission['status'] ?? $submission['verdict'] ?? null;
        $execTime = $submission['execution_time'] ?? $submission['execTime'] ?? $submission['exec_time'] ?? null;

        $taskUrl = null;
        if ($contestId && $problemId) {
            $taskUrl = "https://atcoder.jp/contests/{$contestId}/tasks/{$problemId}";
        }

        $detailUrl = null;
        if ($contestId && $submissionId) {
            $detailUrl = "https://atcoder.jp/contests/{$contestId}/submissions/{$submissionId}";
        }

        return [
            'submissionId' => $submissionId !== null ? (string) $submissionId : null,
            'contestId' => $contestId !== null ? (string) $contestId : null,
            'time' => $time !== null ? (string) $time : null,
            'taskId' => $problemId !== null ? (string) $problemId : null,
            'taskTitle' => $submission['taskTitle'] ?? $submission['task_title'] ?? $problemId,
            'taskUrl' => $submission['taskUrl'] ?? $submission['task_url'] ?? $taskUrl,
            'userName' => $userName !== null ? (string) $userName : null,
            'language' => $submission['language'] ?? null,
            'score' => $score !== null ? (float) $score : null,
            'codeSize' => $codeSize !== null ? (int) $codeSize : null,
            'result' => $result !== null ? (string) $result : null,
            'status' => $result !== null ? (string) $result : null,
            'execTime' => $execTime !== null ? (is_numeric($execTime) ? (int) $execTime : (string) $execTime) : null,
            'memory' => $submission['memory'] ?? null,
            'detailUrl' => $submission['detailUrl'] ?? $submission['detail_url'] ?? $detailUrl,
        ];
    }

    public static function user(array $payload): array
    {
        if (is_array($payload['result'] ?? null)) {
            $payload = $payload['result'];
        }

        return array_merge([
            'username' => null,
            'avatarUrl' => null,
            'country' => null,
            'birthYear' => null,
            'twitterId' => null,
            'topcoderId' => null,
            'codeforcesId' => null,
            'affiliation' => null,
            'contestStatus' => null,
        ], [
            'username' => $payload['username'] ?? $payload['userName'] ?? null,
            'avatarUrl' => $payload['avatarUrl'] ?? $payload['avatar_url'] ?? null,
            'country' => $payload['country'] ?? null,
            'birthYear' => $payload['birthYear'] ?? $payload['birth_year'] ?? null,
            'twitterId' => $payload['twitterId'] ?? $payload['twitter_id'] ?? null,
            'topcoderId' => $payload['topcoderId'] ?? $payload['topcoder_id'] ?? null,
            'codeforcesId' => $payload['codeforcesId'] ?? $payload['codeforces_id'] ?? null,
            'affiliation' => $payload['affiliation'] ?? null,
            'contestStatus' => $payload['contestStatus'] ?? $payload['contest_status'] ?? null,
        ]);
    }

    public static function ratingChanges(array $payload): array
    {
        return array_map([self::class, 'ratingChange'], self::unwrapList($payload));
    }

    public static function ratingChange(array $change): array
    {
        return array_merge([
            'isRated' => null,
            'place' => null,
            'oldRating' => null,
            'newRating' => null,
            'performance' => null,
            'innerPerformance' => null,
            'contestName' => null,
            'contestNameEn' => null,
            'contestScreenName' => null,
            'endTime' => null,
            'contestType' => null,
            'userName' => null,
            'userScreenName' => null,
            'country' => null,
            'affiliation' => null,
            'rating' => null,
            'competitions' => null,
            'atCoderRank' => null,
        ], [
            'isRated' => $change['isRated'] ?? $change['IsRated'] ?? null,
            'place' => $change['place'] ?? $change['Place'] ?? null,
            'oldRating' => $change['oldRating'] ?? $change['OldRating'] ?? null,
            'newRating' => $change['newRating'] ?? $change['NewRating'] ?? null,
            'performance' => $change['performance'] ?? $change['Performance'] ?? null,
            'innerPerformance' => $change['innerPerformance'] ?? $change['InnerPerformance'] ?? null,
            'contestName' => $change['contestName'] ?? $change['ContestName'] ?? null,
            'contestNameEn' => $change['contestNameEn'] ?? $change['ContestNameEn'] ?? null,
            'contestScreenName' => $change['contestScreenName'] ?? $change['ContestScreenName'] ?? null,
            'endTime' => $change['endTime'] ?? $change['EndTime'] ?? null,
            'contestType' => $change['contestType'] ?? $change['ContestType'] ?? $change['contest_type'] ?? null,
            'userName' => $change['userName'] ?? $change['UserName'] ?? null,
            'userScreenName' => $change['userScreenName'] ?? $change['UserScreenName'] ?? null,
            'country' => $change['country'] ?? $change['Country'] ?? null,
            'affiliation' => $change['affiliation'] ?? $change['Affiliation'] ?? null,
            'rating' => $change['rating'] ?? $change['Rating'] ?? null,
            'competitions' => $change['competitions'] ?? $change['Competitions'] ?? null,
            'atCoderRank' => $change['atCoderRank'] ?? $change['AtCoderRank'] ?? null,
        ]);
    }

    public static function standings(array $payload, ?array $contest = null, ?string $contestId = null): array
    {
        $standings = self::unwrapResult($payload);
        $taskInfo = $standings['TaskInfo'] ?? $standings['taskInfo'] ?? [];
        $rows = $standings['StandingsData'] ?? $standings['standingsData'] ?? [];

        $normalizedContest = $contest ?? [
            'id' => $contestId,
            'title' => $contestId,
        ];

        return [
            'contest' => self::contest($normalizedContest),
            'problems' => self::taskInfoList(is_array($taskInfo) ? $taskInfo : [], $contestId),
            'rows' => self::standingsRows(is_array($rows) ? $rows : [], $contestId),
            'raw' => $standings,
        ];
    }

    private static function taskInfoList(array $tasks, ?string $contestId): array
    {
        return array_map(
            fn (array $task): array => self::taskInfo($task, $contestId),
            $tasks
        );
    }

    private static function standingsRows(array $rows, ?string $contestId): array
    {
        return array_map(
            fn (array $row): array => self::standingsRow($row, $contestId),
            $rows
        );
    }

    public static function taskInfo(array $task, ?string $contestId = null): array
    {
        $assignment = $task['assignment'] ?? $task['Assignment'] ?? null;
        $taskName = $task['taskName'] ?? $task['TaskName'] ?? null;
        $taskScreenName = $task['taskScreenName'] ?? $task['TaskScreenName'] ?? null;

        return array_merge([
            'id' => null,
            'contestId' => $contestId,
            'title' => null,
            'position' => null,
            'fullTitle' => null,
            'score' => null,
            'timeLimit' => null,
            'memoryLimit' => null,
            'url' => null,
        ], [
            'id' => $taskScreenName,
            'title' => $taskName,
            'position' => $assignment,
            'fullTitle' => $assignment !== null && $taskName !== null ? "{$assignment} - {$taskName}" : $taskName,
        ]);
    }

    public static function standingsRow(array $row, ?string $contestId = null): array
    {
        $taskResults = $row['TaskResults'] ?? $row['taskResults'] ?? [];
        $normalizedTaskResults = [];

        if (is_array($taskResults)) {
            foreach ($taskResults as $taskId => $taskResult) {
                if (! is_array($taskResult)) {
                    continue;
                }

                $normalizedTaskResults[(string) $taskId] = self::taskResult($taskResult);
            }
        }

        $totalResult = $row['TotalResult'] ?? $row['totalResult'] ?? [];

        return array_merge([
            'rank' => null,
            'userName' => null,
            'userScreenName' => null,
            'userIsDeleted' => null,
            'affiliation' => null,
            'country' => null,
            'rating' => null,
            'oldRating' => null,
            'isRated' => null,
            'isTeam' => null,
            'competitions' => null,
            'atCoderRank' => null,
            'additional' => null,
            'taskResults' => [],
            'totalResult' => [],
            'contestId' => $contestId,
        ], [
            'rank' => isset($row['Rank']) ? (int) $row['Rank'] : (isset($row['rank']) ? (int) $row['rank'] : null),
            'userName' => $row['UserName'] ?? $row['userName'] ?? null,
            'userScreenName' => $row['UserScreenName'] ?? $row['userScreenName'] ?? null,
            'userIsDeleted' => $row['UserIsDeleted'] ?? $row['userIsDeleted'] ?? null,
            'affiliation' => $row['Affiliation'] ?? $row['affiliation'] ?? null,
            'country' => $row['Country'] ?? $row['country'] ?? null,
            'rating' => $row['Rating'] ?? $row['rating'] ?? null,
            'oldRating' => $row['OldRating'] ?? $row['oldRating'] ?? null,
            'isRated' => $row['IsRated'] ?? $row['isRated'] ?? null,
            'isTeam' => $row['IsTeam'] ?? $row['isTeam'] ?? null,
            'competitions' => $row['Competitions'] ?? $row['competitions'] ?? null,
            'atCoderRank' => $row['AtCoderRank'] ?? $row['atCoderRank'] ?? null,
            'additional' => $row['Additional'] ?? $row['additional'] ?? null,
            'taskResults' => $normalizedTaskResults,
            'totalResult' => self::totalResult(is_array($totalResult) ? $totalResult : []),
        ]);
    }

    public static function taskResult(array $taskResult): array
    {
        return array_merge([
            'count' => null,
            'failure' => null,
            'penalty' => null,
            'score' => null,
            'elapsed' => null,
            'status' => null,
            'pending' => null,
            'frozen' => null,
            'submissionId' => null,
            'additional' => null,
        ], [
            'count' => $taskResult['count'] ?? $taskResult['Count'] ?? null,
            'failure' => $taskResult['failure'] ?? $taskResult['Failure'] ?? null,
            'penalty' => $taskResult['penalty'] ?? $taskResult['Penalty'] ?? null,
            'score' => $taskResult['score'] ?? $taskResult['Score'] ?? null,
            'elapsed' => $taskResult['elapsed'] ?? $taskResult['Elapsed'] ?? null,
            'status' => $taskResult['status'] ?? $taskResult['Status'] ?? null,
            'pending' => $taskResult['pending'] ?? $taskResult['Pending'] ?? null,
            'frozen' => $taskResult['frozen'] ?? $taskResult['Frozen'] ?? null,
            'submissionId' => $taskResult['submissionId'] ?? $taskResult['SubmissionID'] ?? null,
            'additional' => $taskResult['additional'] ?? $taskResult['Additional'] ?? null,
        ]);
    }

    public static function totalResult(array $totalResult): array
    {
        return array_merge([
            'count' => null,
            'accepted' => null,
            'penalty' => null,
            'score' => null,
            'elapsed' => null,
            'frozen' => null,
            'additional' => null,
        ], [
            'count' => $totalResult['count'] ?? $totalResult['Count'] ?? null,
            'accepted' => $totalResult['accepted'] ?? $totalResult['Accepted'] ?? null,
            'penalty' => $totalResult['penalty'] ?? $totalResult['Penalty'] ?? null,
            'score' => $totalResult['score'] ?? $totalResult['Score'] ?? null,
            'elapsed' => $totalResult['elapsed'] ?? $totalResult['Elapsed'] ?? null,
            'frozen' => $totalResult['frozen'] ?? $totalResult['Frozen'] ?? null,
            'additional' => $totalResult['additional'] ?? $totalResult['Additional'] ?? null,
        ]);
    }

    private static function unwrapList(array $payload): array
    {
        if (is_array($payload['result'] ?? null)) {
            return $payload['result'];
        }

        return array_is_list($payload) ? $payload : [];
    }

    private static function unwrapResult(array $payload): array
    {
        if (is_array($payload['result'] ?? null)) {
            return $payload['result'];
        }

        return $payload;
    }
}
