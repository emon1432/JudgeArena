<?php

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\DTOs\CodeforcesContestDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesProblemDTO;
use App\Platforms\Codeforces\DTOs\CodeforcesUserDTO;
use App\Platforms\Codeforces\Transformers\ContestTransformer;
use App\Platforms\Codeforces\Transformers\ProblemTransformer;
use App\Platforms\Codeforces\Transformers\SubmissionTransformer;
use App\Platforms\Codeforces\Transformers\UserTransformer;
use Tests\TestCase;

class CodeforcesTransformersTest extends TestCase
{
    private function sample(string $fileName): array
    {
        $path = base_path('docs/platforms/codeforces.com/sample-response/' . $fileName);

        return json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_contest_transformer_maps_standings_contest(): void
    {
        $standings = $this->sample('codeforces-contest-standings.json');
        $contestDto = CodeforcesContestDTO::fromApiResponse($standings['contest']);
        $dto = (new ContestTransformer())->fromApiContest($contestDto);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame('2225', $dto->platformContestId);
        $this->assertSame('Educational Codeforces Round 189 (Rated for Div. 2)', $dto->title);
        $this->assertSame('FINISHED', $dto->phase);
        $this->assertSame(7200, $dto->durationSeconds);
        $this->assertSame(1776782100, $dto->startedAt?->getTimestamp());
        $this->assertSame($standings['contest'], $contestDto->raw);
    }

    public function test_problem_transformer_maps_problem_payload(): void
    {
        $standings = $this->sample('codeforces-contest-standings.json');
        $problemDto = CodeforcesProblemDTO::fromApiResponse($standings['problems'][0]);
        $dto = (new ProblemTransformer())->fromApiProblem($problemDto);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame('2225A', $dto->platformProblemId);
        $this->assertSame('A Number Between Two Others', $dto->title);
        $this->assertSame('2225', $dto->contestPlatformId);
        $this->assertSame(['greedy', 'math'], $dto->tags);
        $this->assertSame($standings['problems'][0], $problemDto->raw);
    }

    public function test_user_transformer_maps_profile_payload(): void
    {
        $profile = $this->sample('codeforces-profile.json');
        $userDto = CodeforcesUserDTO::fromApiResponse($profile);
        $dto = (new UserTransformer())->fromApiUser($userDto);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame('tourist', $dto->platformHandle);
        $this->assertSame('Gennady', $dto->firstName);
        $this->assertSame('Korotkevich', $dto->lastName);
        $this->assertSame(3428, $dto->rating);
        $this->assertSame('Belarus', $dto->country);
        $this->assertSame($profile, $userDto->raw);
    }

    public function test_submission_transformer_maps_submission_payload(): void
    {
        $submissions = $this->sample('codeforces-user-status.json');
        $submissionDto = \App\Platforms\Codeforces\DTOs\CodeforcesSubmissionDTO::fromApiResponse($submissions[0]);
        $dto = (new SubmissionTransformer())->fromApiSubmission($submissionDto);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame((string) $submissions[0]['id'], $dto->platformSubmissionId);
        $this->assertSame('2229H', $dto->problemPlatformId);
        $this->assertSame('tourist', $dto->authorHandle);
        $this->assertSame('OK', $dto->verdict);
        $this->assertSame('C++23 (GCC 14-64, msys2)', $dto->language);
        $this->assertSame($submissions[0], $submissionDto->raw);
    }
}
