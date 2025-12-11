#!/usr/bin/env php
<?php

/**
 * SIT Test Report Generator
 * 
 * Generates detailed HTML and JSON reports from test logs
 */

class TestReportGenerator
{
    private $logDir;
    private $reportDir;
    private $date;
    
    public function __construct()
    {
        $this->logDir = __DIR__ . '/logs';
        $this->reportDir = __DIR__ . '/reports';
        $this->date = date('Y-m-d');
        
        if (!is_dir($this->reportDir)) {
            mkdir($this->reportDir, 0755, true);
        }
    }
    
    public function generate()
    {
        echo "\n📊 Generating Test Report...\n";
        
        $logFile = $this->logDir . '/' . $this->date . '_test_results.log';
        
        if (!file_exists($logFile)) {
            echo "❌ No test results found for {$this->date}\n";
            echo "   Run the tests first: php sit-tests/run-all-tests.php\n\n";
            exit(1);
        }
        
        $logContent = file_get_contents($logFile);
        $testResults = $this->parseTestResults($logContent);
        
        $this->generateJSONReport($testResults);
        $this->generateHTMLReport($testResults);
        $this->generateMarkdownReport($testResults);
        
        echo "\n✅ Reports generated successfully!\n";
        echo "   📁 JSON: " . $this->reportDir . '/' . $this->date . '_report.json' . "\n";
        echo "   📁 HTML: " . $this->reportDir . '/' . $this->date . '_report.html' . "\n";
        echo "   📁 Markdown: " . $this->reportDir . '/' . $this->date . '_report.md' . "\n\n";
    }
    
    private function parseTestResults($logContent)
    {
        $results = [
            'date' => $this->date,
            'timestamp' => date('Y-m-d H:i:s'),
            'test_suites' => [],
            'summary' => [
                'total_suites' => 0,
                'passed_suites' => 0,
                'failed_suites' => 0,
                'total_tests' => 0,
                'passed_tests' => 0,
                'failed_tests' => 0,
                'error_tests' => 0,
                'warning_tests' => 0,
                'skipped_tests' => 0
            ]
        ];
        
        // Split by test suite separator
        $suites = explode('================================================================================', $logContent);
        
        foreach ($suites as $suite) {
            if (trim($suite) === '') continue;
            
            // Parse test suite
            if (preg_match('/Test: (.+)\nTimestamp: (.+)\nDuration: (.+) seconds/', $suite, $matches)) {
                $suiteName = trim($matches[1]);
                $timestamp = trim($matches[2]);
                $duration = trim($matches[3]);
                
                $testCases = [];
                $suiteStats = [
                    'passed' => 0,
                    'failed' => 0,
                    'errors' => 0,
                    'warnings' => 0,
                    'skipped' => 0
                ];
                
                // Parse individual test results
                if (preg_match_all('/\[TEST\] (.+?)\.\.\.(.+?)(?=\[TEST\]|Test Results Summary|$)/s', $suite, $testMatches)) {
                    foreach ($testMatches[1] as $index => $testName) {
                        $testOutput = $testMatches[2][$index];
                        $status = 'UNKNOWN';
                        $message = '';
                        
                        if (strpos($testOutput, '✓') !== false) {
                            $status = 'PASSED';
                            $suiteStats['passed']++;
                            preg_match('/✓ (.+)/', $testOutput, $msgMatch);
                            $message = isset($msgMatch[1]) ? trim($msgMatch[1]) : 'Test passed';
                        } elseif (strpos($testOutput, '✗') !== false && strpos($testOutput, 'error:') !== false) {
                            $status = 'ERROR';
                            $suiteStats['errors']++;
                            preg_match('/✗ .+ error: (.+)/', $testOutput, $msgMatch);
                            $message = isset($msgMatch[1]) ? trim($msgMatch[1]) : 'Test error';
                        } elseif (strpos($testOutput, '✗') !== false) {
                            $status = 'FAILED';
                            $suiteStats['failed']++;
                            preg_match('/✗ (.+)/', $testOutput, $msgMatch);
                            $message = isset($msgMatch[1]) ? trim($msgMatch[1]) : 'Test failed';
                        } elseif (strpos($testOutput, '⚠') !== false) {
                            $status = 'WARNING';
                            $suiteStats['warnings']++;
                            preg_match('/⚠ (.+)/', $testOutput, $msgMatch);
                            $message = isset($msgMatch[1]) ? trim($msgMatch[1]) : 'Warning';
                        }
                        
                        $testCases[] = [
                            'name' => trim($testName),
                            'status' => $status,
                            'message' => $message
                        ];
                    }
                }
                
                // Parse summary if exists
                if (preg_match('/Passed: (\d+) \| Failed: (\d+) \| (?:Errors: (\d+))?/', $suite, $summaryMatch)) {
                    $suiteStats['passed'] = (int)$summaryMatch[1];
                    $suiteStats['failed'] = (int)$summaryMatch[2];
                    if (isset($summaryMatch[3])) {
                        $suiteStats['errors'] = (int)$summaryMatch[3];
                    }
                }
                
                $suiteResult = [
                    'name' => $suiteName,
                    'timestamp' => $timestamp,
                    'duration' => $duration,
                    'tests' => $testCases,
                    'statistics' => $suiteStats,
                    'status' => ($suiteStats['failed'] === 0 && $suiteStats['errors'] === 0) ? 'PASSED' : 'FAILED'
                ];
                
                $results['test_suites'][] = $suiteResult;
                
                // Update summary
                $results['summary']['total_suites']++;
                if ($suiteResult['status'] === 'PASSED') {
                    $results['summary']['passed_suites']++;
                } else {
                    $results['summary']['failed_suites']++;
                }
                
                $results['summary']['total_tests'] += count($testCases);
                $results['summary']['passed_tests'] += $suiteStats['passed'];
                $results['summary']['failed_tests'] += $suiteStats['failed'];
                $results['summary']['error_tests'] += $suiteStats['errors'];
                $results['summary']['warning_tests'] += $suiteStats['warnings'];
                $results['summary']['skipped_tests'] += $suiteStats['skipped'];
            }
        }
        
        return $results;
    }
    
