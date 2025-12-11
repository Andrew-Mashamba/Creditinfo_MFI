<?php

namespace SitTests;

/**
 * Test Pass/Fail Criteria Configuration
 * 
 * Defines the criteria for determining if a test passes or fails
 */
class TestCriteria
{
    /**
     * Default criteria applicable to all tests
     */
    const DEFAULT_CRITERIA = [
        'max_response_time_ms' => 30000,  // 30 seconds
        'require_response' => true,
        'require_valid_http_code' => true,
        'acceptable_http_codes' => [200, 201, 202, 204],
        'require_json_response' => true,
        'require_success_indicator' => true
    ];
    
    /**
     * API-specific test criteria
     */
    const API_CRITERIA = [
        'BankTransactionService' => [
            'IFT Transaction' => [
                'expected_status_code' => 200,
                'required_fields' => ['status', 'transaction_id', 'message'],
                'success_field' => 'status',
                'success_value' => 'success',
                'max_response_time_ms' => 5000,
                'validate_transaction_id' => true
            ],
            'EFT Transaction' => [
                'expected_status_code' => 200,
                'required_fields' => ['status', 'transaction_id', 'message'],
                'success_field' => 'status',
                'success_value' => 'success',
                'max_response_time_ms' => 10000,
                'validate_transaction_id' => true
            ],
            'Mobile Transaction' => [
                'expected_status_code' => 200,
                'required_fields' => ['status', 'transaction_id', 'mobile_network'],
                'success_field' => 'status',
                'success_value' => 'success',
                'max_response_time_ms' => 8000,
                'validate_mobile_network' => true
            ],
            'Invalid Transaction Type' => [
                'expected_status_code' => 400,
                'is_negative_test' => true,
                'expected_error_message' => 'Invalid transaction type',
                'success_field' => 'status',
                'success_value' => 'error'
            ],
            'Connection Failure' => [
                'expected_status_code' => 500,
                'is_negative_test' => true,
                'allow_timeout' => true,
                'max_response_time_ms' => 60000
            ]
        ],
        
        'GEPG Gateway' => [
            'Bill Query' => [
                'expected_status_code' => 200,
                'required_xml_elements' => ['BillStsCode', 'BillStsDesc', 'CustCtrNum'],
                'success_element' => 'BillStsCode',
                'success_value' => '0000',
                'max_response_time_ms' => 10000,
                'validate_control_number' => true,
                'require_signature' => true
            ],
            'Bill Payment' => [
                'expected_status_code' => 200,
                'required_xml_elements' => ['ResultCode', 'ResultDesc', 'ChannelRef'],
                'success_element' => 'ResultCode',
                'success_value' => '0000',
                'max_response_time_ms' => 15000,
                'validate_payment_confirmation' => true
            ],
            'Status Check' => [
                'expected_status_code' => 200,
                'required_xml_elements' => ['TrxStatus', 'TrxStatusDesc'],
                'success_element' => 'TrxStatus',
                'success_value' => 'SUCCESS',
                'max_response_time_ms' => 5000
            ]
        ],
        
        'Luku Gateway' => [
            'Token Query' => [
                'expected_status_code' => 200,
                'required_fields' => ['status', 'data'],
                'required_data_fields' => ['meter_number', 'customer_name', 'tariff'],
                'success_field' => 'status',
                'success_value' => 'success',
                'max_response_time_ms' => 8000
            ],
            'Token Purchase' => [
                'expected_status_code' => 200,
                'required_fields' => ['status', 'data'],
                'required_data_fields' => ['token', 'units', 'amount', 'transaction_id'],
                'success_field' => 'status',
                'success_value' => 'success',
                'validate_token_format' => true,
                'max_response_time_ms' => 20000
            ],
            'Meter Validation' => [
                'expected_status_code' => 200,
                'required_fields' => ['status', 'valid'],
                'success_field' => 'status',
                'success_value' => 'success',
                'max_response_time_ms' => 3000
            ]
        ],
        
        'NBC SMS' => [
            'Single SMS' => [
                'expected_status_code' => 200,
                'required_fields' => ['status', 'message_id', 'reference'],
                'success_field' => 'status',
                'success_value' => 'success',
                'max_response_time_ms' => 5000,
                'validate_message_id' => true
            ],
            'Bulk SMS' => [
                'expected_status_code' => 200,
                'required_fields' => ['status', 'batch_id', 'total_messages', 'accepted'],
                'success_field' => 'status',
                'success_value' => 'success',
                'validate_batch_processing' => true,
                'max_response_time_ms' => 10000
            ],
            'SMS Status' => [
                'expected_status_code' => 200,
                'required_fields' => ['status', 'message_id', 'delivery_status'],
                'success_field' => 'status',
                'success_value' => 'success',
                'valid_delivery_statuses' => ['DELIVERED', 'PENDING', 'FAILED'],
                'max_response_time_ms' => 3000
            ],
            'Rate Limiting' => [
                'is_negative_test' => true,
                'expected_status_code' => 429,
                'required_fields' => ['status', 'message', 'retry_after'],
                'success_field' => 'status',
                'success_value' => 'error',
                'validate_rate_limit_headers' => true
            ]
        ],
        
        'AI Services' => [
            'Groq API' => [
                'expected_status_code' => 200,
                'required_fields' => ['id', 'object', 'created', 'model', 'choices'],
                'validate_ai_response' => true,
                'max_response_time_ms' => 30000,
                'min_response_length' => 1
            ],
            'OpenAI API' => [
                'expected_status_code' => 200,
                'required_fields' => ['id', 'object', 'created', 'model', 'choices'],
                'validate_ai_response' => true,
                'max_response_time_ms' => 60000,
                'min_response_length' => 1
            ],
            'Claude API' => [
                'expected_status_code' => 200,
                'required_fields' => ['id', 'type', 'role', 'content'],
                'validate_ai_response' => true,
                'max_response_time_ms' => 45000,
                'min_response_length' => 1
            ]
        ]
    ];
    
