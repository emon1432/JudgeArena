<?php

namespace App\Platforms\AtCoder\Services;

use App\Platforms\AtCoder\Client\BaseClient;
use App\Platforms\AtCoder\DTOs\AtCoderSubmissionDTO;
use App\Platforms\AtCoder\DTOs\AtCoderUserDTO;
use App\Platforms\AtCoder\Mappers\AtCoderRatingChangeMapper;
use App\Platforms\AtCoder\Mappers\AtCoderSubmissionMapper;
use App\Platforms\AtCoder\Mappers\AtCoderUserMapper;
use App\Platforms\AtCoder\Support\ResponseNormalizer;
use App\Platforms\AtCoder\Transformers\RatingChangeTransformer;
use App\Services\ApplicationLogger;
use RuntimeException;

class Users
{
    public function __construct(
        private readonly BaseClient $client,
        private readonly AtCoderHtmlScraper $scraper,
    ) {}

    // used
    public function info(string $handle): AtCoderUserDTO
    {
        $htmlPayload = $this->scraper->getUserProfile($handle);

        $kenkooooPayload = [];
        try {
            $response = $this->client->requestApi('v3/user_info', ['user' => $handle]);
            if (is_array($response)) {
                $kenkooooPayload = $response;
            }
        } catch (\Throwable $e) {
            // Graceful fallback if Kenkoooo has not indexed this user
            $kenkooooPayload = [];
        }

        $merged = array_merge($htmlPayload, [
            'username' => ! empty($htmlPayload['username']) ? $htmlPayload['username'] : ($kenkooooPayload['user_id'] ?? $handle),
            'accepted_count' => $kenkooooPayload['accepted_count'] ?? null,
            'accepted_count_rank' => $kenkooooPayload['accepted_count_rank'] ?? null,
            'rated_point_sum' => $kenkooooPayload['rated_point_sum'] ?? null,
            'rated_point_sum_rank' => $kenkooooPayload['rated_point_sum_rank'] ?? null,
            'kenkoooo' => $kenkooooPayload,
        ]);

        if (empty($htmlPayload['avatarUrl']) && empty($htmlPayload['contestStatus']['algo']) && empty($kenkooooPayload['user_id'])) {
            throw new RuntimeException("AtCoder user request failed for handle: {$handle}");
        }

        return AtCoderUserMapper::fromNormalized(
            ResponseNormalizer::user($merged)
        );
    }

    /**
     * @param array{
     *     handle: string,
     *     from_second?: int,
     *     fromSecond?: int
     * } $params
     * @return array{
     *     submissions: AtCoderSubmissionDTO[],
     *     reached_stop: bool
     * }
     */
    public function submissions(array $params): array
    {
        $handle = $params['handle'] ?? '';
        $fromSecond = (int) ($params['from_second'] ?? $params['fromSecond'] ?? 0);

        $payload = $this->client->requestApi('v3/user/submissions', [
            'user' => $handle,
            'from_second' => $fromSecond,
        ]);

        $rawList = is_array($payload) ? $payload : [];

        return [
            'submissions' => AtCoderSubmissionMapper::fromNormalizedList(
                ResponseNormalizer::submissions($rawList)
            ),
            'reached_stop' => count($rawList) < 500,
        ];
    }

    // used
    public function ratingHistory(string $handle): array
    {
        $types = ['algo', 'heuristic'];
        $rawEntries = [];

        foreach ($types as $type) {
            $entries = $this->client->requestWebJson('/users/'.$handle.'/history/json?contestType='.$type);
            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    if (is_array($entry)) {
                        $entry['contest_type'] = $type;
                        $rawEntries[] = $entry;
                    }
                }
            }
        }

        return RatingChangeTransformer::fromApiRatingChanges(
            AtCoderRatingChangeMapper::fromNormalizedList(
                ResponseNormalizer::ratingChanges($rawEntries)
            ),
            null,
            $handle,
        );
    }

    public function exists(string $handle): bool
    {
        try {
            $this->info($handle);

            return true;
        } catch (RuntimeException $e) {
            app(ApplicationLogger::class)->warning('AtCoder user lookup failed', [
                'category' => 'api',
                'platform' => 'atcoder',
                'source' => self::class,
                'user_handle' => $handle,
            ], $e);

            return false;
        }
    }
}
