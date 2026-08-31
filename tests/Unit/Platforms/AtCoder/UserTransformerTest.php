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
    }
}
