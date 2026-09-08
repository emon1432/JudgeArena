<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\DTOs\CodeforcesRatingChangeDTO;
use App\Platforms\Codeforces\Transformers\RatingChangeTransformer;
use Tests\TestCase;

class RatingChangeTransformerTest extends TestCase
{
    public function test_from_api_rating_changes_transforms_codeforces_dtos(): void
    {
        $dto = new CodeforcesRatingChangeDTO(
            contestPlatformId: '1000',
            contestName: 'Codeforces Round 1000',
            handle: 'tourist',
            rank: 1,
            ratingUpdateTimeSeconds: 1672531200,
            oldRating: 3700,
            newRating: 3750,
            raw: ['contestId' => 1000],
        );

        $results = RatingChangeTransformer::fromApiRatingChanges([$dto]);

        $this->assertCount(1, $results);
        $core = $results[0];

        $this->assertSame('codeforces', $core->platform);
        $this->assertSame('1000', $core->contestPlatformId);
        $this->assertSame('tourist', $core->handle);
        $this->assertSame(1, $core->rank);
        $this->assertSame(3700, $core->oldRating);
        $this->assertSame(3750, $core->newRating);
        $this->assertSame(50, $core->ratingChange);
    }
}