    private function generateJSONReport($results)
    {
        $jsonFile = $this->reportDir . '/' . $this->date . '_report.json';
        file_put_contents($jsonFile, json_encode($results, JSON_PRETTY_PRINT));
    }
    
    private function generateHTMLReport($results)
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIT Test Report - ' . $this->date . '</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .summary-card h3 {
            margin-top: 0;
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
        }
        .summary-card .value {
            font-size: 32px;
            font-weight: bold;
        }
        .passed { color: #10b981; }
        .failed { color: #ef4444; }
        .error { color: #f59e0b; }
        .warning { color: #eab308; }
        .suite {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .suite-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .test-case {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .test-case.passed { background: #d1fae5; }
        .test-case.failed { background: #fee2e2; }
        .test-case.error { background: #fed7aa; }
        .test-case.warning { background: #fef3c7; }
        .status-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-badge.passed { background: #10b981; color: white; }
        .status-badge.failed { background: #ef4444; color: white; }
        .status-badge.error { background: #f59e0b; color: white; }
        .status-badge.warning { background: #eab308; color: white; }
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧪 SIT Test Report</h1>
        <p>SACCOS Core System - System Integration Tests</p>
        <p>Date: ' . $results['date'] . ' | Generated: ' . $results['timestamp'] . '</p>
    </div>
    
    <div class="summary">
        <div class="summary-card">
            <h3>Total Suites</h3>
            <div class="value">' . $results['summary']['total_suites'] . '</div>
        </div>
        <div class="summary-card">
            <h3>Passed Suites</h3>
            <div class="value passed">' . $results['summary']['passed_suites'] . '</div>
        </div>
        <div class="summary-card">
            <h3>Failed Suites</h3>
            <div class="value failed">' . $results['summary']['failed_suites'] . '</div>
        </div>
        <div class="summary-card">
            <h3>Total Tests</h3>
            <div class="value">' . $results['summary']['total_tests'] . '</div>
        </div>
        <div class="summary-card">
            <h3>Passed Tests</h3>
            <div class="value passed">' . $results['summary']['passed_tests'] . '</div>
        </div>
        <div class="summary-card">
            <h3>Failed Tests</h3>
            <div class="value failed">' . $results['summary']['failed_tests'] . '</div>
        </div>
        <div class="summary-card">
            <h3>Error Tests</h3>
            <div class="value error">' . $results['summary']['error_tests'] . '</div>
        </div>
        <div class="summary-card">
            <h3>Success Rate</h3>
            <div class="value">' . 
            ($results['summary']['total_tests'] > 0 ? 
                round(($results['summary']['passed_tests'] / $results['summary']['total_tests']) * 100, 1) : 0) 
            . '%</div>
        </div>
    </div>';
        
        foreach ($results['test_suites'] as $suite) {
            $statusClass = strtolower($suite['status']);
            $html .= '
    <div class="suite">
        <div class="suite-header">
            <div>
                <h2>' . $suite['name'] . '</h2>
                <p style="color: #666; margin: 0;">Duration: ' . $suite['duration'] . 's | ' . $suite['timestamp'] . '</p>
            </div>
            <span class="status-badge ' . $statusClass . '">' . $suite['status'] . '</span>
        </div>';
            
            foreach ($suite['tests'] as $test) {
                $testStatusClass = strtolower($test['status']);
                $html .= '
        <div class="test-case ' . $testStatusClass . '">
            <div>
                <strong>' . $test['name'] . '</strong>
                <div style="font-size: 14px; color: #666; margin-top: 5px;">' . htmlspecialchars($test['message']) . '</div>
            </div>
            <span class="status-badge ' . $testStatusClass . '">' . $test['status'] . '</span>
        </div>';
            }
            
            $html .= '
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5; color: #666; font-size: 14px;">
            Passed: ' . $suite['statistics']['passed'] . ' | 
            Failed: ' . $suite['statistics']['failed'] . ' | 
            Errors: ' . $suite['statistics']['errors'] . ' | 
            Warnings: ' . $suite['statistics']['warnings'] . '
        </div>
    </div>';
        }
        
        $html .= '
    <div class="footer">
        <p>Generated by SACCOS Core System SIT Test Suite</p>
    </div>
</body>
</html>';
        
        $htmlFile = $this->reportDir . '/' . $this->date . '_report.html';
        file_put_contents($htmlFile, $html);
    }
    
    private function generateMarkdownReport($results)
    {
        $md = "# SIT Test Report\n\n";
        $md .= "**Date:** {$results['date']}  \n";
        $md .= "**Generated:** {$results['timestamp']}\n\n";
        
        $md .= "## Summary\n\n";
        $md .= "| Metric | Value |\n";
        $md .= "|--------|-------|\n";
        $md .= "| Total Suites | {$results['summary']['total_suites']} |\n";
        $md .= "| Passed Suites | {$results['summary']['passed_suites']} |\n";
        $md .= "| Failed Suites | {$results['summary']['failed_suites']} |\n";
        $md .= "| Total Tests | {$results['summary']['total_tests']} |\n";
        $md .= "| Passed Tests | {$results['summary']['passed_tests']} |\n";
        $md .= "| Failed Tests | {$results['summary']['failed_tests']} |\n";
        $md .= "| Error Tests | {$results['summary']['error_tests']} |\n";
        $md .= "| Warning Tests | {$results['summary']['warning_tests']} |\n\n";
        
        $md .= "## Test Suites\n\n";
        
        foreach ($results['test_suites'] as $suite) {
            $icon = $suite['status'] === 'PASSED' ? '✅' : '❌';
            $md .= "### {$icon} {$suite['name']}\n\n";
            $md .= "- **Status:** {$suite['status']}\n";
            $md .= "- **Duration:** {$suite['duration']}s\n";
            $md .= "- **Timestamp:** {$suite['timestamp']}\n\n";
            
            if (!empty($suite['tests'])) {
                $md .= "| Test | Status | Message |\n";
                $md .= "|------|--------|----------|\n";
                
                foreach ($suite['tests'] as $test) {
                    $statusIcon = '';
                    switch ($test['status']) {
                        case 'PASSED': $statusIcon = '✅'; break;
                        case 'FAILED': $statusIcon = '❌'; break;
                        case 'ERROR': $statusIcon = '🔴'; break;
                        case 'WARNING': $statusIcon = '⚠️'; break;
                        default: $statusIcon = '❓';
                    }
                    $md .= "| {$test['name']} | {$statusIcon} {$test['status']} | {$test['message']} |\n";
                }
            }
            
            $md .= "\n**Statistics:** ";
            $md .= "Passed: {$suite['statistics']['passed']} | ";
            $md .= "Failed: {$suite['statistics']['failed']} | ";
            $md .= "Errors: {$suite['statistics']['errors']} | ";
            $md .= "Warnings: {$suite['statistics']['warnings']}\n\n";
            $md .= "---\n\n";
        }
        
        $mdFile = $this->reportDir . '/' . $this->date . '_report.md';
        file_put_contents($mdFile, $md);
    }
}

// Run the report generator
$generator = new TestReportGenerator();
$generator->generate();