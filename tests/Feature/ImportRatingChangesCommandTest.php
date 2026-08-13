<?php

namespace Tests\Feature;

use App\Core\Contracts\Importers\RatingChangeImporter as RatingChangeImporterContract;
use App\Core\Platforms\PlatformRegistry;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportRatingChangesCommandTest extends TestCase
{
    public function test_rating_change_import_command_requires_a_platform_and_delegates_to_the_adapter_importer(): void
    {
        $fakeImporter = new class implements RatingChangeImporterContract {
            public function import(): \App\Core\Results\ImportResult
            {
                return new \App\Core\Results\ImportResult(
                    checked: 1,
                    fetched: 3,
                    created: 2,
                    updated: 1,
                    failed: 0,
                    skipped: 0,
                    metadata: [
                        'contests_checked' => 1,
                        'contests_synced' => 1,
                        'contests_already_synced' => 0,
                        'contests_failed' => 0,
                        'contests_unsupported_platform' => 0,
                        'rating_changes_fetched' => 3,
                        'rating_changes_created' => 2,
                        'rating_changes_updated' => 1,
                    ]
                );
            }
        };

        $adapter = $this->mock(CodeforcesAdapter::class, function ($mock) use ($fakeImporter): void {
            $mock->shouldReceive('ratingChangeImporter')
                ->once()
                ->andReturn($fakeImporter);
        });

        $this->mock(PlatformRegistry::class, function ($mock) use ($adapter): void {
            $mock->shouldReceive('resolve')
                ->once()
                ->with('codeforces')
                ->andReturn($adapter);
        });

        $this->app->instance(ApplicationLogger::class, new class {
            public function info() {}
            public function warning() {}
            public function error() {}
            public function critical() {}
        });

        $exitCode = Artisan::call('judgearena:import-rating-changes', [
            'platform' => 'codeforces',
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode, "Import command failed with output:\n" . $output);
        $this->assertStringContainsString('Platform: codeforces', $output);
        $this->assertStringContainsString('Contests Checked: 1', $output);
        $this->assertStringContainsString('Contests Synced: 1', $output);
        $this->assertStringContainsString('Rating Changes Fetched: 3', $output);
        $this->assertStringContainsString('Rating Changes Created: 2', $output);
        $this->assertStringContainsString('Rating Changes Updated: 1', $output);
    }
}
