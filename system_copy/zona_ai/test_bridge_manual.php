#!/usr/bin/env php
<?php

/**
 * Manual Bridge Test
 * This script tests the bridge without timeouts
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\LocalClaudeService();

echo "\033[1;36m╔════════════════════════════════════════════════════════════════╗\n";
echo "║                Manual Bridge Test                              ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\033[0m\n\n";

// Check if bridge is running
if ($service->isAvailable()) {
    echo "\033[1;32m✅ Bridge is running!\033[0m\n\n";
} else {
    echo "\033[1;31m❌ Bridge is NOT running!\033[0m\n";
    echo "Please start the bridge: ./zona_ai/start_claude_bridge.sh\n";
    exit(1);
}

// Get test question
$question = $argv[1] ?? "How many members does the system have?";

echo "\033[1;33mSending question: $question\033[0m\n\n";

// Generate request ID
$requestId = uniqid('req_', true);

// Prepare request data
$requestData = [
    'id' => $requestId,
    'message' => $question,
    'context' => [
        'user_name' => 'Test User',
        'user_role' => 'Admin',
        'session_id' => 'test_' . uniqid(),
        'test_mode' => true,
        'timestamp' => now()->toIso8601String(),
        'project_path' => base_path()
    ]
];

// Write request
$requestFile = storage_path('app/claude-bridge/requests/' . $requestId . '.json');
file_put_contents($requestFile, json_encode($requestData, JSON_PRETTY_PRINT));

echo "\033[1;32m✓ Request written to: $requestFile\033[0m\n";
echo "\033[1;33m⏳ Waiting for Claude Code to respond...\033[0m\n\n";

echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
echo "\033[1;35m📋 INSTRUCTIONS FOR CLAUDE CODE:\033[0m\n";
echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
echo "1. Look at the bridge terminal for the question and context\n";
echo "2. Write your response using this command:\n";
echo "   \033[1;32mphp zona_ai/write_response.php $requestId \"Your response here\"\033[0m\n";
echo "3. The response will be sent back to the user\n";
echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n\n";

// Wait for response
$responseFile = storage_path('app/claude-bridge/responses/' . $requestId . '.json');
$startTime = time();
$timeout = 300; // 5 minutes

while (!file_exists($responseFile)) {
    if (time() - $startTime > $timeout) {
        echo "\033[1;31m❌ Timeout after 5 minutes\033[0m\n";
        break;
    }
    
    echo "\033[1;33m⏳ Still waiting... (" . (time() - $startTime) . "s elapsed)\033[0m\n";
    sleep(5);
}

if (file_exists($responseFile)) {
    $responseData = json_decode(file_get_contents($responseFile), true);
    
    echo "\033[1;32m✅ Response received!\033[0m\n\n";
    echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
    echo "\033[1;35m📨 RESPONSE:\033[0m\n";
    echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
    echo $responseData['message'] . "\n";
    echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n\n";
    
    // Clean up
    @unlink($requestFile);
    @unlink($responseFile);
    
    echo "\033[1;32m✓ Files cleaned up\033[0m\n";
} else {
    echo "\033[1;31m❌ No response received\033[0m\n";
}
