#!/usr/bin/env php
<?php

/**
 * System Integration Test Runner
 * 
 * This script runs all SIT tests for external API integrations
 * in the SACCOS Core System.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

class SITTestRunner
{
    public $tests = [];
    private $results = [];
    private $startTime;
    private $endTime;
    
    public function __construct()
    {
        // Register all test classes
        $this->tests = [
            'Bank Transaction Service' => 'BankTransactionServiceTest',
            'GEPG Gateway' => 'GEPGGatewayTest',
            'Luku Gateway' => 'LukuGatewayTest',
            'NBC SMS' => 'NBCSMSTest',
            'AI Services' => 'AIServicesTest'
        ];
    }
    
    public function run()
    {
        $this->printHeader();
        $this->startTime = microtime(true);
        
        foreach ($this->tests as $name => $className) {
            $this->runTest($name, $className);
        }
        
        $this->endTime = microtime(true);
        $this->printSummary();
    }
    
    private function runTest($name, $className)
    {
        $fullClassName = "\\SitTests\\$className";
        $testFile = __DIR__ . "/$className.php";
        
        if (!file_exists($testFile)) {
            echo "\n⚠ Warning: Test file not found: $testFile\n";
            $this->logError($name, "Test file not found: $testFile");
            $this->results[$name] = 'NOT_FOUND';
            return;
        }
        
        try {
            require_once $testFile;
            
            if (!class_exists($fullClassName)) {
                echo "\n⚠ Warning: Test class not found: $fullClassName\n";
                $this->logError($name, "Test class not found: $fullClassName");
                $this->results[$name] = 'CLASS_NOT_FOUND';
                return;
            }
            
            $test = new $fullClassName();
            
            // Capture output
            ob_start();
            $startTime = microtime(true);
            $test->runAllTests();
            $endTime = microtime(true);
            $output = ob_get_clean();
            
            // Display output
            echo $output;
            
            // Log test output
            $this->logTestOutput($name, $output, $endTime - $startTime);
            
            // Parse results from output
            if (strpos($output, 'Passed:') !== false) {
                preg_match('/Passed: (\d+)/', $output, $passed);
                preg_match('/Failed: (\d+)/', $output, $failed);
                preg_match('/Errors: (\d+)/', $output, $errors);
                
                $passedCount = isset($passed[1]) ? (int)$passed[1] : 0;
                $failedCount = isset($failed[1]) ? (int)$failed[1] : 0;
                $errorCount = isset($errors[1]) ? (int)$errors[1] : 0;
                
                if ($failedCount === 0 && $errorCount === 0 && $passedCount > 0) {
                    $this->results[$name] = 'PASSED';
                } elseif ($failedCount > 0 || $errorCount > 0) {
                    $this->results[$name] = 'FAILED';
                } else {
                    $this->results[$name] = 'UNKNOWN';
                }
            } else {
                $this->results[$name] = 'UNKNOWN';
            }
            
        } catch (\Exception $e) {
            echo "\n✗ Error running $name test: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
            $this->logError($name, $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->results[$name] = 'ERROR';
        }
    }
    
    private function logTestOutput($testName, $output, $duration)
    {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logFile = $logDir . '/' . date('Y-m-d') . '_test_results.log';
        
        $logEntry = "================================================================================\n";
        $logEntry .= "Test: $testName\n";
        $logEntry .= "Timestamp: $timestamp\n";
        $logEntry .= "Duration: " . round($duration, 3) . " seconds\n";
        $logEntry .= "--------------------------------------------------------------------------------\n";
        $logEntry .= $output . "\n";
        $logEntry .= "================================================================================\n\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    private function logError($testName, $error)
    {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $errorFile = $logDir . '/' . date('Y-m-d') . '_errors.log';
        
        $errorEntry = "[$timestamp] ERROR in $testName:\n";
        $errorEntry .= $error . "\n";
        $errorEntry .= "--------------------------------------------------------------------------------\n";
        
        file_put_contents($errorFile, $errorEntry, FILE_APPEND | LOCK_EX);
    }
    
    private function printHeader()
    {
        echo "\n";
        echo "════════════════════════════════════════════════════════════════\n";
        echo "                SACCOS CORE SYSTEM - SIT TEST SUITE             \n";
        echo "════════════════════════════════════════════════════════════════\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n";
        echo "Environment: " . app()->environment() . "\n";
        echo "════════════════════════════════════════════════════════════════\n";
    }
    
    private function printSummary()
    {
        $duration = round($this->endTime - $this->startTime, 2);
        
        echo "\n";
        echo "════════════════════════════════════════════════════════════════\n";
        echo "                        OVERALL TEST SUMMARY                     \n";
        echo "════════════════════════════════════════════════════════════════\n";
        
        $passed = 0;
        $failed = 0;
        $errors = 0;
        $notFound = 0;
        $unknown = 0;
        
        foreach ($this->results as $test => $result) {
            $icon = $this->getStatusIcon($result);
            echo sprintf("%-30s: %s %s\n", $test, $icon, $result);
            
            switch ($result) {
                case 'PASSED':
                    $passed++;
                    break;
                case 'FAILED':
                    $failed++;
                    break;
                case 'ERROR':
                case 'CLASS_NOT_FOUND':
                    $errors++;
                    break;
                case 'NOT_FOUND':
                    $notFound++;
                    break;
                default:
                    $unknown++;
            }
        }
        
        echo "────────────────────────────────────────────────────────────────\n";
        echo "Total Test Suites: " . count($this->results) . "\n";
        echo "Passed: $passed | Failed: $failed | Errors: $errors";
        
        if ($notFound > 0) {
            echo " | Not Found: $notFound";
        }
        if ($unknown > 0) {
            echo " | Unknown: $unknown";
        }
        
        echo "\n";
        echo "Execution Time: {$duration}s\n";
        echo "════════════════════════════════════════════════════════════════\n";
        
        // Log file information
        $logDir = __DIR__ . '/logs';
        $resultsLog = $logDir . '/' . date('Y-m-d') . '_test_results.log';
        $errorsLog = $logDir . '/' . date('Y-m-d') . '_errors.log';
        
        echo "\n📁 Log Files:\n";
        echo "   Results: $resultsLog\n";
        if (file_exists($errorsLog)) {
            echo "   Errors:  $errorsLog\n";
        }
        
        // Exit with appropriate code
        if ($failed > 0 || $errors > 0) {
            echo "\n❌ TEST SUITE FAILED\n\n";
            exit(1);
        } else {
            echo "\n✅ TEST SUITE PASSED\n\n";
            exit(0);
        }
    }
    
    private function getStatusIcon($status)
    {
        switch ($status) {
            case 'PASSED':
                return '✅';
            case 'FAILED':
                return '❌';
            case 'ERROR':
            case 'CLASS_NOT_FOUND':
                return '🔴';
            case 'NOT_FOUND':
                return '⚠️';
            default:
                return '❓';
        }
    }
}

// Parse command line arguments
$options = getopt('h', ['help', 'test:', 'list']);

if (isset($options['h']) || isset($options['help'])) {
    echo "\nUsage: php run-all-tests.php [options]\n";
    echo "\nOptions:\n";
    echo "  -h, --help        Show this help message\n";
    echo "  --test=<name>     Run a specific test (e.g., --test=BankTransactionServiceTest)\n";
    echo "  --list            List all available tests\n";
    echo "\nExamples:\n";
    echo "  php run-all-tests.php                    # Run all tests\n";
    echo "  php run-all-tests.php --test=NBCSMSTest  # Run only NBC SMS test\n";
    echo "  php run-all-tests.php --list             # List available tests\n\n";
    exit(0);
}

$runner = new SITTestRunner();

if (isset($options['list'])) {
    echo "\nAvailable tests:\n";
    echo "────────────────────────────────────────\n";
    foreach ($runner->tests as $name => $className) {
        echo "  • $className\n";
    }
    echo "\n";
    exit(0);
}

if (isset($options['test'])) {
    $testName = $options['test'];
    $found = false;
    
    foreach ($runner->tests as $name => $className) {
        if ($className === $testName) {
            $runner->tests = [$name => $className];
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "\n✗ Test not found: $testName\n";
        echo "Use --list to see available tests.\n\n";
        exit(1);
    }
}

// Run the tests
$runner->run();