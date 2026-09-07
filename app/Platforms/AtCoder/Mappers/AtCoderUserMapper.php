<?php

namespace App\Platforms\AtCoder\Mappers;

use App\Platforms\AtCoder\DTOs\AtCoderUserDTO;

final class AtCoderUserMapper
{
    public static function fromNormalized(array $user): AtCoderUserDTO
    {
        return new AtCoderUserDTO(
            username: $user['username'] ?? null,
            avatarUrl: $user['avatarUrl'] ?? $user['avatar_url'] ?? null,
            country: $user['country'] ?? null,
            birthYear: isset($user['birthYear']) ? (string) $user['birthYear'] : (isset($user['birth_year']) ? (string) $user['birth_year'] : null),
            twitterId: $user['twitterId'] ?? $user['twitter_id'] ?? null,
            topcoderId: $user['topcoderId'] ?? $user['topcoder_id'] ?? null,
            codeforcesId: $user['codeforcesId'] ?? $user['codeforces_id'] ?? null,
            affiliation: $user['affiliation'] ?? null,
            contestStatus: is_array($user['contestStatus'] ?? null) ? $user['contestStatus'] : (is_array($user['contest_status'] ?? null) ? $user['contest_status'] : null),
            acceptedCount: isset($user['acceptedCount']) ? (int) $user['acceptedCount'] : (isset($user['accepted_count']) ? (int) $user['accepted_count'] : null),
            acceptedCountRank: isset($user['acceptedCountRank']) ? (int) $user['acceptedCountRank'] : (isset($user['accepted_count_rank']) ? (int) $user['accepted_count_rank'] : null),
            ratedPointSum: isset($user['ratedPointSum']) ? (int) $user['ratedPointSum'] : (isset($user['rated_point_sum']) ? (int) $user['rated_point_sum'] : null),
            ratedPointSumRank: isset($user['ratedPointSumRank']) ? (int) $user['ratedPointSumRank'] : (isset($user['rated_point_sum_rank']) ? (int) $user['rated_point_sum_rank'] : null),
            raw: $user,
        );
    }
}

