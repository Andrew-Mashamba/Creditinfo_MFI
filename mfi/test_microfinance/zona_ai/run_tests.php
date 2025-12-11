#!/usr/bin/env php
<?php

/**
 * Test Runner for HybridAiService
 * Runs 10 test questions from test_questions.md
 */

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\033[1;36m";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           MFI AI Test Suite - 10 Questions                 ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\033[0m\n";

$service = new \App\Services\HybridAiService();

// Test questions from test_questions.md
$testQuestions = [
    1 => "How many total members does the SACCO have?",
    2 => "What are the names of all branches?",
    3 => "How many users are registered in the system?",
    4 => "What is the total number of active loans?",
    5 => "How many savings accounts are active?",
    6 => "What types of loan products are available?",
    7 => "How many transactions were processed today?",
    8 => "What are the names of all system users?",
    9 => "How many accounts are in the system?",
    10 => "Which branch was created first?"
];

$results = [];
$successCount = 0;
$failureCount = 0;
$totalTime = 0;

foreach ($testQuestions as $num => $question) {
    echo "\033[1;33m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
    echo "\033[1;36mTest #$num:\033[0m $question\n";
    echo "\033[1;33m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
    
    $startTime = microtime(true);
    
    try {
        // Process the question
        $response = $service->processMessage($question);
        
        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);
        $totalTime += $executionTime;
        
        if ($response['success']) {
            echo "\033[1;32m✓ Success\033[0m (Time: {$executionTime}s)\n";
            echo "\033[1;37mResponse:\033[0m\n";
            
            // Clean up the response for display
            $cleanResponse = strip_tags($response['message']);
            $cleanResponse = str_replace(['**', '__'], '', $cleanResponse);
            echo wordwrap($cleanResponse, 60, "\n") . "\n";
            
            $successCount++;
            $results[$num] = [
                'status' => 'success',
                'time' => $executionTime,
                'response' => substr($cleanResponse, 0, 100) . '...'
            ];
        } else {
            echo "\033[1;31m✗ Failed\033[0m (Time: {$executionTime}s)\n";
            echo "\033[1;31mError:\033[0m " . $response['message'] . "\n";
            
            $failureCount++;
            $results[$num] = [
                'status' => 'failed',
                'time' => $executionTime,
                'error' => $response['error'] ?? 'Unknown error'
            ];
        }
        
    } catch (\Exception $e) {
        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);
        $totalTime += $executionTime;
        
        echo "\033[1;31m✗ Exception\033[0m (Time: {$executionTime}s)\n";
        echo "\033[1;31mError:\033[0m " . $e->getMessage() . "\n";
        
        $failureCount++;
        $results[$num] = [
            'status' => 'exception',
            'time' => $executionTime,
            'error' => $e->getMessage()
        ];
    }
    
    echo "\n";
    
    // Small delay between questions
    usleep(500000); // 0.5 seconds
}

// Summary
echo "\033[1;36m";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                         TEST SUMMARY                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\033[0m\n";

echo "\033[1;32m✓ Successful:\033[0m $successCount\n";
echo "\033[1;31m✗ Failed:\033[0m $failureCount\n";
echo "\033[1;33m⏱ Total Time:\033[0m " . round($totalTime, 2) . " seconds\n";
echo "\033[1;33m⏱ Average Time:\033[0m " . round($totalTime / count($testQuestions), 2) . " seconds\n";

$successRate = round(($successCount / count($testQuestions)) * 100, 2);
if ($successRate >= 95) {
    echo "\033[1;32m🎉 Success Rate: {$successRate}% - EXCELLENT!\033[0m\n";
} elseif ($successRate >= 80) {
    echo "\033[1;33m📊 Success Rate: {$successRate}% - Good\033[0m\n";
} else {
    echo "\033[1;31m⚠ Success Rate: {$successRate}% - Needs Improvement\033[0m\n";
}

// Detailed results
echo "\n\033[1;36mDetailed Results:\033[0m\n";
echo "┌─────┬──────────┬──────────┬─────────────────────────────────────┐\n";
echo "│ Test│ Status   │ Time (s) │ Result                              │\n";
echo "├─────┼──────────┼──────────┼─────────────────────────────────────┤\n";

foreach ($results as $num => $result) {
    $statusIcon = $result['status'] === 'success' ? '✓' : '✗';
    $statusColor = $result['status'] === 'success' ? "\033[1;32m" : "\033[1;31m";
    $statusText = $result['status'] === 'success' ? 'Success' : 'Failed';
    
    $resultText = $result['status'] === 'success' 
        ? substr($result['response'], 0, 35)
        : substr($result['error'], 0, 35);
    
    printf("│ %3d │ %s%-8s\033[0m │ %8.2f │ %-35s │\n", 
        $num,
        $statusColor,
        $statusText,
        $result['time'],
        $resultText
    );
}

echo "└─────┴──────────┴──────────┴─────────────────────────────────────┘\n";

// Check service status
echo "\n\033[1;36mService Status:\033[0m\n";
$status = $service->getStatus();
foreach ($status as $key => $value) {
    if (is_bool($value)) {
        $value = $value ? 'Yes' : 'No';
    }
    echo "• " . str_replace('_', ' ', ucfirst($key)) . ": $value\n";
}

echo "\n\033[1;32m✓ Test suite completed!\033[0m\n";