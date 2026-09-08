<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Platforms\Codeforces\Importers\UserRatingHistoryImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportUserRatingHistoryTest extends TestCase
{
    public function test_import_user_rating_history_persists_changes_to_database(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1900',
            'name' => 'CF Round 1900',
        ]);

        Http::fake([
            '*user.rating*' => Http::response([
                'status' => 'OK',
                'result' => [
                    [
                        'contestId' => 1900,
                        'contestName' => 'CF Round 1900',
                        'handle' => 'tourist',
                        'rank' => 1,
                        'ratingUpdateTimeSeconds' => 1695739900,
                        'oldRating' => 3800,
                        'newRating' => 3820,
                    ],
                ],
            ], 200),
        ]);

        $importer = app(UserRatingHistoryImporter::class);
        $result = $importer->import('tourist');

        $this->assertSame(1, $result->checked);

        $change = ContestRatingChange::query()
            ->where('contest_id', $contest->id)
            ->where('platform_profile_id', $profile->id)
            ->first();

        $this->assertNotNull($change);
        $this->assertSame(1, $change->rank);
        $this->assertSame(3800, $change->old_rating);
        $this->assertSame(3820, $change->new_rating);
    }
}

