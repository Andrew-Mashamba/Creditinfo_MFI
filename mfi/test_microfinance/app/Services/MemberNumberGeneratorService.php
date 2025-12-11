<?php

namespace App\Services;

use App\Models\ClientsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberNumberGeneratorService
{
    /**
     * Generate a unique sequential member number
     * 
     * @return string
     * @throws \Exception
     */
    public function generate(): string
    {
        try {
            Log::info('Starting member number generation process');
            
            // Start a database transaction
            DB::beginTransaction();
            Log::info('Database transaction started');

            // Get the last used number
            $lastNumber = ClientsModel::max('client_number');
            
            // If no numbers exist yet, start with 1
            $nextNumber = $lastNumber ? (int)$lastNumber + 1 : 1;
            
            // Keep trying until we find a unique number
            do {
                // Format the number with leading zeros
                $memberNumber = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
                
                Log::info('Checking member number', [
                    'raw_number' => $nextNumber,
                    'formatted_number' => $memberNumber
                ]);
                
                // Check if this number exists
                $exists = ClientsModel::where('client_number', $memberNumber)->exists();
                
                if ($exists) {
                    Log::warning('Number already exists, incrementing', [
                        'member_number' => $memberNumber
                    ]);
                    $nextNumber++;
                }
            } while ($exists);
            
            Log::info('Found unique member number', [
                'member_number' => $memberNumber,
                'generated_at' => now()->toDateTimeString()
            ]);
            
            DB::commit();
            Log::info('Database transaction committed successfully');
            
            return $memberNumber;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating member number: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate if a member number is in the correct format
     * 
     * @param string $memberNumber
     * @return bool
     */
    public function validateFormat(string $memberNumber): bool
    {
        Log::info('Validating member number format', ['member_number' => $memberNumber]);

        // Check if the number is 5 digits
        if (!preg_match('/^\d{5}$/', $memberNumber)) {
            Log::warning('Invalid member number format - not 5 digits', [
                'member_number' => $memberNumber,
                'length' => strlen($memberNumber)
            ]);
            return false;
        }

        // Check if the number is between 00001 and 99999
        $number = (int)$memberNumber;
        $isValid = $number >= 1 && $number <= 99999;

        Log::info('Member number format validation result', [
            'member_number' => $memberNumber,
            'is_valid' => $isValid,
            'numeric_value' => $number
        ]);

        return $isValid;
    }

    /**
     * Check if a member number is available (not already in use)
     * 
     * @param string $memberNumber
     * @return bool
     */
    public function isAvailable(string $memberNumber): bool
    {
        Log::info('Checking member number availability', ['member_number' => $memberNumber]);

        $exists = ClientsModel::where('client_number', $memberNumber)->exists();
        
        Log::info('Member number availability check result', [
            'member_number' => $memberNumber,
            'is_available' => !$exists,
            'timestamp' => now()->toDateTimeString()
        ]);

        return !$exists;
    }
} 