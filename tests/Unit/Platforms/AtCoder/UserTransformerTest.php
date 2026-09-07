<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\DTOs\AtCoderUserDTO;
use App\Platforms\AtCoder\Transformers\UserTransformer;
use Tests\TestCase;

class UserTransformerTest extends TestCase
{
    public function test_user_transformer_maps_atcoder_user(): void
    {
        $userDto = new AtCoderUserDTO(
            username: 'chokudai',
            avatarUrl: 'https://example.com/avatar.jpg',
            country: 'Japan',
            birthYear: '1985',
            twitterId: 'chokudai',
            topcoderId: 'chokudai',
            codeforcesId: 'chokudai',
            affiliation: 'AtCoder Inc.',
            contestStatus: [
                'algo' => [
                    'rating' => 3000,
                    'highest_rating' => 3100,
                    'rank' => 10,
                ],
            ],
            raw: ['username' => 'chokudai']
        );

        $dto = (new UserTransformer())->fromApiUser($userDto);

        $this->assertSame('atcoder', $dto->platform);
        $this->assertSame('chokudai', $dto->platformHandle);
        $this->assertSame(3000, $dto->rating);
        $this->assertSame('Japan', $dto->country);
        $this->assertSame(3000, $dto->raw['rating']);
    }

    public function test_user_transformer_maps_kenkoooo_metrics_and_falls_back_to_heuristic_rating(): void
    {
        $normalized = [
            'username' => 'tourist',
            'avatarUrl' => 'https://img.atcoder.jp/icons/sample.jpg',
            'country' => 'Belarus',
            'contestStatus' => [
                'algo' => null,
                'heuristic' => [
                    'rating' => 2066,
                    'highest_rating' => 2383,
                ],
            ],
            'acceptedCount' => 1057,
            'acceptedCountRank' => 4421,
            'ratedPointSum' => 688543,
            'ratedPointSumRank' => 677,
        ];

        $userDto = \App\Platforms\AtCoder\Mappers\AtCoderUserMapper::fromNormalized($normalized);

        $this->assertSame(1057, $userDto->acceptedCount);
        $this->assertSame(4421, $userDto->acceptedCountRank);
        $this->assertSame(688543, $userDto->ratedPointSum);
        $this->assertSame(677, $userDto->ratedPointSumRank);

        $dto = (new UserTransformer())->fromApiUser($userDto);

        $this->assertSame('atcoder', $dto->platform);
        $this->assertSame('tourist', $dto->platformHandle);
        $this->assertSame(2066, $dto->rating);
        $this->assertSame('Belarus', $dto->country);
        $this->assertSame(1057, $dto->raw['accepted_count']);
        $this->assertSame(688543, $dto->raw['rated_point_sum']);
    }
}
