<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Support;

final class ResponseNormalizer
{
    public static function user(array $user): array
    {
        return array_merge([
            'handle' => null,
            'email' => null,
            'vkId' => null,
            'openId' => null,
            'firstName' => null,
            'lastName' => null,
            'country' => null,
            'city' => null,
            'organization' => null,
            'contribution' => null,
            'rank' => null,
            'rating' => null,
            'maxRank' => null,
            'maxRating' => null,
            'lastOnlineTimeSeconds' => null,
            'registrationTimeSeconds' => null,
            'friendOfCount' => null,
            'avatar' => null,
            'titlePhoto' => null,
        ], $user);
    }

    public static function users(array $users): array
    {
        return array_map([self::class, 'user'], $users);
    }

    public static function blogEntry(array $blogEntry): array
    {
        return array_merge([
            'id' => null,
            'originalLocale' => null,
            'creationTimeSeconds' => null,
            'authorHandle' => null,
            'title' => null,
            'content' => null,
            'locale' => null,
            'modificationTimeSeconds' => null,
            'allowViewHistory' => null,
            'tags' => [],
            'rating' => null,
        ], $blogEntry);
    }

    public static function blogEntries(array $blogEntries): array
    {
        return array_map([self::class, 'blogEntry'], $blogEntries);
    }

    public static function comment(array $comment): array
    {
        return array_merge([
            'id' => null,
            'creationTimeSeconds' => null,
            'commentatorHandle' => null,
            'locale' => null,
            'text' => null,
            'parentCommentId' => null,
            'rating' => null,
        ], $comment);
    }

    public static function comments(array $comments): array
    {
        return array_map([self::class, 'comment'], $comments);
    }

    public static function recentAction(array $action): array
    {
        $normalized = array_merge([
            'timeSeconds' => null,
            'blogEntry' => null,
            'comment' => null,
        ], $action);

        if (is_array($normalized['blogEntry'] ?? null)) {
            $normalized['blogEntry'] = self::blogEntry($normalized['blogEntry']);
        }

        if (is_array($normalized['comment'] ?? null)) {
            $normalized['comment'] = self::comment($normalized['comment']);
        }

        return $normalized;
    }

    public static function recentActions(array $actions): array
    {
        return array_map([self::class, 'recentAction'], $actions);
    }

    public static function ratingChange(array $ratingChange): array
    {
        return array_merge([
            'contestId' => null,
            'contestName' => null,
            'handle' => null,
            'rank' => null,
            'ratingUpdateTimeSeconds' => null,
            'oldRating' => null,
            'newRating' => null,
        ], $ratingChange);
    }

    public static function ratingChanges(array $ratingChanges): array
    {
        return array_map([self::class, 'ratingChange'], $ratingChanges);
    }

    public static function contest(array $contest): array
    {
        return array_merge([
            'id' => null,
            'name' => null,
            'type' => null,
            'phase' => null,
            'frozen' => null,
            'durationSeconds' => null,
            'freezeDurationSeconds' => null,
            'startTimeSeconds' => null,
            'relativeTimeSeconds' => null,
            'preparedBy' => null,
            'url' => null,
            'description' => null,
            'difficulty' => null,
            'kind' => null,
            'icpcRegion' => null,
            'country' => null,
            'city' => null,
            'season' => null,
        ], $contest);
    }

    public static function contests(array $contests): array
    {
        return array_map([self::class, 'contest'], $contests);
    }

    public static function member(array $member): array
    {
        return array_merge([
            'handle' => null,
            'name' => null,
        ], $member);
    }

    public static function members(array $members): array
    {
        return array_map([self::class, 'member'], $members);
    }

    public static function party(array $party): array
    {
        $normalized = array_merge([
            'contestId' => null,
            'members' => [],
            'participantType' => null,
            'teamId' => null,
            'teamName' => null,
            'ghost' => null,
            'room' => null,
            'startTimeSeconds' => null,
        ], $party);

        if (is_array($normalized['members'] ?? null)) {
            $normalized['members'] = self::members($normalized['members']);
        }

        return $normalized;
    }

    public static function problem(array $problem): array
    {
        return array_merge([
            'contestId' => null,
            'index' => null,
            'name' => null,
            'type' => null,
            'points' => null,
            'rating' => null,
            'tags' => [],
        ], $problem);
    }

