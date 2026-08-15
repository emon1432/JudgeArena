<?php

namespace App\Enums;

/**
 * Platform sync states exist to track orchestration progress independently
 * from the imported domain rows.
 *
 * Contest and contest-problem syncs are intentionally separate because a
 * contest may already have some problems imported while the contest-level
 * sync is still a distinct future workflow.
 */
enum PlatformSyncEntityType: string
{
    case Contest = 'contest';
    case ContestProblems = 'contest_problems';
    case User = 'user';
    case UserRatingHistory = 'user_rating_history';
    case UserSubmissions = 'user_submissions';
    case UserStandings = 'user_standings';
}
