#!/usr/bin/env php
<?php

/**
 * Claude Code Bridge - Proper Implementation
 * This script ONLY provides context to Claude Code and waits for Claude Code to respond
 * It does NOT auto-respond - Claude Code must provide the actual answer
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
echo "║        Claude Code Bridge - Proper Implementation             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\033[0m\n";

echo "\033[1;32m✓ Connected to SACCOS Core System\033[0m\n";
echo "\033[1;32m✓ Database connection established\033[0m\n";
echo "\033[1;33m⚡ Monitoring for requests - Claude Code must answer\033[0m\n\n";

$service = new \App\Services\LocalClaudeService();
$markerFile = storage_path('app/claude-bridge/claude-monitor.active');
$processedRequests = [];

// Signal handler for clean shutdown
pcntl_async_signals(true);
pcntl_signal(SIGINT, function() use ($markerFile) {
    echo "\n\033[1;31m✗ Shutting down Claude Code Bridge...\033[0m\n";
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
        
        // Gather database context
        $context = gatherDatabaseContext($request['message']);
        
        echo "\033[1;34m📊 Database Context Gathered:\033[0m\n";
        echo $context . "\n";
        
        echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
        echo "\033[1;35m🤖 CLAUDE CODE - PLEASE ANSWER THIS QUESTION:\033[0m\n\n";
        
        echo "**User Question:** \"" . $request['message'] . "\"\n\n";
        echo "**User:** " . ($request['context']['user_name'] ?? 'Unknown') . "\n\n";
        echo "**Database Context:**\n" . $context . "\n\n";
        echo "**Instructions for Claude Code:**\n";
        echo "1. You have full access to the SACCOS Core System at: \033[1;32m" . base_path() . "\033[0m\n";
        echo "2. Use the database context above along with your knowledge of the codebase\n";
        echo "3. Provide a comprehensive, accurate answer based on real data\n";
        echo "4. If it's a greeting, respond friendly and explain your capabilities\n";
        echo "5. If it's about specific data, use the context provided\n";
        echo "6. If it's about code/features, reference the actual implementation\n\n";
        echo "**IMPORTANT:** You must write your answer to: \033[1;32m" . storage_path('app/claude-bridge/responses/' . $request['id'] . '.json') . "\033[0m\n\n";
        
        echo "\033[1;33m⏳ Waiting for Claude Code to respond...\033[0m\n";
        echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n\n";
        
        // Create a signal file for Claude Code to know there's a pending request
        $signalFile = storage_path('app/claude-bridge/pending/' . $request['id'] . '.signal');
        @mkdir(dirname($signalFile), 0755, true);
        file_put_contents($signalFile, json_encode([
            'request_id' => $request['id'],
            'message' => $request['message'],
            'context' => $context,
            'timestamp' => now()->toIso8601String()
        ], JSON_PRETTY_PRINT));
        
        // Wait for Claude Code to respond (don't auto-respond)
        $responseFile = storage_path('app/claude-bridge/responses/' . $request['id'] . '.json');
        $startTime = time();
        $timeout = 300; // 5 minutes timeout for complex queries
        $checkInterval = 0;
        
        while (!file_exists($responseFile)) {
            if (time() - $startTime > $timeout) {
                echo "\033[1;31m❌ Timeout waiting for Claude Code response after {$timeout} seconds\033[0m\n";
                
                // Send timeout message
                $service->sendResponse($request['id'], "Claude Code did not respond within {$timeout} seconds. Please ensure Claude Code is monitoring and responding to requests.", [
                    'error' => 'TIMEOUT',
                    'timeout' => $timeout,
                    'timestamp' => now()->toIso8601String()
                ]);
                
                // Clean up signal file
                @unlink($signalFile);
                break;
            }
            
            // Show waiting dots every 5 seconds
            if ($checkInterval++ % 5 == 0) {
                echo ".";
            }
            
            // Wait before checking again
            usleep(1000000); // 1 second
        }
        
        if (file_exists($responseFile)) {
            echo "\n\033[1;32m✓ Claude Code responded!\033[0m\n";
            
            // Read and send the response
            $claudeResponse = json_decode(file_get_contents($responseFile), true);
            if ($claudeResponse) {
                $service->sendResponse($request['id'], $claudeResponse['message'] ?? 'Response received', $claudeResponse['metadata'] ?? []);
                echo "\033[1;32m✓ Response sent to Laravel!\033[0m\n\n";
            }
            
            // Clean up files
            @unlink($responseFile);
            @unlink($signalFile);
        }
        
        // Keep only last 100 processed requests
        if (count($processedRequests) > 100) {
            $processedRequests = array_slice($processedRequests, -100);
        }
    }
    
    usleep(500000); // 500ms
}

/**
 * Gather database context for Claude Code
 */
