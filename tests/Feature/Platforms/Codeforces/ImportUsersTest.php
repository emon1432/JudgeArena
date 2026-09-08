<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Platforms\Codeforces\Importers\UserImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportUsersTest extends TestCase
{
    public function test_import_users_updates_platform_profile(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        Http::fake([
            '*user.info*' => Http::response([
                'status' => 'OK',
                'result' => [
                    [
                        'handle' => 'tourist',
                        'rating' => 3900,
                        'maxRating' => 4009,
                        'rank' => 'legendary grandmaster',
                        'maxRank' => 'tourist',
                        'avatar' => 'https://userpic.codeforces.org/avatar.jpg',
                    ],
                ],
            ], 200),
        ]);

        $importer = app(UserImporter::class);
        $result = $importer->import('tourist');

        $this->assertSame(1, $result->checked);
        $this->assertSame(1, $result->updated);

        $profile->refresh();
        $this->assertNotNull($profile->raw);
        $this->assertNotNull($profile->last_synced_at);
        $this->assertSame('tourist', $profile->raw['handle'] ?? null);
        $this->assertSame(3900, $profile->raw['rating'] ?? null);
    }
}
