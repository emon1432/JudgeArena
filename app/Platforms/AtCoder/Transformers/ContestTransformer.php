<?php

declare(strict_types=1);

namespace App\Platforms\AtCoder\Transformers;

use App\Core\DTOs\ContestDTO;
use App\Platforms\AtCoder\DTOs\AtCoderContestDTO;
use App\Platforms\AtCoder\Services\AtCoderTitleTranslatorService;
use DateTimeImmutable;

class ContestTransformer
{
    private readonly AtCoderTitleTranslatorService $translator;

    public function __construct(
        ?AtCoderTitleTranslatorService $translator = null
    ) {
        $this->translator = $translator ?? app(AtCoderTitleTranslatorService::class);
    }

    /**
     * @return ContestDTO
     */
    public function fromApiContest(AtCoderContestDTO $contest): ContestDTO
    {
        $startedAt = $contest->startEpochSecond !== null && $contest->startEpochSecond > 0
            ? (new DateTimeImmutable())->setTimestamp($contest->startEpochSecond)
            : $this->parseStartTime($contest->date);

        $durationSeconds = $contest->durationSecond !== null && $contest->durationSecond > 0
            ? $contest->durationSecond
            : $this->parseDurationSeconds($contest->duration);

        $endedAt = ($startedAt !== null && $durationSeconds !== null)
            ? $startedAt->add(new \DateInterval('PT' . $durationSeconds . 'S'))
            : null;

        $platformContestId = (string) ($contest->id ?? '');
        $type = $this->determineType($platformContestId, $contest->type, (string) ($contest->title ?? ''));
        $phase = $this->determinePhase($startedAt, $endedAt, $type);

        $rawTitle = (string) ($contest->title ?? '');
        $title = $this->translator->formatContestTitle($platformContestId, $rawTitle);
        $slug = \Illuminate\Support\Str::slug($platformContestId . '-' . $title);

        $rateChangeSpec = $this->parseRateChange($contest->rateChange);

        $raw = array_merge($contest->raw, [
            'rate_change_spec' => $rateChangeSpec,
            'is_rated' => $rateChangeSpec['is_rated'],
        ]);

        return new ContestDTO(
            platform: 'atcoder',
            platformContestId: $platformContestId,
            title: $title,
            slug: $slug,
            type: $type,
            phase: $phase,
            startedAt: $startedAt,
            durationSeconds: $durationSeconds,
            endedAt: $endedAt,
            url: $contest->url ?? ($platformContestId !== '' ? "https://atcoder.jp/contests/{$platformContestId}" : null),
            raw: $raw,
        );
    }

    /** @return array<int, ContestDTO> */
    public function fromApiContests(array $contests): array
    {
        return array_map(fn(AtCoderContestDTO $contest): ContestDTO => $this->fromApiContest($contest), $contests);
    }

    private function determineType(string $id, ?string $explicitType, string $title): string
    {
        if ($explicitType !== null && $explicitType !== '' && strcasecmp($explicitType, 'normal') !== 0 && strcasecmp($explicitType, 'algorithm') !== 0) {
            $upper = strtoupper($explicitType);
            if (in_array($upper, ['ABC', 'ARC', 'AGC', 'AHC', 'ADT', 'AWC', 'JOI', 'PAST'], true)) {
                return $upper;
            }

            return ucfirst(strtolower($explicitType));
        }

        $lowerId = strtolower($id);
        $lowerTitle = strtolower($title);

        if (str_starts_with($lowerId, 'abc')) {
            return 'ABC';
        }

        if (str_starts_with($lowerId, 'arc')) {
            return 'ARC';
        }

        if (str_starts_with($lowerId, 'agc')) {
            return 'AGC';
        }

        if (str_starts_with($lowerId, 'ahc')) {
            return 'AHC';
        }

        if (str_starts_with($lowerId, 'adt') || str_contains($lowerId, 'adt_') || str_contains($lowerTitle, 'daily training')) {
            return 'ADT';
        }

        if (str_starts_with($lowerId, 'awc') || str_contains($lowerId, 'awc_') || str_contains($lowerTitle, 'weekday')) {
            return 'AWC';
        }

        if (str_starts_with($lowerId, 'past') || str_contains($lowerTitle, 'practical algorithm')) {
            return 'PAST';
        }

        if (str_starts_with($lowerId, 'joi') || str_starts_with($lowerId, 'joisc') || str_contains($lowerTitle, 'japanese olympiad')) {
            return 'JOI';
        }

        if (
            str_contains($lowerTitle, 'heuristic') ||
            str_contains($lowerTitle, 'marathon') ||
            str_starts_with($lowerId, 'hokudai') ||
            str_starts_with($lowerId, 'asprocon') ||
            str_starts_with($lowerId, 'future-contest') ||
            str_starts_with($lowerId, 'masters')
        ) {
            return 'Heuristic';
        }

        return 'Algorithm';
    }

