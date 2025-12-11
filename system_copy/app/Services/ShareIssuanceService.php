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

class ShareIssuanceService
{
    protected $transactionService;

    public function __construct(TransactionPostingService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Issue shares to a member
     *
     * @param array $data
     * @return array
     */
    public function issueShares(array $data)
    {
        $processId = uniqid('share_issuance_');
        
        try {
            Log::info("[$processId] Starting share issuance process", [
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

            // Validate account balance
            Log::info("[$processId] Validating account balance", [
                'linked_savings_account' => $data['linked_savings_account'],
                'required_amount' => $data['total_value']
            ]);
            try {
                $this->validateAccountBalance($data['linked_savings_account'], $data['total_value']);
                Log::info("[$processId] Account balance validation passed");
            } catch (\Exception $e) {
                Log::error("[$processId] Account balance validation failed", [
                    'linked_savings_account' => $data['linked_savings_account'],
                    'required_amount' => $data['total_value'],
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id()
                ]);
                throw $e;
            }

            // Validate share limits
            Log::info("[$processId] Validating share limits");
            try {
                $this->validateShareLimits($data, $productDetails, $memberDetails);
                Log::info("[$processId] Share limits validation passed");
            } catch (\Exception $e) {
                Log::error("[$processId] Share limits validation failed", [
                    'client_number' => $data['client_number'],
                    'product_id' => $data['product_id'],
                    'number_of_shares' => $data['number_of_shares'],
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

                // Create share issuance record
                Log::info("[$processId] Creating share issuance record");
                $issuanceId = $this->createIssuanceRecord($data, $memberDetails, $referenceNumber);
                Log::info("[$processId] Share issuance record created", [
                    'issuance_id' => $issuanceId,
                    'reference_number' => $referenceNumber
                ]);

                // Create approval request
                Log::info("[$processId] Creating approval request");
                $this->createApprovalRequest($data, $memberDetails, $productDetails, $issuanceId);
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
                    'issuance_id' => $issuanceId ?? 'NOT_CREATED'
                ]);
                DB::rollBack();
                Log::info("[$processId] Database transaction rolled back");
                throw $e;
            }

            Log::info("[$processId] Share issuance process completed successfully", [
                'issuance_id' => $issuanceId,
                'reference_number' => $referenceNumber,
                'client_number' => $data['client_number'],
                'user_id' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Share issuance request submitted successfully.',
                'issuance_id' => $issuanceId,
                'reference_number' => $referenceNumber
            ];

        } catch (ValidationException $e) {
            Log::error("[$processId] Share issuance validation failed", [
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
            Log::error("[$processId] Critical error in share issuance process", [
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
            throw new \Exception('Selected savings account not found.');
        }

        if ($account->balance < $requiredAmount) {
            throw new \Exception('Insufficient balance in the selected savings account.');
        }
    }

    /**
     * Validate share limits
     *
     * @param array $data
     * @param object $product
     * @param object $member
     * @throws \Exception
     */
    protected function validateShareLimits(array $data, object $product, object $member)
    {
        // Check maximum shares per member
        if ($data['number_of_shares'] > $product->shares_per_member) {
            throw new \Exception('Number of shares exceeds maximum allowed per member.');
        }

        // Check available shares in product
        if ($data['number_of_shares'] > $product->available_shares) {
            throw new \Exception('Number of shares exceeds available shares in the product.');
        }

        // Check minimum required shares
        if ($data['number_of_shares'] < $product->min_balance) {
            throw new \Exception('Number of shares is below minimum required.');
        }

        // Check if member already has shares and validate against limits
        // $existingShares = DB::table('share_registers')
        //     ->where('member_id', $member->client_number)
        //     ->where('product_id', $product->id)
        //     ->sum('current_share_balance');

        // if (($existingShares + $data['number_of_shares']) > $product->shares_per_member) {
        //     throw new \Exception('Total shares would exceed maximum allowed per member.');
        // }
    }

    /**
     * Generate reference number
     *
     * @return string
     */
    protected function generateReferenceNumber()
    {
        return 'SH' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create issuance record
     *
     * @param array $data
     * @param object $memberDetails
     * @param string $referenceNumber
     * @return int
     */
    protected function createIssuanceRecord(array $data, object $memberDetails, string $referenceNumber)
    {
        $methodId = uniqid('create_issuance_');
        
        try {
            Log::info("[$methodId] Starting createIssuanceRecord", [
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

            // Validate required parameters
            if (empty($referenceNumber)) {
                Log::error("[$methodId] Reference number is empty");
                throw new \Exception('Reference number is required');
            }

            if (empty($data['client_number'])) {
                Log::error("[$methodId] Client number is empty");
                throw new \Exception('Client number is required');
            }

            if (empty($data['product_id'])) {
                Log::error("[$methodId] Product ID is empty");
                throw new \Exception('Product ID is required');
            }

            // Prepare member name
            Log::info("[$methodId] Preparing member name");
            $memberName = trim($memberDetails->first_name . ' ' . 
                              ($memberDetails->middle_name ?? '') . ' ' . 
                              $memberDetails->last_name);
            
            Log::info("[$methodId] Member name prepared", ['member_name' => $memberName]);

            // Prepare insertion data
            Log::info("[$methodId] Preparing insertion data");
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
                'status' => 'PENDING',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ];

            Log::info("[$methodId] Insertion data prepared", [
                'insert_data' => $insertData,
                'table' => 'issued_shares'
            ]);

            // Perform database insertion
            Log::info("[$methodId] Executing database insertion");
            $issuanceId = DB::table('issued_shares')->insertGetId($insertData);
            
            Log::info("[$methodId] Database insertion completed successfully", [
                'issuance_id' => $issuanceId,
                'reference_number' => $referenceNumber,
                'client_number' => $data['client_number']
            ]);

            return $issuanceId;

        } catch (\Exception $e) {
            Log::error("[$methodId] Error in createIssuanceRecord", [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'full_trace' => $e->getTraceAsString(),
                'reference_number' => $referenceNumber ?? 'NOT_PROVIDED',
                'client_number' => $data['client_number'] ?? 'NOT_PROVIDED',
                'product_id' => $data['product_id'] ?? 'NOT_PROVIDED',
                'number_of_shares' => $data['number_of_shares'] ?? 'NOT_PROVIDED',
                'price_per_share' => $data['price_per_share'] ?? 'NOT_PROVIDED',
                'share_account' => $data['share_account'] ?? 'NOT_PROVIDED',
                'linked_savings_account' => $data['linked_savings_account'] ?? 'NOT_PROVIDED',
                'total_value' => $data['total_value'] ?? 'NOT_PROVIDED',
                'member_id' => $memberDetails->id ?? 'NOT_PROVIDED',
                'user_id' => auth()->id(),
                'input_data' => $data,
                'member_details' => $memberDetails,
                'exception_class' => get_class($e)
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
     * @param int $issuanceId
     */
    protected function createApprovalRequest(array $data, object $memberDetails, object $productDetails, int $issuanceId)
    {
        $methodId = uniqid('create_approval_');
        
        try {
            Log::info("[$methodId] Starting createApprovalRequest", [
                'issuance_id' => $issuanceId,
                'client_number' => $data['client_number'] ?? 'NOT_PROVIDED',
                'product_id' => $data['product_id'] ?? 'NOT_PROVIDED',
                'number_of_shares' => $data['number_of_shares'] ?? 'NOT_PROVIDED',
                'price_per_share' => $data['price_per_share'] ?? 'NOT_PROVIDED',
                'total_value' => $data['total_value'] ?? 'NOT_PROVIDED',
                'linked_savings_account' => $data['linked_savings_account'] ?? 'NOT_PROVIDED',
                'share_account' => $data['share_account'] ?? 'NOT_PROVIDED',
                'member_id' => $memberDetails->id ?? 'NOT_PROVIDED',
                'product_name' => $productDetails->product_name ?? 'NOT_PROVIDED',
                'user_id' => auth()->id()
            ]);

            // Validate required parameters
            if (empty($issuanceId)) {
                Log::error("[$methodId] Issuance ID is empty");
                throw new \Exception('Issuance ID is required');
            }

            if (empty($data['client_number'])) {
                Log::error("[$methodId] Client number is empty");
                throw new \Exception('Client number is required');
            }

            if (empty($data['product_id'])) {
                Log::error("[$methodId] Product ID is empty");
                throw new \Exception('Product ID is required');
            }

            if (empty($productDetails->product_name)) {
                Log::error("[$methodId] Product name is empty");
                throw new \Exception('Product name is required');
            }

            // Prepare member name
            Log::info("[$methodId] Preparing member name");
            $memberName = trim($memberDetails->first_name . ' ' . 
                              ($memberDetails->middle_name ?? '') . ' ' . 
                              $memberDetails->last_name);
            
            Log::info("[$methodId] Member name prepared", ['member_name' => $memberName]);

            // Prepare edit package
            Log::info("[$methodId] Preparing edit package");
            $editPackage = [
                'type' => 'share_issuance',
                'reference_number' => $data['reference_number'] ?? '1000',
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

            Log::info("[$methodId] Edit package prepared", [
                'edit_package' => $editPackage,
                'edit_package_json' => json_encode($editPackage)
            ]);

            // Prepare approval data
            Log::info("[$methodId] Preparing approval data");
            $approvalData = [
                'process_name' => 'share_issuance',
                'process_description' => ' Mandatory Share Issuance - ' . $data['number_of_shares'] . ' shares to ' . $memberName,
                'approval_process_description' => 'Share issuance approval required',
                'process_code' => 'SHARE_ISS',
                'process_id' => $issuanceId,
                'process_status' => 'PENDING',
                'user_id' => auth()->id() ?? 1,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($editPackage)
            ];

            Log::info("[$methodId] Approval data prepared", [
                'approval_data' => $approvalData,
                'table' => 'approvals'
            ]);

            // Create approval record
            Log::info("[$methodId] Creating approval record");
            $approval = approvals::create($approvalData);
            
            Log::info("[$methodId] Approval record created successfully", [
                'approval_id' => $approval->id,
                'issuance_id' => $issuanceId,
                'client_number' => $data['client_number'],
                'process_code' => 'SHARE_ISS'
            ]);

        } catch (\Exception $e) {
            Log::error("[$methodId] Error in createApprovalRequest", [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'full_trace' => $e->getTraceAsString(),
                'issuance_id' => $issuanceId ?? 'NOT_PROVIDED',
                'client_number' => $data['client_number'] ?? 'NOT_PROVIDED',
                'product_id' => $data['product_id'] ?? 'NOT_PROVIDED',
                'number_of_shares' => $data['number_of_shares'] ?? 'NOT_PROVIDED',
                'price_per_share' => $data['price_per_share'] ?? 'NOT_PROVIDED',
                'total_value' => $data['total_value'] ?? 'NOT_PROVIDED',
                'linked_savings_account' => $data['linked_savings_account'] ?? 'NOT_PROVIDED',
                'share_account' => $data['share_account'] ?? 'NOT_PROVIDED',
                'member_id' => $memberDetails->id ?? 'NOT_PROVIDED',
                'product_name' => $productDetails->product_name ?? 'NOT_PROVIDED',
                'user_id' => auth()->id(),
                'input_data' => $data,
                'member_details' => $memberDetails,
                'product_details' => $productDetails,
                'exception_class' => get_class($e)
            ]);
            
            throw $e;
        }
    }

    /**
     * Process approved share issuance
     *
     * @param object $approval
     * @return array
     */
    public function processApprovedIssuance($approval)
    {
        $methodId = uniqid('process_approved_');
        
        try {
            Log::info("[$methodId] Starting processApprovedIssuance", [
                'approval_id' => $approval->id ?? 'NOT_PROVIDED',
                'process_id' => $approval->process_id ?? 'NOT_PROVIDED',
                'process_code' => $approval->process_code ?? 'NOT_PROVIDED',
                'process_status' => $approval->process_status ?? 'NOT_PROVIDED',
                'approval_status' => $approval->approval_status ?? 'NOT_PROVIDED',
                'user_id' => auth()->id()
            ]);

            // Validate approval object
            if (!$approval) {
                Log::error("[$methodId] Approval object is null");
                throw new \Exception('Approval object is required');
            }

            if (empty($approval->edit_package)) {
                Log::error("[$methodId] Edit package is empty", ['approval_id' => $approval->id]);
                throw new \Exception('Edit package is required');
            }

            // Decode edit package
            Log::info("[$methodId] Decoding edit package");
            $editPackage = json_decode($approval->edit_package, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("[$methodId] Failed to decode edit package JSON", [
                    'approval_id' => $approval->id,
                    'json_error' => json_last_error_msg(),
                    'edit_package_raw' => $approval->edit_package
                ]);
                throw new \Exception('Invalid edit package JSON format');
            }

            Log::info("[$methodId] Edit package decoded successfully", [
                'edit_package' => $editPackage,
                'approval_id' => $approval->id
            ]);

            // Validate edit package structure
            Log::info("[$methodId] Validating edit package structure");
            if (!isset($editPackage['product_id'])) {
                Log::error("[$methodId] Product ID missing from edit package", [
                    'edit_package' => $editPackage,
                    'approval_id' => $approval->id
                ]);
                throw new \Exception('Invalid edit package format - product_id missing');
            }

            if (!isset($editPackage['member_id'])) {
                Log::error("[$methodId] Member ID missing from edit package", [
                    'edit_package' => $editPackage,
                    'approval_id' => $approval->id
                ]);
                throw new \Exception('Invalid edit package format - member_id missing');
            }

            if (!isset($editPackage['number_of_shares'])) {
                Log::error("[$methodId] Number of shares missing from edit package", [
                    'edit_package' => $editPackage,
                    'approval_id' => $approval->id
                ]);
                throw new \Exception('Invalid edit package format - number_of_shares missing');
            }

            Log::info("[$methodId] Edit package validation passed");

            // Get share product details
            Log::info("[$methodId] Fetching share product details", ['product_id' => $editPackage['product_id']]);
            $shareProduct = $this->getProductDetails($editPackage['product_id']);
            if (!$shareProduct) {
                Log::error("[$methodId] Share product not found", [
                    'product_id' => $editPackage['product_id'],
                    'approval_id' => $approval->id
                ]);
                throw new \Exception('Share product not found');
            }
            Log::info("[$methodId] Share product details retrieved", [
                'product_id' => $shareProduct->id,
                'product_name' => $shareProduct->product_name ?? 'N/A'
            ]);

            // Get member details
            Log::info("[$methodId] Fetching member details", ['member_id' => $editPackage['member_id']]);
            $member = $this->getMemberDetails($editPackage['member_id']);
            if (!$member) {
                Log::error("[$methodId] Member not found", [
                    'member_id' => $editPackage['member_id'],
                    'approval_id' => $approval->id
                ]);
                throw new \Exception('Member not found');
            }
            Log::info("[$methodId] Member details retrieved", [
                'member_id' => $member->client_number,
                'member_name' => $member->first_name . ' ' . $member->last_name ?? 'N/A'
            ]);

            // Start database transaction
            Log::info("[$methodId] Starting database transaction");
            DB::beginTransaction();

            try {
                // Create or update share register
                Log::info("[$methodId] Updating share register");
                $this->updateShareRegister($editPackage, $shareProduct, $member, $approval);
                Log::info("[$methodId] Share register updated successfully");

                // Update issued shares status
                Log::info("[$methodId] Updating issued shares status", ['process_id' => $approval->process_id]);
                $updatedRows = DB::table('issued_shares')
                    ->where('id', $approval->process_id)
                    ->update([
                        'status' => 'COMPLETED',
                        'updated_at' => now()
                    ]);
                
                Log::info("[$methodId] Issued shares status updated", [
                    'process_id' => $approval->process_id,
                    'updated_rows' => $updatedRows
                ]);

                // Update available shares in sub_products
                Log::info("[$methodId] Updating available shares in sub_products", [
                    'product_id' => $shareProduct->id,
                    'shares_to_allocate' => $editPackage['number_of_shares']
                ]);
                
                $updatedProductRows = DB::table('sub_products')
                    ->where('id', $shareProduct->id)
                    ->update([
                        'shares_allocated' => DB::raw('CAST(shares_allocated AS INTEGER) + ' . $editPackage['number_of_shares']),
                        'available_shares' => DB::raw('CAST(available_shares AS INTEGER) - ' . $editPackage['number_of_shares']),
                        'updated_at' => now()
                    ]);
                
                Log::info("[$methodId] Sub products updated", [
                    'product_id' => $shareProduct->id,
                    'updated_rows' => $updatedProductRows
                ]);

                // Process payment if linked to savings account
                if (!empty($editPackage['linked_savings_account'])) {
                    Log::info("[$methodId] Processing payment", [
                        'linked_savings_account' => $editPackage['linked_savings_account'],
                        'share_account' => $editPackage['share_account'] ?? 'N/A'
                    ]);
                    $this->processPayment($editPackage, $shareProduct);
                    Log::info("[$methodId] Payment processed successfully");
                } else {
                    Log::info("[$methodId] No linked savings account, skipping payment processing");
                }

                // Commit transaction
                DB::commit();
                Log::info("[$methodId] Database transaction committed successfully");

            } catch (\Exception $e) {
                Log::error("[$methodId] Error during database operations", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'approval_id' => $approval->id,
                    'product_id' => $editPackage['product_id'] ?? 'NOT_PROVIDED',
                    'member_id' => $editPackage['member_id'] ?? 'NOT_PROVIDED',
                    'user_id' => auth()->id()
                ]);
                DB::rollBack();
                Log::info("[$methodId] Database transaction rolled back");
                throw $e;
            }

            Log::info("[$methodId] Share issuance processed successfully", [
                'approval_id' => $approval->id,
                'member_id' => $member->client_number,
                'shares' => $editPackage['number_of_shares'],
                'product_id' => $shareProduct->id
            ]);

            return [
                'success' => true,
                'message' => 'Share issuance processed successfully'
            ];

        } catch (\Exception $e) {
            Log::error("[$methodId] Critical error in processApprovedIssuance", [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'full_trace' => $e->getTraceAsString(),
                'approval_id' => $approval->id ?? 'NOT_PROVIDED',
                'process_id' => $approval->process_id ?? 'NOT_PROVIDED',
                'process_code' => $approval->process_code ?? 'NOT_PROVIDED',
                'process_status' => $approval->process_status ?? 'NOT_PROVIDED',
                'approval_status' => $approval->approval_status ?? 'NOT_PROVIDED',
                'edit_package_raw' => $approval->edit_package ?? 'NOT_PROVIDED',
                'user_id' => auth()->id(),
                'approval_object' => $approval,
                'exception_class' => get_class($e)
            ]);
            
            return [
                'success' => false,
                'message' => 'Error processing share issuance: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update share register
     *
     * @param array $editPackage
     * @param object $shareProduct
     * @param object $member
     * @param object $approval
     */
    protected function updateShareRegister(array $editPackage, object $shareProduct, object $member, object $approval)
    {
        $memberName = trim($member->first_name . ' ' . 
                          ($member->middle_name ?? '') . ' ' . 
                          $member->last_name);

        $shareRegister = DB::table('share_registers')
            ->where('member_id', $member->client_number)
            ->where('product_id', $shareProduct->id)
            ->first();

        if (!$shareRegister) {
            // Create new share register
            DB::table('share_registers')->insert([
                'branch_id' => $member->branch_id ?? 1,
                'member_id' => $member->client_number,
                'member_number' => $member->client_number,
                'member_name' => $memberName,
                'product_id' => $shareProduct->id,
                'product_name' => $shareProduct->product_name,
                'product_type' => $shareProduct->product_type,
                'share_account_number' => $editPackage['share_account'],
                'nominal_price' => $shareProduct->nominal_price,
                'current_price' => $shareProduct->nominal_price,
                'total_shares_issued' => $editPackage['number_of_shares'],
                'current_share_balance' => $editPackage['number_of_shares'],
                'total_share_value' => $editPackage['number_of_shares'] * $shareProduct->nominal_price,
                'linked_savings_account' => $editPackage['linked_savings_account'],
                'status' => 'ACTIVE',
                'opening_date' => now(),
                'last_activity_date' => now(),
                'last_transaction_type' => 'ISSUE',
                'last_transaction_reference' => $approval->reference_number ?? 'SH' . time(),
                'last_transaction_date' => now(),
                'created_by' => $approval->created_by ?? auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            // Update existing share register
            DB::table('share_registers')
                ->where('id', $shareRegister->id)
                ->update([
                    'total_shares_issued' => DB::raw('total_shares_issued + ' . $editPackage['number_of_shares']),
                    'current_share_balance' => DB::raw('current_share_balance + ' . $editPackage['number_of_shares']),
                    'total_share_value' => DB::raw('total_share_value + (' . $editPackage['number_of_shares'] . ' * nominal_price)'),
                    'last_activity_date' => now(),
                    'last_transaction_type' => 'ISSUE',
                    'last_transaction_reference' => $approval->reference_number ?? 'SH' . time(),
                    'last_transaction_date' => now(),
                    'updated_by' => $approval->created_by ?? auth()->id(),
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Process payment transaction
     *
     * @param array $editPackage
     * @param object $shareProduct
     */
    protected function processPayment(array $editPackage, object $shareProduct)
    {
        $totalAmount = $editPackage['number_of_shares'] * $shareProduct->nominal_price;
        
        $transactionData = [
            'first_account' => $editPackage['share_account'],
            'second_account' => $editPackage['linked_savings_account'],
            'amount' => $totalAmount,
            'narration' => 'Share purchase - ' . $editPackage['number_of_shares'] . ' shares',
            'action' => 'share_purchase'
        ];

        $result = $this->transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            throw new \Exception('Failed to post transaction: ' . ($result['message'] ?? 'Unknown error'));
        }
    }
}