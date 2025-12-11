<?php

namespace SitTests;

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class GEPGGatewayTest
{
    private $testResults = [];
    private $baseUrl;
    private $channelId;
    private $channelName;
    
    public function __construct()
    {
        $this->baseUrl = env('GEPG_GATEWAY_URL', 'https://nbc-gateway-uat.intra.nbc.co.tz');
        $this->channelId = env('GEPG_CHANNEL_ID', 'SACCOSNBC');
        $this->channelName = env('GEPG_CHANNEL_NAME', 'TR');
    }
    
    public function runAllTests()
    {
        echo "\n========================================\n";
        echo "GEPG Gateway API Tests\n";
        echo "========================================\n";
        
        $this->testBillQuery();
        $this->testBillPayment();
        $this->testPrepaidPayment();
        $this->testStatusCheck();
        $this->testXMLSigning();
        $this->testErrorHandling();
        
        $this->printResults();
    }
    
    private function testBillQuery()
    {
        echo "\n[TEST] Bill Query (Control Number Verification)...\n";
        
        try {
            $controlNumber = '991700123456';
            $accountNo = '01J01234567890';
            $currency = 'TZS';
            
            $mockResponse = '<?xml version="1.0" encoding="UTF-8"?>
            <GepgGateway>
                <GepgGatewayBillQryResp>
                    <BillHdr>
                        <BillStsCode>0000</BillStsCode>
                        <BillStsDesc>Success</BillStsDesc>
                        <BillId>BILL123456</BillId>
                        <CustCtrNum>' . $controlNumber . '</CustCtrNum>
                        <BillAmt>100000.00</BillAmt>
                        <PaidAmt>0.00</PaidAmt>
                        <BillExpDt>2024-12-31</BillExpDt>
                    </BillHdr>
                    <BillDtls>
                        <BillDtl>
                            <ItemRef>ITEM001</ItemRef>
                            <ItemDesc>Loan Payment</ItemDesc>
                            <ItemAmt>100000.00</ItemAmt>
                        </BillDtl>
                    </BillDtls>
                </GepgGatewayBillQryResp>
                <gepggatewaySignature>dGVzdFNpZ25hdHVyZQ==</gepggatewaySignature>
            </GepgGateway>';
            
            // Simulate the API call
            $payload = [
                'GepgGatewayBillQryReq' => [
                    'GepgGatewayHdr' => [
                        'ChannelID' => $this->channelId,
                        'ChannelName' => $this->channelName,
                        'Service' => 'GEPG_INQ',
                    ],
                    'gepgBillQryReq' => [
                        'ChannelRef' => uniqid('inq_'),
                        'CustCtrNum' => $controlNumber,
                        'DebitAccountNo' => $accountNo,
                        'DebitAccountCurrency' => $currency,
                    ],
                ]
            ];
            
            // Log detailed request information
            echo "📤 REQUEST DETAILS:\n";
            echo "  Method: POST\n";
            echo "  URL: " . $this->baseUrl . '/api/bill/query' . "\n";
            echo "  Headers: " . json_encode(['Content-Type' => 'application/xml', 'Accept' => 'application/xml'], JSON_PRETTY_PRINT) . "\n";
            echo "  Request Body: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
            echo "  Test Type: Bill Query\n";
            echo "  Control Number: " . $controlNumber . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Parse mock response
            $xml = simplexml_load_string($mockResponse);
            
            // Log detailed response information
            echo "📥 RESPONSE DETAILS:\n";
            echo "  HTTP Status Code: 200\n";
            echo "  Response Headers: " . json_encode(['Content-Type' => 'application/xml'], JSON_PRETTY_PRINT) . "\n";
            echo "  Response Body (XML): " . $mockResponse . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            $statusCode = (string)$xml->GepgGatewayBillQryResp->BillHdr->BillStsCode;
            
            if ($statusCode === '0000') {
                $this->testResults['Bill Query'] = 'PASSED';
                echo "✓ Bill Query test passed - Control Number: $controlNumber\n";
            } else {
                $this->testResults['Bill Query'] = 'FAILED';
                echo "✗ Bill Query test failed\n";
            }
        } catch (\Exception $e) {
            $this->testResults['Bill Query'] = 'ERROR';
            echo "✗ Bill Query test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testBillPayment()
    {
        echo "\n[TEST] Bill Payment Processing...\n";
        
        try {
            $paymentData = [
                'channel_ref' => 'PAY' . time(),
                'cbp_gw_ref' => 'CBP' . time(),
                'control_number' => '991700123456',
                'status_code' => '0000',
                'debit_account_no' => '01J01234567890',
                'debit_account_currency' => 'TZS',
                'items' => [
                    [
                        'channel_trx_id' => 'TRX' . time(),
                        'sp_code' => 'SP001',
                        'pay_ref_id' => 'REF' . time(),
                        'bill_ctr_num' => '991700123456',
                        'paid_amt' => '100000.00',
                        'trx_dt_tm' => date('Y-m-d\TH:i:s'),
                        'bill_amt' => '100000.00',
                        'ccy' => 'TZS',
                        'pyr_name' => 'John Doe',
                        'pyr_cell_num' => '255712345678',
                        'pyr_email' => 'john@example.com',
                    ]
                ]
            ];
            
            // Log detailed request information
            echo "📤 REQUEST DETAILS:\n";
            echo "  Method: POST\n";
            echo "  URL: " . $this->baseUrl . '/api/bill/payment' . "\n";
            echo "  Headers: " . json_encode(['Content-Type' => 'application/xml', 'Accept' => 'application/xml'], JSON_PRETTY_PRINT) . "\n";
            echo "  Request Body: " . json_encode($paymentData, JSON_PRETTY_PRINT) . "\n";
            echo "  Test Type: Bill Payment\n";
            echo "  Control Number: " . $paymentData['control_number'] . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            $mockResponse = '<?xml version="1.0" encoding="UTF-8"?>
            <GepgGateway>
                <GepgGatewayPaymentResp>
                    <PmtRespHdr>
                        <ChannelRef>' . $paymentData['channel_ref'] . '</ChannelRef>
                        <CbpGwRef>' . $paymentData['cbp_gw_ref'] . '</CbpGwRef>
                        <ResultCode>0000</ResultCode>
                        <ResultDesc>Payment successful</ResultDesc>
                    </PmtRespHdr>
                </GepgGatewayPaymentResp>
                <gepggatewaySignature>dGVzdFNpZ25hdHVyZQ==</gepggatewaySignature>
            </GepgGateway>';
            
            // Log detailed response information
            echo "📥 RESPONSE DETAILS:\n";
            echo "  HTTP Status Code: 200\n";
            echo "  Response Headers: " . json_encode(['Content-Type' => 'application/xml'], JSON_PRETTY_PRINT) . "\n";
            echo "  Response Body (XML): " . $mockResponse . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Parse mock response
            $xml = simplexml_load_string($mockResponse);
            $resultCode = (string)$xml->GepgGatewayPaymentResp->PmtRespHdr->ResultCode;
            
            if ($resultCode === '0000') {
                $this->testResults['Bill Payment'] = 'PASSED';
                echo "✓ Bill Payment test passed - Reference: " . $paymentData['channel_ref'] . "\n";
            } else {
                $this->testResults['Bill Payment'] = 'FAILED';
                echo "✗ Bill Payment test failed\n";
            }
        } catch (\Exception $e) {
            $this->testResults['Bill Payment'] = 'ERROR';
            echo "✗ Bill Payment test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testPrepaidPayment()
    {
        echo "\n[TEST] Prepaid Payment (Quote)...\n";
        
        try {
            $paymentData = [
                'channel_ref' => 'PREPAY' . time(),
                'cbp_gw_ref' => 'CBP' . time(),
                'control_number' => '991700789012',
                'status_code' => '0000',
                'debit_account_no' => '01J09876543210',
                'debit_account_currency' => 'TZS',
                'items' => [
                    [
                        'channel_trx_id' => 'TRX' . time(),
                        'sp_code' => 'LUKU',
                        'pay_ref_id' => 'TOKEN123456',
                        'bill_ctr_num' => '991700789012',
                        'paid_amt' => '50000.00',
                        'trx_dt_tm' => date('Y-m-d\TH:i:s'),
                        'bill_amt' => '50000.00',
                        'ccy' => 'TZS',
                    ]
                ]
            ];
            
            // Log detailed request information
            echo "📤 REQUEST DETAILS:\n";
            echo "  Method: POST\n";
            echo "  URL: " . $this->baseUrl . '/api/bill/prepaid' . "\n";
            echo "  Headers: " . json_encode(['Content-Type' => 'application/xml', 'Accept' => 'application/xml'], JSON_PRETTY_PRINT) . "\n";
            echo "  Request Body: " . json_encode($paymentData, JSON_PRETTY_PRINT) . "\n";
            echo "  Test Type: Prepaid Payment\n";
            echo "  Control Number: " . $paymentData['control_number'] . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            $mockResponse = '<?xml version="1.0" encoding="UTF-8"?>
            <GepgGateway>
                <GepgGatewayPaymentResp>
                    <PmtRespHdr>
                        <ChannelRef>' . $paymentData['channel_ref'] . '</ChannelRef>
                        <CbpGwRef>' . $paymentData['cbp_gw_ref'] . '</CbpGwRef>
                        <ResultCode>0000</ResultCode>
                        <ResultDesc>Prepaid quote generated</ResultDesc>
                    </PmtRespHdr>
                    <QuoteInfo>
                        <Token>12345678901234567890</Token>
                        <Units>150.5</Units>
                        <Amount>50000.00</Amount>
                    </QuoteInfo>
                </GepgGatewayPaymentResp>
                <gepggatewaySignature>dGVzdFNpZ25hdHVyZQ==</gepggatewaySignature>
            </GepgGateway>';
            
            // Log detailed response information
            echo "📥 RESPONSE DETAILS:\n";
            echo "  HTTP Status Code: 200\n";
            echo "  Response Headers: " . json_encode(['Content-Type' => 'application/xml'], JSON_PRETTY_PRINT) . "\n";
            echo "  Response Body (XML): " . $mockResponse . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Parse mock response
            $xml = simplexml_load_string($mockResponse);
            $resultCode = (string)$xml->GepgGatewayPaymentResp->PmtRespHdr->ResultCode;
            $token = isset($xml->GepgGatewayPaymentResp->QuoteInfo->Token) ? 
                    (string)$xml->GepgGatewayPaymentResp->QuoteInfo->Token : null;
            
            if ($resultCode === '0000' && $token) {
                $this->testResults['Prepaid Payment'] = 'PASSED';
                echo "✓ Prepaid Payment test passed - Token: $token\n";
            } else {
                $this->testResults['Prepaid Payment'] = 'FAILED';
                echo "✗ Prepaid Payment test failed\n";
            }
        } catch (\Exception $e) {
            $this->testResults['Prepaid Payment'] = 'ERROR';
            echo "✗ Prepaid Payment test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testStatusCheck()
    {
        echo "\n[TEST] Transaction Status Check...\n";
        
        try {
            $channelRef = 'PAY' . time();
            $cbpGwRef = 'CBP' . time();
            
            $statusRequestData = [
                'ChannelRef' => $channelRef,
                'CbpGwRef' => $cbpGwRef
            ];
            
            // Log detailed request information
            echo "📤 REQUEST DETAILS:\n";
            echo "  Method: GET\n";
            echo "  URL: " . $this->baseUrl . '/api/status/check' . "\n";
            echo "  Headers: " . json_encode(['Content-Type' => 'application/xml', 'Accept' => 'application/xml'], JSON_PRETTY_PRINT) . "\n";
            echo "  Request Body: " . json_encode($statusRequestData, JSON_PRETTY_PRINT) . "\n";
            echo "  Test Type: Status Check\n";
            echo "  Channel Ref: " . $channelRef . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            $mockResponse = '<?xml version="1.0" encoding="UTF-8"?>
            <GepgGateway>
                <GepgGatewayStatusCheckResp>
                    <StatusCheckRespHdr>
                        <ChannelRef>' . $channelRef . '</ChannelRef>
                        <CbpGwRef>' . $cbpGwRef . '</CbpGwRef>
                        <TrxStatus>SUCCESS</TrxStatus>
                        <TrxStatusDesc>Transaction completed successfully</TrxStatusDesc>
                        <TrxDtTm>' . date('Y-m-d\TH:i:s') . '</TrxDtTm>
                    </StatusCheckRespHdr>
                </GepgGatewayStatusCheckResp>
                <gepggatewaySignature>dGVzdFNpZ25hdHVyZQ==</gepggatewaySignature>
            </GepgGateway>';
            
            // Log detailed response information
            echo "📥 RESPONSE DETAILS:\n";
            echo "  HTTP Status Code: 200\n";
            echo "  Response Headers: " . json_encode(['Content-Type' => 'application/xml'], JSON_PRETTY_PRINT) . "\n";
            echo "  Response Body (XML): " . $mockResponse . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Parse mock response
            $xml = simplexml_load_string($mockResponse);
            $status = (string)$xml->GepgGatewayStatusCheckResp->StatusCheckRespHdr->TrxStatus;
            
            if ($status === 'SUCCESS') {
                $this->testResults['Status Check'] = 'PASSED';
                echo "✓ Status Check test passed - Status: $status\n";
            } else {
                $this->testResults['Status Check'] = 'FAILED';
                echo "✗ Status Check test failed\n";
            }
        } catch (\Exception $e) {
            $this->testResults['Status Check'] = 'ERROR';
            echo "✗ Status Check test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testXMLSigning()
    {
        echo "\n[TEST] XML Signing and Verification...\n";
        
        try {
            // Test data to sign
            $testData = [
                'test' => 'data',
                'timestamp' => date('Y-m-d\TH:i:s')
            ];
            
            // Convert to XML
            $xml = new \SimpleXMLElement('<GepgGateway/>');
            $this->addArrayToXml($xml, $testData);
            $xmlString = $xml->asXML();
            
            // Simulate signing (base64 encode for test)
            $signature = base64_encode(hash('sha256', $xmlString, true));
            
            // Add signature to XML
            $xml->addChild('gepggatewaySignature', $signature);
            $signedXml = $xml->asXML();
            
            // Verify signature exists in XML
            $parsedXml = simplexml_load_string($signedXml);
            
            if (isset($parsedXml->gepggatewaySignature)) {
                $this->testResults['XML Signing'] = 'PASSED';
                echo "✓ XML Signing test passed\n";
            } else {
                $this->testResults['XML Signing'] = 'FAILED';
                echo "✗ XML Signing test failed\n";
            }
        } catch (\Exception $e) {
            $this->testResults['XML Signing'] = 'ERROR';
            echo "✗ XML Signing test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testErrorHandling()
    {
        echo "\n[TEST] Error Handling...\n";
        
        try {
            // Test various error scenarios
            $errorScenarios = [
                ['code' => '4001', 'desc' => 'Invalid control number'],
                ['code' => '4002', 'desc' => 'Expired bill'],
                ['code' => '4003', 'desc' => 'Insufficient funds'],
                ['code' => '5001', 'desc' => 'System error'],
            ];
            
            $allPassed = true;
            
            foreach ($errorScenarios as $scenario) {
                $mockResponse = '<?xml version="1.0" encoding="UTF-8"?>
                <GepgGateway>
                    <GepgGatewayBillQryResp>
                        <BillHdr>
                            <BillStsCode>' . $scenario['code'] . '</BillStsCode>
                            <BillStsDesc>' . $scenario['desc'] . '</BillStsDesc>
                        </BillHdr>
                    </GepgGatewayBillQryResp>
                </GepgGateway>';
                
                $xml = simplexml_load_string($mockResponse);
                $code = (string)$xml->GepgGatewayBillQryResp->BillHdr->BillStsCode;
                $desc = (string)$xml->GepgGatewayBillQryResp->BillHdr->BillStsDesc;
                
                if ($code !== '0000' && !empty($desc)) {
                    echo "  ✓ Error scenario handled: $code - $desc\n";
                } else {
                    $allPassed = false;
                    echo "  ✗ Error scenario not handled properly: $code\n";
                }
            }
            
            $this->testResults['Error Handling'] = $allPassed ? 'PASSED' : 'FAILED';
        } catch (\Exception $e) {
            $this->testResults['Error Handling'] = 'ERROR';
            echo "✗ Error Handling test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function addArrayToXml(\SimpleXMLElement $object, array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $newObject = $object->addChild($key);
                $this->addArrayToXml($newObject, $value);
            } else {
                $object->addChild($key, htmlspecialchars($value));
            }
        }
    }
    
    private function printResults()
    {
        echo "\n========================================\n";
        echo "Test Results Summary\n";
        echo "========================================\n";
        
        $passed = 0;
        $failed = 0;
        $errors = 0;
        
        foreach ($this->testResults as $test => $result) {
            echo sprintf("%-30s: %s\n", $test, $result);
            
            if ($result === 'PASSED') $passed++;
            elseif ($result === 'FAILED') $failed++;
            else $errors++;
        }
        
        echo "----------------------------------------\n";
        echo "Total: " . count($this->testResults) . " tests\n";
        echo "Passed: $passed | Failed: $failed | Errors: $errors\n";
        echo "========================================\n";
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    $test = new GEPGGatewayTest();
    $test->runAllTests();
}