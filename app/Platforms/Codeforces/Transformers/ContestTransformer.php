<?php

declare(strict_types=1);

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
        $platformContestId = (string) ($contest->id ?? '');
        $title = (string) ($contest->name ?? '');
        $slug = \Illuminate\Support\Str::slug($platformContestId . '-' . $title);

        return new ContestDTO(
            platform: 'codeforces',
            platformContestId: $platformContestId,
            title: $title,
            slug: $slug,
            type: $contest->type ?? null,
            phase: $contest->phase ?? null,
            startedAt: isset($contest->startTimeSeconds)
            ? (new DateTimeImmutable())->setTimestamp((int) $contest->startTimeSeconds)
            : null,
            durationSeconds: $contest->durationSeconds,
            endedAt: isset($contest->startTimeSeconds, $contest->durationSeconds)
            ? (new DateTimeImmutable())->setTimestamp((int) $contest->startTimeSeconds + (int) $contest->durationSeconds)
            : null,
            url: $contest->url,
            raw: $contest->raw,
        );
    }

    public function fromApiContests(array $contests): array
    {
        return array_map(fn(CodeforcesContestDTO $contest): ContestDTO => $this->fromApiContest($contest), $contests);
    }
}

