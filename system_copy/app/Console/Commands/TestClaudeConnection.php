<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClaudeService;

class TestClaudeConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claude:test {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Claude AI connection';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing Claude AI Connection...');
        $this->info('================================');
        
        $claudeService = new ClaudeService();
        
        // Get model info
        $modelInfo = $claudeService->getModelInfo();
        $this->info('Model: ' . $modelInfo['model']);
        $this->info('Max Tokens: ' . $modelInfo['max_tokens']);
        $this->info('API Configured: ' . ($modelInfo['api_configured'] ? 'Yes' : 'No'));
        
        if (!$modelInfo['api_configured']) {
            $this->error('Claude API key is not configured!');
            $this->warn('Please add CLAUDE_API_KEY to your .env file');
            return 1;
        }
        
        $this->info('');
        $this->info('Sending test message...');
        
        // Get message from argument or use default
        $message = $this->argument('message') ?? 
            "Hello Claude! Can you confirm you're connected to the SACCOS Core System? Please tell me what you know about this system.";
        
        $this->info('Message: ' . $message);
        $this->info('');
        
        // Test the connection
        $response = $claudeService->sendMessage($message, [
            'user_name' => 'System Administrator',
            'user_role' => 'Admin',
            'session_id' => 'test_' . uniqid()
        ]);
        
        if ($response['success']) {
            $this->info('✅ Success! Claude responded:');
            $this->info('--------------------------------');
            $this->line($response['message']);
            $this->info('--------------------------------');
            
            if (isset($response['usage'])) {
                $this->info('');
                $this->info('Token Usage:');
                $this->info('Input: ' . ($response['usage']['input_tokens'] ?? 'N/A'));
                $this->info('Output: ' . ($response['usage']['output_tokens'] ?? 'N/A'));
            }
            
            return 0;
        } else {
            $this->error('❌ Failed to connect to Claude!');
            $this->error('Error: ' . $response['message']);
            
            if (isset($response['error'])) {
                $this->error('Error Type: ' . $response['error']);
            }
            
            return 1;
        }
    }
}