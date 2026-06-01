<?php

namespace App\Platforms\AtCoder\Transformers;

use App\Core\DTOs\RatingChangeDTO;
use App\Platforms\AtCoder\DTOs\AtCoderRatingChangeDTO;

final class AtCoderRatingChangeTransformer
{
    /**
     * @param AtCoderRatingChangeDTO[] $ratingChanges
     * @return RatingChangeDTO[]
     */
    public static function fromApiRatingChanges(array $ratingChanges, string $platformContestId): array
    {
        return array_map(
            fn(AtCoderRatingChangeDTO $ratingChange): RatingChangeDTO => self::toCore($ratingChange, $platformContestId),
            $ratingChanges
        );
    }

    private static function toCore(AtCoderRatingChangeDTO $ratingChange, string $platformContestId): RatingChangeDTO
    {
        $oldRating = $ratingChange->oldRating;
        $newRating = $ratingChange->newRating;
        $ratingChangeDelta = null;
        $contestPlatformId = trim((string) ($ratingChange->contestPlatformId ?? $platformContestId));

        if ($oldRating !== null && $newRating !== null) {
            $ratingChangeDelta = $newRating - $oldRating;
        }

        $handle = $ratingChange->userScreenName ?? $ratingChange->userName ?? '';

        return new RatingChangeDTO(
            platform: 'atcoder',
            contestPlatformId: $contestPlatformId,
            handle: (string) $handle,
            isRated: (bool) $ratingChange->isRated,
            rank: $ratingChange->place,
            oldRating: $ratingChange->oldRating,
            newRating: $ratingChange->newRating,
            ratingChange: $ratingChangeDelta,
            performance: $ratingChange->performance,
            metadata: [
                'contest_name' => $ratingChange->contestName,
                'contest_screen_name' => $ratingChange->contestScreenName,
                'contest_type' => $ratingChange->contestType,
                'country' => $ratingChange->country,
                'affiliation' => $ratingChange->affiliation,
                'atcoder_rank' => $ratingChange->atCoderRank,
                'inner_performance' => $ratingChange->innerPerformance,
                'source' => 'atcoder-api',
            ],
            raw: $ratingChange->raw,
        );
    }
}