    public static function problems(array $problems): array
    {
        return array_map([self::class, 'problem'], $problems);
    }

    public static function problemStatistics(array $statistics): array
    {
        return array_merge([
            'contestId' => null,
            'index' => null,
            'solvedCount' => null,
        ], $statistics);
    }

    public static function problemStatisticsList(array $statisticsList): array
    {
        return array_map([self::class, 'problemStatistics'], $statisticsList);
    }

    public static function problemResult(array $problemResult): array
    {
        return array_merge([
            'points' => null,
            'penalty' => null,
            'rejectedAttemptCount' => null,
            'type' => null,
            'bestSubmissionTimeSeconds' => null,
        ], $problemResult);
    }

    public static function problemResults(array $problemResults): array
    {
        return array_map([self::class, 'problemResult'], $problemResults);
    }

    public static function ranklistRow(array $row): array
    {
        $normalized = array_merge([
            'party' => null,
            'rank' => null,
            'points' => null,
            'penalty' => null,
            'successfulHackCount' => null,
            'unsuccessfulHackCount' => null,
            'problemResults' => [],
            'lastSubmissionTimeSeconds' => null,
        ], $row);

        if (is_array($normalized['party'] ?? null)) {
            $normalized['party'] = self::party($normalized['party']);
        }

        if (is_array($normalized['problemResults'] ?? null)) {
            $normalized['problemResults'] = self::problemResults($normalized['problemResults']);
        }

        return $normalized;
    }

    public static function ranklistRows(array $rows): array
    {
        return array_map([self::class, 'ranklistRow'], $rows);
    }

    public static function standings(array $standings): array
    {
        $normalized = $standings;

        if (is_array($normalized['contest'] ?? null)) {
            $normalized['contest'] = self::contest($normalized['contest']);
        }

        if (is_array($normalized['problems'] ?? null)) {
            $normalized['problems'] = self::problems($normalized['problems']);
        }

        if (is_array($normalized['rows'] ?? null)) {
            $normalized['rows'] = self::ranklistRows($normalized['rows']);
        }

        return $normalized;
    }

    public static function submission(array $submission): array
    {
        $normalized = array_merge([
            'id' => null,
            'contestId' => null,
            'creationTimeSeconds' => null,
            'relativeTimeSeconds' => null,
            'problem' => null,
            'author' => null,
            'programmingLanguage' => null,
            'verdict' => null,
            'testset' => null,
            'passedTestCount' => null,
            'timeConsumedMillis' => null,
            'memoryConsumedBytes' => null,
            'points' => null,
        ], $submission);

        if (is_array($normalized['problem'] ?? null)) {
            $normalized['problem'] = self::problem($normalized['problem']);
        }

        if (is_array($normalized['author'] ?? null)) {
            $normalized['author'] = self::party($normalized['author']);
        }

        return $normalized;
    }

    public static function submissions(array $submissions): array
    {
        return array_map([self::class, 'submission'], $submissions);
    }

    public static function judgeProtocol(array $judgeProtocol): array
    {
        return array_merge([
            'manual' => null,
            'protocol' => null,
            'verdict' => null,
        ], $judgeProtocol);
    }

    public static function hack(array $hack): array
    {
        $normalized = array_merge([
            'id' => null,
            'creationTimeSeconds' => null,
            'hacker' => null,
            'defender' => null,
            'verdict' => null,
            'problem' => null,
            'test' => null,
            'judgeProtocol' => null,
        ], $hack);

        if (is_array($normalized['hacker'] ?? null)) {
            $normalized['hacker'] = self::party($normalized['hacker']);
        }

        if (is_array($normalized['defender'] ?? null)) {
            $normalized['defender'] = self::party($normalized['defender']);
        }

        if (is_array($normalized['problem'] ?? null)) {
            $normalized['problem'] = self::problem($normalized['problem']);
        }

        if (is_array($normalized['judgeProtocol'] ?? null)) {
            $normalized['judgeProtocol'] = self::judgeProtocol($normalized['judgeProtocol']);
        }

        return $normalized;
    }

    public static function hacks(array $hacks): array
    {
        return array_map([self::class, 'hack'], $hacks);
    }
}

