<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClaudeCliService;
use App\Services\ContextEnhancementService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TestDirectClaude extends Command
{
    protected $signature = 'test:direct-claude {message?}';
    protected $description = 'Test sending message directly to Claude without any interception';
    
    public function handle()
    {
        $message = $this->argument('message') ?? 'list accounts belonging to MASHAMBA';
        
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════════╗');
        $this->info('║          DIRECT CLAUDE TEST - NO INTERCEPTION                     ║');
        $this->info('╚══════════════════════════════════════════════════════════════════╝');
        $this->info('');
        
        // Authenticate
        $user = User::first();
        Auth::login($user);
        
        $this->info("📝 User Question: '{$message}'");
        $this->info('════════════════════════════════════════════════════════════════════');
        $this->info('');
        
        // STEP 1: Build context
        $this->info("Building Context & Sending to Claude");
        $this->info('────────────────────────────────────────');
        
        $contextService = new ContextEnhancementService();
        $context = $contextService->buildContext($message, [
            'session_id' => 'direct_test_' . uniqid(),
            'user_name' => $user->name,
            'user_role' => 'Admin'
        ]);
        
        $this->line("✅ Context built: " . strlen($context['enhanced_message']) . " characters");
        
        // STEP 2: Send DIRECTLY to Claude - no interception
        $this->info("\nSending to Claude CLI (no interception)...");
        $this->info('────────────────────────────────────────');
        
        $claudeService = new ClaudeCliService();
        
        if (!$claudeService->isAvailable()) {
            $this->error("❌ Claude CLI is not available");
            $this->info("\nThis is the issue: Claude CLI needs to be configured with MCP tools");
            $this->showMcpConfiguration();
            return 1;
        }
        
        // Send the enhanced message directly to Claude
        $response = $claudeService->sendMessage($context['enhanced_message']);
        
        if ($response['success']) {
            $this->info("\n📥 Claude's Response:");
            $this->info('────────────────────────────────────────');
            $this->line($response['message']);
        } else {
            $this->error("❌ Failed to get response from Claude");
            $this->error($response['message']);
        }
        
        return 0;
    }
    
    private function showMcpConfiguration()
    {
        $this->info("\n🔧 MCP Configuration Needed");
        $this->info('════════════════════════════════════════════');
        $this->info("
Claude needs to be configured with MCP database tools to directly execute queries.

Add this to your Claude Desktop config file:

macOS: ~/Library/Application Support/Claude/claude_desktop_config.json
Windows: %APPDATA%\\Claude\\claude_desktop_config.json

{
  \"mcpServers\": {
    \"postgres\": {
      \"command\": \"npx\",
      \"args\": [
        \"-y\",
        \"@executeautomation/database-server\",
        \"--postgresql\",
        \"--host\", \"localhost\",
        \"--database\", \"saccos_core_system\",
        \"--user\", \"postgres\",
        \"--password\", \"your_password\",
        \"--port\", \"5432\"
      ]
    }
  }
}

With this configuration, Claude can:
1. Receive the question and context
2. Use MCP tools to query the database directly
3. Return the final answer

No interception or processing needed from our side!
        ");
    }
}