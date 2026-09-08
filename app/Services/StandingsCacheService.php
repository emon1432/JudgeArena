<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DTOs\ContestDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemDTO;
use App\Core\DTOs\ProblemResultDTO;
use App\Core\Platforms\PlatformRegistry;
use App\Services\GoogleDrive\GoogleDriveClient;
use DateTimeImmutable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StandingsCacheService
{
    public function __construct(
        private readonly GoogleDriveClient $googleDriveClient,
        private readonly PlatformRegistry $platformRegistry,
    ) {}

    public function isEnabled(): bool
    {
        return (bool) config('standings.cache_enabled', true);
    }

    public function diskName(): string
    {
        return (string) config('standings.disk', env('STANDINGS_DISK', 'local'));
    }

    /**
     * Get platform folder name dynamically from PlatformRegistry (e.g. 'Codeforces', 'AtCoder').
     */
    public function platformFolder(string $platform): string
    {
        return $this->platformRegistry->getPlatformName($platform);
    }

    /**
     * Folder hierarchy: [Platform, Topic] (e.g. ['Codeforces', 'Standings'])
     *
     * @return array<int, string>
     */
    public function targetSubfolder(string $platform): array
    {
        return [$this->platformFolder($platform), 'Standings'];
    }

    public function has(string $platform, string $contestId): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        try {
            if ($this->diskName() === 'google') {
                return $this->googleDriveClient->exists(
                    $this->googleFilename($contestId),
                    $this->targetSubfolder($platform)
                );
            }

            return Storage::disk($this->diskName())->exists($this->diskPath($platform, $contestId));
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('StandingsCacheService has check failed', [
                'category' => 'storage',
                'platform' => $platform,
                'contest_id' => $contestId,
                'disk' => $this->diskName(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function get(string $platform, string $contestId): ?ContestStandingsDTO
    {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            $binary = null;

            if ($this->diskName() === 'google') {
                $binary = $this->googleDriveClient->get(
                    $this->googleFilename($contestId),
                    $this->targetSubfolder($platform)
                );
            } else {
                $path = $this->diskPath($platform, $contestId);
                $disk = Storage::disk($this->diskName());

                if ($disk->exists($path)) {
                    $binary = $disk->get($path);
                }
            }

            if ($binary === null || $binary === '') {
                return null;
            }

            $json = @gzdecode($binary);
            if ($json === false || $json === '') {
                // If not gzipped, try raw json
                $json = $binary;
            }

            $payload = json_decode($json, true);
            if (! is_array($payload)) {
                return null;
            }

            return $this->deserialize($payload);
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('StandingsCacheService get failed', [
                'category' => 'storage',
                'platform' => $platform,
                'contest_id' => $contestId,
                'disk' => $this->diskName(),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function put(string $platform, string $contestId, ContestStandingsDTO $standings): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        try {
            $serialized = $this->serialize($standings);
            $json = json_encode($serialized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($json === false) {
                return false;
            }

            $level = (int) config('standings.compression_level', 9);
            $compressed = gzencode($json, $level);

            if ($compressed === false) {
                return false;
            }

            if ($this->diskName() === 'google') {
                return $this->googleDriveClient->put(
                    $this->googleFilename($contestId),
                    $compressed,
                    'application/gzip',
                    $this->targetSubfolder($platform)
                );
            }

            return Storage::disk($this->diskName())->put(
                $this->diskPath($platform, $contestId),
                $compressed
            );
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('StandingsCacheService put failed', [
                'category' => 'storage',
                'platform' => $platform,
                'contest_id' => $contestId,
                'disk' => $this->diskName(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(string $platform, string $contestId): bool
    {
        try {
            if ($this->diskName() === 'google') {
                return $this->googleDriveClient->delete(
                    $this->googleFilename($contestId),
                    $this->targetSubfolder($platform)
                );
            }

            $path = $this->diskPath($platform, $contestId);
            $disk = Storage::disk($this->diskName());

            if ($disk->exists($path)) {
                return $disk->delete($path);
            }

            return true;
        } catch (Throwable $e) {
            app(ApplicationLogger::class)->warning('StandingsCacheService delete failed', [
                'category' => 'storage',
                'platform' => $platform,
                'contest_id' => $contestId,
                'disk' => $this->diskName(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function diskPath(string $platform, string $contestId): string
    {
        return "{$this->platformFolder($platform)}/Standings/{$contestId}.json.gz";
    }

    public function googleFilename(string $contestId): string
    {
        return "{$contestId}.json.gz";
    }

    public function serialize(ContestStandingsDTO $standings): array
    {
        return [
            'contest' => [
                'platform' => $standings->contest->platform,
                'platformContestId' => $standings->contest->platformContestId,
                'title' => $standings->contest->title,
                'slug' => $standings->contest->slug,
                'type' => $standings->contest->type,
                'phase' => $standings->contest->phase,
                'startedAt' => $standings->contest->startedAt?->format('c'),
                'durationSeconds' => $standings->contest->durationSeconds,
                'endedAt' => $standings->contest->endedAt?->format('c'),
                'url' => $standings->contest->url,
                'raw' => $standings->contest->raw,
            ],
            'problems' => array_map(function (ProblemDTO $problem): array {
                return [
                    'platform' => $problem->platform,
                    'platformProblemId' => $problem->platformProblemId,
                    'title' => $problem->title,
                    'contestPlatformId' => $problem->contestPlatformId,
                    'code' => $problem->code,
                    'points' => $problem->points,
                    'rating' => $problem->rating,
                    'timeLimit' => $problem->timeLimit,
                    'memoryLimit' => $problem->memoryLimit,
                    'tags' => $problem->tags,
                    'url' => $problem->url,
                    'raw' => $problem->raw,
                    'solvedCount' => $problem->solvedCount,
                ];
            }, $standings->problems),
            'rows' => array_map(function (ParticipantDTO $participant): array {
                return [
                    'rank' => $participant->rank,
                    'points' => $participant->points,
                    'penalty' => $participant->penalty,
                    'members' => $participant->members,
                    'problemResults' => array_map(function (ProblemResultDTO $pr): array {
                        return [
                            'points' => $pr->points,
                            'penalty' => $pr->penalty,
                            'rejectedAttemptCount' => $pr->rejectedAttemptCount,
                            'type' => $pr->type,
                            'bestSubmissionTimeSeconds' => $pr->bestSubmissionTimeSeconds,
                        ];
                    }, $participant->problemResults),
                    'raw' => $participant->raw,
                ];
            }, $standings->rows),
            'raw' => $standings->raw,
        ];
    }

    public function deserialize(array $data): ContestStandingsDTO
    {
        $contestData = (array) ($data['contest'] ?? []);
        $startedAtStr = (string) ($contestData['startedAt'] ?? '');
        $endedAtStr = (string) ($contestData['endedAt'] ?? '');

        $contest = new ContestDTO(
            platform: (string) ($contestData['platform'] ?? ''),
            platformContestId: (string) ($contestData['platformContestId'] ?? ''),
            title: (string) ($contestData['title'] ?? ''),
            slug: isset($contestData['slug']) ? (string) $contestData['slug'] : null,
            type: isset($contestData['type']) ? (string) $contestData['type'] : null,
            phase: isset($contestData['phase']) ? (string) $contestData['phase'] : null,
            startedAt: $startedAtStr !== '' ? new DateTimeImmutable($startedAtStr) : null,
            durationSeconds: isset($contestData['durationSeconds']) ? (int) $contestData['durationSeconds'] : null,
            endedAt: $endedAtStr !== '' ? new DateTimeImmutable($endedAtStr) : null,
            url: isset($contestData['url']) ? (string) $contestData['url'] : null,
            raw: (array) ($contestData['raw'] ?? []),
        );

        $problems = array_map(function (array $p): ProblemDTO {
            return new ProblemDTO(
                platform: (string) ($p['platform'] ?? ''),
                platformProblemId: (string) ($p['platformProblemId'] ?? ''),
                title: (string) ($p['title'] ?? ''),
                contestPlatformId: isset($p['contestPlatformId']) ? (string) $p['contestPlatformId'] : null,
                code: isset($p['code']) ? (string) $p['code'] : null,
                points: isset($p['points']) ? (float) $p['points'] : null,
                rating: isset($p['rating']) ? (int) $p['rating'] : null,
                timeLimit: isset($p['timeLimit']) ? (int) $p['timeLimit'] : null,
                memoryLimit: isset($p['memoryLimit']) ? (int) $p['memoryLimit'] : null,
                tags: (array) ($p['tags'] ?? []),
                url: isset($p['url']) ? (string) $p['url'] : null,
                raw: (array) ($p['raw'] ?? []),
                solvedCount: isset($p['solvedCount']) ? (int) $p['solvedCount'] : null,
            );
        }, (array) ($data['problems'] ?? []));

        $rows = array_map(function (array $r): ParticipantDTO {
            $problemResults = array_map(function (array $pr): ProblemResultDTO {
                return new ProblemResultDTO(
                    points: isset($pr['points']) ? (float) $pr['points'] : null,
                    penalty: isset($pr['penalty']) ? (int) $pr['penalty'] : null,
                    rejectedAttemptCount: isset($pr['rejectedAttemptCount']) ? (int) $pr['rejectedAttemptCount'] : null,
                    type: isset($pr['type']) ? (string) $pr['type'] : null,
                    bestSubmissionTimeSeconds: isset($pr['bestSubmissionTimeSeconds']) ? (int) $pr['bestSubmissionTimeSeconds'] : null,
                );
            }, (array) ($r['problemResults'] ?? []));

            return new ParticipantDTO(
                rank: (int) ($r['rank'] ?? 0),
                points: isset($r['points']) ? (int) $r['points'] : null,
                penalty: isset($r['penalty']) ? (int) $r['penalty'] : null,
                members: (array) ($r['members'] ?? []),
                problemResults: $problemResults,
                raw: (array) ($r['raw'] ?? []),
            );
        }, (array) ($data['rows'] ?? []));

        return new ContestStandingsDTO(
            contest: $contest,
            problems: $problems,
            rows: $rows,
            raw: (array) ($data['raw'] ?? []),
        );
    }
}
