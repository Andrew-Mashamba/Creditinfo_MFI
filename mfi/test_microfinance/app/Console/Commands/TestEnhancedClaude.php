<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LocalClaudeService;

class TestEnhancedClaude extends Command
{
    protected $signature = 'claude:test-enhanced {question?}';
    protected $description = 'Test the enhanced LocalClaudeService with direct database queries';

    public function handle()
    {
        $this->info('Testing Enhanced LocalClaudeService with Direct Database Queries');
        $this->info('================================================================');
        
        $service = new LocalClaudeService();
        
        // Get question from argument or use default
        $question = $this->argument('question');
        
        if (!$question) {
            // Test with Mashamba query
            $question = "How many accounts does Andrew Mashamba have and what are their balances?";
        }
        
        $this->line("\nQuestion: " . $question);
        $this->line(str_repeat('-', 60));
        
        // Send the message
        $response = $service->sendMessage($question, [
            'user_name' => 'Admin',
            'user_role' => 'Administrator',
            'session_id' => 'test-' . time()
        ]);
        
        if ($response['success']) {
            $this->info("\nResponse:");
            $this->line($response['message']);
            
            // Show context if available
            if (isset($response['context'])) {
                $this->line("\n" . str_repeat('-', 60));
                $this->info("Context:");
                $this->line("Method: " . ($response['context']['method'] ?? 'unknown'));
                if (isset($response['context']['context_service_used'])) {
                    $this->line("Context Service Used: " . ($response['context']['context_service_used'] ? 'Yes' : 'No'));
                }
                if (isset($response['context']['bypassed_claude_cli'])) {
                    $this->line("Bypassed Claude CLI: " . ($response['context']['bypassed_claude_cli'] ? 'Yes' : 'No'));
                }
                if (isset($response['context']['query_type'])) {
                    $this->line("Query Type: " . $response['context']['query_type']);
                }
            }
        } else {
            $this->error("Error: " . $response['message']);
            if (isset($response['error'])) {
                $this->error("Error Code: " . $response['error']);
            }
        }
        
        $this->line("\n" . str_repeat('=', 60));
        
        // Test multiple questions if no specific question was provided
        if (!$this->argument('question')) {
            $this->info("\nTesting additional queries...\n");
            
            $testQuestions = [
                "What is the total count of all accounts in the database?",
                "Show me the client information for Andrew Mashamba",
                "List the first 5 clients in the system"
            ];
            
            foreach ($testQuestions as $testQuestion) {
                $this->line("Q: " . $testQuestion);
                
                $response = $service->sendMessage($testQuestion, [
                    'user_name' => 'Admin',
                    'session_id' => 'test-' . time()
                ]);
                
                if ($response['success']) {
                    $this->info("A: " . substr($response['message'], 0, 200) . "...");
                } else {
                    $this->error("Error: " . $response['message']);
                }
                
                $this->line("");
            }
        }
        
        $this->info("\nTest completed!");
        
        return Command::SUCCESS;
    }
}