#!/usr/bin/env php
<?php

/**
 * SIT Test Results Viewer
 * 
 * Displays test results in a colorful, easy-to-read terminal format
 */

class TestResultsViewer
{
    private $colors = [
        'reset' => "\033[0m",
        'bold' => "\033[1m",
        'green' => "\033[32m",
        'red' => "\033[31m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
        'bg_green' => "\033[42m",
        'bg_red' => "\033[41m",
        'bg_yellow' => "\033[43m",
    ];
    
    public function display()
    {
        $reportFile = __DIR__ . '/reports/' . date('Y-m-d') . '_report.json';
        
        if (!file_exists($reportFile)) {
            $this->error("No test results found for today (" . date('Y-m-d') . ")");
            echo "Run the tests first: php sit-tests/run-all-tests.php\n\n";
            exit(1);
        }
        
        $results = json_decode(file_get_contents($reportFile), true);
        
        $this->displayHeader($results);
        $this->displaySummary($results['summary']);
        $this->displayTestSuites($results['test_suites']);
        $this->displayFooter($results);
    }
    
    private function displayHeader($results)
    {
        $this->line();
        echo $this->color('bold') . $this->color('cyan');
        echo "                  SIT TEST RESULTS VIEWER                  \n";
        echo $this->color('reset');
        $this->line();
        echo "  Date: " . $this->color('white') . $results['date'] . $this->color('reset');
        echo "  |  Generated: " . $this->color('white') . $results['timestamp'] . $this->color('reset') . "\n";
        $this->line();
    }
    
    private function displaySummary($summary)
    {
        echo "\n" . $this->color('bold') . "📊 TEST SUMMARY" . $this->color('reset') . "\n\n";
        
        // Calculate success rate
        $successRate = $summary['total_tests'] > 0 
            ? round(($summary['passed_tests'] / $summary['total_tests']) * 100, 1) 
            : 0;
        
        // Display progress bar
        $this->displayProgressBar($successRate);
        
        echo "\n";
        
        // Display statistics in columns
        $stats = [
            ['label' => 'Test Suites', 'value' => $summary['total_suites'], 'color' => 'white'],
            ['label' => 'Total Tests', 'value' => $summary['total_tests'], 'color' => 'white'],
            ['label' => 'Passed', 'value' => $summary['passed_tests'], 'color' => 'green'],
            ['label' => 'Failed', 'value' => $summary['failed_tests'], 'color' => 'red'],
            ['label' => 'Errors', 'value' => $summary['error_tests'], 'color' => 'yellow'],
            ['label' => 'Warnings', 'value' => $summary['warning_tests'], 'color' => 'magenta'],
        ];
        
        foreach ($stats as $stat) {
            echo sprintf(
                "  %-15s: %s%3d%s\n",
                $stat['label'],
                $this->color($stat['color']) . $this->color('bold'),
                $stat['value'],
                $this->color('reset')
            );
        }
        
        echo "\n";
    }
    
    private function displayProgressBar($percentage)
    {
        $width = 50;
        $filled = (int)($width * $percentage / 100);
        $empty = $width - $filled;
        
        echo "  Success Rate: ";
        echo "[";
        
        if ($percentage >= 80) {
            echo $this->color('green');
        } elseif ($percentage >= 60) {
            echo $this->color('yellow');
        } else {
            echo $this->color('red');
        }
        
        echo str_repeat("█", $filled);
        echo $this->color('reset');
        echo str_repeat("░", $empty);
        echo "] ";
        
        echo $this->color('bold') . sprintf("%5.1f%%", $percentage) . $this->color('reset');
    }
    
    private function displayTestSuites($suites)
    {
        echo "\n" . $this->color('bold') . "🧪 TEST SUITE DETAILS" . $this->color('reset') . "\n";
        
        foreach ($suites as $suite) {
            echo "\n";
            $this->displaySuite($suite);
        }
    }
    
    private function displaySuite($suite)
    {
        // Suite header
        $statusIcon = $suite['status'] === 'PASSED' ? '✅' : '❌';
        $statusColor = $suite['status'] === 'PASSED' ? 'green' : 'red';
        
        echo $this->color('bold');
        echo "  $statusIcon " . $suite['name'];
        echo " [" . $this->color($statusColor) . $suite['status'] . $this->color('reset') . $this->color('bold') . "]";
        echo $this->color('reset') . "\n";
        
        echo "     Duration: " . $suite['duration'] . "s | ";
        echo "Tests: " . count($suite['tests']) . " | ";
        echo "Passed: " . $this->color('green') . $suite['statistics']['passed'] . $this->color('reset') . " | ";
        echo "Failed: " . $this->color('red') . $suite['statistics']['failed'] . $this->color('reset');
        
        if ($suite['statistics']['errors'] > 0) {
            echo " | Errors: " . $this->color('yellow') . $suite['statistics']['errors'] . $this->color('reset');
        }
        if ($suite['statistics']['warnings'] > 0) {
            echo " | Warnings: " . $this->color('magenta') . $suite['statistics']['warnings'] . $this->color('reset');
        }
        echo "\n";
        
        // Show failed/error tests
        $problemTests = array_filter($suite['tests'], function($test) {
            return !in_array($test['status'], ['PASSED', 'UNKNOWN']);
        });
        
        if (!empty($problemTests)) {
            echo "\n";
            foreach ($problemTests as $test) {
                $this->displayTestCase($test);
            }
        }
    }
    
