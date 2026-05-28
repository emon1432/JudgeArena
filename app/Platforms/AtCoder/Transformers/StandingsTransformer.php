<?php

namespace App\Platforms\AtCoder\Transformers;

use App\Core\DTOs\ContestStandingsDTO;
use App\Core\DTOs\ParticipantDTO;
use App\Core\DTOs\ProblemResultDTO;
use App\Platforms\AtCoder\DTOs\AtCoderProblemResultDTO;
use App\Platforms\AtCoder\DTOs\AtCoderRanklistRowDTO;
use App\Platforms\AtCoder\DTOs\AtCoderStandingsDTO;

class StandingsTransformer
{
    public function __construct(
        protected ContestTransformer $contestTransformer = new ContestTransformer(),
        protected ProblemTransformer $problemTransformer = new ProblemTransformer(),
    ) {}

    /**
     * Transform AtCoder standings payload into normalized DTOs
     *
     * @return ContestStandingsDTO
     */
    public function fromApiStandings(AtCoderStandingsDTO $standings): ContestStandingsDTO
    {
        $contestDto = $this->contestTransformer->fromApiContest($standings->contest);
        $problems = $this->problemTransformer->fromApiProblems($standings->problems);

        $rows = array_map(
            fn (AtCoderRanklistRowDTO $row): ParticipantDTO => new ParticipantDTO(
                rank: $row->rank ?? 0,
                points: $row->points,
                penalty: $row->penalty,
                members: $row->party?->members ?? [],
                problemResults: array_map(
                    fn (AtCoderProblemResultDTO $problemResult): ProblemResultDTO => new ProblemResultDTO(
                        points: $problemResult->points,
                        penalty: $problemResult->penalty,
                        rejectedAttemptCount: $problemResult->rejectedAttemptCount,
                        type: $problemResult->type,
                        bestSubmissionTimeSeconds: $problemResult->bestSubmissionTimeSeconds,
                    ),
                    $row->problemResults
                ),
                raw: $row->raw,
            ),
            $standings->rows
        );

        return new ContestStandingsDTO(
            contest: $contestDto,
            problems: $problems,
            rows: $rows,
            raw: $standings->raw,
        );
    }
}

