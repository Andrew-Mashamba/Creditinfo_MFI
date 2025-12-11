<?php

namespace App\Services;

use App\Models\ClientsModel;
use Illuminate\Support\Facades\Log;

class MembershipVerificationService
{
    /**
     * Verify if a membership number exists and is active
     *
     * @param string $membershipNumber
     * @return array
     */
    public function verifyMembership(string $membershipNumber): array
    {
        Log::info('Starting membership verification', [
            'membership_number' => $membershipNumber,
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            $member = ClientsModel::where('client_number', $membershipNumber)
                ->where('status', 'ACTIVE')
                ->first();

            if (!$member) {
                Log::warning('Membership verification failed - Invalid or inactive member', [
                    'membership_number' => $membershipNumber,
                    'reason' => 'Member not found or not active'
                ]);

                return [
                    'exists' => false,
                    'message' => 'Invalid membership number or member is not active',
                    'member' => null
                ];
            }

            Log::info('Membership verification successful', [
                'membership_number' => $membershipNumber,
                'member_id' => $member->id,
                'member_type' => $member->membership_type,
                'member_name' => $member->membership_type === 'Individual' 
                    ? $member->first_name . ' ' . $member->last_name 
                    : $member->business_name
            ]);

            return [
                'exists' => true,
                'message' => 'Valid active member',
                'member' => [
                    'id' => $member->id,
                    'name' => $member->membership_type === 'Individual' 
                        ? $member->first_name . ' ' . $member->last_name 
                        : $member->business_name,
                    'membership_type' => $member->membership_type,
                    'client_number' => $member->client_number,
                    'account_number' => $member->account_number
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error verifying membership', [
                'error' => $e->getMessage(),
                'membership_number' => $membershipNumber,
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);

            return [
                'exists' => false,
                'message' => 'Error verifying membership number',
                'member' => null
            ];
        } finally {
            Log::info('Completed membership verification process', [
                'membership_number' => $membershipNumber,
                'timestamp' => now()->toDateTimeString()
            ]);
        }
    }
} 