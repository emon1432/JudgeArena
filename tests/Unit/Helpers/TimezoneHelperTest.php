<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class TimezoneHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_timezone_defaults_to_utc_for_guest_on_web_routes(): void
    {
        $this->app->instance('request', Request::create('/contests', 'GET'));

        $this->assertSame('UTC', display_timezone());
    }

    public function test_display_timezone_returns_admin_setting_on_admin_routes(): void
    {
        $this->app->instance('request', Request::create('/admin/dashboard', 'GET'));

        $expected = settings('system_settings', 'app_timezone', 'Asia/Dhaka');
        $this->assertSame($expected, display_timezone());
    }

    public function test_display_timezone_returns_user_timezone_when_configured(): void
    {
        $user = new User();
        $user->timezone = 'America/New_York';

        $this->assertSame('America/New_York', display_timezone($user));
    }

    public function test_to_display_timezone_converts_utc_to_target_timezone(): void
    {
        $utcTime = '2026-09-25 12:00:00';

        $converted = to_display_timezone($utcTime, 'Asia/Dhaka');

        $this->assertNotNull($converted);
        $this->assertSame('Asia/Dhaka', $converted->timezoneName);
        $this->assertSame('2026-09-25 18:00:00', $converted->format('Y-m-d H:i:s'));
    }

    public function test_to_display_timezone_handles_null_and_empty_values(): void
    {
        $this->assertNull(to_display_timezone(null));
        $this->assertNull(to_display_timezone(''));
    }

    public function test_to_display_timezone_handles_carbon_instances(): void
    {
        $carbonUtc = Carbon::create(2026, 9, 25, 12, 0, 0, 'UTC');

        $converted = to_display_timezone($carbonUtc, 'Asia/Dhaka');

        $this->assertNotNull($converted);
        $this->assertSame('2026-09-25 18:00:00', $converted->format('Y-m-d H:i:s'));
    }

    public function test_to_display_timezone_handles_numeric_timestamps(): void
    {
        $timestamp = Carbon::create(2026, 9, 25, 12, 0, 0, 'UTC')->timestamp;

        $converted = to_display_timezone($timestamp, 'Asia/Dhaka');

        $this->assertNotNull($converted);
        $this->assertSame('2026-09-25 18:00:00', $converted->format('Y-m-d H:i:s'));
    }

    public function test_format_date_time_formats_correctly_with_timezone(): void
    {
        $utcTime = '2026-09-25 12:00:00';

        $formatted = format_date_time($utcTime, 'Asia/Dhaka', 'Y-m-d h:i A');

        $this->assertSame('2026-09-25 06:00 PM', $formatted);
    }

    public function test_format_date_and_format_time(): void
    {
        $utcTime = '2026-09-25 22:30:00';

        // 22:30 UTC is next day 04:30 AM in Asia/Dhaka (+6)
        $formattedDate = format_date($utcTime, 'Asia/Dhaka', 'Y-m-d');
        $formattedTime = format_time($utcTime, 'Asia/Dhaka', 'h:i A');

        $this->assertSame('2026-09-26', $formattedDate);
        $this->assertSame('04:30 AM', $formattedTime);
    }
}
