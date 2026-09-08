<?php

declare(strict_types=1);

namespace Tests\Feature\Platforms\Codeforces;

use App\Models\Contest;
use App\Platforms\Codeforces\Importers\ContestImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportContestsTest extends TestCase
{
    public function test_import_contests_persists_contests_to_database(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        Http::fake([
            '*contest.list*' => Http::response([
                'status' => 'OK',
                'result' => [
                    [
                        'id' => 1900,
                        'name' => 'Codeforces Round 900 (Div. 3)',
                        'type' => 'CF',
                        'phase' => 'FINISHED',
                        'frozen' => false,
                        'durationSeconds' => 7200,
                        'startTimeSeconds' => 1695738900,
                        'relativeTimeSeconds' => 7200,
                    ],
                ],
            ], 200),
        ]);

        $importer = app(ContestImporter::class);
        $result = $importer->import();

        $this->assertGreaterThanOrEqual(1, $result->checked);

        $contest = Contest::query()
            ->where('platform_id', $platform->id)
            ->where('platform_contest_id', '1900')
            ->first();

        $this->assertNotNull($contest);
        $this->assertSame('Codeforces Round 900 (Div. 3)', $contest->name);
        $this->assertSame('FINISHED', $contest->phase);
        $this->assertSame(7200, $contest->duration_seconds);
    }
}

