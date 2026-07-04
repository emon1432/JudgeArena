<?php

namespace Tests\Feature;

use App\Core\Contracts\Importers\StandingsImporter as StandingsImporterContract;
use App\Core\Platforms\PlatformRegistry;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportStandingsCommandTest extends TestCase
{
    public function test_standings_import_command_requires_a_platform_and_delegates_to_the_adapter_importer(): void
    {
        $fakeImporter = new class implements StandingsImporterContract {
            public function import(): array
            {
                return [
                    'contests_checked' => 1,
                    'contests_synced' => 1,
                    'contests_already_synced' => 0,
                    'contests_failed' => 0,
                    'contests_unsupported_platform' => 0,
                    'standings_fetched' => 8,
                    'standings_created' => 5,
                    'standings_updated' => 3,
                    'task_results_created' => 12,
                    'task_results_updated' => 4,
                    'task_results_skipped' => 0,
                ];
            }
        };

        $adapter = $this->mock(CodeforcesAdapter::class, function ($mock) use ($fakeImporter): void {
            $mock->shouldReceive('standingsImporter')
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

        $exitCode = Artisan::call('judgearena:import-standings', [
            'platform' => 'codeforces',
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode, "Import command failed with output:\n" . $output);
        $this->assertStringContainsString('Platform: codeforces', $output);
        $this->assertStringContainsString('Contests Checked: 1', $output);
        $this->assertStringContainsString('Contests Synced: 1', $output);
        $this->assertStringContainsString('Standings Fetched: 8', $output);
        $this->assertStringContainsString('Standings Created: 5', $output);
        $this->assertStringContainsString('Standings Updated: 3', $output);
        $this->assertStringContainsString('Task Results Created: 12', $output);
        $this->assertStringContainsString('Task Results Updated: 4', $output);
    }
}
