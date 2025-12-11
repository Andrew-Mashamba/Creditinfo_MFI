#!/usr/bin/env php
<?php

/**
 * Claude Bridge - Context Provider Only
 * This script ONLY provides context to Claude Code
 * It does NOT answer questions - Claude Code does that
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
echo "║     Claude Bridge - Context Provider for Claude Code          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\033[0m\n";

echo "\033[1;32m✓ Connected to database\033[0m\n";
echo "\033[1;33m⚡ Providing context to Claude Code (not answering directly)...\033[0m\n\n";

$service = new \App\Services\LocalClaudeService();
$markerFile = storage_path('app/claude-bridge/claude-monitor.active');
$processedRequests = [];

// Signal handler for clean shutdown
pcntl_async_signals(true);
pcntl_signal(SIGINT, function() use ($markerFile) {
    echo "\n\033[1;31m✗ Shutting down Claude Bridge...\033[0m\n";
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
        echo "\033[1;35m📨 Request from: " . ($request['context']['user_name'] ?? 'Unknown') . "\033[0m\n";
        echo "\033[1;33mQuestion: " . $request['message'] . "\033[0m\n\n";
        
        // Gather database context
        $context = gatherContext($request['message']);
        
        echo "\033[1;34m📊 Context gathered from database:\033[0m\n";
        echo $context . "\n";
        
        echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
        echo "\033[1;35m🤖 Claude Code, please answer this question:\033[0m\n\n";
        
        // Build message for Claude Code
        $messageForClaude = "**User Question:** \"" . $request['message'] . "\"\n\n";
        $messageForClaude .= "**User:** " . ($request['context']['user_name'] ?? 'Unknown') . "\n\n";
        $messageForClaude .= "**Database Context:**\n" . $context . "\n\n";
        $messageForClaude .= "**Instructions:**\n";
        $messageForClaude .= "1. You have full access to the SACCOS Core System at: " . base_path() . "\n";
        $messageForClaude .= "2. Use the database context above along with your knowledge of the codebase\n";
        $messageForClaude .= "3. Provide a comprehensive, accurate answer based on real data\n";
        $messageForClaude .= "4. If it's a greeting, respond friendly and explain your capabilities\n";
        $messageForClaude .= "5. If it's about specific data, use the context provided\n";
        $messageForClaude .= "6. If it's about code/features, reference the actual implementation\n\n";
        $messageForClaude .= "Please provide your answer now.";
        
        echo $messageForClaude . "\n";
        echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n\n";
        
        // For now, send the context as a response that Claude Code should replace
        $response = "[Claude Code should answer this question using the context provided]\n\n" . 
                   "Question: " . $request['message'] . "\n\n" .
                   "Context Available:\n" . $context;
        
        // Send placeholder response
        $service->sendResponse($request['id'], $response, [
            'needs_claude_code_answer' => true,
            'context_provided' => true,
            'timestamp' => now()->toIso8601String()
        ]);
        
        echo "\033[1;32m✓ Context provided - awaiting Claude Code's answer\033[0m\n\n";
        
        // Keep only last 100 processed requests
        if (count($processedRequests) > 100) {
            $processedRequests = array_slice($processedRequests, -100);
        }
    }
    
    usleep(500000); // 500ms
}

/**
 * Gather context from database - DO NOT ANSWER, just provide data
 */
function gatherContext($message) {
    $context = "";
    $message = strtolower($message);
    
    try {
        // Always provide basic statistics
        $stats = [
            'Total Members/Clients' => DB::table('clients')->count(),
            'Active Members' => DB::table('clients')->where('client_status', 'ACTIVE')->count(),
            'System Users' => DB::table('users')->count(),
            'Branches' => DB::table('branches')->count(),
            'Total Accounts' => DB::table('accounts')->count(),
            'Total Loans' => DB::table('loans')->count(),
            'Active Loans' => DB::table('loans')->where('status', 'ACTIVE')->count(),
        ];
        
        $context .= "**Database Statistics:**\n";
        foreach ($stats as $key => $value) {
            $context .= "- $key: $value\n";
        }
        
        // If asking about specific member
        if (preg_match('/(?:named|called|by the name|name)\s+(\w+)/i', $message, $matches)) {
            $searchName = $matches[1];
            
            $members = DB::table('clients')
                ->where(DB::raw('LOWER(first_name)'), 'like', '%' . strtolower($searchName) . '%')
                ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . strtolower($searchName) . '%')
                ->get();
            
            $context .= "\n**Members matching '$searchName':** " . $members->count() . " found\n";
            foreach ($members as $member) {
                $context .= "\nMember Data:\n";
                foreach ($member as $key => $value) {
                    if ($value !== null) {
                        $context .= "- $key: $value\n";
                    }
                }
            }
        }
        
        // If asking about members list
        if ((strpos($message, 'member') !== false || strpos($message, 'client') !== false) && 
            (strpos($message, 'name') !== false || strpos($message, 'list') !== false || strpos($message, 'all') !== false)) {
            
            $members = DB::table('clients')
                ->select('id', 'first_name', 'last_name', 'client_number', 'email', 'client_status')
                ->limit(10)
                ->get();
            
            $context .= "\n**Member List (first 10):**\n";
            foreach ($members as $member) {
                $context .= "- {$member->first_name} {$member->last_name} (Client #{$member->client_number})\n";
            }
        }
        
        // If asking about branches
        if (strpos($message, 'branch') !== false) {
            $branches = DB::table('branches')->get();
            $context .= "\n**Branches:** " . $branches->count() . " total\n";
            foreach ($branches as $branch) {
                $context .= "- {$branch->name} (Branch #{$branch->branch_number}, Region: {$branch->region})\n";
            }
        }
        
        // If asking about users
        if (strpos($message, 'user') !== false) {
            $users = DB::table('users')->select('name', 'email')->get();
            $context .= "\n**System Users:** " . $users->count() . " total\n";
            foreach ($users as $user) {
                $context .= "- {$user->name} ({$user->email})\n";
            }
        }
        
        // Add project info
        $context .= "\n**Project Info:**\n";
        $context .= "- Path: " . base_path() . "\n";
        $context .= "- Framework: Laravel 9.x with Livewire\n";
        $context .= "- Database: PostgreSQL\n";
        $context .= "- Modules: 31 active\n";
        
    } catch (\Exception $e) {
        $context .= "\n**Error gathering context:** " . $e->getMessage() . "\n";
    }
    
    return $context;
}