function gatherDatabaseContext($message) {
    $context = "";
    $message = strtolower($message);
    
    try {
        // Basic system statistics
        $stats = [
            'Total Members/Clients' => DB::table('clients')->count(),
            'Active Members' => DB::table('clients')->where('client_status', 'ACTIVE')->count(),
            'System Users' => DB::table('users')->count(),
            'Branches' => DB::table('branches')->count(),
            'Total Accounts' => DB::table('accounts')->count(),
            'Total Loans' => DB::table('loans')->count(),
            'Active Loans' => DB::table('loans')->where('status', 'ACTIVE')->count(),
            'Transactions Today' => DB::table('transactions')->whereDate('created_at', today())->count(),
        ];
        
        $context .= "**Current Database Statistics:**\n";
        foreach ($stats as $key => $value) {
            $context .= "• $key: $value\n";
        }
        $context .= "\n";
        
        // If asking about specific member
        if (preg_match('/(?:named|called|by the name|name)\s+(\w+)/i', $message, $matches)) {
            $searchName = $matches[1];
            
            $members = DB::table('clients')
                ->where(DB::raw('LOWER(first_name)'), 'like', '%' . strtolower($searchName) . '%')
                ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . strtolower($searchName) . '%')
                ->get();
            
            if (!$members->isEmpty()) {
                $context .= "**Found " . $members->count() . " member(s) matching '$searchName':**\n";
                foreach ($members as $member) {
                    $context .= "\nMember Details:\n";
                    $context .= "• Full Name: {$member->first_name} {$member->middle_name} {$member->last_name}\n";
                    $context .= "• Client Number: {$member->client_number}\n";
                    $context .= "• Account Number: {$member->account_number}\n";
                    $context .= "• Email: {$member->email}\n";
                    $context .= "• Phone: {$member->phone_number}\n";
                    $context .= "• Mobile: {$member->mobile_phone_number}\n";
                    $context .= "• Status: {$member->client_status}\n";
                    $context .= "• Branch: {$member->branch}\n";
                    $context .= "• Registration Date: {$member->registration_date}\n";
                    
                    // Get accounts
                    $accounts = DB::table('accounts')
                        ->where('client_number', $member->client_number)
                        ->count();
                    $context .= "• Number of Accounts: $accounts\n";
                    
                    // Get loans
                    $loans = DB::table('loans')
                        ->where('client_number', $member->client_number)
                        ->count();
                    $context .= "• Number of Loans: $loans\n";
                }
            } else {
                $context .= "**No members found matching '$searchName'**\n";
            }
        }
        
        // If asking about branches
        if (strpos($message, 'branch') !== false) {
            $branches = DB::table('branches')->get();
            $context .= "\n**Branch Information:**\n";
            foreach ($branches as $branch) {
                $context .= "• {$branch->name} (Branch #{$branch->branch_number})\n";
                $context .= "  - Region: {$branch->region}\n";
                $context .= "  - Address: {$branch->address}\n";
                $context .= "  - Email: {$branch->email}\n";
                $context .= "  - Phone: {$branch->phone_number}\n";
            }
        }
        
        // If asking about users
        if (strpos($message, 'user') !== false && (strpos($message, 'system') !== false || strpos($message, 'name') !== false)) {
            $users = DB::table('users')->get();
            $context .= "\n**System Users:**\n";
            foreach ($users as $user) {
                $context .= "• {$user->name} - {$user->email}\n";
            }
        }
        
        // If asking about members
        if (strpos($message, 'member') !== false && strpos($message, 'name') !== false) {
            $members = DB::table('clients')->select('first_name', 'last_name', 'client_number')->limit(20)->get();
            $context .= "\n**Members in System:**\n";
            foreach ($members as $member) {
                $context .= "• {$member->first_name} {$member->last_name} (Client #{$member->client_number})\n";
            }
        }
        
        // Add system information
        $context .= "\n**System Information:**\n";
        $context .= "• System Name: SACCOS Core System Template\n";
        $context .= "• Version: 6.0\n";
        $context .= "• Database: PostgreSQL\n";
        $context .= "• Framework: Laravel 9.x with Livewire\n";
        $context .= "• Total Modules: 31\n";
        $context .= "• Project Location: " . base_path() . "\n";
        
    } catch (\Exception $e) {
        $context .= "\n**Error gathering context:** " . $e->getMessage() . "\n";
        $context .= "Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
    return $context;
}
