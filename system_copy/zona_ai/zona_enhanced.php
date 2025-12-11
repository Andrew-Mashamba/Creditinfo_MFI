#!/usr/bin/env php
<?php

/**
 * Zona AI Enhanced Responder
 * Uses directives and real database queries for accurate responses
 */

// Increase memory limit
ini_set('memory_limit', '512M');

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Load directives
$directives = require __DIR__ . '/zona_directives.php';

echo "\033[1;36m";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          Zona AI Enhanced - SACCOS Core System                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\033[0m\n";

echo "\033[1;32m✓ Directives loaded\033[0m\n";
echo "\033[1;32m✓ Connected to database\033[0m\n";
echo "\033[1;33m⚡ Ready to answer questions accurately...\033[0m\n\n";

$service = new \App\Services\LocalClaudeService();
$markerFile = storage_path('app/claude-bridge/claude-monitor.active');
$processedRequests = [];
$conversationMemory = [];

// Signal handler for clean shutdown
pcntl_async_signals(true);
pcntl_signal(SIGINT, function() use ($markerFile) {
    echo "\n\033[1;31m✗ Shutting down Zona AI Enhanced...\033[0m\n";
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
        echo "\033[1;35m📨 Processing Question\033[0m\n";
        echo "\033[1;33mQ: " . $request['message'] . "\033[0m\n";
        
        // Generate response using directives
        $response = generateSmartResponse($request['message'], $request['context'], $directives, $conversationMemory);
        
        // Add to conversation memory
        $conversationMemory[] = [
            'question' => $request['message'],
            'answer' => $response,
            'timestamp' => now()
        ];
        
        // Keep only last 20 exchanges in memory
        if (count($conversationMemory) > 20) {
            $conversationMemory = array_slice($conversationMemory, -20);
        }
        
        // Send response back
        $service->sendResponse($request['id'], $response, [
            'answered_by' => 'Zona AI Enhanced',
            'answered_at' => now()->toIso8601String(),
            'used_directives' => true
        ]);
        
        echo "\033[1;32mA: " . substr($response, 0, 200) . "...\033[0m\n";
        echo "\033[1;32m✓ Response sent!\033[0m\n";
        echo "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n\n";
    }
    
    usleep(500000); // 500ms
}

/**
 * Generate smart response using directives and real queries
 */
function generateSmartResponse($message, $context, $directives, $memory) {
    $message = strtolower($message);
    
    try {
        // Member search queries (check for specific member lookups)
        if ((strpos($message, 'member') !== false || strpos($message, 'client') !== false) && 
            (strpos($message, 'name') !== false || strpos($message, 'called') !== false || strpos($message, 'by the name') !== false)) {
            
            // Extract potential name from the message
            $searchName = null;
            
            // Common patterns: "member named X", "member called X", "member by the name X"
            if (preg_match('/(?:named|called|by the name|name)\s+(\w+)/i', $message, $matches)) {
                $searchName = $matches[1];
            }
            
            if ($searchName) {
                // Search for the member
                $members = DB::table('clients')
                    ->where(DB::raw('LOWER(first_name)'), 'like', '%' . strtolower($searchName) . '%')
                    ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . strtolower($searchName) . '%')
                    ->select('id', 'first_name', 'last_name', 'client_number', 'email', 'phone_number', 'client_status')
                    ->get();
                
                if ($members->isEmpty()) {
                    return "No member found with the name **{$searchName}**.";
                }
                
                if ($members->count() == 1) {
                    $member = $members->first();
                    $response = "Yes, we have a member matching **{$searchName}**:\n\n";
                    $response .= "**Name:** {$member->first_name} {$member->last_name}\n";
                    $response .= "**Client Number:** {$member->client_number}\n";
                    $response .= "**Client ID:** {$member->id}\n";
                    if ($member->email) {
                        $response .= "**Email:** {$member->email}\n";
                    }
                    if ($member->phone_number) {
                        $response .= "**Phone:** {$member->phone_number}\n";
                    }
                    $response .= "**Status:** {$member->client_status}\n";
                    
                    // Check if they have accounts
                    $accounts = DB::table('accounts')
                        ->where('client_number', $member->client_number)
                        ->count();
                    if ($accounts > 0) {
                        $response .= "\nThis member has **{$accounts} account(s)** with us.";
                    }
                    
                    return $response;
                } else {
                    $response = "Found **{$members->count()} members** matching **{$searchName}**:\n\n";
                    foreach ($members as $member) {
                        $response .= "• **{$member->first_name} {$member->last_name}** (Client #{$member->client_number})\n";
                    }
                    return $response;
                }
            }
        }
        
        // List all members query - more flexible matching
        if ((strpos($message, 'member') !== false || strpos($message, 'client') !== false) && 
            (strpos($message, 'name') !== false) &&
            (strpos($message, 'what are') !== false || strpos($message, 'list') !== false || 
             strpos($message, 'all') !== false || strpos($message, 'show') !== false ||
             strpos($message, 'who are') !== false)) {
            
            $members = DB::table('clients')
                ->select('first_name', 'last_name', 'client_number')
                ->limit(20)
                ->get();
            
            if ($members->isEmpty()) {
                return "There are no members registered in the system yet.";
            }
            
            $totalCount = DB::table('clients')->count();
            $response = "Here are the members in the system (showing first 20 of {$totalCount}):\n\n";
            
            foreach ($members as $member) {
                $response .= "• **{$member->first_name} {$member->last_name}** (Client #{$member->client_number})\n";
            }
            
            if ($totalCount > 20) {
                $response .= "\n...and " . ($totalCount - 20) . " more members.";
            }
            
            return $response;
        }
        
        // Greeting responses
        if (strpos($message, 'hello') !== false || strpos($message, 'helo') !== false || 
            strpos($message, 'hi') !== false || strpos($message, 'hey') !== false) {
            return "Hello! I'm Zona, your SACCOS AI assistant. I can help you with:\n\n" .
                   "• Member information and searches\n" .
                   "• Branch and user details\n" .
                   "• Loan and savings statistics\n" .
                   "• System information\n\n" .
                   "Try asking: 'Do we have a member named John?' or 'How many active loans do we have?'";
        }
        
        // Branch questions (check first, outside of "how many")
        if (strpos($message, 'branch') !== false) {
            $count = DB::table('branches')->count();
            
            if (strpos($message, 'name') !== false || strpos($message, 'which') !== false || strpos($message, 'what are') !== false) {
                $branches = DB::table('branches')->select('name', 'branch_number', 'address', 'region')->get();
                
                if ($branches->isEmpty()) {
                    return "There are no branches configured yet.";
                }
                
                $response = "There are **{$count} branch" . ($count == 1 ? "" : "es") . "** in the system:\n\n";
                foreach ($branches as $branch) {
                    $response .= "• **{$branch->name}** (Branch #{$branch->branch_number})\n";
                    if ($branch->region) {
                        $response .= "  Region: {$branch->region}\n";
                    }
                    if ($branch->address) {
                        $response .= "  Address: {$branch->address}\n";
                    }
                }
                return $response;
            }
            
            if (strpos($message, 'how many') !== false) {
                return "There are **{$count} branches** in the system.";
            }
        }
        
        // Dashboard/Overview questions
        if (strpos($message, 'how many') !== false || strpos($message, 'total') !== false) {
            
            // Users
            if (strpos($message, 'user') !== false) {
                $count = DB::table('users')->count();
                
                if (strpos($message, 'name') !== false || strpos($message, 'who') !== false) {
                    $users = DB::table('users')->select('name', 'email')->get();
                    if ($users->isEmpty()) {
                        return "There are no users in the system yet.";
                    }
                    
                    $response = "There are {$count} users in the system:\n\n";
                    foreach ($users as $user) {
                        $response .= "• **{$user->name}** ({$user->email})\n";
                    }
                    return $response;
                }
                
                return "There are **{$count} users** in the system.";
            }
            
            // Members/Clients
            if (strpos($message, 'member') !== false || strpos($message, 'client') !== false) {
                $count = DB::table('clients')->count();
                
                if (strpos($message, 'active') !== false) {
                    $active = DB::table('clients')->where('status', 'ACTIVE')->count();
                    return "There are **{$active} active members** out of {$count} total members.";
                }
                
                if (strpos($message, 'name') !== false || strpos($message, 'who') !== false) {
                    $clients = DB::table('clients')
                        ->select('first_name', 'last_name', 'client_number')
                        ->limit(10)
                        ->get();
                    
                    if ($clients->isEmpty()) {
                        return "There are no members registered yet.";
                    }
                    
                    $response = "There are **{$count} members** in the system. Here are the first 10:\n\n";
                    foreach ($clients as $client) {
                        $response .= "• **{$client->first_name} {$client->last_name}** (Client #{$client->client_number})\n";
                    }
                    if ($count > 10) {
                        $response .= "\n...and " . ($count - 10) . " more members.";
                    }
                    return $response;
                }
                
                return "There are **{$count} members/clients** registered in the system.";
            }
            
            
            // Loans
            if (strpos($message, 'loan') !== false) {
                $count = DB::table('loans')->count();
                
                if (strpos($message, 'active') !== false) {
                    $active = DB::table('loans')->where('status', 'ACTIVE')->count();
                    return "There are **{$active} active loans** out of {$count} total loans.";
                }
                
                if (strpos($message, 'disbursed') !== false) {
                    $disbursed = DB::table('loans')->where('status', 'DISBURSED')->count();
                    return "There are **{$disbursed} disbursed loans** in the system.";
                }
                
                if (strpos($message, 'pending') !== false) {
                    $pending = DB::table('loans')->where('status', 'PENDING')->count();
                    return "There are **{$pending} pending loan applications**.";
                }
                
                return "There are **{$count} loans** in the system.";
            }
            
            // Accounts
            if (strpos($message, 'account') !== false) {
                $count = DB::table('accounts')->count();
                
                if (strpos($message, 'savings') !== false || strpos($message, 'saving') !== false) {
                    $savings = DB::table('accounts')
                        ->where('account_type', 'SAVINGS')
                        ->orWhere('account_type', 'SAVING')
                        ->count();
                    return "There are **{$savings} savings accounts** in the system.";
                }
                
                return "There are **{$count} accounts** in the system.";
            }
            
            // Transactions
            if (strpos($message, 'transaction') !== false) {
                $count = DB::table('transactions')->count();
                
                if (strpos($message, 'today') !== false) {
                    $today = DB::table('transactions')
                        ->whereDate('created_at', today())
                        ->count();
                    return "There were **{$today} transactions** processed today.";
                }
                
                return "There are **{$count} transactions** in the system.";
            }
            
            // Shares
            if (strpos($message, 'share') !== false) {
                $count = DB::table('shares')->count();
                $total = DB::table('shares')->sum(DB::raw('number_of_shares * share_value'));
                
                if (strpos($message, 'capital') !== false || strpos($message, 'value') !== false) {
                    return "The total share capital is **" . number_format($total ?: 0, 2) . "**.";
                }
                
                return "There are **{$count} share records** with total value of **" . number_format($total ?: 0, 2) . "**.";
            }
            
            // Employees
            if (strpos($message, 'employee') !== false || strpos($message, 'staff') !== false) {
                $count = DB::table('employees')->count();
                return "There are **{$count} employees** in the system.";
            }
            
            // Departments
            if (strpos($message, 'department') !== false) {
                $count = DB::table('departments')->count();
                
                if (strpos($message, 'name') !== false || strpos($message, 'which') !== false) {
                    $departments = DB::table('departments')->select('department_name')->get();
                    
                    if ($departments->isEmpty()) {
                        return "There are no departments configured yet.";
                    }
                    
                    $response = "There are **{$count} departments**:\n\n";
                    foreach ($departments as $dept) {
                        $response .= "• {$dept->department_name}\n";
                    }
                    return $response;
                }
                
                return "There are **{$count} departments** in the organization.";
            }
            
            // Approvals
            if (strpos($message, 'approval') !== false) {
                $count = DB::table('approvals')->count();
                
                if (strpos($message, 'pending') !== false) {
                    $pending = DB::table('approvals')->where('status', 'PENDING')->count();
                    return "There are **{$pending} pending approvals** in the system.";
                }
                
                return "There are **{$count} approval records** in the system.";
            }
        }
        
        // Balance/Amount questions
        if (strpos($message, 'balance') !== false || strpos($message, 'amount') !== false) {
            
            if (strpos($message, 'savings') !== false || strpos($message, 'saving') !== false) {
                $total = DB::table('accounts')
                    ->where('account_type', 'SAVINGS')
                    ->orWhere('account_type', 'SAVING')
                    ->sum('balance');
                return "The total savings balance is **" . number_format($total ?: 0, 2) . "**.";
            }
            
            if (strpos($message, 'loan') !== false) {
                $total = DB::table('loans')->sum('principle');
                $outstanding = DB::table('loans')
                    ->where('status', 'ACTIVE')
                    ->sum('principle');
                
                return "Total loan portfolio: **" . number_format($total ?: 0, 2) . "**\n" .
                       "Outstanding principal: **" . number_format($outstanding ?: 0, 2) . "**";
            }
        }
        
        // System information
        if (strpos($message, 'system') !== false || strpos($message, 'version') !== false) {
            return "**SACCOS Core System Information:**\n\n" .
                   "• System: SACCOS Core System Template\n" .
                   "• Version: 6.0\n" .
                   "• Database: PostgreSQL\n" .
                   "• Framework: Laravel 9.x with Livewire\n" .
                   "• Modules: 31 active modules\n" .
                   "• Project Path: " . base_path();
        }
        
        // Module information
        if (strpos($message, 'module') !== false) {
            $modules = $directives['module_mappings'];
            $count = count($modules);
            
            if (strpos($message, 'list') !== false || strpos($message, 'which') !== false) {
                $response = "The system has **{$count} modules**:\n\n";
                foreach ($modules as $id => $module) {
                    $response .= "• **{$module['name']}** - {$module['focus']}\n";
                }
                return $response;
            }
            
            return "The system has **{$count} active modules**.";
        }
        
        // Default statistical overview
        if (strpos($message, 'overview') !== false || strpos($message, 'statistics') !== false) {
            $stats = [
                'users' => DB::table('users')->count(),
                'members' => DB::table('clients')->count(),
                'branches' => DB::table('branches')->count(),
                'accounts' => DB::table('accounts')->count(),
                'loans' => DB::table('loans')->count(),
                'transactions' => DB::table('transactions')->count(),
            ];
            
            $response = "**System Overview:**\n\n";
            $response .= "• Users: **{$stats['users']}**\n";
            $response .= "• Members: **{$stats['members']}**\n";
            $response .= "• Branches: **{$stats['branches']}**\n";
            $response .= "• Accounts: **{$stats['accounts']}**\n";
            $response .= "• Loans: **{$stats['loans']}**\n";
            $response .= "• Transactions: **{$stats['transactions']}**\n";
            
            return $response;
        }
        
    } catch (\Exception $e) {
        return "I encountered an error while fetching the data: " . $e->getMessage() . 
               "\n\nPlease ensure the database tables exist and are properly seeded.";
    }
    
    // If no specific pattern matched, provide a helpful response
    return "I can help you with information about the SACCOS system. Try asking:\n\n" .
           "• How many users/members/branches are there?\n" .
           "• What are the names of users/branches?\n" .
           "• How many active loans exist?\n" .
           "• What is the total savings balance?\n" .
           "• Show system overview/statistics\n\n" .
           "I use real database queries to provide accurate information.";
}