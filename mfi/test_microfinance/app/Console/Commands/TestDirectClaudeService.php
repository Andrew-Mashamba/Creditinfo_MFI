<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DirectClaudeService;

class TestDirectClaudeService extends Command
{
    protected $signature = 'claude:test-direct {question?}';
    protected $description = 'Test the DirectClaudeService with LocalClaudeService integration';

    public function handle()
    {
        $this->info('Testing DirectClaudeService with LocalClaudeService (allowed tools)');
        $this->info('=====================================================================');
        
        $service = new DirectClaudeService();
        
        // Check if available
        if (!$service->isAvailable()) {
            $this->error('Claude CLI is not available');
            return Command::FAILURE;
        }
        
        $this->info('✅ Claude CLI is available');
        $this->line('');
        
        // Get question from argument or use default
        $question = $this->argument('question') ?? "How many accounts does Andrew Mashamba have?";
        
        $this->info("Question: " . $question);
        $this->line(str_repeat('-', 60));
        
        // Process the message
        $startTime = microtime(true);
        
        $response = $service->processMessage($question, [
            'user_name' => 'Admin',
            'user_role' => 'Administrator',
            'session_id' => 'test-direct-' . time()
        ]);
        
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2);
        
        if ($response['success']) {
            $this->info("\n✅ Response received successfully!");
            $this->line("\nAnswer:");
            $this->line($response['message']);
            
            $this->line("\n" . str_repeat('-', 60));
            $this->info("Execution time: {$executionTime}ms");
            
            // Show context if available
            if (isset($response['context'])) {
                $this->line("\nContext:");
                $this->line("Method: " . ($response['context']['method'] ?? 'unknown'));
                if (isset($response['context']['bypassed_claude_cli'])) {
                    $this->line("Bypassed Claude CLI: " . ($response['context']['bypassed_claude_cli'] ? 'Yes' : 'No'));
                }
            }
        } else {
            $this->error("❌ Error: " . $response['message']);
            if (isset($response['error'])) {
                $this->error("Error Code: " . $response['error']);
            }
        }
        
        $this->line("\n" . str_repeat('=', 60));
        $this->info("Test completed!");
        
        return Command::SUCCESS;
    }
}