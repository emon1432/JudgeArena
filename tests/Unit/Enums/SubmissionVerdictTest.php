<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\SubmissionVerdict;
use PHPUnit\Framework\TestCase;

class SubmissionVerdictTest extends TestCase
{
    public function test_codeforces_verdict_mapping(): void
    {
        $this->assertSame(SubmissionVerdict::AC, SubmissionVerdict::fromCodeforces('OK'));
        $this->assertSame(SubmissionVerdict::WA, SubmissionVerdict::fromCodeforces('WRONG_ANSWER'));
        $this->assertSame(SubmissionVerdict::TLE, SubmissionVerdict::fromCodeforces('TIME_LIMIT_EXCEEDED'));
        $this->assertSame(SubmissionVerdict::TLE, SubmissionVerdict::fromCodeforces('IDLENESS_LIMIT_EXCEEDED'));
        $this->assertSame(SubmissionVerdict::MLE, SubmissionVerdict::fromCodeforces('MEMORY_LIMIT_EXCEEDED'));
        $this->assertSame(SubmissionVerdict::RE, SubmissionVerdict::fromCodeforces('RUNTIME_ERROR'));
        $this->assertSame(SubmissionVerdict::RE, SubmissionVerdict::fromCodeforces('CRASHED'));
        $this->assertSame(SubmissionVerdict::CE, SubmissionVerdict::fromCodeforces('COMPILATION_ERROR'));
        $this->assertSame(SubmissionVerdict::PE, SubmissionVerdict::fromCodeforces('PRESENTATION_ERROR'));
        $this->assertSame(SubmissionVerdict::CHALLENGED, SubmissionVerdict::fromCodeforces('CHALLENGED'));
        $this->assertSame(SubmissionVerdict::PARTIAL, SubmissionVerdict::fromCodeforces('PARTIAL'));
        $this->assertSame(SubmissionVerdict::SKIPPED, SubmissionVerdict::fromCodeforces('SKIPPED'));
        $this->assertSame(SubmissionVerdict::PENDING, SubmissionVerdict::fromCodeforces('TESTING'));
        $this->assertSame(SubmissionVerdict::PENDING, SubmissionVerdict::fromCodeforces('SUBMITTED'));
        $this->assertSame(SubmissionVerdict::REJECTED, SubmissionVerdict::fromCodeforces('FAILED'));
        $this->assertSame(SubmissionVerdict::REJECTED, SubmissionVerdict::fromCodeforces('SECURITY_VIOLATED'));
        $this->assertSame(SubmissionVerdict::REJECTED, SubmissionVerdict::fromCodeforces('INPUT_PREPARATION_CRASHED'));
        $this->assertSame(SubmissionVerdict::REJECTED, SubmissionVerdict::fromCodeforces('REJECTED'));
        $this->assertSame(SubmissionVerdict::OTHER, SubmissionVerdict::fromCodeforces('UNKNOWN_VERDICT_XYZ'));
        $this->assertNull(SubmissionVerdict::fromCodeforces(null));
        $this->assertNull(SubmissionVerdict::fromCodeforces(''));
    }

    public function test_atcoder_verdict_mapping(): void
    {
        $this->assertSame(SubmissionVerdict::AC, SubmissionVerdict::fromAtCoder('AC'));
        $this->assertSame(SubmissionVerdict::WA, SubmissionVerdict::fromAtCoder('WA'));
        $this->assertSame(SubmissionVerdict::TLE, SubmissionVerdict::fromAtCoder('TLE'));
        $this->assertSame(SubmissionVerdict::MLE, SubmissionVerdict::fromAtCoder('MLE'));
        $this->assertSame(SubmissionVerdict::RE, SubmissionVerdict::fromAtCoder('RE'));
        $this->assertSame(SubmissionVerdict::CE, SubmissionVerdict::fromAtCoder('CE'));
        $this->assertSame(SubmissionVerdict::QLE, SubmissionVerdict::fromAtCoder('QLE'));
        $this->assertSame(SubmissionVerdict::OLE, SubmissionVerdict::fromAtCoder('OLE'));
        $this->assertSame(SubmissionVerdict::REJECTED, SubmissionVerdict::fromAtCoder('IE'));
        $this->assertSame(SubmissionVerdict::PENDING, SubmissionVerdict::fromAtCoder('WJ'));
        $this->assertSame(SubmissionVerdict::PENDING, SubmissionVerdict::fromAtCoder('WR'));
        $this->assertSame(SubmissionVerdict::PENDING, SubmissionVerdict::fromAtCoder('Judging'));
        $this->assertSame(SubmissionVerdict::OTHER, SubmissionVerdict::fromAtCoder('UNKNOWN_ATCODER'));
        $this->assertNull(SubmissionVerdict::fromAtCoder(null));
        $this->assertNull(SubmissionVerdict::fromAtCoder(''));
    }

    public function test_helper_methods(): void
    {
        $this->assertTrue(SubmissionVerdict::AC->isAccepted());
        $this->assertFalse(SubmissionVerdict::WA->isAccepted());
        $this->assertFalse(SubmissionVerdict::TLE->isAccepted());

        $this->assertSame('Accepted', SubmissionVerdict::AC->label());
        $this->assertSame('Wrong Answer', SubmissionVerdict::WA->label());

        $this->assertStringContainsString('success', SubmissionVerdict::AC->badgeClass());
        $this->assertStringContainsString('danger', SubmissionVerdict::WA->badgeClass());

        $this->assertSame('fa-solid fa-circle-check', SubmissionVerdict::AC->icon());
        $this->assertSame('fa-solid fa-circle-xmark', SubmissionVerdict::WA->icon());
    }
}
