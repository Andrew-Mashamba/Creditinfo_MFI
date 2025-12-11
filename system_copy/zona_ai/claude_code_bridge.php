#!/usr/bin/env php
<?php

/**
 * Claude Code Bridge
 * This script bridges between Laravel requests and Claude Code responses
 * It provides context but lets Claude Code do the actual answering
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
echo "║          Claude Code Bridge - SACCOS Core System              ║\n";
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
        echo "\033[1;35m🤖 Claude Code, please answer this question:\033[0m\n\n";
        
        echo "The user is asking: \033[1;33m\"" . $request['message'] . "\"\033[0m\n\n";
        
        echo "You have:\n";
        echo "1. Full access to the SACCOS Core System codebase at: \033[1;32m" . base_path() . "\033[0m\n";
        echo "2. The database context provided above\n";
        echo "3. Your knowledge of the entire project structure and implementation\n\n";
        
        echo "Please provide a comprehensive answer based on:\n";
        echo "- The actual database data (use the context provided)\n";
        echo "- Your knowledge of the codebase\n";
        echo "- The specific modules and features implemented\n\n";
        
        echo "\033[1;33m📝 Your response will be sent back to the user through the Laravel chat interface.\033[0m\n";
        echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n\n";
        
        // Wait for Claude Code to provide response
        echo "\033[1;35m⏳ Waiting for Claude Code to respond...\033[0m\n";
        echo "\033[1;33m(Claude Code should read the context above and provide an answer)\033[0m\n\n";
        
        // In a real implementation, this would wait for Claude Code's response
        // For now, we'll provide the context as the response
        $claudeResponse = "**[Awaiting Claude Code Response]**\n\n";
        $claudeResponse .= "Context has been gathered. Claude Code needs to:\n";
        $claudeResponse .= "1. Read the user's question\n";
        $claudeResponse .= "2. Use the database context provided\n";
        $claudeResponse .= "3. Reference the actual codebase\n";
        $claudeResponse .= "4. Provide a comprehensive answer\n\n";
        $claudeResponse .= "**Database Context:**\n" . $context;
        
        // Send response back
        $service->sendResponse($request['id'], $claudeResponse, [
            'answered_by' => 'Claude Code Bridge',
            'context_provided' => true,
            'awaiting_claude_code' => true,
            'timestamp' => now()->toIso8601String()
        ]);
        
        echo "\033[1;32m✓ Context sent to interface, awaiting Claude Code's actual response\033[0m\n\n";
        
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