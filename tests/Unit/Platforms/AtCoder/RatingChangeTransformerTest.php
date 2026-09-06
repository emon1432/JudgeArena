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

    public function test_rating_change_normalization_and_mapping_from_atcoder_json(): void
    {
        $rawJsonEntry = [
            'IsRated' => true,
            'Place' => 2,
            'OldRating' => 0,
            'NewRating' => 2720,
            'Performance' => 3920,
            'InnerPerformance' => 3920,
            'ContestScreenName' => 'agc004.contest.atcoder.jp',
            'ContestName' => 'AtCoder Grand Contest 004',
            'ContestNameEn' => '',
            'EndTime' => '2016-09-04T22:50:00+09:00',
            'contest_type' => 'algo',
        ];

        $normalizedList = \App\Platforms\AtCoder\Support\ResponseNormalizer::ratingChanges([$rawJsonEntry]);
        $dtos = \App\Platforms\AtCoder\Mappers\AtCoderRatingChangeMapper::fromNormalizedList($normalizedList);
        $coreDtos = RatingChangeTransformer::fromApiRatingChanges($dtos, null, 'tourist');

        $this->assertCount(1, $coreDtos);
        $change = $coreDtos[0];

        $this->assertSame('atcoder', $change->platform);
        $this->assertSame('agc004', $change->contestPlatformId);
        $this->assertSame('tourist', $change->handle);
        $this->assertTrue($change->isRated);
        $this->assertSame(2, $change->rank);
        $this->assertSame(0, $change->oldRating);
        $this->assertSame(2720, $change->newRating);
        $this->assertSame(2720, $change->ratingChange);
        $this->assertSame(3920, $change->performance);
        $this->assertSame('algo', $change->metadata['contest_type']);
    }
}
