<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\AtCoder;

use App\Models\Contest;
use App\Platforms\AtCoder\Importers\ContestImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportContestsTest extends TestCase
{
    public function test_import_contests_persists_atcoder_contests_to_database(): void
    {
        $platform = $this->createPlatform('atcoder', 'AtCoder');

        Http::fake([
            '*contests.json*' => Http::response([
                [
                    'id' => 'abc300',
                    'start_epoch_second' => 1682769600,
                    'duration_second' => 6000,
                    'title' => 'AtCoder Beginner Contest 300',
                    'rate_change' => ' ~ 1999',
                ],
            ], 200),
        ]);

        $importer = app(ContestImporter::class);
        $result = $importer->import();

        $this->assertGreaterThanOrEqual(1, $result->checked);

        $contest = Contest::query()
            ->where('platform_id', $platform->id)
            ->where('platform_contest_id', 'abc300')
            ->first();

        $this->assertNotNull($contest);
        $this->assertSame('AtCoder Beginner Contest 300', $contest->name);
        $this->assertSame('FINISHED', $contest->phase);
        $this->assertSame(6000, $contest->duration_seconds);
    }
}

