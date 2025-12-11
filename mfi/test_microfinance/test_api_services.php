#!/usr/bin/env php
<?php
/**
 * Comprehensive API Service Testing Script
 * 
 * Usage:
 *   php test_api_services.php                    # Run all tests
 *   php test_api_services.php list               # List all available tests
 *   php test_api_services.php internal_transfer  # Run specific test
 *   php test_api_services.php --help            # Show help
 * 
 * @author MFI Core System
 * @version 1.0.0
 */

// Configuration
define('BASE_URL', 'http://127.0.0.1:8000/api/test-services');
define('TIMEOUT', 30);
define('VERBOSE', in_array('--verbose', $argv) || in_array('-v', $argv));

// Color codes for terminal output
class Colors {
    const RESET = "\033[0m";
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const MAGENTA = "\033[35m";
    const CYAN = "\033[36m";
    const WHITE = "\033[37m";
    const BOLD = "\033[1m";
}

/**
 * Main API Test Class
 */
class ApiServiceTester {
    
    private $results = [];
    private $startTime;
    private $baseUrl;
    
    public function __construct($baseUrl = BASE_URL) {
        $this->baseUrl = $baseUrl;
        $this->startTime = microtime(true);
    }
    
    /**
     * Print colored output
     */
    private function print($message, $color = Colors::WHITE) {
        echo $color . $message . Colors::RESET . PHP_EOL;
    }
    
    /**
     * Print test header
     */
    private function printHeader($title) {
        echo PHP_EOL;
        $this->print(str_repeat('═', 80), Colors::CYAN);
        $this->print("  $title", Colors::BOLD . Colors::CYAN);
        $this->print(str_repeat('═', 80), Colors::CYAN);
    }
    
    /**
     * Make HTTP request
     */
    private function makeRequest($endpoint, $method = 'POST', $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-Key: test_api_key'
        ];
        
