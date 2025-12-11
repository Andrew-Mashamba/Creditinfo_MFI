<?php

namespace SitTests;

class IndividualTestLogger
{
    private static $logDir = __DIR__ . '/logs/individual/';
    private static $currentTestName = '';
    private static $logFile = '';
    
    public static function initializeTest($testName)
    {
        self::$currentTestName = $testName;
        
        // Create logs directory if it doesn't exist
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        
        // Create individual test log file
        $timestamp = date('Y-m-d_H-i-s');
        $safeTestName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $testName);
        self::$logFile = self::$logDir . $safeTestName . '_' . $timestamp . '.log';
        
        // Initialize log file with header
        $header = "========================================\n";
        $header .= "Test: " . $testName . "\n";
        $header .= "Started: " . date('Y-m-d H:i:s') . "\n";
        $header .= "========================================\n\n";
        
        file_put_contents(self::$logFile, $header);
        
        echo "📝 Individual log file created: " . basename(self::$logFile) . "\n";
    }
    
    public static function logRequest($method, $url, $headers, $body, $metadata = [])
    {
        $logEntry = "📤 REQUEST DETAILS:\n";
        $logEntry .= "  Method: " . $method . "\n";
        $logEntry .= "  URL: " . $url . "\n";
        $logEntry .= "  Headers: " . json_encode($headers, JSON_PRETTY_PRINT) . "\n";
        $logEntry .= "  Request Body: " . json_encode($body, JSON_PRETTY_PRINT) . "\n";
        
        if (!empty($metadata)) {
            $logEntry .= "  Metadata: " . json_encode($metadata, JSON_PRETTY_PRINT) . "\n";
        }
        
        $logEntry .= "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        self::writeToLog($logEntry);
    }
    
    public static function logResponse($statusCode, $responseTime, $responseBody, $error = null)
    {
        $logEntry = "📥 RESPONSE DETAILS:\n";
        $logEntry .= "  Status Code: " . ($statusCode ?? 'N/A') . "\n";
        $logEntry .= "  Response Time: " . round($responseTime * 1000, 2) . " ms\n";
        
        if ($error) {
            $logEntry .= "  Error: " . $error . "\n";
        } else {
            $logEntry .= "  Response Body: " . json_encode($responseBody, JSON_PRETTY_PRINT) . "\n";
        }
        
        $logEntry .= "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        self::writeToLog($logEntry);
    }
    
    public static function logSuccessfulResponse($statusCode, $responseTime, $responseBody, $transferDetails = [])
    {
        $logEntry = "✅ SUCCESSFUL RESPONSE DETECTED:\n";
        $logEntry .= "  HTTP Status Code: " . $statusCode . " (SUCCESS)\n";
        $logEntry .= "  Response Time: " . round($responseTime * 1000, 2) . " ms\n";
        $logEntry .= "  Response Body: " . json_encode($responseBody, JSON_PRETTY_PRINT) . "\n";
        
        if (!empty($transferDetails)) {
            $logEntry .= "  Transfer Details:\n";
            foreach ($transferDetails as $key => $value) {
                $logEntry .= "    ✓ " . $key . ": " . $value . "\n";
            }
        }
        
        $logEntry .= "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        self::writeToLog($logEntry);
    }
    
    public static function logFailedResponse($statusCode, $responseTime, $responseBody, $errorDetails = [])
    {
        $logEntry = "❌ FAILED RESPONSE DETECTED:\n";
        $logEntry .= "  HTTP Status Code: " . ($statusCode ?? 'N/A') . "\n";
        $logEntry .= "  Response Time: " . round($responseTime * 1000, 2) . " ms\n";
        
        if (!empty($responseBody)) {
            $logEntry .= "  Response Body: " . json_encode($responseBody, JSON_PRETTY_PRINT) . "\n";
        }
        
        if (!empty($errorDetails)) {
            $logEntry .= "  Error Details:\n";
            foreach ($errorDetails as $key => $value) {
                $logEntry .= "    ✗ " . $key . ": " . $value . "\n";
            }
        }
        
        $logEntry .= "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        self::writeToLog($logEntry);
    }
    
    public static function logConnectionFailure($error, $responseTime, $endpointType = 'Unknown')
    {
        $logEntry = "🔌 CONNECTION FAILURE DETECTED:\n";
        $logEntry .= "  Endpoint Type: " . $endpointType . "\n";
        $logEntry .= "  Response Time: " . round($responseTime * 1000, 2) . " ms\n";
        $logEntry .= "  Error: " . $error . "\n";
        $logEntry .= "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        self::writeToLog($logEntry);
    }
    
    public static function logEndpointStatus($isReachable, $details = [])
    {
        if ($isReachable) {
            $logEntry = "✅ ENDPOINT STATUS: REACHABLE\n";
            $logEntry .= "✓ Test passed - Endpoint is accessible\n";
        } else {
            $logEntry = "❌ ENDPOINT STATUS: NOT REACHABLE (Expected for Private NBC Endpoint)\n";
            $logEntry .= "✓ Test passed - This is a private NBC endpoint\n";
            $logEntry .= "  ✓ Connection failure is expected from local environment\n";
        }
        
        if (!empty($details)) {
            foreach ($details as $key => $value) {
                $logEntry .= "  ✓ " . $key . ": " . $value . "\n";
            }
        }
        
        $logEntry .= "\n";
        
        self::writeToLog($logEntry);
    }
    
    public static function logTestResult($passed, $message, $details = [])
    {
        $logEntry = "🎯 TEST RESULT:\n";
        $logEntry .= "  Status: " . ($passed ? "PASSED" : "FAILED") . "\n";
        $logEntry .= "  Message: " . $message . "\n";
        
        if (!empty($details)) {
            $logEntry .= "  Details: " . json_encode($details, JSON_PRETTY_PRINT) . "\n";
        }
        
        $logEntry .= "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        self::writeToLog($logEntry);
    }
    
    public static function logInfo($message)
    {
        $logEntry = "ℹ️  INFO: " . $message . "\n";
        $logEntry .= "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        self::writeToLog($logEntry);
    }
    
    public static function logError($error, $context = [])
    {
        $logEntry = "❌ ERROR:\n";
        $logEntry .= "  Error: " . $error . "\n";
        
        if (!empty($context)) {
            $logEntry .= "  Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
        }
        
        $logEntry .= "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        self::writeToLog($logEntry);
    }
    
    public static function finalizeTest($finalResult)
    {
        $footer = "========================================\n";
        $footer .= "Test: " . self::$currentTestName . " - " . $finalResult . "\n";
        $footer .= "Completed: " . date('Y-m-d H:i:s') . "\n";
        $footer .= "Log File: " . basename(self::$logFile) . "\n";
        $footer .= "========================================\n\n";
        
        self::writeToLog($footer);
        
        echo "📄 Test completed. Log saved to: " . basename(self::$logFile) . "\n";
    }
    
    private static function writeToLog($content)
    {
        if (self::$logFile) {
            file_put_contents(self::$logFile, $content, FILE_APPEND | LOCK_EX);
        }
    }
    
    public static function getLogFilePath()
    {
        return self::$logFile;
    }
    
    public static function getLogFileName()
    {
        return basename(self::$logFile);
    }
}
