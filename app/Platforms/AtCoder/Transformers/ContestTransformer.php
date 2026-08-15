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
        $startedAt = $this->parseStartTime($contest->date);
        $durationSeconds = $this->parseDurationSeconds($contest->duration);
        $endedAt = ($startedAt !== null && $durationSeconds !== null)
            ? $startedAt->add(new \DateInterval('PT' . $durationSeconds . 'S'))
            : null;

        $phase = $this->determinePhase($startedAt, $endedAt, $contest->type);

        $platformContestId = (string) ($contest->id ?? '');
        $title = (string) ($contest->title ?? '');
        $slug = \Illuminate\Support\Str::slug($platformContestId . '-' . $title);

        return new ContestDTO(
            platform: 'atcoder',
            platformContestId: $platformContestId,
            title: $title,
            slug: $slug,
            type: $contest->type,
            phase: $phase,
            startedAt: $startedAt,
            durationSeconds: $durationSeconds,
            endedAt: $endedAt,
            url: $contest->url,
            raw: $contest->raw,
        );
    }

    /** @return array<int, ContestDTO> */
    public function fromApiContests(array $contests): array
    {
        return array_map(fn(AtCoderContestDTO $contest): ContestDTO => $this->fromApiContest($contest), $contests);
    }

    private function determinePhase(?DateTimeImmutable $startedAt, ?DateTimeImmutable $endedAt, ?string $type = null): ?string
    {
        if ($type === 'permanent') {
            return 'CODING';
        }

        if ($startedAt === null && $endedAt === null) {
            return 'CODING';
        }

        $now = new DateTimeImmutable();

        if ($startedAt !== null && $now < $startedAt) {
            return 'BEFORE';
        }

        if ($endedAt !== null && $now > $endedAt) {
            return 'FINISHED';
        }

        return 'CODING';
    }

    private function parseStartTime(?string $date): ?DateTimeImmutable
    {
        if ($date === null) {
            return null;
        }

        $date = trim($date);
        if ($date === '' || strcasecmp($date, 'Permanent') === 0 || $date === '-') {
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
        if ($duration === '' || $duration === '-') {
            return null;
        }

        if (strcasecmp($duration, 'Permanent') === 0) {
            return 3153600000; // 100 years in seconds
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

