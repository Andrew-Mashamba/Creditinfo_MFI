<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClaudeCliService;

class TestClaudeCli extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claude:test-cli {message : The message to send to Claude CLI}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Claude CLI integration';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $message = $this->argument('message');
        
        $this->info('🤖 Sending to Claude CLI: ' . $message);
        $this->line('');
        
        $service = new ClaudeCliService();
        
        // Check if available
        if (!$service->isAvailable()) {
            $this->error('❌ Claude CLI is not installed');
            $this->line('Install it using: brew install claude');
            return Command::FAILURE;
        }
        
        $this->info('✅ Claude CLI is available');
        $this->line('');
        
        // Send message
        $response = $service->sendMessage($message);
        
        if ($response['success']) {
            $this->info('📝 Response from Claude CLI:');
            $this->line('');
            $this->line($response['message']);
            $this->line('');
            $this->info('✅ Success!');
        } else {
            $this->error('❌ Error: ' . $response['message']);
            if (isset($response['error'])) {
                $this->error('Error Code: ' . $response['error']);
            }
        }
        
        return Command::SUCCESS;
    }
}