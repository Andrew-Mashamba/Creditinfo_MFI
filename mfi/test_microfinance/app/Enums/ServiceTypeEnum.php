<?php

namespace App\Enums;

class ServiceTypeEnum
{
    public const Share = 'SHA';
    public const Deposit = 'DEP';
    public const Loan = 'LOA';
    public const Savings = 'SAV';

    public static function getCode(string $service): ?string
    {
        return match($service) {
            'Share' => self::Share,
            'Deposit' => self::Deposit,
            'Loan' => self::Loan,
            'Savings' => self::Savings,
            default => null,
        };
    }
}
