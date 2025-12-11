<?php

namespace App\Services;

use App\Models\LoanGuarantor;
use App\Models\LoanCollateral;
use App\Models\Account;
use App\Models\ClientsModel;
use App\Models\LoansModel;
use App\Models\LockedAmount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CollateralManagementService
{
    /**
     * Check if a member can be a guarantor (not already guaranteeing too many loans)
     */
    public function canMemberBeGuarantor($memberId, $loanId = null)
    {
        $activeGuarantees = LoanGuarantor::active()
            ->byMember($memberId)
            ->when($loanId, function($query) use ($loanId) {
                return $query->where('loan_id', '!=', $loanId);
            })
            ->count();

        // You can adjust this limit based on your SACCO policy
        $maxActiveGuarantees = 3; // Example: max 3 active guarantees per member
        
        return $activeGuarantees < $maxActiveGuarantees;
    }

    /**
     * Check if an account can be used as collateral
     */
    public function canAccountBeCollateral($accountId, $amount, $loanId = null)
    {
        $account = Account::find($accountId);
        if (!$account) {
            return false;
        }

        // Check if account has sufficient balance
        if ($account->balance < $amount) {
            return false;
        }

        // Check if account is already being used as collateral for other loans
        $existingCollateral = LoanCollateral::active()
            ->byAccount($accountId)
            ->when($loanId, function($query) use ($loanId) {
                return $query->whereHas('loanGuarantor', function($q) use ($loanId) {
                    $q->where('loan_id', '!=', $loanId);
                });
            })
            ->first();

        if ($existingCollateral) {
            // Check if there's enough available balance
            $availableBalance = $account->balance - $existingCollateral->locked_amount;
            return $availableBalance >= $amount;
        }

        return true;
    }

    /**
     * Get available accounts for a member by type
     */
    public function getAvailableAccounts($memberId, $accountType, $loanId = null)
    {
        try {
            // Get client number from member ID
            $client = DB::table('clients')->find($memberId);
            if (!$client) {
                Log::warning('Client not found', ['memberId' => $memberId]);
                return collect();
            }

            $clientNumber = $client->client_number;
            
            // Map account type to product number
            $productNumberMap = [
                'shares' => 1000,
                'savings' => 2000,
                'deposits' => 3000,
            ];

            $productNumber = $productNumberMap[$accountType] ?? null;
            if (!$productNumber) {
                Log::warning('Invalid account type', ['accountType' => $accountType]);
                return collect();
            }

            // Get accounts for this client and product type
            $accounts = Account::where('client_number', $clientNumber)
                ->where('product_number', $productNumber)
                ->where('status', 'ACTIVE')
                ->get();

            Log::info('Retrieved accounts from database', [
                'clientNumber' => $clientNumber,
                'accountType' => $accountType,
                'productNumber' => $productNumber,
                'totalAccounts' => $accounts->count(),
                'accountIds' => $accounts->pluck('id')->toArray(),
                'accountNumbers' => $accounts->pluck('account_number')->toArray(),
                'accountBalances' => $accounts->pluck('balance')->toArray(),
                'statuses' => $accounts->pluck('status')->toArray()
            ]);

            // Add availability information to each account
            $accountsWithAvailability = $accounts->map(function ($account) use ($loanId) {
                $availableBalance = LockedAmount::getAvailableBalance($account->id);
                $isAlreadyUsed = false;
                $reason = null;
                
                // Check if account is already used as collateral for this loan
                if ($loanId) {
                    $existingCollateral = LoanCollateral::where('account_id', $account->id)
                        ->whereHas('loanGuarantor', function ($query) use ($loanId) {
                            $query->where('loan_id', $loanId);
                        })
                        ->first();
                    
                    if ($existingCollateral) {
                        $isAlreadyUsed = true;
                        $reason = 'Already used as collateral for this loan';
                        Log::info('Account already has collateral for this loan', [
                            'accountId' => $account->id,
                            'loanId' => $loanId,
                            'existingCollateralId' => $existingCollateral->id
                        ]);
                    }
                }
                
                // Determine if account is available for selection
                // Account is available if it has sufficient available balance, regardless of whether it's already used
                $isAvailable = $availableBalance > 0;
                
                if (!$isAvailable) {
                    $reason = 'Insufficient available balance';
                } elseif ($isAlreadyUsed) {
                    // If already used but has available balance, it can be used for additional collateral
                    $reason = 'Can be used for additional collateral';
                }
                
                Log::info('Account availability check', [
                    'accountId' => $account->id,
                    'accountNumber' => $account->account_number,
                    'balance' => $account->balance,
                    'lockedAmount' => LockedAmount::getLockedAmount($account->id),
                    'availableBalance' => $availableBalance,
                    'isAvailable' => $isAvailable,
                    'isAlreadyUsed' => $isAlreadyUsed,
                    'reason' => $reason
                ]);
                
                // Add availability properties to the account object
                $account->is_available = $isAvailable;
                $account->available_balance = $availableBalance;
                $account->locked_amount = LockedAmount::getLockedAmount($account->id);
                $account->is_already_used = $isAlreadyUsed;
                $account->unavailable_reason = $reason;
                
                return $account;
            });

            Log::info('Processed accounts with availability', [
                'totalAccounts' => $accountsWithAvailability->count(),
                'availableAccounts' => $accountsWithAvailability->where('is_available', true)->count(),
                'unavailableAccounts' => $accountsWithAvailability->where('is_available', false)->count(),
                'availableAccountIds' => $accountsWithAvailability->where('is_available', true)->pluck('id')->toArray(),
                'unavailableAccountIds' => $accountsWithAvailability->where('is_available', false)->pluck('id')->toArray()
            ]);

            return $accountsWithAvailability;

        } catch (\Exception $e) {
            Log::error('Error getting available accounts: ' . $e->getMessage(), [
                'memberId' => $memberId,
                'accountType' => $accountType,
                'loanId' => $loanId
            ]);
            return collect();
        }
    }

    /**
     * Create a guarantor for a loan
     */
    public function createGuarantor($loanId, $guarantorMemberId, $guarantorType, $relationship = null)
    {
        // For self-guarantee, get the loan applicant's member ID
        if ($guarantorType === 'self_guarantee') {
            $loan = LoansModel::find($loanId);
            if (!$loan) {
                throw new \Exception('Loan not found');
            }
            
            // Get the member ID from the loan's client_number
            $client = DB::table('clients')->where('client_number', $loan->client_number)->first();
            if (!$client) {
                throw new \Exception('Loan applicant not found');
            }
            
            $guarantorMemberId = $client->id;
        }

        if (!$this->canMemberBeGuarantor($guarantorMemberId, $loanId)) {
            throw new \Exception('Member cannot be a guarantor. Too many active guarantees.');
        }

        // Check if there's an existing guarantor (active or inactive) for this loan and member
        $existingGuarantor = LoanGuarantor::where('loan_id', $loanId)
            ->where('guarantor_member_id', $guarantorMemberId)
            ->first();

        if ($existingGuarantor) {
            // If guarantor exists but is inactive, reactivate it
            if ($existingGuarantor->status === 'inactive' || $existingGuarantor->status === 'released') {
                $existingGuarantor->update([
                    'guarantor_type' => $guarantorType,
                    'relationship' => $relationship,
                    'status' => 'active',
                ]);
                return $existingGuarantor;
            } else {
                // If guarantor is already active, return it
                return $existingGuarantor;
            }
        }

        // Create new guarantor if none exists
        return LoanGuarantor::create([
            'loan_id' => $loanId,
            'guarantor_member_id' => $guarantorMemberId,
            'guarantor_type' => $guarantorType,
            'relationship' => $relationship,
            'status' => 'active',
        ]);
    }

    /**
     * Add financial collateral to a guarantor
     */
    public function addFinancialCollateral($guarantorId, $accountId, $amount)
    {
        DB::transaction(function () use ($guarantorId, $accountId, $amount) {
            // Check if account has sufficient available balance
            if (!LockedAmount::checkAccountAvailability($accountId, $amount)) {
                throw new \Exception('Insufficient available balance in account');
            }

            // Check if this account is already used as collateral for this guarantor
            $existingCollateral = LoanCollateral::where('loan_guarantor_id', $guarantorId)
                ->where('account_id', $accountId)
                ->where('status', 'active')
                ->first();

            if ($existingCollateral) {
                // Update existing collateral with additional amount
                $newTotalAmount = $existingCollateral->collateral_amount + $amount;
                
                // Lock the additional amount
                LockedAmount::lockAmount(
                    $accountId,
                    $amount,
                    'loan_collateral',
                    $existingCollateral->id,
                    'loan_guarantee',
                    "Additional loan collateral for guarantor ID: {$guarantorId}"
                );

                // Update the collateral record
                $existingCollateral->update([
                    'collateral_amount' => $newTotalAmount,
                    'locked_amount' => LockedAmount::getLockedAmount($accountId)
                ]);

                return $existingCollateral;
            } else {
                // Create new collateral record
                $collateral = LoanCollateral::create([
                    'loan_guarantor_id' => $guarantorId,
                    'account_id' => $accountId,
                    'collateral_type' => $this->getCollateralTypeFromAccount($accountId),
                    'collateral_amount' => $amount,
                    'locked_amount' => 0, // Will be updated by LockedAmount system
                    'status' => 'active',
                ]);

                // Lock the amount using the new system
                LockedAmount::lockAmount(
                    $accountId,
                    $amount,
                    'loan_collateral',
                    $collateral->id,
                    'loan_guarantee',
                    "Loan collateral for guarantor ID: {$guarantorId}"
                );

                // Update the collateral's locked_amount field
                $collateral->update([
                    'locked_amount' => LockedAmount::getLockedAmount($accountId)
                ]);

                return $collateral;
            }
        });
    }

    /**
     * Add physical collateral to a guarantor
     */
    public function addPhysicalCollateral($guarantorId, $collateralData)
    {
        return LoanCollateral::create([
            'loan_guarantor_id' => $guarantorId,
            'collateral_type' => 'physical',
            'physical_collateral_id' => $collateralData['collateral_id'],
            'physical_collateral_description' => $collateralData['description'],
            'physical_collateral_value' => $collateralData['value'],
            'physical_collateral_location' => $collateralData['location'],
            'physical_collateral_owner_name' => $collateralData['owner_name'],
            'physical_collateral_owner_nida' => $collateralData['owner_nida'],
            'physical_collateral_owner_contact' => $collateralData['owner_contact'],
            'physical_collateral_owner_address' => $collateralData['owner_address'],
            'physical_collateral_valuation_date' => !empty($collateralData['valuation_date']) ? $collateralData['valuation_date'] : null,
            'physical_collateral_valuation_method' => $collateralData['valuation_method'],
            'physical_collateral_valuer_name' => $collateralData['valuer_name'],
            'insurance_policy_number' => $collateralData['insurance_policy_number'],
            'insurance_company_name' => $collateralData['insurance_company_name'],
            'insurance_coverage_details' => $collateralData['insurance_coverage_details'],
            'insurance_expiration_date' => !empty($collateralData['insurance_expiration_date']) ? $collateralData['insurance_expiration_date'] : null,
            'collateral_amount' => $collateralData['value'],
            'locked_amount' => $collateralData['value'], // Physical collateral is fully locked
            'status' => 'active',
        ]);
    }

    /**
     * Release collateral when loan is paid off
     */
    public function releaseCollateral($collateralId)
    {
        $collateral = LoanCollateral::find($collateralId);
        if (!$collateral) {
            throw new \Exception('Collateral not found');
        }

        DB::transaction(function () use ($collateral) {
            if ($collateral->isFinancialCollateral()) {
                // Release the locked amount
                LockedAmount::releaseAllForService('loan_collateral', $collateral->id);
                
                // Update the collateral record
                $collateral->update([
                    'locked_amount' => 0,
                    'status' => 'released'
                ]);
            } else {
                // Physical collateral - just mark as released
                $collateral->update([
                    'locked_amount' => 0,
                    'status' => 'released'
                ]);
            }
        });
    }

    /**
     * Get collateral summary for a loan
     */
    public function getCollateralSummary($guarantorId)
    {
        $guarantor = LoanGuarantor::with(['collaterals.account'])->find($guarantorId);
        if (!$guarantor) {
            return null;
        }

        $summary = [
            'guarantor_id' => $guarantor->id,
            'guarantor_type' => $guarantor->guarantor_type,
            'relationship' => $guarantor->relationship,
            'total_collateral_amount' => 0,
            'total_locked_amount' => 0,
            'collaterals' => []
        ];

        foreach ($guarantor->collaterals as $collateral) {
            $collateralData = [
                'id' => $collateral->id,
                'type' => $collateral->collateral_type,
                'amount' => $collateral->collateral_amount,
                'locked_amount' => $collateral->locked_amount,
                'available_amount' => $collateral->collateral_amount - $collateral->locked_amount,
            ];

            if ($collateral->isFinancialCollateral()) {
                $account = $collateral->account;
                $collateralData['account_number'] = $account ? $account->account_number : 'N/A';
                $collateralData['account_balance'] = $account ? $account->balance : 0;
                
                // Get detailed lock information
                $lockSummary = LockedAmount::getAccountLockSummary($account->id);
                $collateralData['lock_details'] = $lockSummary;
            } else {
                $collateralData['description'] = $collateral->physical_collateral_description;
                $collateralData['location'] = $collateral->physical_collateral_location;
                $collateralData['owner_name'] = $collateral->physical_collateral_owner_name;
            }

            $summary['collaterals'][] = $collateralData;
            $summary['total_collateral_amount'] += $collateral->collateral_amount;
            $summary['total_locked_amount'] += $collateral->locked_amount;
        }

        return $summary;
    }

    /**
     * Get total collateral amounts for a loan
     */
    public function getLoanCollateralTotals($loanId)
    {
        $totals = DB::table('loan_collaterals')
            ->join('loan_guarantors', 'loan_collaterals.loan_guarantor_id', '=', 'loan_guarantors.id')
            ->where('loan_guarantors.loan_id', $loanId)
            ->where('loan_guarantors.status', 'active')
            ->where('loan_collaterals.status', 'active')
            ->selectRaw('
                SUM(collateral_amount) as total_collateral_amount,
                SUM(locked_amount) as total_locked_amount,
                COUNT(*) as total_collaterals
            ')
            ->first();

        return [
            'total_collateral_amount' => $totals->total_collateral_amount ?? 0,
            'total_locked_amount' => $totals->total_locked_amount ?? 0,
            'total_collaterals' => $totals->total_collaterals ?? 0
        ];
    }

    /**
     * Check if loan has sufficient collateral coverage
     */
    public function hasSufficientCollateral($loanId, $requiredAmount)
    {
        $totals = $this->getLoanCollateralTotals($loanId);
        return $totals['total_collateral_amount'] >= $requiredAmount;
    }

    /**
     * Get account type for collateral mapping
     */
    private function getCollateralTypeFromAccount($accountId)
    {
        $account = Account::find($accountId);
        if (!$account) {
            return 'savings'; // Default to savings instead of unknown
        }

        $productNumber = $account->product_number;
        switch ($productNumber) {
            case 1000:
                return 'shares';
            case 2000:
                return 'savings';
            case 3000:
                return 'deposits';
            default:
                return 'savings'; // Default to savings instead of unknown
        }
    }

    /**
     * Get loan product collateral settings
     */
    public function getLoanProductCollateralSettings($loanSubProductId)
    {
        // Implementation for getting loan product collateral settings
        return [
            'requires_collateral' => true,
            'minimum_collateral_ratio' => 0.5,
            'accepted_collateral_types' => ['savings', 'deposits', 'shares', 'physical'],
        ];
    }

    /**
     * Finalize collateral registration and mark guarantor tab as completed
     */
    public function finalizeCollateralRegistration($loanId)
    {
        DB::transaction(function () use ($loanId) {
            // Get collateral totals using the dedicated method
            $totals = $this->getLoanCollateralTotals($loanId);
            
            // Get guarantors count for logging
            $guarantors = LoanGuarantor::where('loan_id', $loanId)
                ->where('status', 'active')
                ->with('collaterals')
                ->get();

            Log::info('Collateral totals calculated', [
                'loanId' => $loanId,
                'totalCollateralAmount' => $totals['total_collateral_amount'],
                'totalLockedAmount' => $totals['total_locked_amount'],
                'totalCollaterals' => $totals['total_collaterals']
            ]);

            // Mark guarantor tab as completed in the loan process
            $tabStateService = app(\App\Services\LoanTabStateService::class);
            $completedTabs = $tabStateService->getCompletedTabs($loanId);
            
            // Add guarantor tab if not already present
            if (!in_array('guarantor', $completedTabs)) {
                $completedTabs[] = 'guarantor';
            }
            
            $tabStateService->saveTabCompletionStatus($loanId, $completedTabs);

            Log::info('Collateral registration finalized', [
                'loanId' => $loanId,
                'totalCollateralAmount' => $totals['total_collateral_amount'],
                'totalLockedAmount' => $totals['total_locked_amount'],
                'totalCollaterals' => $totals['total_collaterals'],
                'guarantorsCount' => $guarantors->count()
            ]);

            return [
                'success' => true,
                'totalCollateralAmount' => $totals['total_collateral_amount'],
                'totalLockedAmount' => $totals['total_locked_amount'],
                'totalCollaterals' => $totals['total_collaterals'],
                'guarantorsCount' => $guarantors->count(),
            ];
        });
    }
} 