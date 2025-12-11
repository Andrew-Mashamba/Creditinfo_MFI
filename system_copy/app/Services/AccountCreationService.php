<?php

namespace App\Services;

use App\Models\AccountsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


// The new account number formats are:
//     External: BBMMMMMSSSSX (where B=branch, M=member, S=sub-category, X=check digit)
//     Internal: BB00000SSSSX (where B=branch, S=sub-category, X=check digit)

class AccountCreationService
{
    private const status_ACTIVE = 'ACTIVE';
    private const ACCOUNT_USE_INTERNAL = 'internal';
    private const ACCOUNT_USE_EXTERNAL = 'external';
    private const REQUIRED_FIELDS = [
        'account_use',
        'account_name',
        'product_number'
    ];

    /**
     * Create a new account with proper hierarchy and validation
     *
     * @param array $data Account creation data
     * @param string|null $parentAccountNumber Parent account number if creating a sub-account
     * @return AccountsModel
     * @throws \Exception
     */
    public function createAccount(array $data, ?string $parentAccountNumber = null): AccountsModel
    {
        try {
            Log::info('Starting account creation process', [
                'account_use' => $data['account_use'],
                'branch_number' => $data['branch_number'],
                'account_name' => $data['account_name'],
                'parent_account' => $parentAccountNumber,
                'user_id' => Auth::id(),
                'member_number' => $data['member_number'] ?? 'N/A'
            ]);

            DB::beginTransaction();

            // Get branch from logged in user
            if (isset($data['branch_number'])) {
                $data['branch_number'] = $data['branch_number'];
            } else {
                $data['branch_number'] = Auth::user()->branch;
            }
            Log::info('Branch number retrieved from user', [
                'branch_number' => $data['branch_number'],
                'user_id' => Auth::id()
            ]);

            // Map member_number to client_number for database storage
            if (isset($data['member_number'])) {
                $data['client_number'] = $data['member_number'];
                unset($data['member_number']);
                Log::info('Mapped member_number to client_number', [
                    'client_number' => $data['client_number']
                ]);
            }

            // Validate required fields
            $this->validateAccountData($data);
            Log::info('Account data validation passed');

            // Validate type for top-level accounts
            if (!$parentAccountNumber && empty($data['type'])) {
                Log::error('Type validation failed for top-level account');
                throw new \InvalidArgumentException('Type is required for top-level accounts');
            }

            $parentAccount = null;
            if ($parentAccountNumber) {
                Log::info('Processing sub-account creation', ['parent_account' => $parentAccountNumber]);
                $parentAccount = $this->getAndValidateParentAccount($parentAccountNumber);
                $data = $this->prepareSubAccountData($data, $parentAccount);
                Log::info('Sub-account data prepared', [
                    'parent_account' => $parentAccountNumber,
                    'account_level' => $data['account_level']
                ]);

                // Generate member_account_code for Level 4 accounts
                if ($data['account_level'] == 4 && !empty($parentAccount->sub_category_code)) {
                    $data['member_account_code'] = $this->generateMemberAccountCode($parentAccount->sub_category_code);
                    Log::info('Member account code generated', [
                        'parent_sub_category' => $parentAccount->sub_category_code,
                        'member_account_code' => $data['member_account_code']
                    ]);
                }
            }

            // Set default values for optional fields
            $data = $this->setDefaultValues($data);
            Log::info('Default values set for account');

            // Ensure client_number is properly formatted for external accounts
            if ($data['account_use'] === self::ACCOUNT_USE_EXTERNAL && !empty($data['client_number'])) {
                $data['client_number'] = $this->padNumber($data['client_number'], 5);
                Log::info('Client number formatted', [
                    'original' => $data['client_number'],
                    'formatted' => $data['client_number']
                ]);
            }

            $data['account_number'] = $this->generateAccountNumber($data);
            Log::info('Account number generated', ['account_number' => $data['account_number']]);

            $account = AccountsModel::create($data);
            Log::info('Account created in database', [
                'account_id' => $account->id,
                'account_number' => $account->account_number,
                'client_number' => $account->client_number,
                'member_account_code' => $account->member_account_code ?? 'N/A'
            ]);

            DB::commit();
            Log::info('Account creation completed successfully', [
                'account_number' => $account->account_number,
                'account_name' => $account->account_name,
                'account_type' => $account->type,
                'client_number' => $account->client_number,
                'member_account_code' => $account->member_account_code ?? 'N/A'
            ]);

            return $account;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
                'parent_account' => $parentAccountNumber,
                'user_id' => Auth::id()
            ]);
            throw $e;
        }
    }

    /**
     * Validate account creation data
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    protected function validateAccountData(array $data): void
    {
        Log::info('Starting account data validation', [
            'account_use' => $data['account_use'],
            'account_name' => $data['account_name']
        ]);

        // Validate required fields
        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty($data[$field])) {
                Log::error('Required field missing', ['field' => $field]);
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Validate account use
        if (!in_array($data['account_use'], [self::ACCOUNT_USE_INTERNAL, self::ACCOUNT_USE_EXTERNAL])) {
            Log::error('Invalid account use value', ['account_use' => $data['account_use']]);
            throw new \InvalidArgumentException('Invalid account_use value. Must be either "internal" or "external"');
        }

        // Validate branch number exists
        if (empty($data['branch_number'])) {
            Log::error('Branch number missing', ['user_id' => Auth::id()]);
            throw new \InvalidArgumentException('Branch number is required. Please ensure you are logged in with a valid branch.');
        }

        $this->validateFieldLengths($data);
        Log::info('Account data validation completed successfully');
    }

    /**
     * Validate field lengths according to database schema
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    protected function validateFieldLengths(array $data): void
    {
        $fieldLengths = [
            'branch_number' => 120,
            'client_number' => 120,
            'account_use' => 120,
            'product_number' => 120,
            'sub_product_number' => 120,
            'major_category_code' => 20,
            'category_code' => 20,
            'sub_category_code' => 20,
            'account_name' => 200,
            'account_number' => 50,
            'status' => 100,
            'parent_account_number' => 150,
            'phone_number' => 30
        ];

        foreach ($fieldLengths as $field => $maxLength) {
            if (isset($data[$field]) && strlen($data[$field]) > $maxLength) {
                throw new \InvalidArgumentException("Field {$field} exceeds maximum length of {$maxLength} characters");
            }
        }
    }

    /**
     * Get and validate parent account
     *
     * @param string $parentAccountNumber
     * @return AccountsModel
     * @throws \Exception
     */
    protected function getAndValidateParentAccount(string $parentAccountNumber): AccountsModel
    {
        Log::info('Retrieving parent account', ['parent_account_number' => $parentAccountNumber]);

        $parentAccount = AccountsModel::where('account_number', $parentAccountNumber)->first();

        if (!$parentAccount) {
            Log::error('Parent account not found', ['parent_account_number' => $parentAccountNumber]);
            throw new \Exception("Parent account not found: {$parentAccountNumber}");
        }

        // if ($parentAccount->status !== self::status_ACTIVE) {
        //     Log::error('Parent account not active', [
        //         'parent_account_number' => $parentAccountNumber,
        //         'status' => $parentAccount->status
        //     ]);
        //     throw new \Exception("Parent account is not active: {$parentAccountNumber}");
        // }

        Log::info('Parent account validated successfully', [
            'parent_account_number' => $parentAccountNumber,
            'status' => $parentAccount->status
        ]);

        return $parentAccount;
    }

    /**
     * Prepare data for sub-account creation
     *
     * @param array $data
     * @param AccountsModel $parentAccount
     * @return array
     */
    protected function prepareSubAccountData(array $data, AccountsModel $parentAccount): array
    {
        $data['major_category_code'] = $parentAccount->major_category_code;
        $data['category_code'] = $parentAccount->category_code;
        $data['sub_category_code'] = $this->generateSubCategoryCode($parentAccount);
        $data['account_level'] = (int)$parentAccount->account_level + 1;
        $data['parent_account_number'] = $parentAccount->account_number;
        $data['type'] = $parentAccount->type;

        return $data;
    }

    /**
     * Generate unique sub-category code
     *
     * @param AccountsModel $parentAccount
     * @return string
     */
    protected function generateSubCategoryCode(AccountsModel $parentAccount): string
    {
        Log::info('Generating sub-category code', [
            'parent_category_code' => $parentAccount->category_code
        ]);

        $baseCode = $parentAccount->category_code;
        $incrementedCode = $this->padNumber((int)$baseCode + 1, strlen($baseCode));
        
        // Check if the incremented code exists
        $exists = AccountsModel::where('sub_category_code', $incrementedCode)->exists();
        
        if (!$exists) {
            Log::info('Generated new sub-category code', [
                'parent_code' => $baseCode,
                'new_code' => $incrementedCode
            ]);
            return $incrementedCode;
        }

        // If exists, keep incrementing until we find an unused code
        do {
            $incrementedCode = $this->padNumber((int)$incrementedCode + 1, strlen($baseCode));
            $exists = AccountsModel::where('sub_category_code', $incrementedCode)->exists();
        } while ($exists);

        Log::info('Generated new sub-category code after incrementing', [
            'parent_code' => $baseCode,
            'new_code' => $incrementedCode
        ]);

        return $incrementedCode;
    }

    /**
     * Generate account number based on account type and hierarchy
     *
     * @param array $data
     * @return string
     */
    protected function generateAccountNumber(array $data): string
    {
        Log::info('Generating account number', [
            'account_use' => $data['account_use'],
            'branch_number' => $data['branch_number']
        ]);

        $accountNumber = $data['account_use'] === self::ACCOUNT_USE_EXTERNAL
            ? $this->generateExternalAccountNumber($data)
            : $this->generateInternalAccountNumber($data);

        Log::info('Account number generated', ['account_number' => $accountNumber]);
        return $accountNumber;
    }

    /**
     * Generate external account number (member accounts)
     *
     * @param array $data
     * @return string
     */
    protected function generateExternalAccountNumber(array $data): string
    {
        Log::info('Generating external account number', [
            'branch' => $data['branch_number'],
            'client_number' => $data['client_number'] ?? 'N/A',
            'sub_category' => $data['sub_category_code'] ?? 'N/A'
        ]);

        if (empty($data['client_number'])) {
            Log::error('Client number is required for external accounts');
            throw new \InvalidArgumentException('Client number is required for external accounts');
        }

        $branch = $this->padNumber($data['branch_number'], 2);
        $memberNumber = $this->padNumber($data['client_number'], 5);
        $subCategory = $this->padNumber($data['sub_category_code'], 4);
        
        $baseNumber = $branch . $memberNumber . $subCategory;
        $checkDigit = $this->calculateCheckDigit($baseNumber);
        
        $accountNumber = $baseNumber . $checkDigit;
        
        Log::info('External account number generated', [
            'base_number' => $baseNumber,
            'check_digit' => $checkDigit,
            'final_number' => $accountNumber,
            'client_number' => $memberNumber
        ]);
        
        return $accountNumber;
    }

    /**
     * Generate internal account number (GL accounts)
     *
     * @param array $data
     * @return string
     */
    protected function generateInternalAccountNumber(array $data): string
    {
        Log::info('Generating internal account number', [
            'branch' => $data['branch_number'],
            'sub_category' => $data['sub_category_code'] ?? 'N/A'
        ]);

        $branch = $this->padNumber($data['branch_number'], 2);
        $zeros = '00000';
        $subCategory = $this->padNumber($data['sub_category_code'], 4);
        
        $baseNumber = $branch . $zeros . $subCategory;
        $checkDigit = $this->calculateCheckDigit($baseNumber);
        
        $accountNumber = $baseNumber . $checkDigit;
        
        Log::info('Internal account number generated', [
            'base_number' => $baseNumber,
            'check_digit' => $checkDigit,
            'final_number' => $accountNumber
        ]);
        
        return $accountNumber;
    }

    /**
     * Calculate check digit using Luhn algorithm
     *
     * @param string $number
     * @return string
     */
    private function calculateCheckDigit(string $number): string
    {
        $sum = 0;
        $length = strlen($number);
        
        // Double every second digit from right to left
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int)$number[$i];
            
            if (($length - $i) % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }
        
        // Calculate check digit
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return (string)$checkDigit;
    }

    /**
     * Log account creation
     *
     * @param AccountsModel $account
     */
    protected function logAccountCreation(AccountsModel $account): void
    {
        Log::info('Account created successfully', [
            'account_number' => $account->account_number,
            'account_name' => $account->account_name,
            'account_level' => $account->account_level,
            'parent_account' => $account->parent_account_number,
            'created_at' => now()
        ]);
    }

    /**
     * Pad a number with leading zeros
     *
     * @param int|string $number
     * @param int $length
     * @return string
     */
    private function padNumber($number, int $length): string
    {
        return str_pad((string)$number, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Set default values for optional fields
     *
     * @param array $data
     * @return array
     */
    private function setDefaultValues(array $data): array
    {
        Log::info('Setting default values for account data', [
            'account_use' => $data['account_use'] ?? 'N/A',
            'type' => $data['type'] ?? 'N/A',
            'client_number' => $data['client_number'] ?? 'N/A'
        ]);

        $defaults = [
            'status' => self::status_ACTIVE,
            'account_level' => '1',
            'sub_product_number' => null,
            'major_category_code' => null,
            'category_code' => null,
            'sub_category_code' => null,
            'phone_number' => null
        ];

        // Merge defaults with provided data, keeping provided values
        $data = array_merge($defaults, $data);

        Log::info('Default values set', [
            'status' => $data['status'],
            'account_level' => $data['account_level'],
            'client_number' => $data['client_number'] ?? 'N/A'
        ]);

        return $data;
    }

    /**
     * Generate unique member account code based on parent sub-category code
     *
     * @param string $parentSubCategoryCode
     * @return string
     */
    private function generateMemberAccountCode(string $parentSubCategoryCode): string
    {
        Log::info('Generating member account code', [
            'parent_sub_category_code' => $parentSubCategoryCode
        ]);

        // Get the last used member account code for this sub-category
        $lastAccount = AccountsModel::where('sub_category_code', $parentSubCategoryCode)
            ->whereNotNull('member_account_code')
            ->orderBy('member_account_code', 'desc')
            ->first();

        if (!$lastAccount || !$lastAccount->member_account_code) {
            // If no existing member accounts, start with parent code + 1
            $newCode = $parentSubCategoryCode + 1;
        } else {
            // Just increment the last account code by 1
            $newCode = $lastAccount->member_account_code + 1;
        }

        Log::info('Generated new member account code', [
            'parent_code' => $parentSubCategoryCode,
            'new_code' => $newCode
        ]);

        return $newCode;
    }
} 