<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\SubmissionVerdict;
use Tests\TestCase;

class SubmissionVerdictTest extends TestCase
{
    public function test_accepted_verdict_identifies_as_accepted(): void
    {
        $this->assertTrue(SubmissionVerdict::AC->isAccepted());
        $this->assertFalse(SubmissionVerdict::WA->isAccepted());
        $this->assertFalse(SubmissionVerdict::TLE->isAccepted());
        $this->assertFalse(SubmissionVerdict::MLE->isAccepted());
    }

    public function test_verdict_labels_and_badge_classes_are_defined(): void
    {
        foreach (SubmissionVerdict::cases() as $case) {
            $this->assertNotEmpty($case->label());
            $this->assertNotEmpty($case->badgeClass());
            $this->assertNotEmpty($case->icon());
        }
    }
}
