#!/usr/bin/env php
<?php

/**
 * Response Writer for Claude Code
 * Use this script to write responses to the bridge
 * 
 * Usage: php zona_ai/write_response.php <request_id> <response_message>
 */

if ($argc < 3) {
    echo "Usage: php zona_ai/write_response.php <request_id> <response_message>\n";
    echo "Example: php zona_ai/write_response.php req_123456 'Hello! I can help you with...'\n";
    exit(1);
}

$requestId = $argv[1];
$responseMessage = $argv[2];

// For multi-word responses, join the remaining arguments
if ($argc > 3) {
    $responseMessage = implode(' ', array_slice($argv, 2));
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\LocalClaudeService();

$responseData = [
    'id' => $requestId,
    'message' => $responseMessage,
    'context' => [
        'answered_by' => 'Claude Code',
        'answered_at' => now()->toIso8601String(),
        'method' => 'direct_response'
    ],
    'timestamp' => now()->toIso8601String()
];

$responseFile = storage_path('app/claude-bridge/responses/' . $requestId . '.json');

if ($service->sendResponse($requestId, $responseMessage, $responseData['context'])) {
    echo "✓ Response written successfully!\n";
    echo "Request ID: $requestId\n";
    echo "Response: " . substr($responseMessage, 0, 100) . "...\n";
    echo "File: $responseFile\n";
} else {
    echo "❌ Failed to write response\n";
    exit(1);
}
