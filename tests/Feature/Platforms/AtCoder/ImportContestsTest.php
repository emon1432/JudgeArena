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
                [
                    'id' => 'abs',
                    'start_epoch_second' => 0,
                    'duration_second' => 3153600000,
                    'title' => 'AtCoder Beginners Selection',
                    'rate_change' => '-',
                ],
            ], 200),
        ]);

        $importer = app(ContestImporter::class);
        $result = $importer->import();

        $this->assertGreaterThanOrEqual(2, $result->checked);

        $contest = Contest::query()
            ->where('platform_id', $platform->id)
            ->where('platform_contest_id', 'abc300')
            ->first();

        $this->assertNotNull($contest);
        $this->assertSame('AtCoder Beginner Contest 300', $contest->name);
        $this->assertSame('FINISHED', $contest->phase);
        $this->assertSame(6000, $contest->duration_seconds);

        $absContest = Contest::query()
            ->where('platform_id', $platform->id)
            ->where('platform_contest_id', 'abs')
            ->first();

        $this->assertNotNull($absContest);
        $this->assertSame('AtCoder Beginners Selection', $absContest->name);
        $this->assertSame('CODING', $absContest->phase);
        $this->assertNull($absContest->duration_seconds);
        $this->assertNull($absContest->start_time);
        $this->assertNull($absContest->end_time);
    }
}
