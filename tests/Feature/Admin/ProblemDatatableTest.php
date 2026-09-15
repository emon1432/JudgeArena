<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Contest;
use App\Models\Problem;
use Tests\TestCase;

class ProblemDatatableTest extends TestCase
{
    public function test_admin_can_search_problems_in_datatable_without_sql_column_errors(): void
    {
        $admin = $this->createAdminUser();
        $platform = $this->createPlatform('codeforces', 'Codeforces');

        $contest = Contest::query()->create([
            'platform_id' => $platform->id,
            'platform_contest_id' => '1000',
            'name' => 'CF Round 1000',
            'phase' => 'FINISHED',
        ]);

        Problem::query()->create([
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => '1000A',
            'name' => 'Codeforces Problem A',
            'code' => 'A',
            'rating' => 1200,
        ]);

        Problem::query()->create([
            'platform_id' => $platform->id,
            'contest_id' => $contest->id,
            'platform_problem_id' => '1000B',
            'name' => 'AtCoder Similar Task B',
            'code' => 'B',
            'rating' => 1400,
        ]);

        // Search for 'Codeforces' - should return 1 matching record without 1054 SQL error
        $response = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('admin.all-problems.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'search' => ['value' => 'Codeforces'],
                'order' => [
                    ['column' => 0, 'dir' => 'asc'],
                ],
            ]));

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertSame(1, $json['draw']);
        $this->assertSame(2, $json['recordsTotal']);
        $this->assertGreaterThanOrEqual(1, $json['recordsFiltered']);
    }

    public function test_admin_can_search_by_platform_problem_id(): void
    {
        $admin = $this->createAdminUser();
        $platform = $this->createPlatform('atcoder', 'AtCoder');

        Problem::query()->create([
            'platform_id' => $platform->id,
            'platform_problem_id' => 'abc300_a',
            'name' => 'N-choice question',
            'code' => 'A',
            'rating' => 100,
        ]);

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('admin.all-problems.index', [
                'draw' => 2,
                'start' => 0,
                'length' => 10,
                'search' => ['value' => 'abc300_a'],
            ]));

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertSame(1, $json['recordsFiltered']);
    }

    public function test_admin_can_filter_problems_by_platform_and_rating_range(): void
    {
        $admin = $this->createAdminUser();
        $cf = $this->createPlatform('codeforces', 'Codeforces');
        $atc = $this->createPlatform('atcoder', 'AtCoder');

        Problem::query()->create([
            'platform_id' => $cf->id,
            'platform_problem_id' => '100A',
            'name' => 'CF 100 A',
            'code' => 'A',
            'rating' => 1000,
            'status' => 'active',
        ]);

        Problem::query()->create([
            'platform_id' => $cf->id,
            'platform_problem_id' => '100B',
            'name' => 'CF 100 B',
            'code' => 'B',
            'rating' => 1500,
            'status' => 'active',
        ]);

        Problem::query()->create([
            'platform_id' => $atc->id,
            'platform_problem_id' => 'abc100_a',
            'name' => 'AtCoder 100 A',
            'code' => 'A',
            'rating' => 1000,
            'status' => 'active',
        ]);

        // Filter by platform = Codeforces
        $response = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('admin.all-problems.index', [
                'platform' => (string) $cf->id,
            ]));

        $response->assertStatus(200);
        $this->assertSame(2, $response->json('recordsFiltered'));

        // Filter by rating_range = 800-1199
        $responseRating = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('admin.all-problems.index', [
                'rating_range' => '800-1199',
            ]));

        $responseRating->assertStatus(200);
        $this->assertSame(2, $responseRating->json('recordsFiltered'));

        // Filter by platform = Codeforces AND rating_range = 800-1199
        $responseBoth = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('admin.all-problems.index', [
                'platform' => (string) $cf->id,
                'rating_range' => '800-1199',
            ]));

        $responseBoth->assertStatus(200);
        $this->assertSame(1, $responseBoth->json('recordsFiltered'));
    }
}
