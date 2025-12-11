<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LocalClaudeService;

class TestLocalClaudeConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claude:test-local {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the local Claude Code connection';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing Local Claude Code Connection...');
        $this->info('==========================================');
        
        $localClaudeService = new LocalClaudeService();
        
        // Check if available
        if ($localClaudeService->isAvailable()) {
            $this->info('✅ Claude Code monitor is running!');
        } else {
            $this->error('❌ Claude Code monitor is NOT running!');
            $this->warn('Please start the monitor: php claude-monitor.php');
            return 1;
        }
        
        $this->info('');
        $this->info('Sending test message...');
        
        // Get message from argument or use default
        $message = $this->argument('message') ?? 
            "Hello Claude Code! Can you confirm you're connected to the SACCOS Core System and have full project context?";
        
        $this->info('Message: ' . $message);
        $this->info('');
        
        // Test the connection
        $response = $localClaudeService->sendMessage($message, [
            'user_name' => 'System Administrator',
            'user_role' => 'Admin',
            'session_id' => 'test_' . uniqid(),
            'test_mode' => true
        ]);
        
        if ($response['success']) {
            $this->info('✅ Success! Claude Code responded:');
            $this->info('--------------------------------');
            $this->line($response['message']);
            $this->info('--------------------------------');
            
            if (isset($response['context'])) {
                $this->info('');
                $this->info('Response Context:');
                foreach ($response['context'] as $key => $value) {
                    $this->info($key . ': ' . (is_array($value) ? json_encode($value) : $value));
                }
            }
            
            return 0;
        } else {
            $this->error('❌ Failed to connect to Claude Code!');
            $this->error('Error: ' . $response['message']);
            
            if (isset($response['error'])) {
                $this->error('Error Type: ' . $response['error']);
                
                if ($response['error'] === 'TIMEOUT') {
                    $this->warn('');
                    $this->warn('Make sure the monitor script is running:');
                    $this->warn('php claude-monitor.php');
                }
            }
            
            return 1;
        }
    }
}