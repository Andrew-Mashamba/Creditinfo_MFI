<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Models\AccountsModel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingService
{
    private $logger;

    public function __construct()
    {
        $this->logger = new TransactionLogger();
    }

    public function generateControlNumber($member, $service, $isRecurring, $paymentMode)
    {
        $institution_id = DB::table('institutions')->where('id',1)->value('institution_id');
        $sacco = preg_replace('/[^0-9]/', '', $institution_id);
        $controlNumber = '1' . 
            str_pad($sacco, 4, '0', STR_PAD_LEFT) .
            str_pad($member, 5, '0', STR_PAD_LEFT) .
            $service .
            $isRecurring .
            $paymentMode;

        $this->logger->logTransactionStart([
            'member' => $member,
            'service' => $service,
            'is_recurring' => $isRecurring,
            'payment_mode' => $paymentMode,
            'control_number' => $controlNumber
        ]);

        return $controlNumber;
    }

    public function createBill($clientNumber, $serviceId, $isRecurring, $paymentMode, $controlNumber, $amount)
    {
        try {
            $this->logger->logTransactionStart([
                'client_number' => $clientNumber,
                'service_id' => $serviceId,
                'control_number' => $controlNumber,
                'amount' => $amount
            ]);

            // Get service details
            $service = DB::table('services')->where('id', $serviceId)->first();
            if (!$service) {
                throw new \Exception("Service with ID {$serviceId} not found");
            }

            Log::info('Service details retrieved', [
                'service_id' => $serviceId,
                'service_name' => $service->name,
                'service_code' => $service->code,
                'debit_account' => $service->debit_account,
                'credit_account' => $service->credit_account
            ]);

            // Determine credit account based on service type
            // REG, MMF, LNF, AGM, DPF, WDF, LPF: Always use service's configured income account
            // Other services (DIP, SAV, SHC): Can use member-specific accounts from session
            $servicesWithFixedCreditAccount = ['REG', 'MMF', 'LNF', 'AGM', 'DPF', 'WDF', 'LPF', 'INS'];

            if (in_array($service->code, $servicesWithFixedCreditAccount)) {
                // These services MUST use their configured credit account (income accounts)
                $creditAccount = $service->credit_account;

                Log::info('Using fixed service credit account (Income/Fee account)', [
                    'service_code' => $service->code,
                    'credit_account' => $creditAccount,
                    'reason' => 'Service requires specific income account'
                ]);
            } else {
                // Other services can use member-specific accounts from session
                $creditAccount = session()->get('saved_credit_account');
                if (!$creditAccount) {
                    $creditAccount = $service->credit_account;
                } else {
                    $creditAccount = $creditAccount->account_number;
                }

                Log::info('Using session or service credit account', [
                    'service_code' => $service->code,
                    'credit_account' => $creditAccount,
                    'source' => session()->has('saved_credit_account') ? 'session' : 'service'
                ]);
            }

            //TODO: log the inserted bill data 
            Log::info('Inserted bill data --------- xxxxxxxxxxxxxxxxxxxxxxxxxxx', [
                'member_id' => $clientNumber,
                'client_number' => $clientNumber,
                'service_id' => DB::table('sub_products')->where('product_account', $service->debit_account)->value('id') ?? $serviceId,
                'control_number' => $controlNumber,
                'amount_due' => $amount,
                'amount_paid' => 0,
                'is_recurring' => $isRecurring,
                'payment_mode' => $paymentMode,
                'status' => 'PENDING',
                'due_date' => now()->addDays(14),
                'credit_account_number' => $creditAccount ?? null,
                'debit_account_number' => $service->debit_account ?? null,
                'created_by' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);


            // Create the bill record with both client_number and member_id
            $bill = DB::table('bills')->insertGetId([
                'member_id' => $clientNumber,
                'client_number' => $clientNumber,  // Added client_number
                'service_id' => DB::table('sub_products')->where('product_account', $service->debit_account)->value('id') ?? $serviceId,
                'control_number' => $controlNumber,
                'amount_due' => $amount,
                'amount_paid' => 0,
                'is_recurring' => $isRecurring,
                'payment_mode' => $paymentMode,
                'status' => 'PENDING',
                'due_date' => now()->addDays(14),
                'credit_account_number' => $creditAccount ?? null,
                'debit_account_number' => $service->debit_account ?? null,
               
                'created_by' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            session()->forget('saved_credit_account');

            Log::info('Bill created with account numbers', [
                'bill_id' => $bill,
                'control_number' => $controlNumber,
                'credit_account_number' => $service->credit_account,
                'debit_account_number' => $service->debit_account
            ]);

            // Log the transaction record with all required fields
            $this->logger->logTransactionRecord([
                'reference_number' => $controlNumber,
                'transaction_type' => 'BILL_CREATION',
                'amount' => $amount,
                'debit_account' => $service->debit_account ?? $clientNumber,
                'credit_account' => $service->credit_account ?? 'SERVICE_' . $serviceId
            ]);

            $this->logger->logTransactionCompletion($controlNumber, 'SUCCESS');

            return $bill;

        } catch (\Exception $e) {
            $this->logger->logError($e, [
                'client_number' => $clientNumber,
                'service_id' => $serviceId,
                'control_number' => $controlNumber
            ]);
            throw $e;
        }
    }

    public function processPayment($controlNumber, $paymentData)
    {
        // Set flag to prevent automatic client status updates during payment processing
        config(['payment.processing' => true]);
        
        Log::info('Starting payment processing', [
            'control_number' => $controlNumber,
            'payment_data' => $paymentData,
            'timestamp' => now()->toIso8601String(),
            'prevent_status_update' => true
        ]);

        $this->logger->logTransactionStart([
            'control_number' => $controlNumber,
            'payment_data' => $paymentData
        ]);

        try {
            DB::beginTransaction();
            Log::info('Database transaction started');

            $bill = Bill::where('control_number', $controlNumber)->first();
            
            if (!$bill) {
                Log::error('Bill not found', [
                    'control_number' => $controlNumber,
                    'search_time' => now()->toIso8601String()
                ]);

                $this->logger->logError(new \Exception("Bill not found"), [
                    'control_number' => $controlNumber
                ]);
                return [
                    'success' => false,
                    'status' => 'error',
                    'message' => "Bill with control number {$controlNumber} not found.",
                    'status_code' => 404,
                    'bill' => null
                ];
            }

            Log::info('Bill found', [
                'bill_id' => $bill->id,
                'control_number' => $bill->control_number,
                'amount_due' => $bill->amount_due,
                'amount_paid' => $bill->amount_paid,
                'status' => $bill->status,
                'service_id' => $bill->service_id,
                'client_number' => $bill->client_number
            ]);

            $validationResult = $this->validatePaymentAmount($bill, $paymentData['amount']);
            Log::info('Payment amount validation result', [
                'validation_result' => $validationResult,
                'amount_paid' => $paymentData['amount'],
                'amount_due' => $bill->amount_due,
                'payment_mode' => $bill->payment_mode
            ]);

            if (!$validationResult['success']) {
                $this->logger->logTransactionRecord([
                    'control_number' => $controlNumber,
                    'validation_result' => $validationResult
                ]);
                return $validationResult;
            }

            $bill->amount_paid += $paymentData['amount'];
            Log::info('Updated bill amount paid', [
                'bill_id' => $bill->id,
                'new_amount_paid' => $bill->amount_paid,
                'amount_due' => $bill->amount_due,
                'payment_amount' => $paymentData['amount'],
                'payment_mode' => $bill->payment_mode
            ]);

            // Only check for overpayment on non-infinity payment modes
            // Payment mode 5 (Infinity) allows unlimited payments without exceeding amount_due
            if ($bill->payment_mode != '5' && $bill->amount_paid > $bill->amount_due) {
                Log::error('Payment amount exceeds total amount due', [
                    'bill_id' => $bill->id,
                    'amount_paid' => $bill->amount_paid,
                    'amount_due' => $bill->amount_due,
                    'excess_amount' => $bill->amount_paid - $bill->amount_due,
                    'payment_mode' => $bill->payment_mode
                ]);

                $this->logger->logTransactionRecord([
                    'control_number' => $controlNumber,
                    'amount_paid' => $bill->amount_paid,
                    'amount_due' => $bill->amount_due
                ]);
                return [
                    'success' => false,
                    'status' => 'error',
                    'message' => "Payment amount exceeds the total amount due.",
                    'status_code' => 422,
                    'bill' => $bill
                ];
            }

            // Update bill status
            $oldStatus = $bill->status;
            if ($bill->amount_paid >= $bill->amount_due) {
                $bill->status = 'PAID';




            } elseif ($bill->due_date < now()) {
                $bill->status = 'OVERDUE';
            }

            Log::info('Updating bill status', [
                'bill_id' => $bill->id,
                'old_status' => $oldStatus,
                'new_status' => $bill->status,
                'amount_paid' => $bill->amount_paid,
                'amount_due' => $bill->amount_due,
                'due_date' => $bill->due_date
            ]);

            $bill->save();

            $this->logger->logTransactionRecord([
                'control_number' => $controlNumber,
                'new_status' => $bill->status,
                'amount_paid' => $bill->amount_paid
            ]);

            DB::commit();
            Log::info('Database transaction committed successfully');

            $narrationSuffix = $bill->service->name;
            $debitAccountCode = $bill->debit_account_number;
            $creditAccountCode = $bill->credit_account_number;

            Log::info('Processing transaction', [
                'bill_id' => $bill->id,
                'narration' => $narrationSuffix,
                'debit_account' => $debitAccountCode,
                'credit_account' => $creditAccountCode,
                'amount' => $paymentData['amount']
            ]);

            $this->processTransaction($paymentData['amount'], $narrationSuffix, $bill);

            $this->logger->logTransactionCompletion($controlNumber, 'success');

            Log::info('Payment processing completed successfully', [
                'bill_id' => $bill->id,
                'control_number' => $controlNumber,
                'final_status' => $bill->status,
                'final_amount_paid' => $bill->amount_paid
            ]);

            // Reset payment processing flag
            config(['payment.processing' => false]);
            
            return [
                'success' => true,
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'status_code' => 200,
                'bill' => $bill
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            // Reset payment processing flag
            config(['payment.processing' => false]);
            
            Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'control_number' => $controlNumber,
                'payment_data' => $paymentData,
                'failed_at' => now()->toIso8601String()
            ]);

            $this->logger->logError($e, [
                'control_number' => $controlNumber,
                'payment_data' => $paymentData
            ]);
            return [
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
                'status_code' => 500,
                'bill' => null
            ];
        }
    }

    protected function validatePaymentAmount($bill, $amount)
    {
        try {
            switch ($bill->payment_mode) {
                case '1': // Partial
                    if ($amount <= 0) {
                        throw new \Exception('Partial payment amount must be greater than 0');
                    }
                    break;

                case '2': // Full
                    if ($amount < $bill->amount_due) {
                        throw new \Exception('Full payment must cover the entire amount due');
                    }
                    break;

                case '3': // Exact
                    if ($amount != $bill->amount_due) {
                        throw new \Exception('Exact payment must match the amount due');
                    }
                    break;

                case '4': // Limited
                    $remaining = $bill->amount_due - $bill->amount_paid;
                    if ($amount > $remaining) {
                        throw new \Exception('Limited payment cannot exceed remaining amount');
                    }
                    break;

                case '5': // Infinity
                    if ($amount <= 0) {
                        throw new \Exception('Infinity payment amount must be greater than 0');
                    }
                    break;
            }

            return [
                'success' => true,
                'status' => 'valid',
                'message' => 'Payment amount is valid',
                'status_code' => 200
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'error',
                'message' => 'Payment amount is invalid. ' . $e->getMessage(),
                'status_code' => 422
            ];
        }
    }

    public function handlePaymentNotification($notificationData)
    {
        try {
            DB::beginTransaction();

            $notification = PaymentNotification::create([
                'control_number' => $notificationData['control_number'],
                'received_at' => now(),
                'raw_payload' => $notificationData,
                'status' => 'Pending'
            ]);

            // Process the payment
            $this->processPayment(
                $notificationData['control_number'],
                [
                    'payment_ref' => $notificationData['payment_ref'],
                    'amount' => $notificationData['amount'],
                    'payment_channel' => $notificationData['payment_channel'],
                    'paid_at' => $notificationData['paid_at'] ?? now()
                ]
            );

            $notification->update([
                'status' => 'Processed',
                'processed_at' => now()
            ]);

            DB::commit();
            return $notification;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment notification processing failed: ' . $e->getMessage());
            
            if (isset($notification)) {
                $notification->update([
                    'status' => 'Failed',
                    'processed_at' => now()
                ]);
            }

            throw $e;
        }
    }

    public function getBillStatus($controlNumber)
    {
        $bill = Bill::where('control_number', $controlNumber)
            ->with(['sacco', 'member', 'service'])
            ->firstOrFail();

        return [
            'control_number' => $bill->control_number,
            'amount_due' => $bill->amount_due,
            'amount_paid' => $bill->amount_paid,
            'status' => $bill->status,
            'due_date' => $bill->due_date,
            'member_name' => $bill->member->name,
            'service_name' => $bill->service->name
        ];
    }

    protected function validateAccounts($debitAccountNumber, $creditAccountNumber)
    {
        Log::info('Starting account validation', [
            'debit_account_number' => $debitAccountNumber,
            'credit_account_number' => $creditAccountNumber,
            'timestamp' => now()->toIso8601String()
        ]);

        $debitAccount = AccountsModel::where('account_number', $debitAccountNumber)->first();
        $creditAccount = AccountsModel::where('account_number', $creditAccountNumber)->first();

        if (!$debitAccount) {
            Log::error('Debit account not found', [
                'account_number' => $debitAccountNumber,
                'validation_time' => now()->toIso8601String()
            ]);
        } else {
            Log::info('Debit account found', [
                'account_number' => $debitAccount->account_number,
                'account_name' => $debitAccount->account_name,
                'sub_category_code' => $debitAccount->sub_category_code,
                'status' => $debitAccount->status
            ]);
        }

        if (!$creditAccount) {
            Log::error('Credit account not found', [
                'account_number' => $creditAccountNumber,
                'validation_time' => now()->toIso8601String()
            ]);
        } else {
            Log::info('Credit account found', [
                'account_number' => $creditAccount->account_number,
                'account_name' => $creditAccount->account_name,
                'sub_category_code' => $creditAccount->sub_category_code,
                'status' => $creditAccount->status
            ]);
        }

        // Skip status check for internal operating accounts
        $isValid = !is_null($debitAccount) && !is_null($creditAccount) && 
                  ($creditAccount->status === 'ACTIVE' || 
                   ($debitAccount->account_use === 'internal' && $debitAccount->status === 'PENDING'));
        
        Log::info('Account validation completed', [
            'is_valid' => $isValid,
            'debit_account_found' => !is_null($debitAccount),
            'credit_account_found' => !is_null($creditAccount),
            'validation_time' => now()->toIso8601String()
        ]);

        return [
            'debit_account' => $debitAccountNumber,
            'credit_account' => $creditAccountNumber,
            'is_valid' => $isValid,
            'validation_details' => [
                'debit_account_details' => $debitAccount ? [
                    'account_name' => $debitAccount->account_name,
                    'sub_category_code' => $debitAccount->sub_category_code,
                    'status' => $debitAccount->status,
                    'account_use' => $debitAccount->account_use
                ] : null,
                'credit_account_details' => $creditAccount ? [
                    'account_name' => $creditAccount->account_name,
                    'sub_category_code' => $creditAccount->sub_category_code,
                    'status' => $creditAccount->status
                ] : null
            ]
        ];
    }

    protected function getGroupAccount($creditAccount)
    {
        try {
            $account = DB::table('accounts')
                ->where('account_number', $creditAccount)
                ->first();

            if (!$account) {
                Log::error('Group account not found', ['credit_account' => $creditAccount]);
                throw new \Exception("Group account not found for account: {$creditAccount}");
            }

            return $account;
        } catch (\Exception $e) {
            Log::error('Error retrieving group account', [
                'credit_account' => $creditAccount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function processTransaction($amount, $narrationSuffix, $bill)
    {
        $this->logger->logTransactionStart([
            'amount' => $amount,
            'narration' => $narrationSuffix,
            'bill_id' => $bill->id,
            'control_number' => $bill->control_number
        ]);

        try {
            // Validate bill reference
            if (!$bill->control_number) {
                throw new \Exception('Bill reference is required');
            }

            // Get service details
            $service = DB::table('services')->where('id', $bill->service_id)->first();
            if (!$service) {
                Log::error('Service not found', [
                    'service_id' => $bill->service_id,
                    'bill_id' => $bill->id,
                    'error' => 'Service not found for bill ID: ' . $bill->id
                ]);
                throw new \Exception("Service not found for bill ID: {$bill->id}");
            }

            Log::info('Service details --------- xxxxxxxxxxxxxxxxxxxxxxxxxxx', [
                'service' => $service,
                'bill_id' => $bill->id,
                'control_number' => $bill->control_number
            ]);

            $debitAccountCode = $service->debit_account;
            $creditAccountCode = $bill->credit_account_number;

            Log::info('Starting transaction processing', [
                'bill_id' => $bill->id,
                'debit_account' => $debitAccountCode,
                'credit_account' => $creditAccountCode,
                'amount' => $amount,
                'service_id' => $service->id,
                'service_name' => $service->name
            ]);

            // Validate accounts
            $accountValidation = $this->validateAccounts($debitAccountCode, $creditAccountCode);

            // if (!$accountValidation['is_valid']) {
            //     Log::error('Account validation failed', [
            //         'validation_details' => $accountValidation['validation_details'],
            //         'bill_id' => $bill->id,
            //         'debit_account' => $debitAccountCode,
            //         'credit_account' => $creditAccountCode
            //     ]);
            //     throw new \Exception('Invalid account configuration');
            // }

            $narration = "{$narrationSuffix} : Bill ID {$bill->id}";
            $debit_account = $accountValidation['debit_account'];
            $credit_account = $accountValidation['credit_account'];

            // Handle different service types
            if ($service->code == 'REG') {
                // REG: Registration Fee - uses direct account mapping
                // Debit: Operating Account, Credit: Membership Fees (both from service table)

                Log::info('===== REG SERVICE PROCESSING START =====', [
                    'bill_id' => $bill->id,
                    'control_number' => $bill->control_number,
                    'service_code' => $service->code,
                    'service_name' => $service->name,
                    'amount' => $amount
                ]);

                Log::info('REG: Service Configuration from Database', [
                    'service->debit_account' => $service->debit_account ?? 'NULL',
                    'service->credit_account' => $service->credit_account ?? 'NULL',
                ]);

                Log::info('REG: Bill Stored Account Numbers', [
                    'bill->debit_account_number' => $bill->debit_account_number ?? 'NULL',
                    'bill->credit_account_number' => $bill->credit_account_number ?? 'NULL',
                ]);

                Log::info('REG: Accounts Used for Transaction Processing', [
                    'debitAccountCode (from service)' => $debitAccountCode,
                    'creditAccountCode (from bill)' => $creditAccountCode,
                ]);

                Log::info('REG: Validated Account Objects', [
                    'debit_account (validated)' => $debit_account,
                    'credit_account (validated)' => $credit_account,
                ]);

                // Fetch full account details for logging
                $debitAccountDetails = AccountsModel::where('account_number', $debitAccountCode)->first();
                $creditAccountDetails = AccountsModel::where('account_number', $creditAccountCode)->first();

                Log::info('REG: Complete Account Details', [
                    'DEBIT_ACCOUNT' => [
                        'account_number' => $debitAccountDetails->account_number ?? 'NOT FOUND',
                        'account_name' => $debitAccountDetails->account_name ?? 'NOT FOUND',
                        'major_category_code' => $debitAccountDetails->major_category_code ?? 'NOT FOUND',
                        'sub_category_code' => $debitAccountDetails->sub_category_code ?? 'NOT FOUND',
                        'account_type' => $debitAccountDetails->type ?? 'NOT FOUND',
                        'current_balance' => $debitAccountDetails->balance ?? 'NOT FOUND',
                    ],
                    'CREDIT_ACCOUNT' => [
                        'account_number' => $creditAccountDetails->account_number ?? 'NOT FOUND',
                        'account_name' => $creditAccountDetails->account_name ?? 'NOT FOUND',
                        'major_category_code' => $creditAccountDetails->major_category_code ?? 'NOT FOUND',
                        'sub_category_code' => $creditAccountDetails->sub_category_code ?? 'NOT FOUND',
                        'account_type' => $creditAccountDetails->type ?? 'NOT FOUND',
                        'current_balance' => $creditAccountDetails->balance ?? 'NOT FOUND',
                    ]
                ]);

                // Accounts already set from service table, no lookup needed

            } elseif ($service->code == 'REP') {
                // REP: Loan Repayment - uses LoanRepaymentService
                Log::info('Processing loan repayment service via LoanRepaymentService', [
                    'bill_id' => $bill->id,
                    'service_code' => $service->code,
                    'client_number' => $bill->client_number,
                    'amount' => $amount
                ]);

                try {
                    // Find the active loan for the client (ACTIVE or RESTRUCTURED status)
                    $loan = DB::table('loans')
                        ->where('client_number', $bill->client_number)
                        ->whereIn('status', ['ACTIVE', 'RESTRUCTURED'])
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if (!$loan) {
                        Log::error('Active loan not found for loan repayment', [
                            'client_number' => $bill->client_number,
                            'bill_id' => $bill->id
                        ]);
                        throw new \Exception("No active loan found for member: {$bill->client_number}");
                    }

                    Log::info('Active loan found - calling LoanRepaymentService', [
                        'loan_id' => $loan->loan_id,
                        'loan_account_number' => $loan->loan_account_number,
                        'loan_status' => $loan->status,
                        'client_number' => $bill->client_number,
                        'bill_id' => $bill->id,
                        'payment_amount' => $amount
                    ]);

                    // Call LoanRepaymentService to handle the complete repayment process
                    $loanRepaymentService = new LoanRepaymentService(new TransactionPostingService());

                    // Prepare payment details for the repayment service
                    $paymentDetails = [
                        'reference' => $bill->control_number,
                        'bill_id' => $bill->id,
                        'payment_channel' => 'NBC_BILLS_PAYMENT',
                        'source' => 'BILLING_API'
                    ];

                    // Process repayment using LoanRepaymentService
                    // This handles: allocation, schedule updates, transaction posting,
                    // payment history, loan status updates, receipts, and notifications
                    $repaymentResult = $loanRepaymentService->processRepayment(
                        $loan->loan_id,
                        $amount,
                        'MOBILE', // Payment method from NBC gateway
                        $paymentDetails
                    );

                    if (!$repaymentResult['success']) {
                        throw new \Exception("Loan repayment failed: " . $repaymentResult['message']);
                    }

                    Log::info('Loan repayment processed successfully via LoanRepaymentService', [
                        'loan_id' => $loan->loan_id,
                        'bill_id' => $bill->id,
                        'receipt_number' => $repaymentResult['receipt_number'],
                        'payment_id' => $repaymentResult['payment_id'],
                        'allocation' => $repaymentResult['allocation'],
                        'outstanding_balance' => $repaymentResult['outstanding_balance']
                    ]);

                    // Return early - LoanRepaymentService has handled everything
                    $this->logger->logTransactionCompletion($bill->control_number, 'SUCCESS');
                    session()->flash('message', json_encode($repaymentResult));
                    return $repaymentResult;

                } catch (\Exception $e) {
                    Log::error('Error processing loan repayment via LoanRepaymentService', [
                        'error' => $e->getMessage(),
                        'client_number' => $bill->client_number,
                        'bill_id' => $bill->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }

            } elseif ($service->code == 'DIP') {
                // DIP: Member Deposits - credit goes to member's deposit account
                // Get group account from institutions table
                Log::info('Processing member deposits service', [
                    'bill_id' => $bill->id,
                    'service_code' => $service->code,
                    'client_number' => $bill->client_number
                ]);

                try {
                    // Get institution configuration
                    $institution = DB::table('institutions')->where('id', 1)->first();
                    if (!$institution) {
                        throw new \Exception("Institution configuration not found");
                    }

                    // Get operating account (debit) and deposits group account (for finding member's account)
                    $debit_account = $institution->operations_account;
                    $depositsGroupAccountNumber = $institution->mandatory_deposits_account;

                    Log::info('Institution accounts for DIP service', [
                        'operations_account' => $debit_account,
                        'mandatory_deposits_account' => $depositsGroupAccountNumber,
                        'bill_id' => $bill->id
                    ]);

                    // Get the group deposit account to extract category codes
                    $groupAccount = DB::table('accounts')
                        ->where('account_number', $depositsGroupAccountNumber)
                        ->first();

                    if (!$groupAccount) {
                        throw new \Exception("Group deposit account not found: {$depositsGroupAccountNumber}");
                    }

                    Log::info('Group deposit account details', [
                        'group_account' => $groupAccount,
                        'account_number' => $depositsGroupAccountNumber,
                        'bill_id' => $bill->id
                    ]);

                    $majorCategory = $groupAccount->major_category_code;
                    $category = $groupAccount->category_code;

                    // Find member's specific deposit account
                    $memberAccount = DB::table('accounts')
                        ->where('major_category_code', $majorCategory)
                        ->where('category_code', $category)
                        ->where('client_number', $bill->client_number)
                        ->where('product_number', 3000)
                        ->first();

                    if (!$memberAccount) {
                        Log::error('Member deposit account not found', [
                            'client_number' => $bill->client_number,
                            'major_category' => $majorCategory,
                            'category' => $category,
                            'bill_id' => $bill->id
                        ]);
                        throw new \Exception("Member deposit account not found for member: {$bill->client_number}");
                    }

                    Log::info('Member deposit account found', [
                        'member_account' => $memberAccount,
                        'account_number' => $memberAccount->account_number,
                        'account_name' => $memberAccount->account_name,
                        'client_number' => $bill->client_number,
                        'bill_id' => $bill->id
                    ]);

                    $credit_account = $memberAccount->account_number;

                } catch (\Exception $e) {
                    Log::error('Error processing member deposit', [
                        'error' => $e->getMessage(),
                        'client_number' => $bill->client_number,
                        'bill_id' => $bill->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }

            } elseif ($service->code == 'SAV') {
                // SAV: Savings Deposit - credit goes to member's savings account
                // Get group account from institutions table
                Log::info('Processing savings deposit service', [
                    'bill_id' => $bill->id,
                    'service_code' => $service->code,
                    'client_number' => $bill->client_number
                ]);

                try {
                    // Get institution configuration
                    $institution = DB::table('institutions')->where('id', 1)->first();
                    if (!$institution) {
                        throw new \Exception("Institution configuration not found");
                    }

                    // Get operating account (debit) and savings group account (for finding member's account)
                    $debit_account = $institution->operations_account;
                    $savingsGroupAccountNumber = $institution->mandatory_savings_account;

                    Log::info('Institution accounts for SAV service', [
                        'operations_account' => $debit_account,
                        'mandatory_savings_account' => $savingsGroupAccountNumber,
                        'bill_id' => $bill->id
                    ]);

                    // Get the group savings account to extract category codes
                    $groupAccount = DB::table('accounts')
                        ->where('account_number', $savingsGroupAccountNumber)
                        ->first();

                    if (!$groupAccount) {
                        throw new \Exception("Group savings account not found: {$savingsGroupAccountNumber}");
                    }

                    Log::info('Group savings account details', [
                        'group_account' => $groupAccount,
                        'account_number' => $savingsGroupAccountNumber,
                        'bill_id' => $bill->id
                    ]);

                    $majorCategory = $groupAccount->major_category_code;
                    $category = $groupAccount->category_code;

                    // Find member's specific savings account
                    $memberAccount = DB::table('accounts')
                        ->where('major_category_code', $majorCategory)
                        ->where('category_code', $category)
                        ->where('client_number', $bill->client_number)
                        ->where('product_number', 2000)
                        ->first();

                    if (!$memberAccount) {
                        Log::error('Member savings account not found', [
                            'client_number' => $bill->client_number,
                            'major_category' => $majorCategory,
                            'category' => $category,
                            'bill_id' => $bill->id
                        ]);
                        throw new \Exception("Member savings account not found for member: {$bill->client_number}");
                    }

                    Log::info('Member savings account found', [
                        'member_account' => $memberAccount,
                        'account_number' => $memberAccount->account_number,
                        'account_name' => $memberAccount->account_name,
                        'client_number' => $bill->client_number,
                        'bill_id' => $bill->id
                    ]);

                    $credit_account = $memberAccount->account_number;

                } catch (\Exception $e) {
                    Log::error('Error processing savings deposit', [
                        'error' => $e->getMessage(),
                        'client_number' => $bill->client_number,
                        'bill_id' => $bill->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }

            } elseif ($service->code == 'SHC') {
                // SHC: Share Capital - will be handled through approval workflow
                // DO NOT post transaction here - only create approval request and handle spillover
                Log::info('Processing share capital service - skipping immediate transaction posting', [
                    'bill_id' => $bill->id,
                    'service_code' => $service->code,
                    'client_number' => $bill->client_number,
                    'note' => 'Share transaction will be posted when approval is processed'
                ]);

                // Skip the main transaction posting for SHC
                // The approval workflow will handle this later
                // Jump directly to SHC-specific processing below

            } else {
                // Default: other services - use existing pattern
                Log::warning('Unknown service code - using default processing', [
                    'service_code' => $service->code,
                    'bill_id' => $bill->id
                ]);

                // Accounts already set from service table
            }

            // Post the transaction (skip for SHC - it goes through approval workflow)
            if ($service->code != 'SHC') {
                $transactionService = new TransactionPostingService();
                $transactionData = [
                    'first_account' => $credit_account,
                    'second_account' => $debit_account,
                    'source_account' => $debit_account,        // Money enters from this account
                    'destination_account' => $credit_account,   // Money goes to this account
                    'amount' => $amount,
                    'narration' => $narration,
                ];

                Log::info('Posting transaction', [
                    'debit_account' => $debitAccountCode,
                    'credit_account' => $creditAccountCode,
                    'amount' => $amount,
                    'bill_id' => $bill->id,
                    'transaction_data' => $transactionData
                ]);

                // Enhanced logging for REG transactions
                if ($service->code == 'REG') {
                    Log::info('===== REG TRANSACTION DATA PREPARATION =====', [
                        'transaction_data' => [
                            'first_account' => $credit_account . ' (' . ($creditAccountDetails->account_name ?? 'Unknown') . ')',
                            'second_account' => $debit_account . ' (' . ($debitAccountDetails->account_name ?? 'Unknown') . ')',
                            'source_account' => $debit_account . ' (Money enters FROM here)',
                            'destination_account' => $credit_account . ' (Money goes TO here)',
                            'amount' => $amount,
                            'narration' => $narration,
                        ],
                        'NOTE' => 'TransactionPostingService will determine which account to DEBIT and which to CREDIT based on account types'
                    ]);
                }

                try {
                    $response = $transactionService->postTransaction($transactionData);

                Log::info('Transaction posted successfully', [
                    'response' => $response,
                    'bill_id' => $bill->id,
                    'control_number' => $bill->control_number,
                    'reference_number' => $response['reference_number'] ?? null
                ]);

                // Enhanced logging for REG transaction completion
                if ($service->code == 'REG') {
                    Log::info('===== REG TRANSACTION POSTED SUCCESSFULLY =====', [
                        'reference_number' => $response['reference_number'] ?? null,
                        'bill_id' => $bill->id,
                        'control_number' => $bill->control_number,
                        'amount' => $amount,
                        'response_data' => $response
                    ]);

                    // Log final balances
                    $finalDebitBalance = AccountsModel::where('account_number', $debitAccountCode)->value('balance');
                    $finalCreditBalance = AccountsModel::where('account_number', $creditAccountCode)->value('balance');

                    Log::info('===== REG FINAL ACCOUNT BALANCES =====', [
                        'DEBIT_ACCOUNT' => [
                            'account_number' => $debitAccountCode,
                            'account_name' => $debitAccountDetails->account_name ?? 'Unknown',
                            'balance_before' => $debitAccountDetails->balance ?? 'Unknown',
                            'balance_after' => $finalDebitBalance,
                            'change' => $finalDebitBalance - ($debitAccountDetails->balance ?? 0)
                        ],
                        'CREDIT_ACCOUNT' => [
                            'account_number' => $creditAccountCode,
                            'account_name' => $creditAccountDetails->account_name ?? 'Unknown',
                            'balance_before' => $creditAccountDetails->balance ?? 'Unknown',
                            'balance_after' => $finalCreditBalance,
                            'change' => $finalCreditBalance - ($creditAccountDetails->balance ?? 0)
                        ]
                    ]);
                }

                $this->logger->logTransactionRecord([
                    'reference_number' => $response['reference_number'] ?? null,
                    'transaction_type' => 'payment',
                    'amount' => $amount,
                    'debit_account' => $debitAccountCode,
                    'credit_account' => $creditAccountCode,
                    'bill_id' => $bill->id,
                    'control_number' => $bill->control_number
                ]);

                // Update mandatory savings tracking for SAV payments
                if ($service->code == 'SAV') {
                    $this->updateMandatorySavingsTracking($bill->client_number, $amount);
                }

                session()->flash('message', json_encode($response));
                return $response;

                } catch (\Exception $e) {
                    Log::error('Transaction posting failed', [
                        'error' => $e->getMessage(),
                        'transaction_data' => $transactionData,
                        'bill_id' => $bill->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            } else {
                // SHC: Share Capital - only create approval and handle spillover (no immediate transaction)
                $transactionService = new TransactionPostingService();

                Log::info('Processing SHC payment - creating share issuance approval request', [
                    'service_code' => $service->code,
                    'bill_id' => $bill->id,
                    'client_number' => $bill->client_number,
                    'control_number' => $bill->control_number,
                    'amount' => $amount
                ]);

                try {
                    // Get institution configuration for default share product
                    $institution = DB::table('institutions')->where('id', 1)->first();
                    if (!$institution || !$institution->default_share_product_id) {
                        throw new \Exception("Default share product not configured in institution settings");
                    }

                    // Get share product details
                    $shareProduct = DB::table('sub_products')
                        ->where('id', $institution->default_share_product_id)
                        ->first();

                    if (!$shareProduct) {
                        throw new \Exception("Share product not found: " . $institution->default_share_product_id);
                    }

                    Log::info('Share product details', [
                        'product_id' => $shareProduct->id,
                        'product_name' => $shareProduct->product_name,
                        'nominal_price' => $shareProduct->nominal_price
                    ]);

                        // Get member details
                        $member = DB::table('clients')
                            ->where('client_number', $bill->client_number)
                            ->first();

                        if (!$member) {
                            throw new \Exception("Member not found: " . $bill->client_number);
                        }

                        $memberName = trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name);

                        // Get linked savings account (product_number = 2000, oldest first)
                        $savingsAccount = DB::table('accounts')
                            ->where('client_number', $bill->client_number)
                            ->where('product_number', 2000)
                            ->orderBy('id', 'asc')
                            ->first();

                        if (!$savingsAccount) {
                            Log::warning('No savings account found for member', [
                                'client_number' => $bill->client_number,
                                'bill_id' => $bill->id
                            ]);
                            // Use credit_account as fallback
                            $linkedSavingsAccount = $credit_account;
                        } else {
                            $linkedSavingsAccount = $savingsAccount->account_number;
                            Log::info('Linked savings account found', [
                                'savings_account' => $linkedSavingsAccount,
                                'account_id' => $savingsAccount->id
                            ]);
                        }

                        // Get member's individual share account (product_number = 1000)
                        $memberShareAccount = DB::table('accounts')
                            ->where('client_number', $bill->client_number)
                            ->where('product_number', 1000)
                            ->first();

                        if (!$memberShareAccount) {
                            throw new \Exception("Member's individual share account not found for client: " . $bill->client_number);
                        }

                        $memberShareAccountNumber = $memberShareAccount->account_number;
                        Log::info('Member individual share account found', [
                            'share_account' => $memberShareAccountNumber,
                            'account_id' => $memberShareAccount->id,
                            'account_name' => $memberShareAccount->account_name
                        ]);

                        // Calculate number of shares based on payment amount
                        $pricePerShare = floatval($shareProduct->nominal_price);
                        $numberOfShares = floor($amount / $pricePerShare);

                        if ($numberOfShares < 1) {
                            throw new \Exception("Payment amount insufficient for even 1 share. Minimum: " . $pricePerShare);
                        }

                        Log::info('Share calculation', [
                            'amount_paid' => $amount,
                            'price_per_share' => $pricePerShare,
                            'number_of_shares' => $numberOfShares,
                            'total_value' => $numberOfShares * $pricePerShare
                        ]);

                        // Generate reference number (following "Issue New Shares" pattern)
                        $referenceNumber = 'SH' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

                        // Create share issuance record with PENDING status
                        $issuanceId = DB::table('issued_shares')->insertGetId([
                            'reference_number' => $referenceNumber,
                            'share_id' => $shareProduct->id,
                            'member' => $memberName,
                            'product' => $shareProduct->id,
                            'account_number' => $memberShareAccountNumber, // Member's individual share account
                            'price' => $pricePerShare,
                            'branch' => $member->branch_id ?? 1,
                            'client_number' => $bill->client_number,
                            'number_of_shares' => $numberOfShares,
                            'nominal_price' => $pricePerShare,
                            'total_value' => $numberOfShares * $pricePerShare,
                            'linked_savings_account' => $linkedSavingsAccount,
                            'linked_share_account' => $memberShareAccountNumber,
                            'status' => 'PENDING', // Will be COMPLETED after approval
                            'created_by' => 1, // System user
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        Log::info('Share issuance record created', [
                            'issuance_id' => $issuanceId,
                            'reference_number' => $referenceNumber,
                            'status' => 'PENDING'
                        ]);

                        // Prepare edit_package for approval (following "Issue New Shares" pattern)
                        $editPackage = [
                            'type' => 'share_issuance',
                            'reference_number' => $referenceNumber,
                            'member_id' => $bill->client_number,
                            'member_name' => $memberName,
                            'product_id' => $shareProduct->id,
                            'product_name' => $shareProduct->product_name,
                            'number_of_shares' => $numberOfShares,
                            'nominal_price' => $pricePerShare,
                            'total_amount' => $numberOfShares * $pricePerShare,
                            'linked_savings_account' => $linkedSavingsAccount,
                            'share_account' => $memberShareAccountNumber,
                            'status' => 'COMPLETED',
                            'created_by' => 1,
                            'source' => 'NBC_BILLS_PAYMENT',
                            'control_number' => $bill->control_number,
                            'bill_id' => $bill->id
                        ];

                        // Create approval request (following "Issue New Shares" pattern)
                        $approval = DB::table('approvals')->insertGetId([
                            'process_name' => 'share_issuance',
                            'process_description' => 'NBC Bills Payment - Share Capital: ' . $numberOfShares . ' shares to ' . $memberName,
                            'approval_process_description' => 'Share issuance approval required for NBC Bills Payment',
                            'process_code' => 'SHARE_ISS',
                            'process_id' => $issuanceId,
                            'process_status' => 'PENDING',
                            'user_id' => 1, // System user
                            'approver_id' => null,
                            'approval_status' => 'PENDING',
                            'edit_package' => json_encode($editPackage),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        Log::info('Share issuance approval request created', [
                            'approval_id' => $approval,
                            'process_code' => 'SHARE_ISS',
                            'issuance_id' => $issuanceId,
                            'awaiting_approval' => true
                        ]);

                        // Handle spillover amount (money that doesn't fit into whole shares)
                        $spilloverAmount = $amount - ($numberOfShares * $pricePerShare);

                        if ($spilloverAmount > 0) {
                            Log::info('Spillover amount detected', [
                                'total_payment' => $amount,
                                'shares_value' => $numberOfShares * $pricePerShare,
                                'spillover_amount' => $spilloverAmount,
                                'client_number' => $bill->client_number
                            ]);

                            try {
                                // Get member's deposit account (product_number = 3000)
                                $groupAccount = DB::table('accounts')
                                    ->where('account_number', $institution->mandatory_deposits_account)
                                    ->first();

                                if (!$groupAccount) {
                                    throw new \Exception("Group deposit account not found: " . $institution->mandatory_deposits_account);
                                }

                                $depositAccount = DB::table('accounts')
                                    ->where('major_category_code', $groupAccount->major_category_code)
                                    ->where('category_code', $groupAccount->category_code)
                                    ->where('client_number', $bill->client_number)
                                    ->where('product_number', 3000)
                                    ->orderBy('id', 'asc')
                                    ->first();

                                if (!$depositAccount) {
                                    Log::warning('Member deposit account not found for spillover', [
                                        'client_number' => $bill->client_number,
                                        'spillover_amount' => $spilloverAmount
                                    ]);
                                    throw new \Exception("Member deposit account not found for spillover. Member: " . $bill->client_number);
                                }

                                Log::info('Deposit account found for spillover', [
                                    'deposit_account' => $depositAccount->account_number,
                                    'account_name' => $depositAccount->account_name,
                                    'spillover_amount' => $spilloverAmount
                                ]);

                                // Post spillover transaction: DEBIT operations, CREDIT member deposit
                                // For Asset (operations) → Liability (deposit): first=deposit (credit), second=operations (debit)
                                // But we want: DEBIT operations, CREDIT deposit
                                // So: first=operations (gets debited), second=deposit (gets credited)
                                $spilloverTransactionData = [
                                    'first_account' => $institution->operations_account, // Will be debited (asset decreases)
                                    'second_account' => $depositAccount->account_number, // Will be credited (liability increases)
                                    'source_account' => $institution->operations_account,
                                    'destination_account' => $depositAccount->account_number,
                                    'amount' => $spilloverAmount,
                                    'narration' => 'Spillover from share payment - Control: ' . $bill->control_number,
                                ];

                                $spilloverResponse = $transactionService->postTransaction($spilloverTransactionData);

                                Log::info('Spillover transaction posted successfully', [
                                    'spillover_amount' => $spilloverAmount,
                                    'deposit_account' => $depositAccount->account_number,
                                    'operations_account' => $institution->operations_account,
                                    'transaction_ref' => $spilloverResponse['reference_number'] ?? null,
                                    'control_number' => $bill->control_number
                                ]);

                                $this->logger->logTransactionRecord([
                                    'reference_number' => $spilloverResponse['reference_number'] ?? null,
                                    'transaction_type' => 'spillover_deposit',
                                    'amount' => $spilloverAmount,
                                    'debit_account' => $institution->operations_account,
                                    'credit_account' => $depositAccount->account_number,
                                    'bill_id' => $bill->id,
                                    'control_number' => $bill->control_number
                                ]);

                                session()->flash('success', 'Share capital payment processed. ' . $numberOfShares . ' shares pending approval. Spillover TZS ' . number_format($spilloverAmount, 2) . ' credited to deposit account. Reference: ' . $referenceNumber);

                            } catch (\Exception $spilloverError) {
                                Log::error('Failed to process spillover amount', [
                                    'error' => $spilloverError->getMessage(),
                                    'trace' => $spilloverError->getTraceAsString(),
                                    'spillover_amount' => $spilloverAmount,
                                    'client_number' => $bill->client_number,
                                    'control_number' => $bill->control_number
                                ]);
                                // Don't throw - share issuance was successful, just log spillover failure
                                session()->flash('warning', 'Share issuance pending approval. However, spillover amount TZS ' . number_format($spilloverAmount, 2) . ' could not be credited: ' . $spilloverError->getMessage());
                            }
                        } else {
                            session()->flash('success', 'Share capital payment processed. Share issuance pending approval. Reference: ' . $referenceNumber);
                        }

                    // Log completion and return success response for SHC payment
                    $this->logger->logTransactionCompletion($bill->control_number, 'SUCCESS');

                    $shcResponse = [
                        'success' => true,
                        'status' => 'success',
                        'message' => 'Share capital payment processed successfully. Approval request created.',
                        'reference_number' => $referenceNumber ?? 'N/A',
                        'shares_count' => $numberOfShares ?? 0,
                        'spillover_amount' => $spilloverAmount ?? 0,
                        'status_code' => 200
                    ];

                    session()->flash('message', json_encode($shcResponse));
                    return $shcResponse;

                } catch (\Exception $e) {
                    Log::error('Error creating share issuance approval request', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'bill_id' => $bill->id,
                        'client_number' => $bill->client_number,
                        'control_number' => $bill->control_number
                    ]);
                    // Don't throw - just return error response
                    session()->flash('warning', 'Payment processed but share issuance approval failed: ' . $e->getMessage());

                    $errorResponse = [
                        'success' => false,
                        'status' => 'error',
                        'message' => 'Share issuance approval failed: ' . $e->getMessage(),
                        'status_code' => 500
                    ];

                    session()->flash('message', json_encode($errorResponse));
                    return $errorResponse;
                }
            }

        } catch (\Exception $e) {
            Log::error('Transaction processing failed', [
                'error' => $e->getMessage(),
                'bill_id' => $bill->id,
                'debit_account' => $debitAccountCode ?? null,
                'credit_account' => $creditAccountCode ?? null,
                'amount' => $amount,
                'trace' => $e->getTraceAsString()
            ]);

            $this->logger->logError($e, [
                'debit_account' => $debitAccountCode ?? null,
                'credit_account' => $creditAccountCode ?? null,
                'amount' => $amount,
                'bill_id' => $bill->id,
                'control_number' => $bill->control_number,
                'error_message' => $e->getMessage()
            ]);

            session()->flash('error', 'Transaction failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update mandatory savings tracking when a SAV payment is received.
     * This method finds the current month's tracking record and updates the paid amount.
     *
     * @param string $clientNumber
     * @param float $amount
     * @return void
     */
    protected function updateMandatorySavingsTracking($clientNumber, $amount)
    {
        try {
            $currentYear = now()->year;
            $currentMonth = now()->month;

            // Find the tracking record for current month (or the most recent unpaid one)
            $trackingRecord = \App\Models\MandatorySavingsTracking::where('client_number', $clientNumber)
                ->whereIn('status', ['UNPAID', 'PARTIAL'])
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->first();

            if ($trackingRecord) {
                Log::info('Updating mandatory savings tracking for SAV payment', [
                    'client_number' => $clientNumber,
                    'tracking_id' => $trackingRecord->id,
                    'year' => $trackingRecord->year,
                    'month' => $trackingRecord->month,
                    'required_amount' => $trackingRecord->required_amount,
                    'previous_paid' => $trackingRecord->paid_amount,
                    'new_payment' => $amount,
                    'old_status' => $trackingRecord->status
                ]);

                // Record the payment (this method updates paid_amount, balance, and status)
                $trackingRecord->recordPayment($amount, now());

                Log::info('Mandatory savings tracking updated successfully', [
                    'tracking_id' => $trackingRecord->id,
                    'new_paid_amount' => $trackingRecord->paid_amount,
                    'new_balance' => $trackingRecord->balance,
                    'new_status' => $trackingRecord->status,
                    'is_fully_paid' => $trackingRecord->status === 'PAID'
                ]);
            } else {
                Log::info('No unpaid mandatory savings tracking record found for member', [
                    'client_number' => $clientNumber,
                    'current_year' => $currentYear,
                    'current_month' => $currentMonth,
                    'note' => 'Payment may be for a period without tracking record'
                ]);
            }
        } catch (\Exception $e) {
            // Don't throw - log the error but don't fail the main payment transaction
            Log::error('Error updating mandatory savings tracking', [
                'client_number' => $clientNumber,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 