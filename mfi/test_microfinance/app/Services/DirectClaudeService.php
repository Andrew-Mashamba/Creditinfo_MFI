<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * DirectClaudeService - Optimized minimal wrapper for Claude CLI
 * Simply passes messages to LocalClaudeService without any interception
 */
class DirectClaudeService
{
    private $localClaudeService;
    
    public function __construct()
    {
        $this->localClaudeService = new LocalClaudeService();
    }
    
    /**
     * Process message by directly calling optimized LocalClaudeService
     */
    public function processMessage(string $message, array $options = []): array
    {
        try {
            // Log the call
            Log::channel('daily')->info('🎯 [DirectClaude] Processing', [
                'message_preview' => substr($message, 0, 100),
                'session_id' => $options['session_id'] ?? 'unknown'
            ]);
            
            // Direct pass-through to optimized LocalClaudeService
            return $this->localClaudeService->sendMessage($message, $options);
            
        } catch (Exception $e) {
            Log::error('DirectClaude: Error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'error' => 'EXCEPTION'
            ];
        }
    }
    
    /**
     * Check if Claude CLI is available
     */
    public function isAvailable(): bool
    {
        return $this->localClaudeService->isAvailable();
    }
    
    /**
     * Get service status
     */
    public function getStatus(): array
    {
        return [
            'service' => 'DirectClaudeService',
            'claude_available' => $this->isAvailable(),
            'mode' => 'OPTIMIZED - Direct pipe communication',
            'description' => 'Minimal wrapper with performance optimizations'
        ];
    }
}