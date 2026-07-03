<?php

namespace App\Platforms\AtCoder\Transformers;

use App\Core\DTOs\ContestDTO;
use App\Platforms\AtCoder\DTOs\AtCoderContestDTO;
use DateTimeImmutable;

class ContestTransformer
{
    /**
     * @return ContestDTO
     */
    public function fromApiContest(AtCoderContestDTO $contest): ContestDTO
    {
        return new ContestDTO(
            platform: 'atcoder',
            platformContestId: (string) ($contest->id ?? ''),
            title: (string) ($contest->title ?? ''),
            phase: null,
            startedAt: $this->parseStartTime($contest->date),
            durationSeconds: $this->parseDurationSeconds($contest->duration),
            endedAt: isset($contest->date, $contest->duration)
                ? $this->parseStartTime($contest->date)?->add(new \DateInterval('PT' . $this->parseDurationSeconds($contest->duration) . 'S'))
                : null,
            raw: $contest->raw,
        );
    }

    /** @return array<int, ContestDTO> */
    public function fromApiContests(array $contests): array
    {
        return array_map(fn (AtCoderContestDTO $contest): ContestDTO => $this->fromApiContest($contest), $contests);
    }

    private function parseStartTime(?string $date): ?DateTimeImmutable
    {
        if ($date === null) {
            return null;
        }

        $date = trim($date);
        if ($date === '' || strcasecmp($date, 'Permanent') === 0) {
            return null;
        }

        $timestamp = strtotime($date);

        return $timestamp === false ? null : (new DateTimeImmutable())->setTimestamp($timestamp);
    }

    private function parseDurationSeconds(?string $duration): ?int
    {
        if ($duration === null) {
            return null;
        }

        $duration = trim($duration);
        if ($duration === '' || $duration === '-' || strcasecmp($duration, 'Permanent') === 0) {
            return null;
        }

        if (preg_match('/^(\d+):(\d+)(?::(\d+))?$/', $duration, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = isset($matches[3]) ? (int) $matches[3] : 0;

            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }

        return null;
    }
}

