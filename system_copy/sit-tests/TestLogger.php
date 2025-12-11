<?php

namespace SitTests;

class TestLogger
{
    private static $logDir;
    private static $currentTest;
    
    public static function init()
    {
        self::$logDir = __DIR__ . '/logs/detailed';
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
    }
    
    public static function setCurrentTest($testName)
    {
        self::$currentTest = $testName;
    }
    
    public static function logRequest($testCase, $method, $url, $headers, $body, $additionalInfo = [])
    {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s.u');
        $logFile = self::$logDir . '/' . date('Y-m-d') . '_requests.log';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'test_suite' => self::$currentTest ?? 'Unknown',
            'test_case' => $testCase,
            'request' => [
                'method' => $method,
                'url' => $url,
                'headers' => $headers,
                'body' => $body,
                'additional_info' => $additionalInfo
            ]
        ];
        
        $logText = "================================================================================\n";
        $logText .= "REQUEST LOG\n";
        $logText .= "================================================================================\n";
        $logText .= "Timestamp: $timestamp\n";
        $logText .= "Test Suite: " . (self::$currentTest ?? 'Unknown') . "\n";
        $logText .= "Test Case: $testCase\n";
        $logText .= "--------------------------------------------------------------------------------\n";
        $logText .= "REQUEST DETAILS:\n";
        $logText .= "  Method: $method\n";
        $logText .= "  URL: $url\n";
        $logText .= "  Headers:\n";
        foreach ($headers as $key => $value) {
            $logText .= "    $key: $value\n";
        }
        $logText .= "  Body:\n";
        $logText .= self::formatBody($body) . "\n";
        
        if (!empty($additionalInfo)) {
            $logText .= "  Additional Info:\n";
            foreach ($additionalInfo as $key => $value) {
                $logText .= "    $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
            }
        }
        
        $logText .= "================================================================================\n\n";
        
        file_put_contents($logFile, $logText, FILE_APPEND | LOCK_EX);
        
