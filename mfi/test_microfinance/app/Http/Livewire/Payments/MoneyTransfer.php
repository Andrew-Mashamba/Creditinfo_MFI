<?php

namespace App\Http\Livewire\Payments;

use Livewire\Component;
use App\Services\Payments\ExternalFundsTransferService;
use App\Services\Payments\MobileWalletTransferService;
use App\Services\Payments\InternalFundsTransferService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class MoneyTransfer extends Component
{
    /**
     * Get the custom logger for Money Transfer
     */
    protected function log()
    {
        return Log::channel('money_transfer');
    }
    
    /**
     * Get External Transfer Service instance
     */
    protected function getExternalTransferService()
    {
        if (!$this->externalTransferService) {
            $this->externalTransferService = app(ExternalFundsTransferService::class);
        }
        return $this->externalTransferService;
    }
    
    /**
     * Get Wallet Transfer Service instance
     */
    protected function getWalletTransferService()
    {
        if (!$this->walletTransferService) {
            $this->walletTransferService = app(MobileWalletTransferService::class);
        }
        return $this->walletTransferService;
    }
    
    /**
     * Get Internal Transfer Service instance
     */
    protected function getInternalTransferService()
    {
        if (!$this->internalTransferService) {
            $this->internalTransferService = app(InternalFundsTransferService::class);
        }
        return $this->internalTransferService;
    }
    
    // Transfer type selection
    public $transferCategory = ''; // 'internal' or 'external' - primary choice
    public $transferType = ''; // 'bank' or 'wallet' - for external transfers only
    
    // Common fields
    public $debitAccount = '';
    public $amount = '';
    public $remarks = '';
    public $chargeBearer = 'OUR'; // OUR, BEN, SHA
    
    // Bank transfer fields
    public $beneficiaryAccount = '';
    public $bankCode = '';
    public $beneficiaryName = '';
    
    // Mobile wallet fields
    public $phoneNumber = '';
    public $walletProvider = '';
    
    // Internal transfer fields (NBC to NBC)
    public $internalAccount = '';
    
    // UI State
    public $currentPhase = 'form'; // 'form', 'verify', 'processing', 'complete'
    public $errorMessage = '';
    public $successMessage = '';
    public $isLoading = false;
    
    // Verification data
    public $verificationData = [];
    public $lookupRef = '';
    public $transactionReference = '';
    
    // Available options
    public $availableBanks = [];
    public $availableWallets = [];
    
    // Services (initialized on demand)
    protected $externalTransferService;
    protected $walletTransferService;
    protected $internalTransferService;

    public function mount()
    {
        $this->log()->info('[MoneyTransfer] Component mounting', [
            'user_id' => auth()->id(),
            'session_id' => session()->getId()
        ]);
        
        try {
            $this->loadAvailableOptions();
            
            // Initialize services
            $this->externalTransferService = app(ExternalFundsTransferService::class);
            $this->walletTransferService = app(MobileWalletTransferService::class);
            $this->internalTransferService = app(InternalFundsTransferService::class);
            
            // Set default account if available
            $this->debitAccount = config('services.nbc_payments.saccos_account', '');
            
            $this->log()->info('[MoneyTransfer] Component mounted successfully', [
                'default_account' => $this->debitAccount,
                'banks_loaded' => count($this->availableBanks),
                'wallets_loaded' => count($this->availableWallets)
            ]);
            
        } catch (Exception $e) {
            $this->log()->error('[MoneyTransfer] Failed to mount component', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Failed to initialize transfer service';
        }
    }

    public function loadAvailableOptions()
    {
        // Load ALL FSPs from both configs
        $allFsps = config('fsp_providers');
        $workingFsps = config('working_fsps');
        
        // Available banks for external transfers (ALL banks)
        $this->availableBanks = [];
        
        // First add working banks with indicators
        foreach ($workingFsps['banks'] ?? [] as $key => $bank) {
            if (!($bank['is_internal'] ?? false)) {
                $suffix = '';
                if (isset($bank['average_response_time'])) {
                    if ($bank['average_response_time'] < 1000) {
                        $suffix = ' ✓ (Verified - Fast)';
                    } else {
                        $suffix = ' ✓ (Verified)';
                    }
                }
                $this->availableBanks[$bank['code']] = $bank['name'] . $suffix;
            }
        }
        
        // Then add all other banks from main config
        foreach ($allFsps['banks'] ?? [] as $key => $bank) {
            if (!isset($this->availableBanks[$bank['code']])) {
                $status = '';
                // Check if it's in known issues
                if (isset($workingFsps['known_issues'][$key])) {
                    $status = ' ⚠ (Issues)';
                } elseif ($bank['active']) {
                    $status = ' • (Active)';
                } else {
                    $status = ' ✗ (Inactive)';
                }
                $this->availableBanks[$bank['code']] = $bank['name'] . $status;
            }
        }
        
        // Available mobile wallet providers (ALL wallets)
        $this->availableWallets = [];
        
        // First add working wallets
        foreach ($workingFsps['mobile_wallets'] ?? [] as $key => $wallet) {
            $suffix = ' ✓ (Verified)';
            $this->availableWallets[$key] = $wallet['name'] . $suffix;
        }
        
        // Then add all other wallets
        foreach ($allFsps['mobile_wallets'] ?? [] as $key => $wallet) {
            if (!isset($this->availableWallets[$key])) {
                $status = '';
                // Check if it's in known issues
                if (isset($workingFsps['known_issues'][$key])) {
                    $status = ' ⚠ (Timeout)';
                } elseif ($wallet['active']) {
                    $status = ' • (Active)';
                } else {
                    $status = ' ✗ (Inactive)';
                }
                $this->availableWallets[$key] = $wallet['name'] . $status;
            }
        }
        
        // Sort alphabetically while keeping verified ones at top
        asort($this->availableBanks);
        asort($this->availableWallets);
    }

    public function verifyBeneficiary()
    {
        $this->log()->info('[MoneyTransfer] Starting beneficiary verification', [
            'transfer_category' => $this->transferCategory,
            'transfer_type' => $this->transferType,
            'amount' => $this->amount,
            'from_account' => $this->debitAccount
        ]);
        
        try {
            $this->validate($this->getValidationRules());
            
            $this->log()->info('[MoneyTransfer] Validation passed', [
                'category' => $this->transferCategory,
                'type' => $this->transferType
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->log()->warning('[MoneyTransfer] Validation failed', [
                'errors' => $e->errors(),
                'category' => $this->transferCategory,
                'type' => $this->transferType
            ]);
            throw $e;
        }
        
        $this->isLoading = true;
        $this->errorMessage = '';
        
        try {
            if ($this->transferCategory === 'internal') {
                $this->log()->info('[MoneyTransfer] Verifying internal NBC account', [
                    'account' => $this->internalAccount
                ]);
                $this->verifyInternalAccount();
                
            } elseif ($this->transferCategory === 'external') {
                switch ($this->transferType) {
                    case 'bank':
                        $this->log()->info('[MoneyTransfer] Verifying external bank account', [
                            'bank_code' => $this->bankCode,
                            'account' => $this->beneficiaryAccount
                        ]);
                        $this->verifyBankAccount();
                        break;
                        
                    case 'wallet':
                        $this->log()->info('[MoneyTransfer] Verifying mobile wallet', [
                            'provider' => $this->walletProvider,
                            'phone' => $this->phoneNumber
                        ]);
                        $this->verifyWallet();
                        break;
                }
            }
            
            if (!empty($this->verificationData)) {
                $this->log()->info('[MoneyTransfer] Verification successful', [
                    'verification_data' => $this->verificationData,
                    'lookup_ref' => $this->lookupRef
                ]);
                $this->currentPhase = 'verify';
            } else {
                $this->log()->warning('[MoneyTransfer] Verification returned empty data');
            }
            
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->log()->error('[MoneyTransfer] Verification failed', [
                'category' => $this->transferCategory,
                'type' => $this->transferType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    protected function verifyBankAccount()
    {
        $this->log()->info('[MoneyTransfer] Calling external transfer service for bank lookup', [
            'account' => $this->beneficiaryAccount,
            'bank_code' => $this->bankCode,
            'amount' => $this->amount
        ]);
        
        $result = $this->getExternalTransferService()->lookupAccount(
            $this->beneficiaryAccount,
            $this->bankCode,
            floatval($this->amount)
        );
        
        $this->log()->info('[MoneyTransfer] Bank lookup response', [
            'success' => $result['success'] ?? false,
            'account_name' => $result['account_name'] ?? null,
            'engine_ref' => $result['engine_ref'] ?? null,
            'error' => $result['error'] ?? null
        ]);
        
        if ($result['success']) {
            $this->verificationData = [
                'type' => 'bank',
                'account_name' => $result['account_name'] ?? 'Account Verified',
                'account_number' => $this->beneficiaryAccount,
                'actual_identifier' => $result['actual_identifier'] ?? $this->beneficiaryAccount,
                'bank_name' => $this->availableBanks[$this->bankCode] ?? $this->bankCode,
                'bank_code' => $this->bankCode,
                'fsp_id' => $result['fsp_id'] ?? $this->bankCode,
                'can_receive' => $result['can_receive'] ?? true,
                'engine_ref' => $result['engine_ref'] ?? null,
                'status_code' => $result['status_code'] ?? null,
                'message' => $result['message'] ?? null
            ];
            
            $this->beneficiaryName = $result['account_name'] ?? '';
            $this->lookupRef = $result['engine_ref'] ?? '';
            
            $this->log()->info('[MoneyTransfer] Bank account verified successfully', [
                'beneficiary_name' => $this->beneficiaryName,
                'lookup_ref' => $this->lookupRef
            ]);
        } else {
            $this->log()->error('[MoneyTransfer] Bank account verification failed', [
                'error' => $result['error'] ?? 'Unknown error',
                'response' => $result
            ]);
            throw new Exception($result['error'] ?? 'Account verification failed');
        }
    }

    protected function verifyWallet()
    {
        $this->log()->info('[MoneyTransfer] Calling wallet transfer service for lookup', [
            'phone' => $this->phoneNumber,
            'provider' => $this->walletProvider,
            'amount' => $this->amount
        ]);
        
        $result = $this->getWalletTransferService()->lookupWallet(
            $this->phoneNumber,
            $this->walletProvider,
            floatval($this->amount)
        );
        
        $this->log()->info('[MoneyTransfer] Wallet lookup response', [
            'success' => $result['success'] ?? false,
            'account_name' => $result['account_name'] ?? null,
            'engine_ref' => $result['engine_ref'] ?? null,
            'error' => $result['error'] ?? null
        ]);
        
        if ($result['success']) {
            $this->verificationData = [
                'type' => 'wallet',
                'account_name' => $result['account_name'] ?? 'Wallet Verified',
                'phone_number' => $this->phoneNumber,
                'actual_identifier' => $result['actual_identifier'] ?? $this->phoneNumber,
                'provider' => $this->availableWallets[$this->walletProvider] ?? $this->walletProvider,
                'provider_code' => $this->walletProvider,
                'fsp_id' => $result['fsp_id'] ?? $this->walletProvider,
                'can_receive' => $result['can_receive'] ?? true,
                'engine_ref' => $result['engine_ref'] ?? null,
                'status_code' => $result['status_code'] ?? null,
                'message' => $result['message'] ?? null
            ];
            
            $this->beneficiaryName = $result['account_name'] ?? '';
            $this->lookupRef = $result['engine_ref'] ?? '';
            
            $this->log()->info('[MoneyTransfer] Wallet verified successfully', [
                'beneficiary_name' => $this->beneficiaryName,
                'lookup_ref' => $this->lookupRef
            ]);
        } else {
            $this->log()->error('[MoneyTransfer] Wallet verification failed', [
                'error' => $result['error'] ?? 'Unknown error',
                'response' => $result
            ]);
            throw new Exception($result['error'] ?? 'Wallet verification failed');
        }
    }

    protected function verifyInternalAccount()
    {
        $this->log()->info('[MoneyTransfer] Calling internal transfer service for lookup', [
            'account' => $this->internalAccount
        ]);
        
        $result = $this->getInternalTransferService()->lookupAccount($this->internalAccount);
        
        $this->log()->info('[MoneyTransfer] Internal account lookup response', [
            'success' => $result['success'] ?? false,
            'account_name' => $result['account_name'] ?? null,
            'branch' => $result['branch_name'] ?? null,
            'error' => $result['error'] ?? null
        ]);
        
        if ($result['success']) {
            $this->verificationData = [
                'type' => 'internal',
                'account_name' => $result['account_name'] ?? 'NBC Account Verified',
                'account_number' => $this->internalAccount,
                'branch' => $result['branch_name'] ?? 'NBC Branch',
                'can_receive' => $result['can_receive'] ?? true
            ];
            
            $this->beneficiaryName = $result['account_name'] ?? '';
            
            $this->log()->info('[MoneyTransfer] Internal account verified successfully', [
                'beneficiary_name' => $this->beneficiaryName,
                'branch' => $this->verificationData['branch']
            ]);
        } else {
            $this->log()->error('[MoneyTransfer] Internal account verification failed', [
                'error' => $result['error'] ?? 'Unknown error',
                'response' => $result
            ]);
            throw new Exception($result['error'] ?? 'Account verification failed');
        }
    }

    public function executeTransfer()
    {
        $this->log()->info('[MoneyTransfer] Starting transfer execution', [
            'category' => $this->transferCategory,
            'type' => $this->transferType,
            'amount' => $this->amount,
            'from_account' => $this->debitAccount,
            'lookup_ref' => $this->lookupRef
        ]);
        
        $this->isLoading = true;
        $this->errorMessage = '';
        $this->currentPhase = 'processing';
        
        try {
            $result = [];
            
            if ($this->transferCategory === 'internal') {
                $this->log()->info('[MoneyTransfer] Executing internal transfer', [
                    'from' => $this->debitAccount,
                    'to' => $this->internalAccount,
                    'amount' => $this->amount
                ]);
                $result = $this->executeInternalTransfer();
                
            } elseif ($this->transferCategory === 'external') {
                switch ($this->transferType) {
                    case 'bank':
                        $this->log()->info('[MoneyTransfer] Executing external bank transfer', [
                            'from' => $this->debitAccount,
                            'to' => $this->beneficiaryAccount,
                            'bank' => $this->bankCode,
                            'amount' => $this->amount
                        ]);
                        $result = $this->executeExternalTransfer();
                        break;
                        
                    case 'wallet':
                        $this->log()->info('[MoneyTransfer] Executing wallet transfer', [
                            'from' => $this->debitAccount,
                            'to' => $this->phoneNumber,
                            'provider' => $this->walletProvider,
                            'amount' => $this->amount
                        ]);
                        $result = $this->executeWalletTransfer();
                        break;
                }
            }
            
            $this->log()->info('[MoneyTransfer] Transfer execution response', [
                'success' => $result['success'] ?? false,
                'reference' => $result['reference'] ?? null,
                'nbc_reference' => $result['nbc_reference'] ?? null,
                'message' => $result['message'] ?? null,
                'error' => $result['error'] ?? null
            ]);
            
            if ($result['success'] ?? false) {
                $this->successMessage = $result['message'] ?? 'Transfer completed successfully';
                $this->transactionReference = $result['reference'] ?? $result['nbc_reference'] ?? 'REF' . time();
                $this->currentPhase = 'complete';
                
                $this->log()->info('[MoneyTransfer] Transfer completed successfully', [
                    'reference' => $this->transactionReference,
                    'message' => $this->successMessage
                ]);
                
                // Log successful transaction
                $this->logTransaction($result);
                
            } else {
                throw new Exception($result['error'] ?? 'Transfer failed');
            }
            
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->currentPhase = 'verify'; // Go back to verify phase
            
            $this->log()->error('[MoneyTransfer] Transfer execution failed', [
                'category' => $this->transferCategory,
                'type' => $this->transferType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    protected function executeExternalTransfer()
    {
        return $this->getExternalTransferService()->transfer([
            'from_account' => $this->debitAccount,
            'to_account' => $this->beneficiaryAccount,
            'bank_code' => $this->bankCode,
            'amount' => $this->amount,
            'narration' => $this->remarks,
            'charge_bearer' => $this->chargeBearer,
            'lookup_ref' => $this->lookupRef,
            'payer_phone' => auth()->user()->phone ?? '255715000001'
        ]);
    }

    protected function executeWalletTransfer()
    {
        return $this->getWalletTransferService()->transfer([
            'from_account' => $this->debitAccount,
            'phone_number' => $this->phoneNumber,
            'provider' => $this->walletProvider,
            'amount' => $this->amount,
            'narration' => $this->remarks,
            'charge_bearer' => $this->chargeBearer,
            'payer_phone' => auth()->user()->phone ?? '255715000001'
        ]);
    }

    protected function executeInternalTransfer()
    {
        return $this->getInternalTransferService()->transfer([
            'from_account' => $this->debitAccount,
            'to_account' => $this->internalAccount,
            'amount' => $this->amount,
            'narration' => $this->remarks
        ]);
    }

    public function resetForm()
    {
        $this->log()->info('[MoneyTransfer] Resetting form');
        
        $this->reset([
            'amount',
            'remarks',
            'beneficiaryAccount',
            'bankCode',
            'beneficiaryName',
            'phoneNumber',
            'walletProvider',
            'internalAccount',
            'verificationData',
            'lookupRef',
            'errorMessage',
            'successMessage',
            'transferCategory',
            'transferType',
            'transactionReference'
        ]);
        
        $this->currentPhase = 'form';
    }

    public function goBack()
    {
        $this->log()->info('[MoneyTransfer] Going back from phase', [
            'current_phase' => $this->currentPhase
        ]);
        
        if ($this->currentPhase === 'verify') {
            $this->currentPhase = 'form';
        } elseif ($this->currentPhase === 'complete') {
            $this->resetForm();
        }
    }
    
    public function updated($propertyName)
    {
        $this->log()->debug('[MoneyTransfer] Property updated', [
            'property' => $propertyName,
            'value' => $this->$propertyName ?? null
        ]);
    }

    protected function getValidationRules()
    {
        $rules = [
            'debitAccount' => 'required|string|min:10',
            'amount' => 'required|numeric|min:1000',
            'remarks' => 'required|string|max:50',
            'transferCategory' => 'required|in:internal,external'
        ];
        
        if ($this->transferCategory === 'internal') {
            $rules['internalAccount'] = 'required|string|min:10';
        } elseif ($this->transferCategory === 'external') {
            $rules['chargeBearer'] = 'required|in:OUR,BEN,SHA';
            $rules['transferType'] = 'required|in:bank,wallet';
            
            switch ($this->transferType) {
                case 'bank':
                    $rules['beneficiaryAccount'] = 'required|string|min:10';
                    $rules['bankCode'] = 'required|string';
                    break;
                    
                case 'wallet':
                    $rules['phoneNumber'] = ['required', 'string', 'regex:/^(255|0)[0-9]{9}$/'];
                    $rules['walletProvider'] = 'required|string';
                    break;
            }
        }
        
        // Amount limits based on transfer type
        if ($this->transferType === 'wallet') {
            $rules['amount'] .= '|max:20000000'; // 20M limit for wallets
        }
        
        return $rules;
    }

    protected function logTransaction($result)
    {
        try {
            DB::table('transfer_logs')->insert([
                'user_id' => auth()->id(),
                'transfer_type' => $this->transferType,
                'from_account' => $this->debitAccount,
                'to_account' => $this->beneficiaryAccount ?? $this->phoneNumber ?? $this->internalAccount,
                'amount' => $this->amount,
                'reference' => $result['reference'] ?? null,
                'nbc_reference' => $result['nbc_reference'] ?? null,
                'status' => 'SUCCESS',
                'remarks' => $this->remarks,
                'response_data' => json_encode($result),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (Exception $e) {
            $this->log()->error('Failed to log transaction', ['error' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $this->log()->debug('[MoneyTransfer] Rendering component', [
            'phase' => $this->currentPhase,
            'category' => $this->transferCategory,
            'type' => $this->transferType,
            'has_error' => !empty($this->errorMessage)
        ]);
        
        return view('livewire.payments.money-transfer');
    }
    
    /**
     * Handle JavaScript errors from the frontend
     */
    public function logJsError($error, $context = [])
    {
        $this->log()->error('[MoneyTransfer] JavaScript error', [
            'error' => $error,
            'context' => $context,
            'user_agent' => request()->header('User-Agent')
        ]);
    }
}