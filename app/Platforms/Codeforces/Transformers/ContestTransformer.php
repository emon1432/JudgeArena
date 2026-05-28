<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\ContestDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesContestDTO;
use DateTimeImmutable;

class ContestTransformer
{
    /**
     * @return ContestDTO
     */
    public function fromApiContest(CodeforcesContestDTO $contest): ContestDTO
    {
        return new ContestDTO(
            platform: 'codeforces',
            platformContestId: (string) ($contest->id ?? ''),
            title: (string) ($contest->name ?? ''),
            phase: $contest->phase ?? null,
            startedAt: isset($contest->startTimeSeconds)
                ? (new DateTimeImmutable())->setTimestamp((int) $contest->startTimeSeconds)
                : null,
            durationSeconds: $contest->durationSeconds,
            raw: $contest->raw,
        );
    }

    /** @return array<int, ContestDTO> */
    public function fromApiContests(array $contests): array
    {
        return array_map(fn(CodeforcesContestDTO $contest): ContestDTO => $this->fromApiContest($contest), $contests);
    }
}
