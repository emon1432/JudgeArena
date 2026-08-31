<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesUserMapper;
use App\Platforms\Codeforces\Transformers\UserTransformer;
use Tests\TestCase;

class UserTransformerTest extends TestCase
{
    public function test_user_transformer_maps_profile_payload(): void
    {
        $profile = [
            'handle' => 'tourist',
            'firstName' => 'Gennady',
            'lastName' => 'Korotkevich',
            'country' => 'Belarus',
            'city' => 'Gomel',
            'organization' => 'ITMO University',
            'contribution' => 175,
            'rank' => 'legendary grandmaster',
            'rating' => 3428,
            'maxRank' => 'tourist',
            'maxRating' => 4009,
        ];

        $userDto = CodeforcesUserMapper::fromNormalized($profile);
        $dto = (new UserTransformer())->fromApiUser($userDto);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame('tourist', $dto->platformHandle);
        $this->assertSame('Gennady', $dto->firstName);
        $this->assertSame('Korotkevich', $dto->lastName);
        $this->assertSame(3428, $dto->rating);
        $this->assertSame('Belarus', $dto->country);
        $this->assertSame($profile, $userDto->raw);
    }
}
