<?php

namespace App\Services;

use Illuminate\Support\Str;
use Carbon\Carbon;

class ReferenceNumberService
{
    /**
     * Generate a unique reference number for transactions
     * Format: INST-YYYYMMDD-XXXXX
     * Where:
     * INST: Institution ID (padded to 4 digits)
     * YYYYMMDD: Current date
     * XXXXX: Random 5-digit number
     *
     * @param int $institutionId
     * @return string
     */
    public function generateTransactionReference(int $institutionId): string
    {
        $institutionPrefix = str_pad($institutionId, 4, '0', STR_PAD_LEFT);
        $date = Carbon::now()->format('Ymd');
        $random = str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        
        return "{$institutionPrefix}-{$date}-{$random}";
    }

    /**
     * Generate a unique reference number for share transactions
     * Format: SH-YYYYMMDD-XXXXX
     * Where:
     * SH: Share transaction prefix
     * YYYYMMDD: Current date
     * XXXXX: Random 5-digit number
     *
     * @return string
     */
    public function generateShareReference(): string
    {
        $date = Carbon::now()->format('Ymd');
        $random = str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        
        return "SH-{$date}-{$random}";
    }

    /**
     * Generate a unique reference number for approvals
     * Format: AP-YYYYMMDD-XXXXX
     * Where:
     * AP: Approval prefix
     * YYYYMMDD: Current date
     * XXXXX: Random 5-digit number
     *
     * @return string
     */
    public function generateApprovalReference(): string
    {
        $date = Carbon::now()->format('Ymd');
        $random = str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        
        return "AP-{$date}-{$random}";
    }

    /**
     * Generate a unique reference number for any custom type
     * Format: PREFIX-YYYYMMDD-XXXXX
     * Where:
     * PREFIX: Custom prefix
     * YYYYMMDD: Current date
     * XXXXX: Random 5-digit number
     *
     * @param string $prefix
     * @return string
     */
    public function generateCustomReference(string $prefix): string
    {
        $date = Carbon::now()->format('Ymd');
        $random = str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Validate if a reference number matches the expected format
     *
     * @param string $reference
     * @param string $prefix
     * @return bool
     */
    public function validateReference(string $reference, string $prefix): bool
    {
        $pattern = "/^{$prefix}-\d{8}-\d{5}$/";
        return preg_match($pattern, $reference) === 1;
    }
} 