    private function displayTestCase($test)
    {
        $icons = [
            'PASSED' => '✓',
            'FAILED' => '✗',
            'ERROR' => '⚠',
            'WARNING' => '⚠',
            'UNKNOWN' => '?'
        ];
        
        $colors = [
            'PASSED' => 'green',
            'FAILED' => 'red',
            'ERROR' => 'yellow',
            'WARNING' => 'magenta',
            'UNKNOWN' => 'white'
        ];
        
        $icon = $icons[$test['status']] ?? '?';
        $color = $colors[$test['status']] ?? 'white';
        
        echo "     " . $this->color($color) . $icon . " " . $test['name'] . $this->color('reset') . "\n";
        
        if (!empty($test['message']) && $test['status'] !== 'PASSED') {
            $message = wordwrap($test['message'], 70, "\n       ");
            echo "       " . $this->color('white') . $message . $this->color('reset') . "\n";
        }
    }
    
    private function displayFooter($results)
    {
        $this->line();
        
        // Display log file locations
        echo "\n" . $this->color('bold') . "📁 LOG FILES" . $this->color('reset') . "\n\n";
        
        $logDir = __DIR__ . '/logs';
        $reportDir = __DIR__ . '/reports';
        
        echo "  Test Logs:    " . $this->color('cyan') . $logDir . "/" . date('Y-m-d') . "_test_results.log" . $this->color('reset') . "\n";
        echo "  HTML Report:  " . $this->color('cyan') . $reportDir . "/" . date('Y-m-d') . "_report.html" . $this->color('reset') . "\n";
        echo "  JSON Report:  " . $this->color('cyan') . $reportDir . "/" . date('Y-m-d') . "_report.json" . $this->color('reset') . "\n";
        echo "  MD Report:    " . $this->color('cyan') . $reportDir . "/" . date('Y-m-d') . "_report.md" . $this->color('reset') . "\n";
        
        echo "\n";
        
        // Display commands
        echo $this->color('bold') . "🚀 USEFUL COMMANDS" . $this->color('reset') . "\n\n";
        echo "  Run all tests:        " . $this->color('yellow') . "php sit-tests/run-all-tests.php" . $this->color('reset') . "\n";
        echo "  Run specific test:    " . $this->color('yellow') . "php sit-tests/run-all-tests.php --test=TestName" . $this->color('reset') . "\n";
        echo "  Generate reports:     " . $this->color('yellow') . "php sit-tests/generate-report.php" . $this->color('reset') . "\n";
        echo "  View this summary:    " . $this->color('yellow') . "php sit-tests/view-results.php" . $this->color('reset') . "\n";
        
        echo "\n";
        $this->line();
        echo "\n";
    }
    
    private function line()
    {
        echo str_repeat("═", 60) . "\n";
    }
    
    private function color($name)
    {
        // Check if terminal supports colors
        if (!$this->supportsColors()) {
            return '';
        }
        
        return $this->colors[$name] ?? '';
    }
    
    private function supportsColors()
    {
        // Check if running in a terminal that supports colors
        if (DIRECTORY_SEPARATOR === '\\') {
            return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI') || 'xterm' === getenv('TERM');
        }
        
        return function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }
    
    private function error($message)
    {
        echo $this->color('red') . $this->color('bold') . "❌ ERROR: " . $message . $this->color('reset') . "\n";
    }
}

// Parse command line options
$options = getopt('h', ['help', 'date:', 'no-color']);

if (isset($options['h']) || isset($options['help'])) {
    echo "\nUsage: php view-results.php [options]\n";
    echo "\nOptions:\n";
    echo "  -h, --help       Show this help message\n";
    echo "  --date=<date>    View results for specific date (YYYY-MM-DD)\n";
    echo "  --no-color       Disable colored output\n";
    echo "\nExamples:\n";
    echo "  php view-results.php                    # View today's results\n";
    echo "  php view-results.php --date=2024-01-15  # View results for specific date\n\n";
    exit(0);
}

// Handle date option
if (isset($options['date'])) {
    $_SERVER['TEST_DATE'] = $options['date'];
}

// Run the viewer
$viewer = new TestResultsViewer();
$viewer->display();