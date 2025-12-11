<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\approvals;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\sub_products;
use App\Services\TransactionPostingService;
use Illuminate\Validation\ValidationException;

class ShareRedemptionService
{
    protected $transactionService;

    public function __construct(TransactionPostingService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Redeem shares from a member
     *
     * @param array $data
     * @return array
     */
    public function redeemShares(array $data)
    {
        $processId = uniqid('share_redemption_');

        try {
            Log::info("[$processId] Starting share redemption process", [
                'client_number' => $data['client_number'] ?? 'NOT_PROVIDED',
                'product_id' => $data['product_id'] ?? 'NOT_PROVIDED',
                'number_of_shares' => $data['number_of_shares'] ?? 'NOT_PROVIDED',
                'price_per_share' => $data['price_per_share'] ?? 'NOT_PROVIDED',
                'linked_savings_account' => $data['linked_savings_account'] ?? 'NOT_PROVIDED',
                'share_account' => $data['share_account'] ?? 'NOT_PROVIDED',
                'total_value' => $data['total_value'] ?? 'NOT_PROVIDED',
                'user_id' => auth()->id(),
                'request_data' => $data
            ]);

            // Validate input data
            Log::info("[$processId] Starting input validation");
            try {
                $this->validateInputData($data);
                Log::info("[$processId] Input validation completed successfully");
            } catch (ValidationException $e) {
                Log::error("[$processId] Input validation failed", [
                    'validation_errors' => $e->errors(),
                    'input_data' => $data,
                    'user_id' => auth()->id()
                ]);
                throw $e;
            }

            // Get member details
            Log::info("[$processId] Fetching member details", ['client_number' => $data['client_number']]);
            try {
                $memberDetails = $this->getMemberDetails($data['client_number']);
                if (!$memberDetails) {
                    Log::error("[$processId] Member not found", [
                        'client_number' => $data['client_number'],
                        'user_id' => auth()->id()
                    ]);
                    throw new \Exception('Member not found or not active');
                }
                Log::info("[$processId] Member details retrieved successfully", [
                    'member_id' => $memberDetails->id ?? 'N/A',
                    'member_status' => $memberDetails->status ?? 'N/A',
                    'member_name' => $memberDetails->first_name . ' ' . $memberDetails->last_name ?? 'N/A'
                ]);
            } catch (\Exception $e) {
                Log::error("[$processId] Error fetching member details", [
                    'client_number' => $data['client_number'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => auth()->id()
                ]);
                throw $e;
            }

            // Get product details
            Log::info("[$processId] Fetching product details", ['product_id' => $data['product_id']]);
            try {
                $productDetails = $this->getProductDetails($data['product_id']);
                if (!$productDetails) {
                    Log::error("[$processId] Share product not found", [
                        'product_id' => $data['product_id'],
                        'user_id' => auth()->id()
                    ]);
                    throw new \Exception('Share product not found');
                }
                Log::info("[$processId] Product details retrieved successfully", [
                    'product_id' => $productDetails->id,
                    'product_name' => $productDetails->product_name ?? 'N/A',
                    'product_type' => $productDetails->product_type ?? 'N/A'
                ]);
            } catch (\Exception $e) {
                Log::error("[$processId] Error fetching product details", [
                    'product_id' => $data['product_id'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => auth()->id()
                ]);
                throw $e;
            }

            // Validate member status
            Log::info("[$processId] Validating member status", ['member_status' => $memberDetails->status]);
            if ($memberDetails->status !== 'ACTIVE') {
                Log::error("[$processId] Member account is not active", [
                    'client_number' => $data['client_number'],
                    'member_status' => $memberDetails->status,
                    'user_id' => auth()->id()
                ]);
                throw new \Exception('Member account is not active');
            }
            Log::info("[$processId] Member status validation passed");

            // Validate share balance (member must have sufficient shares)
            Log::info("[$processId] Validating share balance", [
                'share_account' => $data['share_account'],
                'required_shares' => $data['number_of_shares']
            ]);
            try {
                $this->validateShareBalance($data['share_account'], $data['number_of_shares'], $data['product_id'], $data['client_number']);
                Log::info("[$processId] Share balance validation passed");
            } catch (\Exception $e) {
                Log::error("[$processId] Share balance validation failed", [
                    'share_account' => $data['share_account'],
                    'required_shares' => $data['number_of_shares'],
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id()
                ]);
                throw $e;
            }

            // Validate share account balance (must have sufficient funds to transfer)
            Log::info("[$processId] Validating share account balance", [
                'share_account' => $data['share_account'],
                'required_amount' => $data['total_value']
            ]);
            try {
                $this->validateAccountBalance($data['share_account'], $data['total_value']);
                Log::info("[$processId] Share account balance validation passed");
            } catch (\Exception $e) {
                Log::error("[$processId] Share account balance validation failed", [
                    'share_account' => $data['share_account'],
                    'required_amount' => $data['total_value'],
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id()
                ]);
                throw $e;
            }

            // Start database transaction
            Log::info("[$processId] Starting database transaction");
            DB::beginTransaction();

            try {
                // Generate reference number
                Log::info("[$processId] Generating reference number");
                $referenceNumber = $this->generateReferenceNumber();
                Log::info("[$processId] Reference number generated", ['reference_number' => $referenceNumber]);

                // Create share redemption record
                Log::info("[$processId] Creating share redemption record");
                $redemptionId = $this->createRedemptionRecord($data, $memberDetails, $referenceNumber);
                Log::info("[$processId] Share redemption record created", [
                    'redemption_id' => $redemptionId,
                    'reference_number' => $referenceNumber
                ]);

                // Create approval request
                Log::info("[$processId] Creating approval request");
                $this->createApprovalRequest($data, $memberDetails, $productDetails, $redemptionId);
                Log::info("[$processId] Approval request created successfully");

                // Commit transaction
                DB::commit();
                Log::info("[$processId] Database transaction committed successfully");

            } catch (\Exception $e) {
                Log::error("[$processId] Error during database operations", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'client_number' => $data['client_number'],
                    'product_id' => $data['product_id'],
                    'user_id' => auth()->id(),
                    'reference_number' => $referenceNumber ?? 'NOT_GENERATED',
                    'redemption_id' => $redemptionId ?? 'NOT_CREATED'
                ]);
                DB::rollBack();
                Log::info("[$processId] Database transaction rolled back");
                throw $e;
            }

            Log::info("[$processId] Share redemption process completed successfully", [
                'redemption_id' => $redemptionId,
                'reference_number' => $referenceNumber,
                'client_number' => $data['client_number'],
                'user_id' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Share redemption request submitted successfully.',
                'redemption_id' => $redemptionId,
                'reference_number' => $referenceNumber
            ];

        } catch (ValidationException $e) {
            Log::error("[$processId] Share redemption validation failed", [
                'validation_errors' => $e->errors(),
                'client_number' => $data['client_number'] ?? null,
                'product_id' => $data['product_id'] ?? null,
                'user_id' => auth()->id(),
                'input_data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ];

        } catch (\Exception $e) {
            Log::error("[$processId] Critical error in share redemption process", [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'full_trace' => $e->getTraceAsString(),
                'client_number' => $data['client_number'] ?? null,
                'product_id' => $data['product_id'] ?? null,
                'number_of_shares' => $data['number_of_shares'] ?? null,
                'price_per_share' => $data['price_per_share'] ?? null,
                'linked_savings_account' => $data['linked_savings_account'] ?? null,
                'share_account' => $data['share_account'] ?? null,
                'total_value' => $data['total_value'] ?? null,
                'user_id' => auth()->id(),
                'request_data' => $data,
                'exception_class' => get_class($e)
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while processing your request: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate input data
     *
     * @param array $data
     * @throws ValidationException
     */
    protected function validateInputData(array $data)
    {
        $rules = [
            'product_id' => 'required|exists:sub_products,id',
            'client_number' => 'required|string|size:5',
            'number_of_shares' => 'required|numeric|min:1',
            'price_per_share' => 'required|numeric|min:0',
            'linked_savings_account' => 'required|exists:accounts,account_number',
            'share_account' => 'required|exists:accounts,account_number',
            'total_value' => 'required|numeric|min:0'
        ];

        $messages = [
            'product_id.required' => 'Please select a share product.',
            'product_id.exists' => 'Selected share product is invalid.',
            'client_number.required' => 'Client number is required.',
            'client_number.size' => 'Client number must be exactly 5 digits.',
            'number_of_shares.required' => 'Number of shares is required.',
            'number_of_shares.numeric' => 'Number of shares must be a number.',
            'number_of_shares.min' => 'Number of shares must be at least 1.',
            'price_per_share.required' => 'Price per share is required.',
            'price_per_share.numeric' => 'Price per share must be a number.',
            'linked_savings_account.required' => 'Please select a linked savings account.',
            'linked_savings_account.exists' => 'Selected savings account is invalid.',
            'share_account.required' => 'Please select a share account.',
            'share_account.exists' => 'Selected share account is invalid.',
            'total_value.required' => 'Total value is required.',
            'total_value.numeric' => 'Total value must be a number.'
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Get member details
     *
     * @param string $clientNumber
     * @return object|null
     */
    protected function getMemberDetails(string $clientNumber)
    {
        return ClientsModel::where('client_number', $clientNumber)->first();
    }

    /**
     * Get product details
     *
     * @param int $productId
     * @return object|null
     */
    protected function getProductDetails(int $productId)
    {
        return sub_products::find($productId);
    }

    /**
     * Validate account balance
     *
     * @param string $accountNumber
     * @param float $requiredAmount
     * @throws \Exception
     */
    protected function validateAccountBalance(string $accountNumber, float $requiredAmount)
    {
        $account = AccountsModel::where('account_number', $accountNumber)->first();

        if (!$account) {
            throw new \Exception('Selected account not found.');
        }

        if ($account->balance < $requiredAmount) {
            throw new \Exception('Insufficient balance in the share account for redemption.');
        }
    }

    /**
     * Validate share balance - member must have sufficient shares to redeem
     *
     * @param string $shareAccount
     * @param int $requiredShares
     * @param int $productId
     * @param string $clientNumber
     * @throws \Exception
     */
    protected function validateShareBalance(string $shareAccount, int $requiredShares, int $productId, string $clientNumber)
    {
        $shareRegister = DB::table('share_registers')
            ->where('member_id', $clientNumber)
            ->where('product_id', $productId)
            ->first();

        if (!$shareRegister) {
            throw new \Exception('No share ownership found for this member and product.');
        }

        if ($shareRegister->current_share_balance < $requiredShares) {
            throw new \Exception('Insufficient share balance. Available: ' . $shareRegister->current_share_balance . ' shares, Requested: ' . $requiredShares . ' shares');
        }
    }

    /**
     * Generate reference number
     *
     * @return string
     */
    protected function generateReferenceNumber()
    {
        return 'SR' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create redemption record
     *
     * @param array $data
     * @param object $memberDetails
     * @param string $referenceNumber
     * @return int
     */
    protected function createRedemptionRecord(array $data, object $memberDetails, string $referenceNumber)
    {
        $methodId = uniqid('create_redemption_');

        try {
            Log::info("[$methodId] Starting createRedemptionRecord", [
                'reference_number' => $referenceNumber,
                'client_number' => $data['client_number'] ?? 'NOT_PROVIDED',
                'product_id' => $data['product_id'] ?? 'NOT_PROVIDED',
                'number_of_shares' => $data['number_of_shares'] ?? 'NOT_PROVIDED',
                'price_per_share' => $data['price_per_share'] ?? 'NOT_PROVIDED',
                'share_account' => $data['share_account'] ?? 'NOT_PROVIDED',
                'linked_savings_account' => $data['linked_savings_account'] ?? 'NOT_PROVIDED',
                'total_value' => $data['total_value'] ?? 'NOT_PROVIDED',
                'member_id' => $memberDetails->id ?? 'NOT_PROVIDED',
                'user_id' => auth()->id()
            ]);

            // Prepare member name
            $memberName = trim($memberDetails->first_name . ' ' .
                              ($memberDetails->middle_name ?? '') . ' ' .
                              $memberDetails->last_name);

            // Prepare insertion data
            $insertData = [
                'reference_number' => $referenceNumber,
                'share_id' => $data['product_id'],
                'member' => $memberName,
                'product' => $data['product_id'],
                'account_number' => $data['share_account'],
                'price' => $data['price_per_share'],
                'branch' => auth()->user()->branch ?? null,
                'client_number' => $data['client_number'],
                'number_of_shares' => $data['number_of_shares'],
                'nominal_price' => $data['price_per_share'],
                'total_value' => $data['total_value'],
                'linked_savings_account' => $data['linked_savings_account'],
                'linked_share_account' => $data['share_account'],
                'transaction_type' => 'REDEMPTION',
                'status' => 'PENDING',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ];

            Log::info("[$methodId] Insertion data prepared", ['insert_data' => $insertData]);

            // Insert into share_redemptions table
            $redemptionId = DB::table('share_redemptions')->insertGetId($insertData);

            Log::info("[$methodId] Database insertion completed successfully", [
                'redemption_id' => $redemptionId,
                'reference_number' => $referenceNumber
            ]);

            return $redemptionId;

        } catch (\Exception $e) {
            Log::error("[$methodId] Error in createRedemptionRecord", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Create approval request
     *
     * @param array $data
     * @param object $memberDetails
     * @param object $productDetails
     * @param int $redemptionId
     */
    protected function createApprovalRequest(array $data, object $memberDetails, object $productDetails, int $redemptionId)
    {
        $methodId = uniqid('create_approval_');

        try {
            Log::info("[$methodId] Starting createApprovalRequest", [
                'redemption_id' => $redemptionId,
                'client_number' => $data['client_number'],
                'product_id' => $data['product_id']
            ]);

            $memberName = trim($memberDetails->first_name . ' ' .
                              ($memberDetails->middle_name ?? '') . ' ' .
                              $memberDetails->last_name);

            // Prepare edit package
            $editPackage = [
                'type' => 'share_redemption',
                'reference_number' => $data['reference_number'] ?? '2000',
                'member_id' => $data['client_number'],
                'member_name' => $memberName,
                'product_id' => $data['product_id'],
                'product_name' => $productDetails->product_name,
                'number_of_shares' => $data['number_of_shares'],
                'nominal_price' => $data['price_per_share'],
                'total_amount' => $data['total_value'],
                'linked_savings_account' => $data['linked_savings_account'],
                'share_account' => $data['share_account'],
                'status' => 'PENDING',
                'created_by' => auth()->id()
            ];

            // Create approval record
            $approvalData = [
                'process_name' => 'share_redemption',
                'process_description' => 'Share Redemption - ' . $data['number_of_shares'] . ' shares from ' . $memberName,
                'approval_process_description' => 'Share redemption approval required',
                'process_code' => 'SHARE_RED',
                'process_id' => $redemptionId,
                'process_status' => 'PENDING',
                'user_id' => auth()->id() ?? 1,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($editPackage)
            ];

            $approval = approvals::create($approvalData);

            Log::info("[$methodId] Approval record created successfully", [
                'approval_id' => $approval->id,
                'redemption_id' => $redemptionId
            ]);

        } catch (\Exception $e) {
            Log::error("[$methodId] Error in createApprovalRequest", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process approved share redemption
     *
     * @param object $approval
     * @return array
     */
    public function processApprovedRedemption($approval)
    {
        $methodId = uniqid('process_approved_');

        try {
            Log::info("[$methodId] Starting processApprovedRedemption", [
                'approval_id' => $approval->id,
                'process_id' => $approval->process_id
            ]);

            // Get edit package (already decoded by Laravel model cast)
            $editPackage = is_array($approval->edit_package)
                ? $approval->edit_package
                : json_decode($approval->edit_package, true);

            if (!is_array($editPackage)) {
                throw new \Exception('Invalid edit package format');
            }

            // Get product and member details
            $shareProduct = $this->getProductDetails($editPackage['product_id']);
            $member = $this->getMemberDetails($editPackage['member_id']);

            if (!$shareProduct || !$member) {
                throw new \Exception('Product or member not found');
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Update share register (reduce shares)
                Log::info("[$methodId] Updating share register");
                $this->updateShareRegister($editPackage, $shareProduct, $member, $approval);

                // Update redemption status
                Log::info("[$methodId] Updating redemption status");
                DB::table('share_redemptions')
                    ->where('id', $approval->process_id)
                    ->update([
                        'status' => 'COMPLETED',
                        'updated_at' => now()
                    ]);

                // Update available shares in sub_products (increase available)
                Log::info("[$methodId] Updating available shares");
                DB::table('sub_products')
                    ->where('id', $shareProduct->id)
                    ->update([
                        'shares_allocated' => DB::raw('CAST(shares_allocated AS INTEGER) - ' . $editPackage['number_of_shares']),
                        'available_shares' => DB::raw('CAST(available_shares AS INTEGER) + ' . $editPackage['number_of_shares']),
                        'updated_at' => now()
                    ]);

                // Process payment (reverse: share account to savings account)
                if (!empty($editPackage['linked_savings_account'])) {
                    Log::info("[$methodId] Processing payment");
                    $this->processPayment($editPackage, $shareProduct);
                }

                DB::commit();
                Log::info("[$methodId] Database transaction committed successfully");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("[$methodId] Error during database operations", [
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

            Log::info("[$methodId] Share redemption processed successfully");

            return [
                'success' => true,
                'message' => 'Share redemption processed successfully'
            ];

        } catch (\Exception $e) {
            Log::error("[$methodId] Critical error in processApprovedRedemption", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error processing share redemption: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update share register (reduce shares)
     *
     * @param array $editPackage
     * @param object $shareProduct
     * @param object $member
     * @param object $approval
     */
    protected function updateShareRegister(array $editPackage, object $shareProduct, object $member, object $approval)
    {
        $shareRegister = DB::table('share_registers')
            ->where('member_id', $member->client_number)
            ->where('product_id', $shareProduct->id)
            ->first();

        if (!$shareRegister) {
            throw new \Exception('Share register not found for this member');
        }

        // Calculate new balance
        $newBalance = $shareRegister->current_share_balance - $editPackage['number_of_shares'];

        if ($newBalance < 0) {
            throw new \Exception('Cannot redeem more shares than owned');
        }

        // Update share register - reduce shares
        DB::table('share_registers')
            ->where('id', $shareRegister->id)
            ->update([
                'current_share_balance' => $newBalance,
                'total_share_value' => DB::raw('total_share_value - (' . $editPackage['number_of_shares'] . ' * nominal_price)'),
                'last_activity_date' => now(),
                'last_transaction_type' => 'REDEMPTION',
                'last_transaction_reference' => $approval->reference_number ?? 'SR' . time(),
                'last_transaction_date' => now(),
                'updated_by' => $approval->created_by ?? auth()->id(),
                'updated_at' => now()
            ]);
    }

    /**
     * Process payment transaction (reverse of issuance)
     *
     * @param array $editPackage
     * @param object $shareProduct
     */
    protected function processPayment(array $editPackage, object $shareProduct)
    {
        $totalAmount = $editPackage['number_of_shares'] * $shareProduct->nominal_price;

        // Reverse: FROM share account TO savings account
        $transactionData = [
            'first_account' => $editPackage['linked_savings_account'],
            'second_account' => $editPackage['share_account'],
            'amount' => $totalAmount,
            'narration' => 'Share redemption - ' . $editPackage['number_of_shares'] . ' shares',
            'action' => 'share_redemption'
        ];

        $result = $this->transactionService->postTransaction($transactionData);

        if ($result['status'] !== 'success') {
            throw new \Exception('Failed to post transaction: ' . ($result['message'] ?? 'Unknown error'));
        }
    }
}
