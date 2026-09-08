<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesUserMapper;
use App\Platforms\Codeforces\Transformers\UserTransformer;
use Tests\TestCase;

class UserTransformerTest extends TestCase
{
    public function test_from_api_user_transforms_codeforces_user(): void
    {
        $dto = CodeforcesUserMapper::fromNormalized([
            'handle' => 'tourist',
            'firstName' => 'Gennady',
            'lastName' => 'Korotkevich',
            'country' => 'Belarus',
            'city' => 'Gomel',
            'organization' => 'ITMO University',
            'contribution' => 150,
            'rank' => 'legendary grandmaster',
            'rating' => 3800,
            'maxRank' => 'legendary grandmaster',
            'maxRating' => 4000,
            'lastOnlineTimeSeconds' => 1672531200,
            'registrationTimeSeconds' => 1262304000,
            'avatar' => 'https://userpic.codeforces.org/avatar.jpg',
            'titlePhoto' => 'https://userpic.codeforces.org/title.jpg',
        ]);

        $transformer = new UserTransformer();
        $core = $transformer->fromApiUser($dto);

        $this->assertSame('codeforces', $core->platform);
        $this->assertSame('tourist', $core->platformHandle);
        $this->assertSame('Gennady', $core->firstName);
        $this->assertSame('Korotkevich', $core->lastName);
        $this->assertSame('Belarus', $core->country);
        $this->assertSame(3800, $core->rating);
    }
}

