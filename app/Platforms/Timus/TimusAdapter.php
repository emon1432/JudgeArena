<?php

namespace App\Platforms\Timus;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\Platform;
use App\Enums\Verdict;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class TimusAdapter implements PlatformAdapter
{
    public function __construct(
        protected TimusClient $client
    ) {}

    public function platform(): string
    {
        return Platform::TIMUS->value;
    }

    public function profileUrl(string $handle): string
    {
        $this->client->fetchProfile($handle);
        return "http://acm.timus.ru/author.aspx?id=" . $handle;
    }

    public function supportsSubmissions(): bool
    {
        // TIMUS profile page provides authoritative solved count; pagination can miss older submissions.
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $profileData = $this->client->fetchProfile($handle);

        return new ProfileDTO(
            platform: Platform::TIMUS,
            handle: $profileData['handle'],
            rating: $profileData['rating'],
            totalSolved: $profileData['total_solved'],
            raw: [
                'platform_user_id' => (string) ($profileData['user_id'] ?? $profileData['handle']),
                'name' => $profileData['name'] ?? $profileData['handle'],
                'avatar_url' => $profileData['avatar_url'] ?? null,
                'joined_at' => $profileData['joined_at'] ?? null,
                'country' => $profileData['country'] ?? null,
                'profile_url' => $profileData['profile_url'],
                'user_id' => $profileData['user_id'],
                'total_solved' => $profileData['total_solved'],
                'rating' => $profileData['rating'],
                'rank_by_solved' => $profileData['rank_by_solved'] ?? null,
                'rank_by_rating' => $profileData['rank_by_rating'] ?? null,
                'rating_score' => $profileData['rating_score'] ?? null,
            ]
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        try {
            $submissions = $this->client->fetchSubmissions($handle);

            return collect($submissions)
                ->map(fn($sub) => new SubmissionDTO(
                    problemId: (string) ($sub['problem_id'] ?? 'unknown'),
                    problemName: $sub['problem_name'] ?? 'Unknown',
                    difficulty: null,
                    verdict: Verdict::ACCEPTED,
                    submittedAt: CarbonImmutable::parse($sub['timestamp']),
                    raw: [
                        'problem_link' => $sub['problem_link'] ?? null,
                        'language' => $sub['language'] ?? 'Unknown',
                        'status' => $sub['status'] ?? 'AC',
                    ]
                ))
                ->sortByDesc('submittedAt');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Timus submissions fetch failed for {$handle}: {$e->getMessage()}");
            return collect();
        }
    }
}
