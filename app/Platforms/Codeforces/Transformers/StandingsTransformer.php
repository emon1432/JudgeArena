<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemResultDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesContestDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;
use App\Platforms\Codeforces\Support\ResponseNormalizer;

class StandingsTransformer
{
    public function __construct(
        protected ContestTransformer $contestTransformer = new ContestTransformer(),
        protected ProblemTransformer $problemTransformer = new ProblemTransformer(),
    ) {}

    /**
     * Transform Codeforces API standings payload into normalized DTOs
     */
    public function fromApiStandings(array $standings): ContestStandingsDTO
    {
        $normalized = ResponseNormalizer::standings($standings);

        $contest = ResponseNormalizer::contest($normalized['contest'] ?? []);
        $contestDto = $this->contestTransformer->fromApiContest(new CodeforcesContestDTO(
            id: isset($contest['id']) ? (string) $contest['id'] : null,
            name: $contest['name'] ?? null,
            type: $contest['type'] ?? null,
            phase: $contest['phase'] ?? null,
            frozen: array_key_exists('frozen', $contest) ? (bool) $contest['frozen'] : null,
            durationSeconds: isset($contest['durationSeconds']) ? (int) $contest['durationSeconds'] : null,
            freezeDurationSeconds: isset($contest['freezeDurationSeconds']) ? (int) $contest['freezeDurationSeconds'] : null,
            startTimeSeconds: isset($contest['startTimeSeconds']) ? (int) $contest['startTimeSeconds'] : null,
            relativeTimeSeconds: isset($contest['relativeTimeSeconds']) ? (int) $contest['relativeTimeSeconds'] : null,
            preparedBy: $contest['preparedBy'] ?? null,
            websiteUrl: $contest['websiteUrl'] ?? null,
            description: $contest['description'] ?? null,
            difficulty: isset($contest['difficulty']) ? (string) $contest['difficulty'] : null,
            kind: $contest['kind'] ?? null,
            icpcRegion: $contest['icpcRegion'] ?? null,
            country: $contest['country'] ?? null,
            city: $contest['city'] ?? null,
            season: $contest['season'] ?? null,
            raw: $normalized['contest'] ?? [],
        ));
        $problems = $this->problemTransformer->fromApiProblems($this->toProblemDtos($normalized['problems'] ?? []));

        $rows = [];

        foreach ($normalized['rows'] ?? [] as $row) {
            $row = ResponseNormalizer::ranklistRow($row);

            $problemResults = [];
            foreach ($row['problemResults'] ?? [] as $pr) {
                $problemResults[] = new ProblemResultDTO(
                    points: isset($pr['points']) ? (float) $pr['points'] : null,
                    penalty: isset($pr['penalty']) ? (int) $pr['penalty'] : null,
                    rejectedAttemptCount: isset($pr['rejectedAttemptCount']) ? (int) $pr['rejectedAttemptCount'] : null,
                    type: $pr['type'] ?? null,
                    bestSubmissionTimeSeconds: isset($pr['bestSubmissionTimeSeconds']) ? (int) $pr['bestSubmissionTimeSeconds'] : null,
                );
            }

            $members = $row['party']['members'] ?? [];

            $rows[] = new ParticipantDTO(
                rank: (int) ($row['rank'] ?? 0),
                points: isset($row['points']) ? (int) $row['points'] : null,
                penalty: isset($row['penalty']) ? (int) $row['penalty'] : null,
                members: $members,
                problemResults: $problemResults,
                raw: $row,
            );
        }

        return new ContestStandingsDTO(
            contest: $contestDto,
            problems: $problems,
            rows: $rows,
            raw: $normalized,
        );
    }

    /** @return array<int, CodeforcesProblemDTO> */
    private function toProblemDtos(array $problems): array
    {
        return array_map(function (array $problem): CodeforcesProblemDTO {
            return new CodeforcesProblemDTO(
                contestId: isset($problem['contestId']) ? (string) $problem['contestId'] : null,
                problemsetName: $problem['problemsetName'] ?? null,
                index: isset($problem['index']) ? (string) $problem['index'] : null,
                name: $problem['name'] ?? null,
                type: $problem['type'] ?? null,
                points: isset($problem['points']) ? (int) $problem['points'] : null,
                rating: isset($problem['rating']) ? (int) $problem['rating'] : null,
                tags: is_array($problem['tags'] ?? null) ? $problem['tags'] : [],
                raw: $problem,
            );
        }, ResponseNormalizer::problems($problems));
    }
}
