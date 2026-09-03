<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\DTOs\AtCoderRatingChangeDTO;
use App\Platforms\AtCoder\Transformers\RatingChangeTransformer;
use Tests\TestCase;

class RatingChangeTransformerTest extends TestCase
{
    public function test_rating_change_transformer_maps_atcoder_rating_changes(): void
    {
        $dto = new AtCoderRatingChangeDTO(
            contestPlatformId: 'abc350',
            isRated: true,
            place: 1,
            oldRating: 2950,
            newRating: 3000,
            performance: 3200,
            innerPerformance: 3200,
            contestName: 'AtCoder Beginner Contest 350',
            contestScreenName: 'abc350.contest.atcoder.jp',
            contestType: 'Algorithm',
            userName: 'chokudai',
            userScreenName: 'chokudai',
            country: 'Japan',
            affiliation: 'AtCoder Inc.',
            atCoderRank: '1',
            raw: ['place' => 1],
        );

        $coreDtos = RatingChangeTransformer::fromApiRatingChanges([$dto], 'abc350', 'chokudai');

        $this->assertNotEmpty($coreDtos);
        $first = $coreDtos[0];

        $this->assertSame('atcoder', $first->platform);
        $this->assertSame('abc350', $first->contestPlatformId);
        $this->assertSame('chokudai', $first->handle);
        $this->assertTrue($first->isRated);
        $this->assertSame(2950, $first->oldRating);
        $this->assertSame(3000, $first->newRating);
        $this->assertSame(50, $first->ratingChange);
    }
}