    /**
     * Evaluate if a test passes based on criteria
     * 
     * @param string $apiName
     * @param string $testName
     * @param array $testResult
     * @return array ['passed' => bool, 'details' => array]
     */
    public static function evaluate($apiName, $testName, $testResult)
    {
        $criteria = self::getCriteria($apiName, $testName);
        $evaluation = [
            'passed' => true,
            'criteria_checked' => [],
            'failures' => []
        ];
        
        // 1. Check if response was received
        if ($criteria['require_response'] ?? true) {
            if (!($testResult['response_received'] ?? false)) {
                $evaluation['passed'] = false;
                $evaluation['failures'][] = 'No response received from API';
            }
            $evaluation['criteria_checked'][] = 'Response Received';
        }
        
        // 2. Check HTTP status code
        if (isset($testResult['status_code'])) {
            if (isset($criteria['expected_status_code'])) {
                if ($testResult['status_code'] != $criteria['expected_status_code']) {
                    $evaluation['passed'] = false;
                    $evaluation['failures'][] = sprintf(
                        'Expected status code %d, got %d',
                        $criteria['expected_status_code'],
                        $testResult['status_code']
                    );
                }
                $evaluation['criteria_checked'][] = 'Status Code';
            }
        }
        
        // 3. Check response time
        if (isset($testResult['response_time_ms'])) {
            $maxTime = $criteria['max_response_time_ms'] ?? self::DEFAULT_CRITERIA['max_response_time_ms'];
            if ($testResult['response_time_ms'] > $maxTime) {
                $evaluation['passed'] = false;
                $evaluation['failures'][] = sprintf(
                    'Response time %dms exceeded max %dms',
                    $testResult['response_time_ms'],
                    $maxTime
                );
            }
            $evaluation['criteria_checked'][] = 'Response Time';
        }
        
        // 4. Check required fields
        if (isset($criteria['required_fields']) && isset($testResult['response_body'])) {
            foreach ($criteria['required_fields'] as $field) {
                if (!self::fieldExists($testResult['response_body'], $field)) {
                    $evaluation['passed'] = false;
                    $evaluation['failures'][] = "Required field '$field' not found in response";
                }
            }
            $evaluation['criteria_checked'][] = 'Required Fields';
        }
        
        // 5. Check success indicator
        if (isset($criteria['success_field']) && isset($testResult['response_body'])) {
            $actualValue = self::getFieldValue($testResult['response_body'], $criteria['success_field']);
            $expectedValue = $criteria['success_value'];
            
            if ($actualValue != $expectedValue) {
                $evaluation['passed'] = false;
                $evaluation['failures'][] = sprintf(
                    "Success field '%s' expected '%s', got '%s'",
                    $criteria['success_field'],
                    $expectedValue,
                    $actualValue
                );
            }
            $evaluation['criteria_checked'][] = 'Success Indicator';
        }
        
        // 6. For negative tests, check if it failed as expected
        if ($criteria['is_negative_test'] ?? false) {
            // For negative tests, we expect certain failures
            if (isset($criteria['expected_error_message'])) {
                $errorMessage = $testResult['error_message'] ?? '';
                if (stripos($errorMessage, $criteria['expected_error_message']) === false) {
                    $evaluation['passed'] = false;
                    $evaluation['failures'][] = sprintf(
                        "Expected error message containing '%s'",
                        $criteria['expected_error_message']
                    );
                }
            }
            $evaluation['criteria_checked'][] = 'Negative Test Validation';
        }
        
        // 7. Custom validations
        if (isset($criteria['validate_transaction_id']) && $criteria['validate_transaction_id']) {
            $txId = self::getFieldValue($testResult['response_body'] ?? [], 'transaction_id');
            if (empty($txId)) {
                $evaluation['passed'] = false;
                $evaluation['failures'][] = 'Transaction ID is missing or empty';
            }
            $evaluation['criteria_checked'][] = 'Transaction ID Validation';
        }
        
        return $evaluation;
    }
    
