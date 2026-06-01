<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\RatingChangeDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesRatingChangeDTO;

final class CodeforcesRatingChangeTransformer
{
    /**
     * @param CodeforcesRatingChangeDTO[] $ratingChanges
     * @return RatingChangeDTO[]
     */
    public static function fromApiRatingChanges(array $ratingChanges, string $platformContestId): array
    {
        return array_map(
            fn(CodeforcesRatingChangeDTO $ratingChange): RatingChangeDTO => self::toCore($ratingChange, $platformContestId),
            $ratingChanges
        );
    }

    private static function toCore(CodeforcesRatingChangeDTO $ratingChange, string $platformContestId): RatingChangeDTO
    {
        $oldRating = $ratingChange->oldRating;
        $newRating = $ratingChange->newRating;
        $ratingChangeDelta = null;
        $contestPlatformId = trim((string) ($ratingChange->contestPlatformId ?? $platformContestId));

        if ($oldRating !== null && $newRating !== null) {
            $ratingChangeDelta = $newRating - $oldRating;
        }

        return new RatingChangeDTO(
            platform: 'codeforces',
            contestPlatformId: $contestPlatformId,
            handle: (string) ($ratingChange->handle ?? ''),
            isRated: true,
            rank: $ratingChange->rank,
            oldRating: $ratingChange->oldRating,
            newRating: $ratingChange->newRating,
            ratingChange: $ratingChangeDelta,
            performance: null,
            metadata: [
                'contest_name' => $ratingChange->contestName,
                'rating_update_time_seconds' => $ratingChange->ratingUpdateTimeSeconds,
                'source' => 'codeforces-api',
            ],
            raw: $ratingChange->raw,
        );
    }
}