        if ($method === 'POST' && $data !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = 'Content-Length: ' . strlen(json_encode($data));
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'success' => $error === '',
            'response' => $response,
            'http_code' => $httpCode,
            'duration_ms' => $duration,
            'error' => $error,
            'data' => $response ? json_decode($response, true) : null
        ];
    }
    
    /**
     * Display test result
     */
    private function displayResult($testName, $result) {
        $status = $result['success'] && $result['http_code'] == 200 ? 'PASS' : 'FAIL';
        $color = $status === 'PASS' ? Colors::GREEN : Colors::RED;
        $icon = $status === 'PASS' ? '✓' : '✗';
        
        $this->print("  $icon $testName", $color);
        $this->print("    HTTP Code: {$result['http_code']}", Colors::WHITE);
        $this->print("    Duration: {$result['duration_ms']}ms", Colors::WHITE);
        
        if ($result['error']) {
            $this->print("    Error: {$result['error']}", Colors::RED);
        }
        
        if (VERBOSE && $result['data']) {
            $this->print("    Response:", Colors::YELLOW);
            $this->printJson($result['data'], '      ');
        } else if ($result['data'] && isset($result['data']['message'])) {
            $this->print("    Message: {$result['data']['message']}", Colors::YELLOW);
        }
        
        $this->results[$testName] = [
            'status' => $status,
            'http_code' => $result['http_code'],
            'duration_ms' => $result['duration_ms'],
            'message' => $result['data']['message'] ?? $result['error'] ?? 'No message'
        ];
    }
    
    /**
     * Print JSON with indentation
     */
    private function printJson($data, $indent = '') {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $lines = explode("\n", $json);
        foreach ($lines as $line) {
            echo $indent . $line . PHP_EOL;
        }
    }
    
    /**
     * Test 1: Internal Funds Transfer
     */
    public function testInternalFundsTransfer() {
        $this->printHeader('Testing: Internal Funds Transfer');
        
        $data = [
            'source_account' => '06012040022',
            'destination_account' => '06012040023',
            'amount' => 10000.00,
            'currency' => 'TZS',
            'description' => 'Test internal transfer at ' . date('Y-m-d H:i:s'),
            'reference' => 'TEST_INT_' . time(),
            'beneficiary_name' => 'John Doe Test',
            'sender_name' => 'Jane Smith Test'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        $result = $this->makeRequest('/internal-funds-transfer', 'POST', $data);
        $this->displayResult('Internal Funds Transfer', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Test 2: External Funds Transfer
     */
    public function testExternalFundsTransfer() {
        $this->printHeader('Testing: External Funds Transfer');
        
        $data = [
            'source_account' => '06012040022',
            'destination_account' => '1234567890',
            'destination_bank' => 'CRDB',
            'swift_code' => 'CORUTZTZ',
            'amount' => 50000.00,
            'currency' => 'TZS',
            'beneficiary_name' => 'Jane Smith External',
            'description' => 'Test external transfer at ' . date('Y-m-d H:i:s'),
            'reference' => 'TEST_EXT_' . time(),
            'sender_name' => 'John Sender Test'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        $result = $this->makeRequest('/external-funds-transfer', 'POST', $data);
        $this->displayResult('External Funds Transfer', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Test 3: Mini Statement
     */
    public function testMiniStatement() {
        $this->printHeader('Testing: Mini Statement');
        
        $data = [
            'account_number' => '06012040022',
            'limit' => 5,
            'channel_code' => 'MFINBC'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        $result = $this->makeRequest('/mini-statement', 'POST', $data);
        $this->displayResult('Mini Statement', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Test 4: Full Statement
     */
    public function testFullStatement() {
        $this->printHeader('Testing: Full Statement');
        
        $data = [
            'account_number' => '06012040022',
            'from_date' => date('Y-m-d', strtotime('-30 days')),
            'to_date' => date('Y-m-d'),
            'format' => 'json',
            'channel_code' => 'MFINBC'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        $result = $this->makeRequest('/full-statement', 'POST', $data);
        $this->displayResult('Full Statement', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Test 5: Account Lookup
     */
    public function testAccountLookup() {
        $this->printHeader('Testing: Account Lookup');
        
        $data = [
            'account_number' => '06012040022',
            'channel_code' => 'MFINBC'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        $result = $this->makeRequest('/account-lookup', 'POST', $data);
        $this->displayResult('Account Lookup', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Test 6: Utilities Payment
     */
    public function testUtilities() {
        $this->printHeader('Testing: Utilities Payment');
        
        $data = [
            'utility_type' => 'electricity',
            'account_number' => '06012040022',
            'meter_number' => 'LUKU123456',
            'amount' => 20000.00,
            'customer_name' => 'Test Customer',
            'phone_number' => '255700000000',
            'provider_code' => 'TANESCO'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        $result = $this->makeRequest('/utilities', 'POST', $data);
        $this->displayResult('Utilities Payment', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Test 7: SMS Service
     */
    public function testSMS() {
        $this->printHeader('Testing: SMS Service');
        
        $data = [
            'phone_numbers' => ['255700000001', '255700000002'],
            'message' => 'Test SMS from API test at ' . date('H:i:s'),
            'sender_id' => 'MFI',
            'sms_type' => 'TRANSACTIONAL'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        // Set shorter timeout for SMS to avoid hanging
        $result = $this->makeRequest('/sms', 'POST', $data);
        $this->displayResult('SMS Service', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Test 8: Direct Debit
     */
    public function testDirectDebit() {
        $this->printHeader('Testing: Direct Debit');
        
        $data = [
            'account_number' => '06012040022',
            'amount' => 100000.00,
            'mandate_reference' => 'DD_TEST_' . time(),
            'beneficiary_account' => '06012040023',
            'beneficiary_name' => 'Test Beneficiary',
            'frequency' => 'monthly',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 year')),
            'bank_code' => 'NBC'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        $result = $this->makeRequest('/direct-debit', 'POST', $data);
        $this->displayResult('Direct Debit', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Test 9: Control Number Payments
     */
    public function testControlNumberPayments() {
        $this->printHeader('Testing: Control Number Payments');
        
        $data = [
            'control_number' => '991234567890',
            'payer_name' => 'Test Payer',
            'payer_phone' => '255700000000',
            'amount' => 75000.00,
            'payment_description' => 'Test control number payment',
            'currency' => 'TZS',
            'account_number' => '06012040022'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        $result = $this->makeRequest('/control-number-payments', 'POST', $data);
        $this->displayResult('Control Number Payments', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Test 10: LUKU
     */
    public function testLUKU() {
        $this->printHeader('Testing: LUKU Payment');
        
        $data = [
            'meter_number' => '04123456789',
            'amount' => 10000.00,
            'customer_phone' => '255700000000',
            'customer_name' => 'Test LUKU Customer',
            'account_number' => '28012040011'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        $result = $this->makeRequest('/luku', 'POST', $data);
        $this->displayResult('LUKU Payment', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Test 11: GEPG
     */
    public function testGEPG() {
        $this->printHeader('Testing: GEPG Service');
        
        $data = [
            'bill_id' => 'BILL_' . time(),
            'payer_name' => 'Test GEPG Payer',
            'payer_phone' => '255700000000',
            'payer_email' => 'test@example.com',
            'amount' => 150000.00,
            'payment_option' => 'exact',
            'currency' => 'TZS',
            'account_number' => '06012040022'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        $result = $this->makeRequest('/gepg', 'POST', $data);
        $this->displayResult('GEPG Service', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Test 12: Pay By Link
     */
    public function testPayByLink() {
        $this->printHeader('Testing: Pay By Link');
        
        $data = [
            'amount' => 25000.00,
            'description' => 'Test payment link for debugging',
            'customer_email' => 'customer@test.com',
            'customer_phone' => '255700000000',
            'customer_name' => 'Test Link Customer',
            'expiry_hours' => 24,
            'callback_url' => 'https://webhook.site/test-callback',
            'currency' => 'TZS'
        ];
        
        if (VERBOSE) {
            $this->print("  Request Data:", Colors::BLUE);
            $this->printJson($data, '    ');
        }
        
        $result = $this->makeRequest('/pay-by-link', 'POST', $data);
        $this->displayResult('Pay By Link', $result);
        
        return $result['success'] && $result['http_code'] == 200;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        $this->print("\n" . str_repeat('━', 80), Colors::BOLD . Colors::CYAN);
        $this->print("  API SERVICE COMPREHENSIVE TEST SUITE", Colors::BOLD . Colors::CYAN);
        $this->print(str_repeat('━', 80), Colors::BOLD . Colors::CYAN);
        $this->print("\n  Base URL: " . $this->baseUrl, Colors::WHITE);
        $this->print("  Timeout: " . TIMEOUT . " seconds", Colors::WHITE);
        $this->print("  Verbose: " . (VERBOSE ? 'Yes' : 'No'), Colors::WHITE);
        $this->print("  Started: " . date('Y-m-d H:i:s'), Colors::WHITE);
        
        $tests = [
            'testInternalFundsTransfer',
            'testExternalFundsTransfer',
            'testMiniStatement',
            'testFullStatement',
            'testAccountLookup',
            'testUtilities',
            'testSMS',
            'testDirectDebit',
            'testControlNumberPayments',
            'testLUKU',
            'testGEPG',
            'testPayByLink'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $test) {
            if (method_exists($this, $test)) {
                try {
                    $result = $this->$test();
                    if ($result) {
                        $passed++;
                    } else {
                        $failed++;
                    }
                } catch (Exception $e) {
                    $this->print("  ✗ $test - Exception: " . $e->getMessage(), Colors::RED);
                    $failed++;
                }
            }
        }
        
        $this->printSummary($passed, $failed);
    }
    
    /**
     * Run specific test
     */
    public function runTest($testName) {
        $methodMap = [
            'internal_transfer' => 'testInternalFundsTransfer',
            'internal' => 'testInternalFundsTransfer',
            'external_transfer' => 'testExternalFundsTransfer',
            'external' => 'testExternalFundsTransfer',
            'mini_statement' => 'testMiniStatement',
            'mini' => 'testMiniStatement',
            'full_statement' => 'testFullStatement',
            'full' => 'testFullStatement',
            'account_lookup' => 'testAccountLookup',
            'lookup' => 'testAccountLookup',
            'utilities' => 'testUtilities',
            'utility' => 'testUtilities',
            'sms' => 'testSMS',
            'direct_debit' => 'testDirectDebit',
            'debit' => 'testDirectDebit',
            'control_number' => 'testControlNumberPayments',
            'control' => 'testControlNumberPayments',
            'luku' => 'testLUKU',
            'gepg' => 'testGEPG',
            'pay_by_link' => 'testPayByLink',
            'link' => 'testPayByLink'
        ];
        
        $method = $methodMap[strtolower($testName)] ?? null;
        
        if ($method && method_exists($this, $method)) {
            $this->print("\n" . str_repeat('━', 80), Colors::BOLD . Colors::CYAN);
            $this->print("  SINGLE TEST EXECUTION", Colors::BOLD . Colors::CYAN);
            $this->print(str_repeat('━', 80), Colors::BOLD . Colors::CYAN);
            
            try {
                $result = $this->$method();
                $this->printSummary($result ? 1 : 0, $result ? 0 : 1);
            } catch (Exception $e) {
                $this->print("  ✗ Test failed with exception: " . $e->getMessage(), Colors::RED);
            }
        } else {
            $this->print("  ✗ Unknown test: $testName", Colors::RED);
            $this->listTests();
        }
    }
    
    /**
     * List available tests
     */
    public function listTests() {
        $this->print("\n" . str_repeat('═', 80), Colors::CYAN);
        $this->print("  AVAILABLE TESTS", Colors::BOLD . Colors::CYAN);
        $this->print(str_repeat('═', 80), Colors::CYAN);
        
        $tests = [
            'internal_transfer' => 'Internal Funds Transfer',
            'external_transfer' => 'External Funds Transfer',
            'mini_statement' => 'Mini Statement',
            'full_statement' => 'Full Statement',
            'account_lookup' => 'Account Lookup',
            'utilities' => 'Utilities Payment',
            'sms' => 'SMS Service',
            'direct_debit' => 'Direct Debit',
            'control_number' => 'Control Number Payments',
            'luku' => 'LUKU Payment',
            'gepg' => 'GEPG Service',
            'pay_by_link' => 'Pay By Link'
        ];
        
        $this->print("\n  Usage: php test_api_services.php <test_name>\n", Colors::WHITE);
        
        foreach ($tests as $key => $description) {
            $this->print(sprintf("    %-20s - %s", $key, $description), Colors::WHITE);
        }
        
        $this->print("\n  Or run all tests: php test_api_services.php", Colors::YELLOW);
    }
    
    /**
     * Print test summary
     */
    private function printSummary($passed, $failed) {
        $total = $passed + $failed;
        $duration = round(microtime(true) - $this->startTime, 2);
        
        echo PHP_EOL;
        $this->print(str_repeat('═', 80), Colors::CYAN);
        $this->print("  TEST SUMMARY", Colors::BOLD . Colors::CYAN);
        $this->print(str_repeat('═', 80), Colors::CYAN);
        
        $this->print("  Total Tests: $total", Colors::WHITE);
        $this->print("  Passed: $passed", Colors::GREEN);
        $this->print("  Failed: $failed", Colors::RED);
        $this->print("  Duration: {$duration}s", Colors::WHITE);
        
        if ($failed === 0) {
            $this->print("\n  ✓ All tests passed successfully!", Colors::BOLD . Colors::GREEN);
        } else {
            $this->print("\n  ✗ Some tests failed. Check the output above.", Colors::BOLD . Colors::RED);
        }
        
        // Display results table
        if (!empty($this->results)) {
            echo PHP_EOL;
            $this->print("  Detailed Results:", Colors::BOLD . Colors::WHITE);
            $this->print(str_repeat('─', 80), Colors::WHITE);
            
            foreach ($this->results as $test => $result) {
                $statusColor = $result['status'] === 'PASS' ? Colors::GREEN : Colors::RED;
                $statusIcon = $result['status'] === 'PASS' ? '✓' : '✗';
                
                $this->print(sprintf("  %s %-30s [%3d] %6.2fms - %s", 
                    $statusIcon,
                    substr($test, 0, 30),
                    $result['http_code'],
                    $result['duration_ms'],
                    substr($result['message'], 0, 30)
                ), $statusColor);
            }
        }
        
        echo PHP_EOL;
    }
    
    /**
     * Check if server is running
     */
    public function checkServer() {
        $this->print("  Checking server availability...", Colors::YELLOW);
        
        $result = $this->makeRequest('/', 'GET');
        
        if ($result['success'] && $result['http_code'] == 200) {
            $this->print("  ✓ Server is running", Colors::GREEN);
            return true;
        } else {
            $this->print("  ✗ Server is not reachable at " . $this->baseUrl, Colors::RED);
            $this->print("  Make sure to run: php artisan serve", Colors::YELLOW);
            return false;
        }
    }
}

/**
 * Show help message
 */
function showHelp() {
    echo Colors::BOLD . Colors::CYAN . "
╔═══════════════════════════════════════════════════════════════════════════╗
║                     API SERVICE TEST SUITE                               ║
╚═══════════════════════════════════════════════════════════════════════════╝
" . Colors::RESET . "
Usage: php test_api_services.php [options] [test_name]

Options:
  --help, -h          Show this help message
  --verbose, -v       Show detailed request/response data
  --list, list        List all available tests

Test Names:
  internal_transfer   Test Internal Funds Transfer
  external_transfer   Test External Funds Transfer
  mini_statement      Test Mini Statement
  full_statement      Test Full Statement
  account_lookup      Test Account Lookup
  utilities          Test Utilities Payment
  sms                Test SMS Service
  direct_debit       Test Direct Debit
  control_number     Test Control Number Payments
  luku               Test LUKU Payment
  gepg               Test GEPG Service
  pay_by_link        Test Pay By Link

Examples:
  php test_api_services.php                    # Run all tests
  php test_api_services.php list               # List available tests
  php test_api_services.php internal_transfer  # Run specific test
  php test_api_services.php -v sms            # Run SMS test with verbose output
  php test_api_services.php --help            # Show this help

Note: Make sure the Laravel server is running on port 8000 before testing.
      Run: php artisan serve

";
}

/**
 * Main execution
 */
function main($argv) {
    // Check for help
    if (in_array('--help', $argv) || in_array('-h', $argv)) {
        showHelp();
        exit(0);
    }
    
    // Initialize tester
    $tester = new ApiServiceTester();
    
    // Check if server is running
    if (!$tester->checkServer()) {
        exit(1);
    }
    
    // Determine what to run
    $command = $argv[1] ?? null;
    
    // Remove flags from command
    if ($command && strpos($command, '-') === 0) {
        $command = $argv[2] ?? null;
    }
    
    if ($command === 'list' || $command === '--list') {
        $tester->listTests();
    } elseif ($command) {
        $tester->runTest($command);
    } else {
        $tester->runAllTests();
    }
}

// Run the script
main($argv);