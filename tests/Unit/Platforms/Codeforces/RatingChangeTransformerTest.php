<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesRatingChangeMapper;
use App\Platforms\Codeforces\Transformers\RatingChangeTransformer;
use Tests\TestCase;

class RatingChangeTransformerTest extends TestCase
{
    public function test_rating_change_transformer_maps_rating_changes(): void
    {
        $ratingChanges = [
            [
                'contestId' => 2225,
                'contestName' => 'Educational Codeforces Round 189 (Rated for Div. 2)',
                'handle' => 'vietbachleonkroos2326',
                'rank' => 1,
                'ratingUpdateTimeSeconds' => 1776789300,
                'oldRating' => 1664,
                'newRating' => 2060,
            ],
        ];

        $dtos = CodeforcesRatingChangeMapper::fromNormalizedList($ratingChanges);
        $coreDtos = RatingChangeTransformer::fromApiRatingChanges($dtos, '2225');

        $this->assertNotEmpty($coreDtos);
        $first = $coreDtos[0];

        $this->assertSame('codeforces', $first->platform);
        $this->assertSame('vietbachleonkroos2326', $first->handle);
        $this->assertTrue($first->isRated);
        $this->assertSame(1664, $first->oldRating);
        $this->assertSame(2060, $first->newRating);
        $this->assertSame(396, $first->ratingChange);
    }
}
