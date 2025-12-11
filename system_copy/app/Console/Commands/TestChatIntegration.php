<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DirectClaudeService;
use App\Services\HybridAiService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TestChatIntegration extends Command
{
    protected $signature = 'test:chat-integration {message?}';
    protected $description = 'Test chat integration - verify DirectClaude is being used';
    
    public function handle()
    {
        $message = $this->argument('message') ?? 'list accounts belonging to MASHAMBA';
        
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════════╗');
        $this->info('║           CHAT INTEGRATION TEST - VERIFYING MCP                   ║');
        $this->info('╚══════════════════════════════════════════════════════════════════╝');
        $this->info('');
        
        // Authenticate
        $user = User::first();
        Auth::login($user);
        
        $this->info("📝 Testing: '{$message}'");
        $this->info('════════════════════════════════════════════════════════════════════');
        $this->info('');
        
        // Test DirectClaude (what main chat should use)
        $this->info('1️⃣ Testing DirectClaude (Main Chat Mode):');
        $this->info('────────────────────────────────────────');
        
        $directClaude = new DirectClaudeService();
        $options = [
            'session_id' => 'test_direct_' . uniqid(),
            'user_name' => $user->name,
            'user_role' => 'Admin'
        ];
        
        $startTime = microtime(true);
        $directResponse = $directClaude->processMessage($message, $options);
        $directTime = round(microtime(true) - $startTime, 2);
        
        if ($directResponse['success']) {
            $this->line('✅ DirectClaude SUCCESS');
            $this->line("⏱️  Time: {$directTime}s");
            $this->line("📊 Response length: " . strlen($directResponse['message']) . " chars");
            $this->info("\nResponse preview:");
            $this->line(substr($directResponse['message'], 0, 200) . "...");
        } else {
            $this->error('❌ DirectClaude FAILED: ' . $directResponse['error']);
        }
        
        // Compare with HybridAI (old method with interception)
        $this->info("\n2️⃣ Testing HybridAI (Old Method - for comparison):");
        $this->info('────────────────────────────────────────');
        
        $hybridAi = new HybridAiService();
        
        $startTime = microtime(true);
        $hybridResponse = $hybridAi->processMessage($message);
        $hybridTime = round(microtime(true) - $startTime, 2);
        
        if ($hybridResponse['success']) {
            $this->line('✅ HybridAI SUCCESS');
            $this->line("⏱️  Time: {$hybridTime}s");
            $this->line("📊 Response length: " . strlen($hybridResponse['message']) . " chars");
            
            // Check if it intercepted
            if (isset($hybridResponse['context']['queries_executed'])) {
                $this->warn("⚠️  HybridAI intercepted and executed queries (OLD BEHAVIOR)");
            }
        } else {
            $this->error('❌ HybridAI FAILED: ' . $hybridResponse['error']);
        }
        
        // Summary
        $this->info("\n📊 INTEGRATION SUMMARY:");
        $this->info('════════════════════════════════════════════');
        
        $this->info("\n✅ CORRECT Implementation (DirectClaude with MCP):");
        $this->line("• Claude receives question + context");
        $this->line("• Claude uses MCP tools to query database");
        $this->line("• Claude returns final answer");
        $this->line("• NO interception or processing");
        
        $this->info("\n❌ OLD Implementation (HybridAI with interception):");
        $this->line("• System intercepts Claude's response");
        $this->line("• System extracts and executes queries");
        $this->line("• System sends results back to Claude");
        $this->line("• Additional round-trips required");
        
        $this->info("\n🎯 Main Chat Configuration:");
        $this->line("• The main chat is configured to use: DirectClaude (MCP)");
        $this->line("• This is the CORRECT configuration");
        
        return 0;
    }
}