    private function determinePhase(?DateTimeImmutable $startedAt, ?DateTimeImmutable $endedAt, ?string $type = null): ?string
    {
        if (strcasecmp((string) $type, 'permanent') === 0) {
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

        if (preg_match('/^(\d+):(\d+)$/', $duration, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];

            return ($hours * 3600) + ($minutes * 60);
        }

        if (preg_match('/^(\d+):(\d+):(\d+)$/', $duration, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = (int) $matches[3];

            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }

        return null;
    }

    /**
     * Parse raw rate_change string from AtCoder into structured rating change specifications.
     *
     * @return array{
     *     is_rated: bool,
     *     min_rating: ?int,
     *     max_rating: ?int,
     *     label: string,
     *     raw: string
     * }
     */
    private function parseRateChange(?string $rateChange): array
    {
        $raw = trim((string) $rateChange);

        if ($raw === '' || $raw === '-' || $raw === '~ 0' || $raw === '0 ~ 0' || $raw === '~0') {
            return [
                'is_rated' => false,
                'min_rating' => null,
                'max_rating' => null,
                'label' => 'Unrated',
                'raw' => $raw,
            ];
        }

        if (strcasecmp($raw, 'all') === 0) {
            return [
                'is_rated' => true,
                'min_rating' => 0,
                'max_rating' => null,
                'label' => 'Rated for All',
                'raw' => $raw,
            ];
        }

        // Pattern 1: "~ 1999" or "~1199" (Upper bound only)
        if (preg_match('/^~\s*(\d+)$/', $raw, $matches)) {
            $max = (int) $matches[1];

            return [
                'is_rated' => true,
                'min_rating' => 0,
                'max_rating' => $max,
                'label' => "Rated for ≤ {$max}",
                'raw' => $raw,
            ];
        }

        // Pattern 2: "1200 ~ 2799" (Range with lower and upper bound)
        if (preg_match('/^(\d+)\s*~\s*(\d+)$/', $raw, $matches)) {
            $min = (int) $matches[1];
            $max = (int) $matches[2];

            return [
                'is_rated' => true,
                'min_rating' => $min,
                'max_rating' => $max,
                'label' => "Rated for {$min} ~ {$max}",
                'raw' => $raw,
            ];
        }

        // Pattern 3: "1200 ~" or "2000 ~" (Lower bound open-ended)
        if (preg_match('/^(\d+)\s*~$/', $raw, $matches)) {
            $min = (int) $matches[1];

            return [
                'is_rated' => true,
                'min_rating' => $min,
                'max_rating' => null,
                'label' => "Rated for ≥ {$min}",
                'raw' => $raw,
            ];
        }

        // Fallback for any other rated indication with numbers
        if (preg_match('/\d+/', $raw)) {
            return [
                'is_rated' => true,
                'min_rating' => null,
                'max_rating' => null,
                'label' => "Rated ({$raw})",
                'raw' => $raw,
            ];
        }

        return [
            'is_rated' => false,
            'min_rating' => null,
            'max_rating' => null,
            'label' => 'Unrated',
            'raw' => $raw,
        ];
    }
}
