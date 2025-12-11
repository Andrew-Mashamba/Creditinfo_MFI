#!/usr/bin/env php
<?php

/**
 * Zona Context Provider
 * Provides database context to Claude Code for accurate responses
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
echo "║      Zona Context Provider - Bridging to Claude Code          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\033[0m\n";

echo "\033[1;32m✓ Connected to database\033[0m\n";
echo "\033[1;33m⚡ Providing context to Claude Code...\033[0m\n\n";

$service = new \App\Services\LocalClaudeService();
$markerFile = storage_path('app/claude-bridge/claude-monitor.active');
$processedRequests = [];

// Signal handler for clean shutdown
pcntl_async_signals(true);
pcntl_signal(SIGINT, function() use ($markerFile) {
    echo "\n\033[1;31m✗ Shutting down Zona Context Provider...\033[0m\n";
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
        echo "\033[1;35m📨 Processing Request from Laravel\033[0m\n";
        echo "\033[1;33mQuestion: " . $request['message'] . "\033[0m\n\n";
        
        // Gather context from database
        $context = gatherDatabaseContext($request['message']);
        
        // Create enhanced message for Claude Code
        $enhancedMessage = "**User Question:** " . $request['message'] . "\n\n";
        $enhancedMessage .= "**Database Context:**\n" . $context . "\n\n";
        $enhancedMessage .= "**Instructions for Claude Code:**\n";
        $enhancedMessage .= "1. You have full access to the SACCOS Core System project at: " . base_path() . "\n";
        $enhancedMessage .= "2. Use the database context provided above along with your knowledge of the codebase\n";
        $enhancedMessage .= "3. If the question is about specific data, use the context provided\n";
        $enhancedMessage .= "4. If the question is about code or functionality, refer to the actual files\n";
        $enhancedMessage .= "5. Always provide accurate, specific answers based on real data\n\n";
        $enhancedMessage .= "Please answer the user's question comprehensively.";
        
        // Send to Claude Code (this would be picked up by the actual Claude Code instance)
        $response = "I need Claude Code to answer this. Context has been gathered:\n\n" . $context;
        
        // In production, this would wait for Claude Code's actual response
        // For now, we'll mark it as needing Claude Code's attention
        $service->sendResponse($request['id'], $enhancedMessage, [
            'needs_claude_code' => true,
            'context_provided' => true,
            'database_context' => $context,
            'timestamp' => now()->toIso8601String()
        ]);
        
        echo "\033[1;32m✓ Context provided to Claude Code!\033[0m\n";
        echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n\n";
        
        // Keep only last 100 processed requests
        if (count($processedRequests) > 100) {
            $processedRequests = array_slice($processedRequests, -100);
        }
    }
    
    usleep(500000); // 500ms
}

/**
 * Gather relevant database context based on the question
 */
