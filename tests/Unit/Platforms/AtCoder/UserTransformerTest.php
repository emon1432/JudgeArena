<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\AtCoder;

use App\Platforms\AtCoder\Mappers\AtCoderUserMapper;
use App\Platforms\AtCoder\Transformers\UserTransformer;
use Tests\TestCase;

class UserTransformerTest extends TestCase
{
    public function test_from_api_user_transforms_atcoder_user_dto(): void
    {
        $dto = AtCoderUserMapper::fromNormalized([
            'username' => 'tourist',
            'avatarUrl' => 'https://img.atcoder.jp/avatar.png',
            'country' => 'Belarus',
            'birthYear' => '1994',
            'twitterId' => '@que_tourist',
            'topcoderId' => 'tourist',
            'codeforcesId' => 'tourist',
            'affiliation' => 'ITMO University',
            'acceptedCount' => 500,
            'acceptedCountRank' => 1,
            'ratedPointSum' => 250000,
            'ratedPointSumRank' => 1,
            'contestStatus' => [
                'algo' => ['rating' => 3797],
                'heuristic' => ['rating' => 2066],
            ],
            'raw' => ['username' => 'tourist'],
        ]);

        $transformer = new UserTransformer();
        $user = $transformer->fromApiUser($dto);

        $this->assertSame('atcoder', $user->platform);
        $this->assertSame('tourist', $user->platformHandle);
        $this->assertSame('Belarus', $user->country);
        $this->assertSame(3797, $user->rating);
        $this->assertSame(500, $user->raw['accepted_count']);
    }

    public function test_from_api_user_falls_back_to_heuristic_rating(): void
    {
        $dto = AtCoderUserMapper::fromNormalized([
            'username' => 'heuristic_only',
            'contestStatus' => [
                'algo' => ['rating' => null],
                'heuristic' => ['rating' => 2100],
            ],
            'raw' => ['username' => 'heuristic_only'],
        ]);

        $transformer = new UserTransformer();
        $user = $transformer->fromApiUser($dto);

        $this->assertSame(2100, $user->rating);
    }
}
