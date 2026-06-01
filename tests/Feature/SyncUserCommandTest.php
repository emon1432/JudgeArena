<?php

namespace Tests\Feature;

use App\Core\DTOs\UserDTO;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Services\ApplicationLogger;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SyncUserCommandTest extends TestCase
{
    public function test_user_sync_validation_outputs_basic_profile_fields_without_writing_to_the_database(): void
    {
        $user = new UserDTO(
            platform: 'codeforces',
            platformHandle: 'tourist',
            firstName: 'Petr',
            lastName: 'Mitrichev',
            rating: 3797,
            country: 'Russia',
            raw: ['handle' => 'tourist']
        );

        $this->mock(CodeforcesAdapter::class, function ($mock) use ($user): void {
            $mock->shouldReceive('getUser')
                ->once()
                ->with('tourist')
                ->andReturn($user);
        });

        $this->mock(AtCoderAdapter::class, function ($mock): void {
            $mock->shouldNotReceive('getUser');
        });

        $this->app->instance(ApplicationLogger::class, new class {
            public function info() {}
            public function warning() {}
            public function error() {}
            public function critical() {}
        });

        $exitCode = Artisan::call('judgearena:sync-user', [
            'platform' => 'codeforces',
            'handle' => 'tourist',
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode, "Sync command failed with output:\n" . $output);
        $this->assertStringContainsString('Platform: codeforces', $output);
        $this->assertStringContainsString('Handle: tourist', $output);
        $this->assertStringContainsString('Country: Russia', $output);
        $this->assertStringContainsString('Rating: 3797', $output);
    }
}
