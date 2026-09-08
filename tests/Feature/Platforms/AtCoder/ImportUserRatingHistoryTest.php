<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Models\Contest;
use App\Models\ContestRatingChange;
use App\Platforms\AtCoder\Importers\UserRatingHistoryImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportUserRatingHistoryTest extends TestCase
{
    public function test_import_user_rating_history_persists_changes_to_database(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder');
        $profile = $this->createUserWithProfile($platform, 'tourist');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => 'abc300',
            'name' => 'AtCoder Beginner Contest 300',
        ]);

        Http::fake([
            '*history/json*contestType=algo*' => Http::response([
                [
                    'IsRated' => true,
                    'Place' => 1,
                    'OldRating' => 4000,
                    'NewRating' => 4050,
                    'Performance' => 4200,
                    'InnerPerformance' => 4200,
                    'ContestScreenName' => 'abc300.contest.atcoder.jp',
                    'EndTime' => '2023-04-29T22:40:00+09:00',
                    'ContestName' => 'AtCoder Beginner Contest 300',
                ],
            ], 200),
            '*history/json*contestType=heuristic*' => Http::response([], 200),
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
        $this->assertSame(4000, $change->old_rating);
        $this->assertSame(4050, $change->new_rating);
    }
}

