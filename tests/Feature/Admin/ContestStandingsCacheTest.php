<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Core\Contracts\Platforms\PlatformAdapter;
use App\Core\DTOs\ContestDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\Platforms\PlatformRegistry;
use App\Models\Contest;
use App\Models\User;
use Mockery;
use Tests\TestCase;

class ContestStandingsCacheTest extends TestCase
{
    public function test_guest_and_regular_user_cannot_access_contests_or_sync(): void
    {
        $platform = $this->createPlatform('codeforces', 'Codeforces');
        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1001',
            'name' => 'CF Round 1001',
            'phase' => 'FINISHED',
        ]);

        // Unauthenticated
        $this->get(route('admin.all-contests.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.all-contests.sync-standings', $contest))
            ->assertRedirect(route('login'));

        // Regular user
        $user = User::query()->create([
            'name' => 'Regular User',
            'username' => 'reguser1',
            'email' => 'reguser1@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.all-contests.index'))
            ->assertRedirect(route('home'));

        $this->actingAs($user)
            ->post(route('admin.all-contests.sync-standings', $contest))
            ->assertRedirect(route('home'));
    }

    public function test_admin_can_view_contests_datatable_with_standings_status(): void
    {
        $admin = $this->createAdminUser();
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        // Contest 1: Finished, uncached -> should have Upload button
        $contest1 = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1001',
            'name' => 'CF Round 1001',
            'phase' => 'FINISHED',
            'start_time' => now()->subDays(2),
        ]);

        // Contest 2: Before, uncached -> should have Upcoming badge
        $contest2 = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1002',
            'name' => 'CF Round 1002',
            'phase' => 'BEFORE',
            'start_time' => now()->addDays(2),
        ]);

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('admin.all-contests.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]));

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertSame(1, $json['draw']);
        $this->assertSame(2, $json['recordsTotal']);
        $this->assertCount(2, $json['data']);

        $rows = collect($json['data']);
        $row1001 = $rows->firstWhere('platform_contest_id', '1001');
        $row1002 = $rows->firstWhere('platform_contest_id', '1002');

        $this->assertNotNull($row1001);
        $this->assertStringContainsString('sync-standings-btn', $row1001['standingsCache']);
        $this->assertStringContainsString('Upload', $row1001['standingsCache']);

        $this->assertNotNull($row1002);
        $this->assertStringContainsString('Upcoming', $row1002['standingsCache']);
    }

    public function test_admin_can_sync_standings_for_a_contest(): void
    {
        $admin = $this->createAdminUser();
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1003',
            'name' => 'CF Round 1003',
            'phase' => 'FINISHED',
        ]);

        $mockAdapter = Mockery::mock(PlatformAdapter::class);
        $dummyStandings = new ContestStandingsDTO(
            contest: new ContestDTO(
                platform: 'codeforces',
                platformContestId: '1003',
                title: 'CF Round 1003',
            ),
            problems: [],
            rows: [
                new ParticipantDTO(rank: 1, members: ['tourist']),
                new ParticipantDTO(rank: 2, members: ['petr']),
            ]
        );

        $mockAdapter->shouldReceive('getUserStandings')
            ->once()
            ->with('1003')
            ->andReturn($dummyStandings);

        $mockRegistry = Mockery::mock(PlatformRegistry::class);
        $mockRegistry->shouldReceive('resolve')
            ->with('codeforces')
            ->andReturn($mockAdapter);
        $mockRegistry->shouldReceive('get')
            ->with('codeforces')
            ->andReturn($mockAdapter);

        $this->app->instance(PlatformRegistry::class, $mockRegistry);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.all-contests.sync-standings', $contest));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'success' => true,
            ]);

        $this->assertStringContainsString('CF Round 1003', $response->json('message'));
        $this->assertStringContainsString('2 participants', $response->json('message'));

        $this->assertSame(2, $contest->fresh()->participant_count);
    }
}
