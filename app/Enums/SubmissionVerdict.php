<?php

declare(strict_types=1);

namespace App\Enums;

enum SubmissionVerdict: string
{
    case AC = 'AC';
    case WA = 'WA';
    case TLE = 'TLE';
    case MLE = 'MLE';
    case RE = 'RE';
    case CE = 'CE';
    case PE = 'PE';
    case OLE = 'OLE';
    case QLE = 'QLE';
    case CHALLENGED = 'CHALLENGED';
    case PARTIAL = 'PARTIAL';
    case SKIPPED = 'SKIPPED';
    case PENDING = 'PENDING';
    case REJECTED = 'REJECTED';
    case OTHER = 'OTHER';

    public function isAccepted(): bool
    {
        return $this === self::AC;
    }

    public function label(): string
    {
        return match ($this) {
            self::AC => 'Accepted',
            self::WA => 'Wrong Answer',
            self::TLE => 'Time Limit Exceeded',
            self::MLE => 'Memory Limit Exceeded',
            self::RE => 'Runtime Error',
            self::CE => 'Compilation Error',
            self::PE => 'Presentation Error',
            self::OLE => 'Output Limit Exceeded',
            self::QLE => 'Query Limit Exceeded',
            self::CHALLENGED => 'Challenged / Hacked',
            self::PARTIAL => 'Partial Score',
            self::SKIPPED => 'Skipped',
            self::PENDING => 'Judging / In Queue',
            self::REJECTED => 'Rejected / Internal Error',
            self::OTHER => 'Other',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::AC => 'badge-success text-success bg-success-subtle border-success-subtle',
            self::WA => 'badge-danger text-danger bg-danger-subtle border-danger-subtle',
            self::TLE, self::OLE, self::QLE => 'badge-warning text-warning bg-warning-subtle border-warning-subtle',
            self::MLE, self::RE => 'badge-danger text-danger bg-danger-subtle border-danger-subtle',
            self::CE => 'badge-secondary text-secondary bg-secondary-subtle border-secondary-subtle',
            self::PE => 'badge-info text-info bg-info-subtle border-info-subtle',
            self::CHALLENGED => 'badge-purple text-purple bg-purple-subtle border-purple-subtle',
            self::PARTIAL => 'badge-primary text-primary bg-primary-subtle border-primary-subtle',
            self::SKIPPED, self::OTHER => 'badge-secondary text-secondary bg-secondary-subtle border-secondary-subtle',
            self::PENDING => 'badge-info text-info bg-info-subtle border-info-subtle',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AC => 'fa-solid fa-circle-check',
            self::WA => 'fa-solid fa-circle-xmark',
            self::TLE, self::OLE, self::QLE => 'fa-solid fa-clock',
            self::MLE => 'fa-solid fa-microchip',
            self::RE => 'fa-solid fa-triangle-exclamation',
            self::CE => 'fa-solid fa-code',
            self::PE => 'fa-solid fa-align-left',
            self::CHALLENGED => 'fa-solid fa-bolt',
            self::PARTIAL => 'fa-solid fa-star-half-stroke',
            self::SKIPPED => 'fa-solid fa-forward',
            self::PENDING => 'fa-solid fa-spinner fa-spin',
            self::REJECTED, self::OTHER => 'fa-solid fa-circle-question',
        };
    }

    /**
     * Map platform-specific raw strings into canonical SubmissionVerdict.
     */
    public static function fromCodeforces(?string $verdict): ?self
    {
        if ($verdict === null || trim($verdict) === '') {
            return null;
        }

        return match (strtoupper(trim($verdict))) {
            'OK' => self::AC,
            'WRONG_ANSWER' => self::WA,
            'TIME_LIMIT_EXCEEDED', 'IDLENESS_LIMIT_EXCEEDED' => self::TLE,
            'MEMORY_LIMIT_EXCEEDED' => self::MLE,
            'RUNTIME_ERROR', 'CRASHED' => self::RE,
            'COMPILATION_ERROR' => self::CE,
            'PRESENTATION_ERROR' => self::PE,
            'CHALLENGED' => self::CHALLENGED,
            'PARTIAL' => self::PARTIAL,
            'SKIPPED' => self::SKIPPED,
            'TESTING', 'SUBMITTED' => self::PENDING,
            'FAILED', 'SECURITY_VIOLATED', 'INPUT_PREPARATION_CRASHED', 'REJECTED' => self::REJECTED,
            default => self::OTHER,
        };
    }

    /**
     * Map AtCoder status strings into canonical SubmissionVerdict.
     */
    public static function fromAtCoder(?string $verdict): ?self
    {
        if ($verdict === null || trim($verdict) === '') {
            return null;
        }

        return match (strtoupper(trim($verdict))) {
            'AC' => self::AC,
            'WA' => self::WA,
            'TLE' => self::TLE,
            'MLE' => self::MLE,
            'RE' => self::RE,
            'CE' => self::CE,
            'QLE' => self::QLE,
            'OLE' => self::OLE,
            'IE' => self::REJECTED,
            'WJ', 'WR', 'JUDGING' => self::PENDING,
            default => self::OTHER,
        };
    }
}
