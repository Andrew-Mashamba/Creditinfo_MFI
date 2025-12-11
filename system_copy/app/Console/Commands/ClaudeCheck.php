<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClaudeCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claude:check {--detailed : Show detailed request information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for pending Claude Code requests';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pendingDir = storage_path('app/claude-bridge/pending');
        $requestDir = storage_path('app/claude-bridge/requests');
        
        // Check for signal files (new method)
        $signalFiles = glob($pendingDir . '/*.signal');
        
        // Check for request files (old method)
        $requestFiles = glob($requestDir . '/*.json');
        
        $pendingRequests = [];
        
        // Process signal files
        foreach ($signalFiles as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $pendingRequests[] = [
                    'type' => 'signal',
                    'file' => $file,
                    'data' => $data
                ];
            }
        }
        
        // Process request files
        foreach ($requestFiles as $file) {
            $requestId = basename($file, '.json');
            $responseFile = storage_path('app/claude-bridge/responses/' . $requestId . '.json');
            
            // Only show if no response exists
            if (!file_exists($responseFile)) {
                $data = json_decode(file_get_contents($file), true);
                if ($data) {
                    $pendingRequests[] = [
                        'type' => 'request',
                        'file' => $file,
                        'data' => $data
                    ];
                }
            }
        }
        
        if (empty($pendingRequests)) {
            $this->info("No pending requests found.");
            return Command::SUCCESS;
        }
        
        $this->info("Found " . count($pendingRequests) . " pending request(s):\n");
        
        foreach ($pendingRequests as $request) {
            $data = $request['data'];
            $requestId = $data['request_id'] ?? $data['id'] ?? 'unknown';
            
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("Request ID: " . $requestId);
            $this->info("Type: " . $request['type']);
            $this->info("Message: " . ($data['message'] ?? 'N/A'));
            
            if ($this->option('detailed')) {
                $this->info("User: " . ($data['context']['user_name'] ?? 'Unknown'));
                $this->info("Timestamp: " . ($data['timestamp'] ?? $data['created_at'] ?? 'N/A'));
                
                if (isset($data['context']) && is_string($data['context'])) {
                    $this->info("\nDatabase Context:");
                    $this->line($data['context']);
                }
            }
            
            $this->line("\nTo respond, use:");
            $this->comment("php artisan claude:respond \"{$requestId}\" \"Your response here\"");
        }
        
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        return Command::SUCCESS;
    }
}