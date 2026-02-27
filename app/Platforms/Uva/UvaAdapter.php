<?php

namespace App\Platforms\Uva;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\Platform;
use App\Enums\Verdict;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UvaAdapter implements PlatformAdapter
{
    public function __construct(
        protected UvaClient $client
    ) {}

    public function platform(): string
    {
        return Platform::UVA->value;
    }

    public function profileUrl(string $handle): string
    {
        $profile = $this->client->fetchProfile($handle);
        return "https://uhunt.onlinejudge.org/id/{$profile['user_id']}";
    }

    public function supportsSubmissions(): bool
    {
        return true;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $profileData = $this->client->fetchProfile($handle);

        return new ProfileDTO(
            platform: Platform::UVA,
            handle: $profileData['handle'],
            rating: null,
            totalSolved: $profileData['total_solved'],
            raw: [
                'user_id' => $profileData['user_id'],
                'name' => $profileData['name'] ?? null,
                'uname' => $profileData['uname'] ?? null,
                'submissions' => $profileData['submissions'] ?? 0,
                'ranking' => $profileData['ranking'] ?? $profileData['rank'] ?? null,
                'rank' => $profileData['rank'] ?? null,
            ]
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        $submissions = $this->client->fetchSubmissions($handle);
        $problemMapping = $this->client->fetchProblems();

        return collect($submissions)
            ->filter(fn($sub) => $sub[2] === 90) // Only AC (status 90)
            ->map(function ($sub) use ($problemMapping) {
                $problemId = $sub[1];
                $problemName = $problemMapping[$problemId]['name'] ?? "Problem {$problemId}";
                $timestamp = $sub[4]; // Unix timestamp
                $verdict = UvaClient::normalizeVerdict($sub[2]);
                $language = UvaClient::getLanguage($sub[5]);
                $problemUrl = UvaClient::getProblemUrl($problemId);

                return new SubmissionDTO(
                    problemId: (string) $problemId,
                    problemName: $problemName,
                    difficulty: null,
                    verdict: Verdict::ACCEPTED,
                    submittedAt: CarbonImmutable::createFromTimestamp($timestamp),
                    raw: [
                        'problem_number' => $problemId,
                        'language' => $language,
                        'verdict' => $verdict,
                        'problem_url' => $problemUrl,
                    ]
                );
            });
    }
}
