<?php

namespace App\Enums;

/**
 * Platform sync jobs exist to track orchestration progress independently
 * from the imported domain rows.
 * Contest and contest-problem syncs are intentionally separate because a
 * contest may already have some problems imported while the contest-level sync is still a distinct future workflow.
 */
enum PlatformSyncJobEntity: string
{
    case Contest = 'contest';
    case Problem = 'problem';
    case User = 'user';
    case UserRatingHistory = 'user_rating_history';
    case UserSubmissions = 'user_submissions';
    case UserStandings = 'user_standings';

    public function importerMethod(): string
    {
        return match ($this) {
            self::Contest => 'contestImporter',
            self::Problem => 'problemImporter',
            self::User => 'userImporter',
            self::UserRatingHistory => 'userRatingHistoryImporter',
            self::UserSubmissions => 'userSubmissionImporter',
            self::UserStandings => 'userStandingImporter',
        };
    }
}
