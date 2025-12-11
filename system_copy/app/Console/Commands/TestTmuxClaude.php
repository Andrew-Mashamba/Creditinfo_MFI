<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TmuxClaudeService;

class TestTmuxClaude extends Command
{
    protected $signature = 'test:tmux-claude {message?} {--kill : Kill the tmux session}';
    protected $description = 'Test tmux-based Claude CLI integration';

    public function handle()
    {
        $this->info('=== Testing Tmux Claude Service ===');
        $this->newLine();
        
        try {
            $tmuxService = new TmuxClaudeService();
            
            // Check if --kill flag is set
            if ($this->option('kill')) {
                $this->warn('Killing tmux session...');
                $tmuxService->killSession();
                $this->info('Tmux session killed.');
                return 0;
            }
            
            // Check if service is available
            if (!$tmuxService->isAvailable()) {
                $this->error('TmuxClaudeService is NOT available!');
                $this->error('Make sure tmux is installed: brew install tmux');
                $this->error('Make sure claude CLI is available');
                return 1;
            }
            
            $this->info('✓ TmuxClaudeService is available');
            
            // Get session info
            $sessionInfo = $tmuxService->getSessionInfo();
            $this->info('Session Info:');
            $this->table(
                ['Property', 'Value'],
                [
                    ['Session Name', $sessionInfo['session_name']],
                    ['Exists', $sessionInfo['exists'] ? 'Yes' : 'No'],
                    ['Initialized', $sessionInfo['initialized'] ? 'Yes' : 'No'],
                    ['Output Length', $sessionInfo['output_length'] . ' chars'],
                ]
            );
            
            // Send test message
            $message = $this->argument('message') ?? 'What is 2+2? Reply with just the answer.';
            
            $this->newLine();
            $this->info('Sending message: ' . $message);
            $this->info('Please wait for response...');
            
            $startTime = microtime(true);
            
            // Test without streaming
            $response = $tmuxService->sendMessage($message);
            
            $duration = microtime(true) - $startTime;
            
            if ($response['success']) {
                $this->newLine();
                $this->info('✓ Response received in ' . round($duration, 2) . ' seconds:');
                $this->line('----------------------------------------');
                $this->line($response['message']);
                $this->line('----------------------------------------');
            } else {
                $this->error('✗ Failed to get response: ' . $response['error']);
            }
            
            // Test with streaming
            $this->newLine();
            $this->info('Testing streaming mode...');
            
            $streamedResponse = '';
            $response = $tmuxService->sendMessage('Tell me a very short joke', [
                'enable_streaming' => true,
                'stream_callback' => function($chunk) use (&$streamedResponse) {
                    $this->output->write($chunk);
                    $streamedResponse .= $chunk;
                }
            ]);
            
            $this->newLine();
            $this->newLine();
            $this->info('Streaming complete!');
            
            // Show final session info
            $this->newLine();
            $sessionInfo = $tmuxService->getSessionInfo();
            $this->info('Final session output preview:');
            $this->line(str_repeat('-', 50));
            $this->line($sessionInfo['output_preview']);
            $this->line(str_repeat('-', 50));
            
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        $this->newLine();
        $this->info('=== Test Complete ===');
        $this->info('Tmux session remains active for future use.');
        $this->info('Use --kill flag to terminate the session.');
        
        return 0;
    }
}