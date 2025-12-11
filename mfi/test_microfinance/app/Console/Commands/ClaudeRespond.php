<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClaudeRespond extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claude:respond {request_id} {message} {--metadata=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a response from Claude Code to a pending request';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $requestId = $this->argument('request_id');
        $message = $this->argument('message');
        $metadata = $this->option('metadata');
        
        // Parse metadata if provided
        $metadataArray = [];
        if ($metadata) {
            $metadataArray = json_decode($metadata, true) ?? [];
        }
        
        // Add default metadata
        $metadataArray = array_merge([
            'responded_by' => 'Claude Code',
            'timestamp' => now()->toIso8601String(),
            'has_project_context' => true
        ], $metadataArray);
        
        // Create response file
        $responseFile = storage_path('app/claude-bridge/responses/' . $requestId . '.json');
        @mkdir(dirname($responseFile), 0755, true);
        
        $responseData = [
            'request_id' => $requestId,
            'message' => $message,
            'metadata' => $metadataArray,
            'created_at' => now()->toIso8601String()
        ];
        
        file_put_contents($responseFile, json_encode($responseData, JSON_PRETTY_PRINT));
        
        $this->info("✓ Response sent for request: {$requestId}");
        
        // Clean up signal file if it exists
        $signalFile = storage_path('app/claude-bridge/pending/' . $requestId . '.signal');
        if (file_exists($signalFile)) {
            @unlink($signalFile);
        }
        
        return Command::SUCCESS;
    }
}