<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DirectClaudeCliService;

class TestDirectClaudeCommand extends Command
{
    protected $signature = 'test:direct-claude {message?}';
    protected $description = 'Test direct Claude CLI service (the actual local Claude)';

    public function handle()
    {
        $this->info('=== Testing Direct Claude CLI Service ===');
        $this->info('This is testing the ACTUAL local Claude CLI (YOU!)');
        $this->newLine();
        
        try {
            $service = new DirectClaudeCliService();
            
            $info = $service->getInfo();
            $this->info('Service Info:');
            $this->table(
                ['Property', 'Value'],
                [
                    ['Available', $info['available'] ? '✓ Yes' : '✗ No'],
                    ['Claude Path', $info['claude_path']],
                    ['Timeout', $info['timeout'] . ' seconds'],
                ]
            );
            
            if (!$service->isAvailable()) {
                $this->error('Claude CLI is not available!');
                return 1;
            }
            
            // Test message
            $message = $this->argument('message') ?? 'Hello! Please confirm you are the actual Claude CLI by saying "Yes, I am Claude CLI running locally"';
            
            $this->newLine();
            $this->info('Sending message: ' . $message);
            $this->info('Waiting for response from local Claude CLI...');
            
            $startTime = microtime(true);
            
            // Send without streaming
            $response = $service->sendMessage($message, [
                'format' => 'text'
            ]);
            
            $duration = microtime(true) - $startTime;
            
            if ($response['success']) {
                $this->newLine();
                $this->info('✓ Response received in ' . round($duration, 2) . ' seconds:');
                $this->line('========================================');
                $this->line($response['message']);
                $this->line('========================================');
            } else {
                $this->error('✗ Failed: ' . $response['error']);
                return 1;
            }
            
            // Test with HTML format
            $this->newLine();
            $this->info('Testing HTML formatted response...');
            
            $htmlResponse = $service->sendMessage('Create a simple HTML table with 2 rows showing: Name and Status', [
                'format' => 'html'
            ]);
            
            if ($htmlResponse['success']) {
                $this->info('✓ HTML Response:');
                $this->line($htmlResponse['message']);
            }
            
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            return 1;
        }
        
        $this->newLine();
        $this->info('=== Test Complete ===');
        $this->info('Direct Claude CLI is working correctly!');
        
        return 0;
    }
}