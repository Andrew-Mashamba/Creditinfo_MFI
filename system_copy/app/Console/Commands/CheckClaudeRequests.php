<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LocalClaudeService;

class CheckClaudeRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claude:check {--detailed : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for pending Claude requests and bridge status';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $service = new LocalClaudeService();
        
        $this->info('🔍 Claude Bridge Status Check');
        $this->info('=' . str_repeat('=', 50));
        
        // Check if bridge is available
        if ($service->isAvailable()) {
            $this->info('✅ Claude Bridge is running');
        } else {
            $this->warn('❌ Claude Bridge is not running');
            $this->line('Start it with: ./zona_ai/start_claude_bridge.sh');
            return Command::FAILURE;
        }
        
        // Check for pending requests
        $pendingRequests = $service->getPendingRequests();
        
        if (empty($pendingRequests)) {
            $this->info('📭 No pending requests found');
        } else {
            $this->info('📨 Found ' . count($pendingRequests) . ' pending request(s):');
            
            foreach ($pendingRequests as $request) {
                $this->line('');
                $this->line('Request ID: ' . $request['id']);
                $this->line('User: ' . ($request['context']['user_name'] ?? 'Unknown'));
                $this->line('Message: ' . substr($request['message'], 0, 100) . '...');
                $this->line('Timestamp: ' . ($request['context']['timestamp'] ?? 'N/A'));
                
                if ($this->option('detailed')) {
                    $this->line('Full Message: ' . $request['message']);
                    $this->line('Context: ' . json_encode($request['context'], JSON_PRETTY_PRINT));
                }
            }
        }
        
        // Check response files
        $responseDir = storage_path('app/claude-bridge/responses');
        if (is_dir($responseDir)) {
            $responseFiles = glob($responseDir . '/*.json');
            $this->line('');
            $this->info('📁 Response files: ' . count($responseFiles));
            
            if ($this->option('detailed') && !empty($responseFiles)) {
                foreach (array_slice($responseFiles, -5) as $file) { // Show last 5
                    $filename = basename($file);
                    $this->line('  • ' . $filename);
                }
            }
        }
        
        // Check request files
        $requestDir = storage_path('app/claude-bridge/requests');
        if (is_dir($requestDir)) {
            $requestFiles = glob($requestDir . '/*.json');
            $this->line('');
            $this->info('📁 Request files: ' . count($requestFiles));
            
            if ($this->option('detailed') && !empty($requestFiles)) {
                foreach (array_slice($requestFiles, -5) as $file) { // Show last 5
                    $filename = basename($file);
                    $this->line('  • ' . $filename);
                }
            }
        }
        
        return Command::SUCCESS;
    }
}