    /**
     * Get criteria for a specific test
     */
    private static function getCriteria($apiName, $testName)
    {
        $specific = self::API_CRITERIA[$apiName][$testName] ?? [];
        return array_merge(self::DEFAULT_CRITERIA, $specific);
    }
    
    /**
     * Check if a field exists in response data
     */
    private static function fieldExists($data, $field)
    {
        if (is_array($data)) {
            return isset($data[$field]);
        } elseif (is_object($data)) {
            return property_exists($data, $field);
        } elseif (is_string($data)) {
            // For XML responses
            return strpos($data, "<$field>") !== false || strpos($data, "<$field ") !== false;
        }
        return false;
    }
    
    /**
     * Get field value from response data
     */
    private static function getFieldValue($data, $field)
    {
        if (is_array($data)) {
            return $data[$field] ?? null;
        } elseif (is_object($data)) {
            return $data->$field ?? null;
        } elseif (is_string($data)) {
            // For XML responses
            if (preg_match("/<$field>(.*?)<\/$field>/", $data, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
    
    /**
     * Generate a pass/fail report for a test
     */
    public static function generateReport($apiName, $testName, $evaluation)
    {
        $report = "\n";
        $report .= "TEST CRITERIA EVALUATION\n";
        $report .= "========================\n";
        $report .= "API: $apiName\n";
        $report .= "Test: $testName\n";
        $report .= "Result: " . ($evaluation['passed'] ? '✅ PASSED' : '❌ FAILED') . "\n\n";
        
        $report .= "Criteria Checked:\n";
        foreach ($evaluation['criteria_checked'] as $criterion) {
            $report .= "  ✓ $criterion\n";
        }
        
        if (!empty($evaluation['failures'])) {
            $report .= "\nFailure Reasons:\n";
            foreach ($evaluation['failures'] as $failure) {
                $report .= "  ✗ $failure\n";
            }
        }
        
        $report .= "\n";
        return $report;
    }
}