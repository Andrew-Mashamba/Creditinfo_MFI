<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WithdrawalOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_number',
        'otp_code',
        'expires_at',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if OTP is valid (not expired and not verified yet)
     */
    public function isValid(): bool
    {
        return !$this->is_verified && !$this->isExpired();
    }

    /**
     * Mark OTP as verified
     */
    public function markAsVerified(): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => Carbon::now(),
        ]);
    }

    /**
     * Get valid OTP for a member
     */
    public static function getValidOtp(string $membershipNumber, string $otpCode)
    {
        return static::where('membership_number', $membershipNumber)
            ->where('otp_code', $otpCode)
            ->where('is_verified', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    /**
     * Clean up expired OTPs
     */
    public static function cleanupExpired(): int
    {
        return static::where('expires_at', '<', Carbon::now()->subHours(1))
            ->delete();
    }
}