function gatherDatabaseContext($message) {
    $context = "";
    $message = strtolower($message);
    
    try {
        // Always provide basic statistics
        $stats = [
            'Total Members' => DB::table('clients')->count(),
            'Active Members' => DB::table('clients')->where('client_status', 'ACTIVE')->count(),
            'Total Users' => DB::table('users')->count(),
            'Total Branches' => DB::table('branches')->count(),
            'Total Accounts' => DB::table('accounts')->count(),
            'Total Loans' => DB::table('loans')->count(),
            'Active Loans' => DB::table('loans')->where('status', 'ACTIVE')->count(),
        ];
        
        $context .= "**System Statistics:**\n";
        foreach ($stats as $key => $value) {
            $context .= "- $key: $value\n";
        }
        $context .= "\n";
        
        // Member-specific queries
        if (strpos($message, 'member') !== false || strpos($message, 'client') !== false) {
            // Check for specific name
            if (preg_match('/(?:named|called|by the name|name)\s+(\w+)/i', $message, $matches)) {
                $searchName = $matches[1];
                
                $members = DB::table('clients')
                    ->where(DB::raw('LOWER(first_name)'), 'like', '%' . strtolower($searchName) . '%')
                    ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . strtolower($searchName) . '%')
                    ->select('id', 'first_name', 'last_name', 'client_number', 'email', 'phone_number', 'client_status')
                    ->get();
                
                if (!$members->isEmpty()) {
                    $context .= "**Members matching '$searchName':**\n";
                    foreach ($members as $member) {
                        $context .= "- Name: {$member->first_name} {$member->last_name}\n";
                        $context .= "  Client Number: {$member->client_number}\n";
                        $context .= "  Email: {$member->email}\n";
                        $context .= "  Phone: {$member->phone_number}\n";
                        $context .= "  Status: {$member->client_status}\n";
                        
                        // Get account count
                        $accountCount = DB::table('accounts')
                            ->where('client_number', $member->client_number)
                            ->count();
                        $context .= "  Accounts: $accountCount\n\n";
                    }
                } else {
                    $context .= "**No members found matching '$searchName'**\n\n";
                }
            }
            
            // List all members if requested
            if (strpos($message, 'all') !== false || strpos($message, 'list') !== false) {
                $members = DB::table('clients')
                    ->select('first_name', 'last_name', 'client_number')
                    ->limit(10)
                    ->get();
                
                $context .= "**Members (first 10):**\n";
                foreach ($members as $member) {
                    $context .= "- {$member->first_name} {$member->last_name} (#{$member->client_number})\n";
                }
                $context .= "\n";
            }
        }
        
        // Branch queries
        if (strpos($message, 'branch') !== false) {
            $branches = DB::table('branches')
                ->select('name', 'branch_number', 'region', 'address')
                ->get();
            
            $context .= "**Branches:**\n";
            foreach ($branches as $branch) {
                $context .= "- {$branch->name} (#{$branch->branch_number})\n";
                $context .= "  Region: {$branch->region}\n";
                $context .= "  Address: {$branch->address}\n\n";
            }
        }
        
        // User queries
        if (strpos($message, 'user') !== false) {
            if (strpos($message, 'name') !== false || strpos($message, 'who') !== false) {
                $users = DB::table('users')
                    ->select('name', 'email')
                    ->get();
                
                $context .= "**System Users:**\n";
                foreach ($users as $user) {
                    $context .= "- {$user->name} ({$user->email})\n";
                }
                $context .= "\n";
            }
        }
        
        // Loan queries
        if (strpos($message, 'loan') !== false) {
            $loanStats = [
                'Total Loans' => DB::table('loans')->count(),
                'Active Loans' => DB::table('loans')->where('status', 'ACTIVE')->count(),
                'Pending Loans' => DB::table('loans')->where('status', 'PENDING')->count(),
                'Disbursed Loans' => DB::table('loans')->where('status', 'DISBURSED')->count(),
            ];
            
            $context .= "**Loan Statistics:**\n";
            foreach ($loanStats as $key => $value) {
                $context .= "- $key: $value\n";
            }
            $context .= "\n";
        }
        
        // Account queries
        if (strpos($message, 'account') !== false || strpos($message, 'saving') !== false) {
            $accountStats = [
                'Total Accounts' => DB::table('accounts')->count(),
                'Savings Accounts' => DB::table('accounts')->where('account_type', 'SAVINGS')->count(),
                'Total Balance' => DB::table('accounts')->sum('balance'),
            ];
            
            $context .= "**Account Statistics:**\n";
            foreach ($accountStats as $key => $value) {
                if ($key === 'Total Balance') {
                    $context .= "- $key: " . number_format($value ?: 0, 2) . "\n";
                } else {
                    $context .= "- $key: $value\n";
                }
            }
            $context .= "\n";
        }
        
        // Add project structure info
        $context .= "**Project Information:**\n";
        $context .= "- System: SACCOS Core System Template v6.0\n";
        $context .= "- Framework: Laravel 9.x with Livewire\n";
        $context .= "- Database: PostgreSQL\n";
        $context .= "- Project Path: " . base_path() . "\n";
        $context .= "- Total Modules: 31\n";
        
    } catch (\Exception $e) {
        $context .= "**Error gathering context:** " . $e->getMessage() . "\n";
    }
    
    return $context;
}