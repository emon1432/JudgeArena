<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesUserMapper;
use App\Platforms\Codeforces\Transformers\UserTransformer;
use Tests\TestCase;

class UserTransformerTest extends TestCase
{
    private function sample(string $fileName): array
    {
        $path = base_path('docs/platforms/codeforces.com/sample-response/' . $fileName);

        return json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_user_transformer_maps_profile_payload(): void
    {
        $profile = $this->sample('codeforces-profile.json');
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
