<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\ContestDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;
use DateTimeImmutable;

class ContestTransformer
{
    public function fromApiContest(array $contest): ContestDTO
    {
        $normalized = ResponseNormalizer::contest($contest);

        return new ContestDTO(
            platform: 'codeforces',
            platformContestId: (string) ($normalized['id'] ?? ''),
            title: (string) ($normalized['name'] ?? ''),
            phase: $normalized['phase'] ?? null,
            startedAt: isset($normalized['startTimeSeconds']) && $normalized['startTimeSeconds'] !== null
                ? (new DateTimeImmutable())->setTimestamp((int) $normalized['startTimeSeconds'])
                : null,
            durationSeconds: isset($normalized['durationSeconds']) ? (int) $normalized['durationSeconds'] : null,
            raw: $normalized,
        );
    }

    /** @return array<int, ContestDTO> */
    public function fromApiContests(array $contests): array
    {
        return array_map(fn(array $contest): ContestDTO => $this->fromApiContest($contest), $contests);
    }
}
