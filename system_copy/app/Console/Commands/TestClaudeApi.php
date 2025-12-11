<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClaudeService;
use App\Services\LocalClaudeService;

class TestClaudeApi extends Command
{
    protected $signature = 'test:claude-api {message?}';
    protected $description = 'Test Claude API service';

    public function handle()
    {
        $this->info('=== Testing Claude Services ===');
        
        $message = $this->argument('message') ?? 'What is 2+2?';
        $this->info("Test message: $message");
        $this->newLine();
        
        // Test LocalClaudeService
        $this->info('1. Testing LocalClaudeService...');
        $localClaude = new LocalClaudeService();
        
        if ($localClaude->isAvailable()) {
            $this->info('   LocalClaudeService reports as available (but may be simulated)');
            $response = $localClaude->sendMessage($message);
            if ($response['success']) {
                // Check if it's a fallback response
                if (strpos($response['message'], 'npx fallback') !== false) {
                    $this->warn('   ⚠ Response is simulated (npx fallback)');
                } else {
                    $this->info('   ✓ Response: ' . substr($response['message'], 0, 100));
                }
            } else {
                $this->error('   ✗ Error: ' . ($response['error'] ?? 'Unknown error'));
            }
        } else {
            $this->warn('   LocalClaudeService is NOT available');
        }
        
        $this->newLine();
        
        // Test ClaudeService (API)
        $this->info('2. Testing ClaudeService (API)...');
        $apiKey = config('services.claude.api_key');
        
        if (empty($apiKey)) {
            $this->error('   ✗ No API key configured');
            return 1;
        }
        
        $this->info('   API Key: ' . substr($apiKey, 0, 20) . '...');
        
        try {
            $claudeService = new ClaudeService();
            
            // Test connection
            $this->info('   Testing connection...');
            $testResult = $claudeService->testConnection();
            
            if ($testResult['success']) {
                $this->info('   ✓ Connection successful');
                $this->info('   Model: ' . ($testResult['model'] ?? 'Unknown'));
            } else {
                $this->error('   ✗ Connection failed: ' . ($testResult['error'] ?? 'Unknown error'));
            }
            
            // Send test message
            $this->info('   Sending test message...');
            $response = $claudeService->sendMessage($message, [
                'session_id' => 'test_' . time()
            ]);
            
            if ($response['success']) {
                $this->info('   ✓ Response received:');
                $this->line('   ' . substr($response['message'], 0, 200));
            } else {
                $this->error('   ✗ Failed: ' . ($response['error'] ?? 'Unknown error'));
            }
            
        } catch (\Exception $e) {
            $this->error('   ✗ Exception: ' . $e->getMessage());
            return 1;
        }
        
        $this->newLine();
        $this->info('=== Test Complete ===');
        
        return 0;
    }
}