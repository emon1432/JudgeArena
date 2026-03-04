<?php

namespace App\Actions;

use App\Contracts\Platforms\PlatformAdapter;
use App\Enums\Platform as PlatformEnum;
use App\Models\PlatformProfile;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Throwable;

class SyncPlatformProfileAction
{
    public function execute(PlatformProfile $platformProfile, PlatformAdapter $adapter): void
    {
        $start = microtime(true);
        $platformProfile->increment('sync_attempts');

        try {
            DB::transaction(function () use ($platformProfile, $adapter) {
                // 1. Refresh to get latest data
                $platformProfile->refresh();

                // prevent double sync within 60 seconds
                if (
                    $platformProfile->last_synced_at &&
                    $platformProfile->last_synced_at->gt(now()->subSeconds(60))
                ) {
                    return;
                }

                // 2. Fetch profile
                $profileDto = $adapter->fetchProfile($platformProfile->handle);

                // 3. Determine total solved
                if ($adapter->supportsSubmissions()) {
                    try {
                        $submissions = $adapter->fetchSubmissions($platformProfile->handle);

                        $calculatedSolved = $submissions
                            ->unique(fn($sub) => $sub->problemId)
                            ->count();

                        $totalSolved = $calculatedSolved;

                        // SPOJ profile page exposes authoritative total solved count,
                        // while status pagination can miss older submissions.
                        if ($adapter->platform() === PlatformEnum::SPOJ->value) {
                            $totalSolved = max($profileDto->totalSolved, $calculatedSolved);
                        }

                        Log::info("Sync: Counted {$calculatedSolved} unique problems from " . $submissions->count() . " submissions for {$platformProfile->handle}");
                    } catch (\Exception $e) {
                        // If submissions fetch fails (e.g., Cloudflare blocks SPOJ),
                        // fall back to profile's total_solved count
                        Log::warning("Sync: Submissions fetch failed for {$platformProfile->handle}, using profile total_solved: {$profileDto->totalSolved}");
                        $totalSolved = $profileDto->totalSolved;
                    }
                } else {
                    // 🔥 IMPORTANT: trust profile DTO
                    $totalSolved = $profileDto->totalSolved;
                }

                // 4. Update platform_profiles (FIXED)
                $normalizedProfile = $this->normalizeBasicProfileData($profileDto->raw);

                $platformProfile->update([
                    'platform_user_id' => $normalizedProfile['platform_user_id'],
                    'name' => $normalizedProfile['name'],
                    'avatar_url' => $normalizedProfile['avatar_url'],
                    'joined_at' => $normalizedProfile['joined_at'],
                    'ranking' => $normalizedProfile['ranking'],
                    'rating' => $profileDto->rating,
                    'total_solved' => $totalSolved,
                    'profile_source' => $normalizedProfile['profile_source'],
                    'visibility_status' => $normalizedProfile['visibility_status'],
                    'profile_data' => $profileDto->raw,
                    'last_synced_at' => now(),
                    'last_sync_status' => 'success',
                    'last_sync_error' => null,
                    'captured_at' => now(),
                ]);
            });

            $durationMs = (int) ((microtime(true) - $start) * 1000);

            $platformProfile->update([
                'last_sync_duration_ms' => $durationMs,
            ]);

            // 5. Success log
            SyncLog::create([
                'platform_profile_id' => $platformProfile->id,
                'status' => 'success',
                'duration_ms' => $durationMs,
            ]);
        } catch (Throwable $e) {

            $durationMs = (int) ((microtime(true) - $start) * 1000);

            try {
                $platformProfile->update([
                    'last_sync_status' => 'failed',
                    'last_sync_error' => $e->getMessage(),
                    'last_sync_duration_ms' => $durationMs,
                ]);
            } catch (Throwable) {
                // avoid masking original sync exception
            }

            // 6. Failure log
            SyncLog::create([
                'platform_profile_id' => $platformProfile->id,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'duration_ms' => $durationMs,
            ]);

            throw $e;
        }
    }

