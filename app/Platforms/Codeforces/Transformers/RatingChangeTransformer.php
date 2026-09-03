<?php

declare(strict_types=1);

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\RatingChangeDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesRatingChangeDTO;

final class RatingChangeTransformer
{
    /**
     * @param CodeforcesRatingChangeDTO[] $ratingChanges
     * @return RatingChangeDTO[]
     */
    public static function fromApiRatingChanges(array $ratingChanges, ?string $platformContestId = null, ?string $handle = null): array
    {
        return array_map(
            fn(CodeforcesRatingChangeDTO $ratingChange): RatingChangeDTO => self::toCore($ratingChange, $platformContestId, $handle),
            $ratingChanges
        );
    }

    private static function toCore(CodeforcesRatingChangeDTO $ratingChange, ?string $platformContestId, ?string $handle): RatingChangeDTO
    {
        $oldRating = $ratingChange->oldRating;
        $newRating = $ratingChange->newRating;
        $ratingChangeDelta = null;
        $contestPlatformId = (string) ($ratingChange->contestId ?? $platformContestId ?? '');

        if ($oldRating !== null && $newRating !== null) {
            $ratingChangeDelta = $newRating - $oldRating;
        }

        $handle = $ratingChange->handle ?? $handle ?? '';

        return new RatingChangeDTO(
            platform: 'codeforces',
            contestPlatformId: $contestPlatformId,
            handle: $handle,
            isRated: true,
            rank: $ratingChange->rank,
            oldRating: $ratingChange->oldRating,
            newRating: $ratingChange->newRating,
            ratingChange: $ratingChangeDelta,
            metadata: [
                'contest_name' => $ratingChange->contestName,
                'rating_update_time_seconds' => $ratingChange->ratingUpdateTimeSeconds,
                'source' => 'codeforces-api',
            ],
            raw: $ratingChange->raw,
        );
    }
}
