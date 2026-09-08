<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\Mappers\AtCoderRatingChangeMapper;
use App\Platforms\AtCoder\Transformers\RatingChangeTransformer;
use Tests\TestCase;

class RatingChangeTransformerTest extends TestCase
{
    public function test_from_api_rating_changes_transforms_dtos(): void
    {
        $dto = AtCoderRatingChangeMapper::fromNormalized([
            'isRated' => true,
            'place' => 15,
            'oldRating' => 2400,
            'newRating' => 2480,
            'performance' => 2800,
            'innerPerformance' => 2850,
            'contestScreenName' => 'abc300.contest.atcoder.jp',
            'contestName' => 'AtCoder Beginner Contest 300',
            'userName' => 'chokudai',
            'userScreenName' => 'chokudai',
            'country' => 'JP',
            'affiliation' => 'AtCoder Inc.',
            'atCoderRank' => '15',
        ]);

        $results = RatingChangeTransformer::fromApiRatingChanges([$dto]);

        $this->assertCount(1, $results);
        $core = $results[0];

        $this->assertSame('atcoder', $core->platform);
        $this->assertSame('abc300', $core->contestPlatformId);
        $this->assertSame('chokudai', $core->handle);
        $this->assertTrue($core->isRated);
        $this->assertSame(15, $core->rank);
        $this->assertSame(2400, $core->oldRating);
        $this->assertSame(2480, $core->newRating);
        $this->assertSame(80, $core->ratingChange);
        $this->assertSame(2800, $core->performance);
    }
}
