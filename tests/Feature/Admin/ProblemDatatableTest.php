<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Contest;
use App\Models\Platform;
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
}

