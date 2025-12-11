#!/usr/bin/env php
<?php

/**
 * Zona AI - Claude CLI Integration
 * This script sends questions to Claude CLI and gets responses
 */

// Increase memory limit
ini_set('memory_limit', '512M');

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\033[1;36m";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          Zona AI - Claude CLI Integration                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\033[0m\n";

echo "\033[1;32m✓ Connected to SACCOS Core System\033[0m\n";
echo "\033[1;32m✓ Database connection established\033[0m\n";
echo "\033[1;33m⚡ Monitoring for requests...\033[0m\n\n";

$service = new \App\Services\LocalClaudeService();
$markerFile = storage_path('app/claude-bridge/claude-monitor.active');
$processedRequests = [];

// Signal handler for clean shutdown
pcntl_async_signals(true);
pcntl_signal(SIGINT, function() use ($markerFile) {
    echo "\n\033[1;31m✗ Shutting down Zona AI...\033[0m\n";
    @unlink($markerFile);
    exit(0);
});

// Main monitoring loop
while (true) {
    // Update marker file to show we're active
    touch($markerFile);
    
    // Check for pending requests
    $requests = $service->getPendingRequests();
    
    foreach ($requests as $request) {
        if (in_array($request['id'], $processedRequests)) {
            continue;
        }
        
        $processedRequests[] = $request['id'];
        
        echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
        echo "\033[1;35m📨 New Request from Laravel\033[0m\n";
        echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
        echo "\033[1;32mRequest ID:\033[0m " . $request['id'] . "\n";
        echo "\033[1;32mUser:\033[0m " . ($request['context']['user_name'] ?? 'Unknown') . "\n";
        echo "\033[1;32mTimestamp:\033[0m " . ($request['context']['timestamp'] ?? 'N/A') . "\n\n";
        
        echo "\033[1;33mUser Question:\033[0m\n";
        echo "\033[1;37m" . $request['message'] . "\033[0m\n\n";
        
        // Send the question to Claude CLI
        echo "\033[1;34m🤖 Sending to Claude CLI...\033[0m\n";
        
        $claudeResponse = askClaude($request['message']);
        
        if ($claudeResponse) {
            echo "\033[1;32m✓ Claude responded:\033[0m\n";
            echo $claudeResponse . "\n\n";
            
            // Send response back to Laravel
            $service->sendResponse($request['id'], $claudeResponse, [
                'responded_by' => 'Claude CLI',
                'timestamp' => now()->toIso8601String(),
                'has_project_context' => true
            ]);
            
            echo "\033[1;32m✓ Response sent to Laravel!\033[0m\n";
        } else {
            echo "\033[1;31m✗ Failed to get response from Claude CLI\033[0m\n";
            
            // Send error response
            $service->sendResponse($request['id'], "Failed to get response from Claude CLI. Please ensure Claude CLI is installed and configured.", [
                'error' => 'CLAUDE_CLI_ERROR',
                'timestamp' => now()->toIso8601String()
            ]);
        }
        
        echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n\n";
        
        // Keep only last 100 processed requests
        if (count($processedRequests) > 100) {
            $processedRequests = array_slice($processedRequests, -100);
        }
    }
    
    usleep(500000); // 500ms
}

/**
 * Send question to Claude CLI and get response
 */
function askClaude($question) {
    try {
        // Escape the question for shell execution
        $escapedQuestion = escapeshellarg($question);
        
        // Build the command to send to Claude CLI
        $command = "claude " . $escapedQuestion;
        
        echo "\033[1;33mExecuting: $command\033[0m\n";
        
        // Execute the command and capture output
        $output = shell_exec($command . " 2>&1");
        
        if ($output === null) {
            echo "\033[1;31m✗ No output from Claude CLI\033[0m\n";
            return false;
        }
        
        // Clean up the output
        $output = trim($output);
        
        // Check for common errors
        if (strpos($output, 'command not found') !== false) {
            echo "\033[1;31m✗ Claude CLI not found. Please install it first.\033[0m\n";
            return "Claude CLI is not installed. Please install it using: brew install claude";
        }
        
        if (strpos($output, 'error') !== false || strpos($output, 'Error') !== false) {
            echo "\033[1;31m✗ Claude CLI returned an error\033[0m\n";
            return false;
        }
        
        return $output;
        
    } catch (\Exception $e) {
        echo "\033[1;31m✗ Exception: " . $e->getMessage() . "\033[0m\n";
        return false;
    }
}