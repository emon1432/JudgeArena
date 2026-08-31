<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Enums\SubmissionVerdict;
use App\Platforms\Codeforces\Mappers\CodeforcesSubmissionMapper;
use App\Platforms\Codeforces\Transformers\SubmissionTransformer;
use Tests\TestCase;

class SubmissionTransformerTest extends TestCase
{
    private function sample(string $fileName): array
    {
        $path = base_path('docs/platforms/codeforces.com/sample-response/' . $fileName);

        return json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_submission_transformer_maps_submission_payload(): void
    {
        $submissions = $this->sample('codeforces-user-status.json');
        $submission = $submissions[0];
        $submissionDto = CodeforcesSubmissionMapper::fromNormalized($submission);
        $dto = (new SubmissionTransformer())->fromApiSubmission($submissionDto);

        $this->assertSame('codeforces', $dto->platform);
        $this->assertSame((string) $submission['id'], $dto->platformSubmissionId);
        $this->assertSame(SubmissionVerdict::AC, $dto->verdict);
        $this->assertSame('C++23 (GCC 14-64, msys2)', $dto->language);
        $this->assertSame($submission, $submissionDto->raw);
    }
}