    private function normalizeBasicProfileData(array $raw): array
    {
        $name = data_get($raw, 'name')
            ?? data_get($raw, 'full_name')
            ?? data_get($raw, 'display_name')
            ?? data_get($raw, 'profile.name')
            ?? data_get($raw, 'profile.real_name')
            ?? data_get($raw, 'profile.display_name')
            ?? data_get($raw, 'user.name')
            ?? data_get($raw, 'user.real_name')
            ?? data_get($raw, 'username');

        $avatarUrl = data_get($raw, 'avatar')
            ?? data_get($raw, 'avatar_url')
            ?? data_get($raw, 'profile.avatar')
            ?? data_get($raw, 'profile.avatar_url')
            ?? data_get($raw, 'title_photo')
            ?? data_get($raw, 'user.avatar')
            ?? data_get($raw, 'user.avatar_url');

        $ranking = data_get($raw, 'ranking')
            ?? data_get($raw, 'rank_by_solved')
            ?? data_get($raw, 'rank_by_rating')
            ?? data_get($raw, 'global_rank')
            ?? data_get($raw, 'rank')
            ?? data_get($raw, 'profile.ranking')
            ?? data_get($raw, 'contest_global_ranking')
            ?? data_get($raw, 'profile_metrics.global_rank')
            ?? data_get($raw, 'profile.rank');

        $joinedAt = data_get($raw, 'joined_at')
            ?? data_get($raw, 'join_date')
            ?? data_get($raw, 'registered_at')
            ?? data_get($raw, 'registration_date')
            ?? data_get($raw, 'profile.joined_at')
            ?? data_get($raw, 'profile.join_date')
            ?? data_get($raw, 'profile.registered_at')
            ?? data_get($raw, 'user.created_at');

        $platformUserId = data_get($raw, 'platform_user_id')
            ?? data_get($raw, 'user_id')
            ?? data_get($raw, 'id')
            ?? data_get($raw, 'profile.user_id')
            ?? data_get($raw, 'profile.id')
            ?? data_get($raw, 'user.id');

        $visibilityStatus = data_get($raw, 'visibility')
            ?? data_get($raw, 'profile.visibility')
            ?? data_get($raw, 'profile.privacy')
            ?? data_get($raw, 'privacy');

        if ($visibilityStatus === null) {
            $isPrivate = data_get($raw, 'is_private')
                ?? data_get($raw, 'profile.is_private')
                ?? data_get($raw, 'private');

            if ($isPrivate !== null) {
                $visibilityStatus = filter_var($isPrivate, FILTER_VALIDATE_BOOL) ? 'private' : 'public';
            }
        }

        return [
            'platform_user_id' => $this->normalizeString($platformUserId),
            'name' => $this->normalizeString($name),
            'avatar_url' => $this->normalizeString($avatarUrl),
            'joined_at' => $this->normalizeDate($joinedAt),
            'ranking' => $this->normalizeRanking($ranking),
            'profile_source' => $this->detectProfileSource($raw),
            'visibility_status' => $this->normalizeVisibilityStatus($visibilityStatus),
        ];
    }

    private function normalizeVisibilityStatus(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        return match ($normalized) {
            'public' => 'public',
            'private' => 'private',
            default => 'unknown',
        };
    }

    private function detectProfileSource(array $raw): string
    {
        $source = strtolower((string) (data_get($raw, 'source') ?? data_get($raw, 'fetch_mode') ?? ''));

        if (in_array($source, ['api', 'scrape', 'hybrid'], true)) {
            return $source;
        }

        if (
            data_get($raw, 'scraped') === true
            || data_get($raw, 'html') !== null
            || data_get($raw, 'cloudflare_blocked') !== null
        ) {
            return 'scrape';
        }

        return 'api';
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeRanking(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^(\d+)(st|nd|rd|th)?$/i', $normalized, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function normalizeDate(mixed $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $timestamp = (int) $value;

            if ($timestamp > 0) {
                return Carbon::createFromTimestamp($timestamp);
            }

            return null;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