        // Also save as JSON for programmatic access
        $jsonFile = self::$logDir . '/' . date('Y-m-d') . '_requests.json';
        $jsonData = [];
        if (file_exists($jsonFile)) {
            $jsonData = json_decode(file_get_contents($jsonFile), true) ?: [];
        }
        $jsonData[] = $logEntry;
        file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT), LOCK_EX);
        
        return $logEntry;
    }
    
    public static function logResponse($testCase, $statusCode, $headers, $body, $responseTime, $error = null)
    {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s.u');
        $logFile = self::$logDir . '/' . date('Y-m-d') . '_responses.log';
        
        $responseReceived = $error === null;
        
        $logEntry = [
            'timestamp' => $timestamp,
            'test_suite' => self::$currentTest ?? 'Unknown',
            'test_case' => $testCase,
            'response_received' => $responseReceived,
            'response' => [
                'status_code' => $statusCode,
                'headers' => $headers,
                'body' => $body,
                'response_time_ms' => round($responseTime * 1000, 2),
                'error' => $error
            ]
        ];
        
        $logText = "================================================================================\n";
        $logText .= "RESPONSE LOG\n";
        $logText .= "================================================================================\n";
        $logText .= "Timestamp: $timestamp\n";
        $logText .= "Test Suite: " . (self::$currentTest ?? 'Unknown') . "\n";
        $logText .= "Test Case: $testCase\n";
        $logText .= "Response Received: " . ($responseReceived ? "✅ YES" : "❌ NO") . "\n";
        $logText .= "--------------------------------------------------------------------------------\n";
        
        if ($responseReceived) {
            $logText .= "RESPONSE DETAILS:\n";
            $logText .= "  Status Code: $statusCode\n";
            $logText .= "  Response Time: " . round($responseTime * 1000, 2) . " ms\n";
            
            if (!empty($headers)) {
                $logText .= "  Headers:\n";
                foreach ($headers as $key => $value) {
                    $logText .= "    $key: " . (is_array($value) ? implode(', ', $value) : $value) . "\n";
                }
            }
            
            $logText .= "  Body:\n";
            $logText .= self::formatBody($body) . "\n";
        } else {
            $logText .= "ERROR DETAILS:\n";
            $logText .= "  Error: $error\n";
            $logText .= "  Status Code: " . ($statusCode ?: 'N/A') . "\n";
        }
        
        $logText .= "================================================================================\n\n";
        
        file_put_contents($logFile, $logText, FILE_APPEND | LOCK_EX);
        
        // Also save as JSON
        $jsonFile = self::$logDir . '/' . date('Y-m-d') . '_responses.json';
        $jsonData = [];
        if (file_exists($jsonFile)) {
            $jsonData = json_decode(file_get_contents($jsonFile), true) ?: [];
        }
        $jsonData[] = $logEntry;
        file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT), LOCK_EX);
        
        return $logEntry;
    }
    
    public static function logTestResult($testCase, $passed, $message = '', $details = [])
    {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s.u');
        $logFile = self::$logDir . '/' . date('Y-m-d') . '_test_results.log';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'test_suite' => self::$currentTest ?? 'Unknown',
            'test_case' => $testCase,
            'passed' => $passed,
            'message' => $message,
            'details' => $details
        ];
        
        $logText = "--------------------------------------------------------------------------------\n";
        $logText .= "TEST RESULT: $testCase\n";
        $logText .= "  Status: " . ($passed ? "✅ PASSED" : "❌ FAILED") . "\n";
        $logText .= "  Message: $message\n";
        
        if (!empty($details)) {
            $logText .= "  Details:\n";
            foreach ($details as $key => $value) {
                $logText .= "    $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
            }
        }
        
        $logText .= "--------------------------------------------------------------------------------\n\n";
        
        file_put_contents($logFile, $logText, FILE_APPEND | LOCK_EX);
        
        return $logEntry;
    }
    
    private static function formatBody($body)
    {
        if (is_array($body) || is_object($body)) {
            $json = json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return "    " . str_replace("\n", "\n    ", $json);
        } elseif (is_string($body)) {
            // Check if it's JSON
            $decoded = json_decode($body);
            if (json_last_error() === JSON_ERROR_NONE) {
                $json = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                return "    " . str_replace("\n", "\n    ", $json);
            }
            // Check if it's XML
            if (strpos($body, '<?xml') !== false || strpos($body, '<') === 0) {
                $dom = new \DOMDocument();
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                @$dom->loadXML($body);
                return "    " . str_replace("\n", "\n    ", $dom->saveXML());
            }
            // Plain text
            return "    " . str_replace("\n", "\n    ", $body);
        }
        return "    " . (string)$body;
    }
    
    public static function generateSummaryReport()
    {
        self::init();
        
        $requestsFile = self::$logDir . '/' . date('Y-m-d') . '_requests.json';
        $responsesFile = self::$logDir . '/' . date('Y-m-d') . '_responses.json';
        
        $requests = [];
        $responses = [];
        
        if (file_exists($requestsFile)) {
            $requests = json_decode(file_get_contents($requestsFile), true) ?: [];
        }
        
        if (file_exists($responsesFile)) {
            $responses = json_decode(file_get_contents($responsesFile), true) ?: [];
        }
        
        $summary = [
            'date' => date('Y-m-d'),
            'timestamp' => date('Y-m-d H:i:s'),
            'statistics' => [
                'total_requests' => count($requests),
                'total_responses' => count($responses),
                'successful_responses' => 0,
                'failed_responses' => 0,
                'avg_response_time_ms' => 0,
                'apis_tested' => []
            ],
            'details' => []
        ];
        
        $responseTimes = [];
        $apiHosts = [];
        
        foreach ($requests as $index => $request) {
            $url = parse_url($request['request']['url']);
            $host = $url['host'] ?? 'unknown';
            $apiHosts[$host] = true;
            
            // Match with response
            $matchedResponse = null;
            foreach ($responses as $response) {
                if ($response['test_case'] === $request['test_case'] && 
                    abs(strtotime($response['timestamp']) - strtotime($request['timestamp'])) < 5) {
                    $matchedResponse = $response;
                    break;
                }
            }
            
            $detail = [
                'test_case' => $request['test_case'],
                'request_url' => $request['request']['url'],
                'request_method' => $request['request']['method'],
                'response_received' => $matchedResponse ? $matchedResponse['response_received'] : false,
                'status_code' => $matchedResponse ? $matchedResponse['response']['status_code'] : null,
                'response_time_ms' => $matchedResponse ? $matchedResponse['response']['response_time_ms'] : null,
                'error' => $matchedResponse ? $matchedResponse['response']['error'] : 'No response matched'
            ];
            
            $summary['details'][] = $detail;
            
            if ($matchedResponse) {
                if ($matchedResponse['response_received']) {
                    $summary['statistics']['successful_responses']++;
                    if ($matchedResponse['response']['response_time_ms']) {
                        $responseTimes[] = $matchedResponse['response']['response_time_ms'];
                    }
                } else {
                    $summary['statistics']['failed_responses']++;
                }
            }
        }
        
        $summary['statistics']['apis_tested'] = array_keys($apiHosts);
        
        if (!empty($responseTimes)) {
            $summary['statistics']['avg_response_time_ms'] = round(array_sum($responseTimes) / count($responseTimes), 2);
            $summary['statistics']['min_response_time_ms'] = min($responseTimes);
            $summary['statistics']['max_response_time_ms'] = max($responseTimes);
        }
        
        // Save summary
        $summaryFile = self::$logDir . '/' . date('Y-m-d') . '_summary.json';
        file_put_contents($summaryFile, json_encode($summary, JSON_PRETTY_PRINT));
        
        // Generate readable summary
        $readableFile = self::$logDir . '/' . date('Y-m-d') . '_summary.txt';
        $readable = "API TEST SUMMARY REPORT\n";
        $readable .= "========================\n";
        $readable .= "Date: " . $summary['date'] . "\n";
        $readable .= "Generated: " . $summary['timestamp'] . "\n\n";
        
        $readable .= "STATISTICS\n";
        $readable .= "----------\n";
        $readable .= "Total Requests: " . $summary['statistics']['total_requests'] . "\n";
        $readable .= "Successful Responses: " . $summary['statistics']['successful_responses'] . "\n";
        $readable .= "Failed Responses: " . $summary['statistics']['failed_responses'] . "\n";
        
        if (isset($summary['statistics']['avg_response_time_ms'])) {
            $readable .= "Avg Response Time: " . $summary['statistics']['avg_response_time_ms'] . " ms\n";
            $readable .= "Min Response Time: " . $summary['statistics']['min_response_time_ms'] . " ms\n";
            $readable .= "Max Response Time: " . $summary['statistics']['max_response_time_ms'] . " ms\n";
        }
        
        $readable .= "\nAPIs TESTED\n";
        $readable .= "-----------\n";
        foreach ($summary['statistics']['apis_tested'] as $api) {
            $readable .= "• $api\n";
        }
        
        $readable .= "\nDETAILED RESULTS\n";
        $readable .= "----------------\n";
        foreach ($summary['details'] as $detail) {
            $readable .= "\n" . $detail['test_case'] . "\n";
            $readable .= "  URL: " . $detail['request_method'] . " " . $detail['request_url'] . "\n";
            $readable .= "  Response: " . ($detail['response_received'] ? "✅ Received" : "❌ Failed") . "\n";
            
            if ($detail['response_received']) {
                $readable .= "  Status Code: " . $detail['status_code'] . "\n";
                $readable .= "  Response Time: " . $detail['response_time_ms'] . " ms\n";
            } else {
                $readable .= "  Error: " . $detail['error'] . "\n";
            }
        }
        
        file_put_contents($readableFile, $readable);
        
        return $summary;
    }
}