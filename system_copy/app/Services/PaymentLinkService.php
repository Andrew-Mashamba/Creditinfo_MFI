<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class PaymentLinkService
{
    private $baseUrl;
    private $apiKey;
    private $apiSecret;
    private $frontendUrl;
    
    public function __construct()
    {
        $this->baseUrl = config('services.payment_gateway.base_url', 'http://172.240.241.188');
        $this->apiKey = config('services.payment_gateway.api_key', 'sample_client_key_ABC123DEF456');
        $this->apiSecret = config('services.payment_gateway.api_secret', 'sample_client_secret_XYZ789GHI012');
        $this->frontendUrl = config('services.payment_gateway.frontend_url', config('services.payment_gateway.base_url', 'http://172.240.241.188'));
    }
    
    /**
     * Generate a universal payment link
     * 
     * @param array $data Payment link data
     * @return array Response containing payment URL and other details
     * @throws Exception
     */
    public function generateUniversalPaymentLink(array $data)
    {
        try {
            $url = $this->baseUrl . '/api/payment-links/generate-universal';
            
            // Prepare request data
            $requestData = $this->prepareRequestData($data);
            
            // Prepare headers with authentication
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-API-Key' => $this->apiKey,
                'X-API-Secret' => $this->apiSecret
            ];
            
            // Log request details for debugging
            Log::info('Payment link API request', [
                'url' => $url,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-API-Key' => substr($this->apiKey, 0, 10) . '...', // Log partial key for security
                    'X-API-Secret' => substr($this->apiSecret, 0, 10) . '...' // Log partial secret for security
                ],
                'payload_size' => strlen(json_encode($requestData)),
                'customer_reference' => $requestData['customer_reference'] ?? null
            ]);
            
            // Make API request with required authentication headers
            $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($url, $requestData);
            
            // Check if request was successful
            if (!$response->successful()) {
                Log::error('Payment link generation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'request' => $requestData
                ]);
                
                throw new Exception('Failed to generate payment link: ' . $response->body());
            }
            
            $responseData = $response->json();

            // Construct local payment URL using configured frontend URL
            if (isset($responseData['data']['payment_url'])) {
                $originalUrl = $responseData['data']['payment_url'];

                // Extract payment reference from the original URL (e.g., LN202511061953)
                if (preg_match('/\/pay\/([^\/\?]+)/', $originalUrl, $matches)) {
                    $paymentReference = $matches[1];
                    $responseData['data']['payment_url'] = rtrim($this->frontendUrl, '/') . '/pay/' . $paymentReference;
                }
            }

            // Log successful response
            Log::info('Payment link generated successfully', [
                'link_id' => $responseData['data']['link_id'] ?? null,
                'payment_url' => $responseData['data']['payment_url'] ?? null,
                'original_payment_url' => $originalUrl ?? null,
                'customer_reference' => $requestData['customer_reference'] ?? null
            ]);

            return $responseData;
            
        } catch (Exception $e) {
            Log::error('Payment link generation exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Generate payment link and return only the payment URL
     * 
     * @param array $data Payment link data
     * @return string Payment URL
     * @throws Exception
     */
    public function getPaymentUrl(array $data)
    {
        $response = $this->generateUniversalPaymentLink($data);
        
        if (!isset($response['data']['payment_url'])) {
            throw new Exception('Payment URL not found in response');
        }
        
        return $response['data']['payment_url'];
    }
    
    /**
     * Generate payment link for member shares and deposits
     * 
     * @param string $memberReference Member reference/ID
     * @param string $memberName Member full name
     * @param string $memberPhone Member phone number
     * @param string $memberEmail Member email address
     * @param float $sharesAmount Amount for mandatory shares
     * @param float $depositsAmount Amount for deposits
     * @param array $options Additional options
     * @return array Response with payment details
     */
    public function generateMemberPaymentLink(
        string $memberReference,
        string $memberName,
        string $memberPhone,
        string $memberEmail,
        float $sharesAmount,
        float $depositsAmount,
        array $options = []
    ) {
        $items = [];
        
        // Add shares item if amount > 0
        if ($sharesAmount > 0) {
            $items[] = [
                'type' => 'service',
                'product_service_reference' => $options['shares_reference'] ?? 'SHARES_01',
                'product_service_name' => $options['shares_name'] ?? 'MANDATORY SHARES',
                'amount' => $sharesAmount,
                'is_required' => true,
                'allow_partial' => false
            ];
        }
        
        // Add deposits item if amount > 0
        if ($depositsAmount > 0) {
            $items[] = [
                'type' => 'service',
                'product_service_reference' => $options['deposits_reference'] ?? 'DEPOSITS_07',
                'product_service_name' => $options['deposits_name'] ?? 'DEPOSITS',
                'amount' => $depositsAmount,
                'is_required' => true,
                'allow_partial' => true
            ];
        }
        
        if (empty($items)) {
            throw new Exception('At least one payment item (shares or deposits) must have amount > 0');
        }
        
        $data = [
            'description' => $options['description'] ?? 'Saccos services',
            'target' => 'individual',
            'customer_reference' => $memberReference,
            'customer_name' => $memberName,
            'customer_phone' => $this->formatPhoneNumber($memberPhone),
            'customer_email' => $memberEmail,
            'expires_at' => $options['expires_at'] ?? Carbon::now()->addDays(7)->toIso8601String(),
            'items' => $items
        ];
        
        return $this->generateUniversalPaymentLink($data);
    }
    
    /**
     * Generate payment link for loan repayment
     * 
     * @param string $loanReference Loan reference/ID
     * @param string $memberName Member full name
     * @param string $memberPhone Member phone number
     * @param float $amount Repayment amount
     * @param array $options Additional options
     * @return string Payment URL
     */
    public function generateLoanPaymentUrl(
        string $loanReference,
        string $memberName,
        string $memberPhone,
        float $amount,
        array $options = []
    ) {
        $data = [
            'description' => $options['description'] ?? 'Loan repayment',
            'target' => 'individual',
            'customer_reference' => $loanReference,
            'customer_name' => $memberName,
            'customer_phone' => $this->formatPhoneNumber($memberPhone),
            'customer_email' => $options['email'] ?? null,
            'expires_at' => $options['expires_at'] ?? Carbon::now()->addDays(3)->toIso8601String(),
            'items' => [
                [
                    'type' => 'service',
                    'product_service_reference' => 'LOAN_' . $loanReference,
                    'product_service_name' => 'LOAN REPAYMENT',
                    'amount' => $amount,
                    'is_required' => true,
                    'allow_partial' => $options['allow_partial'] ?? true
                ]
            ]
        ];
        
        return $this->getPaymentUrl($data);
    }
    
    /**
     * Generate payment link for loan installments with schedule data
     * 
     * @param int $loanId Loan ID from database
     * @param object $client Client/member object with details
     * @param array $schedules Array of loan schedule objects from loans_schedules table
     * @param array $options Additional options (description, expires_at, etc.)
     * @return array Full response with payment link details
     * @throws Exception
     */
    public function generateLoanInstallmentsPaymentLink($loanId, $client, $schedules, array $options = [])
    {
        try {
            Log::info('Generating payment link for loan installments', [
                'loan_id' => $loanId,
                'client_number' => $client->client_number ?? null,
                'schedules_count' => count($schedules)
            ]);
            
            // Build items array from loan schedules
            $items = [];
            $lastDueDate = null;
            
            $installmentNumber = 1; // Track actual installment number for display

            foreach ($schedules as $index => $schedule) {
                // SKIP first interest installment (already paid during disbursement)
                if (isset($schedule->completion_status) && $schedule->completion_status === 'PAID') {
                    Log::info('Skipping first interest installment from payment link', [
                        'loan_id' => $loanId,
                        'schedule_id' => $schedule->id,
                        'status' => $schedule->completion_status
                    ]);
                    continue;
                }

                // Calculate total amount for this installment
                $installmentAmount = ($schedule->principle ?? 0) +
                                    ($schedule->interest ?? 0) +
                                    ($schedule->penalties ?? 0) +
                                    ($schedule->charges ?? 0);

                // Skip if amount is zero
                if ($installmentAmount <= 0) {
                    continue;
                }

                // Format due date for display
                $dueDate = 'TBA';
                if ($schedule->installment_date) {
                    try {
                        $dueDate = Carbon::parse($schedule->installment_date)->format('d/m/Y');
                    } catch (\Exception $e) {
                        $dueDate = $schedule->installment_date;
                    }
                }

                // Create descriptive name with installment number and due date
                $serviceName = sprintf('Installment #%d - Due: %s', $installmentNumber, $dueDate);

                $items[] = [
                    'type' => 'service',
                    'product_service_reference' => (string) $schedule->id, // Schedule ID as reference
                    'product_service_name' => $serviceName,
                    'amount' => round($installmentAmount, 2),
                    'is_required' => ($installmentNumber === 1), // First unpaid installment is required
                    'allow_partial' => true
                ];

                // Track last due date for expiry
                if ($schedule->installment_date) {
                    $lastDueDate = $schedule->installment_date;
                }

                $installmentNumber++; // Increment for next installment
            }
            
            if (empty($items)) {
                throw new Exception('No valid installments found for payment link generation');
            }
            
            // Determine expiry date (end of last installment due date or provided option)
            $expiresAt = $options['expires_at'] ?? null;
            if (!$expiresAt && $lastDueDate) {
                $expiresAt = Carbon::parse($lastDueDate)->endOfDay()->toIso8601String();
            } elseif (!$expiresAt) {
                $expiresAt = Carbon::now()->addMonths(12)->toIso8601String(); // Default to 12 months
            }
            
            // Build customer name
            $customerName = trim(
                ($client->first_name ?? '') . ' ' . 
                ($client->middle_name ?? '') . ' ' . 
                ($client->last_name ?? $client->present_surname ?? '')
            ) ?: 'SACCOS Member';
            
            // Prepare payment link data
            $data = [
                'description' => $options['description'] ?? 'SACCOS Loan Services - Loan ID: ' . $loanId,
                'target' => 'individual',
                'customer_reference' => $client->client_number,
                'customer_name' => $customerName,
                'customer_phone' => $this->formatPhoneNumber($client->phone_number ?? ''),
                'customer_email' => $client->email ?? null,
                'expires_at' => $expiresAt,
                'items' => $items
            ];
            
            // Generate the payment link
            $response = $this->generateUniversalPaymentLink($data);
            
            Log::info('Loan installments payment link generated successfully', [
                'loan_id' => $loanId,
                'link_id' => $response['data']['link_id'] ?? null,
                'payment_url' => $response['data']['payment_url'] ?? null,
                'total_amount' => $response['data']['total_amount'] ?? null
            ]);
            
            return $response;
            
        } catch (Exception $e) {
            Log::error('Failed to generate loan installments payment link', [
                'loan_id' => $loanId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Prepare request data with validation
     * 
     * @param array $data Raw request data
     * @return array Prepared request data
     */
    private function prepareRequestData(array $data)
    {
        // Validate required fields
        $required = ['description', 'target', 'customer_reference', 'customer_name', 'customer_phone', 'items'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Required field '{$field}' is missing");
            }
        }
        
        // Validate items
        if (!is_array($data['items']) || empty($data['items'])) {
            throw new Exception('Items must be a non-empty array');
        }
        
        foreach ($data['items'] as $index => $item) {
            $itemRequired = ['type', 'product_service_reference', 'product_service_name', 'amount'];
            foreach ($itemRequired as $field) {
                if (!isset($item[$field])) {
                    throw new Exception("Item {$index}: Required field '{$field}' is missing");
                }
            }
        }
        
        // Format phone number if needed
        if (isset($data['customer_phone'])) {
            $data['customer_phone'] = $this->formatPhoneNumber($data['customer_phone']);
        }
        
        // Set default expires_at if not provided
        if (!isset($data['expires_at'])) {
            $data['expires_at'] = Carbon::now()->addDays(7)->toIso8601String();
        }
        
        return $data;
    }
    
    /**
     * Format phone number to required format
     * 
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    private function formatPhoneNumber(string $phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if not present
        if (strlen($phone) === 9) {
            $phone = '255' . $phone;
        } elseif (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            $phone = '255' . substr($phone, 1);
        }
        
        return $phone;
    }
    
    /**
     * Check payment status
     * 
     * @param string $linkId Payment link ID
     * @return array Payment status details
     */
    public function checkPaymentStatus(string $linkId)
    {
        try {
            $url = $this->baseUrl . '/api/payment-links/' . $linkId . '/status';
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-API-Key' => $this->apiKey,
                'X-API-Secret' => $this->apiSecret
            ])
            ->timeout(30)
            ->get($url);
            
            if (!$response->successful()) {
                throw new Exception('Failed to check payment status: ' . $response->body());
            }
            
            return $response->json();
            
        } catch (Exception $e) {
            Log::error('Payment status check failed', [
                'link_id' => $linkId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}