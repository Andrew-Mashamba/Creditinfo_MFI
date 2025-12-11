#!/usr/bin/env php
<?php

/**
 * Test Zona AI with sample questions
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\LocalClaudeService();

$testQuestions = [
    // Dashboard Module
    "How many total members does the SACCO have?",
    "What is the total number of active loans?",
    "What is the total savings balance?",
    "How many branches are currently operational?",
    "How many transactions were processed today?",
    
    // Branches Module
    "How many branches does the SACCO have?",
    "What are the names of all branches?",
    
    // Members Module
    "How many members are registered in the system?",
    "What are the names of all members?",
    "How many active members are there?",
    
    // User Management
    "How many users are registered in the system?",
    "What are the names of all system users?",
    
    // Loans Module
    "How many loans are currently active?",
    "How many loan applications are pending?",
    
    // System Info
    "What version of the SACCOS system is running?",
    "How many modules are active?",
    "Show system overview",
];

$results = [];
$passed = 0;
$failed = 0;

echo "\033[1;36m╔════════════════════════════════════════════════════════════════╗\n";
echo "║                   Zona AI Test Suite                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\033[0m\n\n";

foreach ($testQuestions as $index => $question) {
    $num = $index + 1;
    echo "\033[1;33mTest $num: $question\033[0m\n";
    
    // Send question
    $response = $service->sendMessage($question, [
        'test_mode' => true,
        'test_number' => $num
    ]);
    
    if ($response['success']) {
        $answer = substr($response['message'], 0, 150);
        
        // Check if it's a real answer (not the default help message)
        if (strpos($response['message'], 'I can help you with information') === false) {
            echo "\033[1;32m✅ PASSED\033[0m - $answer...\n";
            $passed++;
            $results[] = ['question' => $question, 'status' => 'PASSED', 'answer' => $response['message']];
        } else {
            echo "\033[1;31m❌ FAILED\033[0m - Got default help message\n";
            $failed++;
            $results[] = ['question' => $question, 'status' => 'FAILED', 'answer' => 'Default message'];
        }
    } else {
        echo "\033[1;31m❌ ERROR\033[0m - " . $response['message'] . "\n";
        $failed++;
        $results[] = ['question' => $question, 'status' => 'ERROR', 'answer' => $response['message']];
    }
    
    echo "\n";
    usleep(500000); // Wait 500ms between questions
}

// Summary
echo "\033[1;36m════════════════════════════════════════════════════════════════\n";
echo "                         TEST SUMMARY                           \n";
echo "════════════════════════════════════════════════════════════════\033[0m\n";
echo "\033[1;32mPassed: $passed\033[0m\n";
echo "\033[1;31mFailed: $failed\033[0m\n";
echo "Success Rate: " . round(($passed / count($testQuestions)) * 100, 2) . "%\n\n";

// Save results
$reportFile = __DIR__ . '/test_results_' . date('Y-m-d_H-i-s') . '.json';
file_put_contents($reportFile, json_encode($results, JSON_PRETTY_PRINT));
echo "Results saved to: $reportFile\n";