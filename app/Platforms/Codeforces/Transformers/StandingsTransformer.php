<?php

namespace App\Platforms\Codeforces\Transformers;

use App\Core\DTOs\ContestDTO;
use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemResultDTO;
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

        $contestDto = $this->contestTransformer->fromApiContest($normalized['contest'] ?? []);
        $problems = $this->problemTransformer->fromApiProblems($normalized['problems'] ?? []);

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
}
