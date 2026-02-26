<?php

namespace App\Platforms\Spoj;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\Platform;
use App\Enums\Verdict;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SpojAdapter implements PlatformAdapter
{
    public function __construct(
        protected SpojClient $client
    ) {}

    public function platform(): string
    {
        return Platform::SPOJ->value;
    }

    public function profileUrl(string $handle): string
    {
        // Don't call fetchProfile here - it may fail due to Cloudflare
        // Just return the URL format directly
        return "https://www.spoj.com/users/{$handle}/";
    }

    public function supportsSubmissions(): bool
    {
        // Submissions parsing is unreliable due to complex HTML structure
        // Use profile's total_solved instead (fetched via FlareSolverr)
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        try {
            $profileData = $this->client->fetchProfile($handle);

            // Build raw data
            $rawData = [
                'handle' => $profileData['handle'],
                'points' => $profileData['points'],
                'rank' => $profileData['rank'],
                'join_date' => $profileData['join_date'],
                'problem_slugs_count' => count($profileData['problem_slugs'] ?? []),
            ];

            return new ProfileDTO(
                platform: Platform::SPOJ,
                handle: $profileData['handle'],
                rating: $profileData['points'],
                totalSolved: $profileData['total_solved'],
                raw: $rawData
            );
        } catch (\Exception $e) {
            // Cloudflare protection blocking access
            if (str_contains($e->getMessage(), 'Cloudflare') || str_contains($e->getMessage(), '403')) {
                Log::warning("SPOJ profile fetch blocked by Cloudflare for {$handle}: {$e->getMessage()}");

                // Return minimal profile to prevent sync failure
                return new ProfileDTO(
                    platform: Platform::SPOJ,
                    handle: $handle,
                    rating: null,
                    totalSolved: 0,
                    raw: [
                        'handle' => $handle,
                        'cloudflare_blocked' => true,
                        'error' => 'SPOJ currently blocks automated requests. Sync unavailable.',
                        'last_attempt' => now()->toIso8601String(),
                    ]
                );
            }

            // Re-throw other exceptions
            throw $e;
        }
    }

    public function fetchSubmissions(string $handle): Collection
    {
        try {
            $submissions = $this->client->fetchSubmissions($handle);

            return collect($submissions)
                ->filter(fn($sub) => $sub['status'] === 'AC')
                ->map(function ($sub) {
                    // Extract problem slug from URL
                    $problemSlug = basename(parse_url($sub['problem_url'], PHP_URL_PATH), '/');

                    return new SubmissionDTO(
                        problemId: $problemSlug,
                        problemName: $sub['problem_name'],
                        difficulty: null, // SPOJ doesn't have difficulty ratings
                        verdict: Verdict::ACCEPTED,
                        submittedAt: $sub['submitted_at'],
                        raw: [
                            'submission_id' => $sub['submission_id'],
                            'problem_url' => $sub['problem_url'],
                            'language' => $sub['language'],
                        ]
                    );
                });
        } catch (\Exception $e) {
            // If Cloudflare blocks submissions, throw exception
            // so sync action falls back to profile's total_solved
            if (str_contains($e->getMessage(), 'Cloudflare') || str_contains($e->getMessage(), '403')) {
                Log::warning("SPOJ submissions blocked by Cloudflare for {$handle}. Sync will use profile total_solved.");
                throw new \RuntimeException("SPOJ submissions blocked by Cloudflare - using profile data instead");
            }

            Log::error("SPOJ fetchSubmissions failed for {$handle}: {$e->getMessage()}");
            throw $e;
        }
    }
}
