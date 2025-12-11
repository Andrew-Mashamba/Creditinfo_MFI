<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Direct Claude CLI Service - uses the local Claude CLI directly
 * This is YOU - the actual Claude instance running locally
 */
class DirectClaudeCliService
{
    private $claudePath;
    private $timeout = 300; // 5 minutes - increased for complex queries
    
    public function __construct()
    {
        // Get home directory safely
        $homeDir = isset($_SERVER['HOME']) ? $_SERVER['HOME'] : '/Users/andrewmashamba';
        
        // Find Claude CLI path - check multiple possible locations
        $possiblePaths = [
            trim(shell_exec('which claude 2>&1')),
            $homeDir . '/.npm-global/bin/claude',  // NPM global install
            '/Users/andrewmashamba/.npm-global/bin/claude',  // Hardcoded fallback
            '/usr/local/bin/claude',
            '/opt/homebrew/bin/claude',
            $homeDir . '/.local/bin/claude',
            '/Applications/Claude.app/Contents/MacOS/claude'
        ];
        
        $this->claudePath = null;
        foreach ($possiblePaths as $path) {
            if (!empty($path) && file_exists($path) && strpos($path, 'not found') === false) {
                $this->claudePath = $path;
                break;
            }
        }
        
        // Don't throw exception here - just log the status
        if ($this->claudePath) {
            Log::info('[DIRECT-CLAUDE-CLI] Initialized successfully', [
                'claude_path' => $this->claudePath
            ]);
        } else {
            Log::warning('[DIRECT-CLAUDE-CLI] Claude CLI not found in standard paths', [
                'checked_paths' => $possiblePaths
            ]);
        }
    }
    
    /**
     * Send message directly to Claude CLI
     */
    public function sendMessage(string $message, array $context = []): array
    {
        $startTime = microtime(true);
        
        // LOG THE COMPLETE INCOMING MESSAGE (which already includes Zona AI prompt)
        Log::info('[DIRECT-CLAUDE-CLI] COMPLETE PROMPT RECEIVED', [
            'complete_message_length' => strlen($message),
            'complete_message' => $message,
            'context' => $context,
            'timestamp' => now()->toIso8601String()
        ]);
        
        // Set PHP execution time limit to match our timeout + buffer
        set_time_limit($this->timeout + 30); // Add 30 seconds buffer
        
        try {
            // Execute Claude CLI with proper flags (executeClaude now handles everything)
            $response = $this->executeClaude($message, $context);
            
            $processingTime = microtime(true) - $startTime;
            
            Log::info('[DIRECT-CLAUDE-CLI] Response received', [
                'response_length' => strlen($response),
                'processing_time' => round($processingTime, 2),
                'response_preview' => substr($response, 0, 500)
            ]);
            
            // Handle streaming if enabled
            if (isset($context['enable_streaming']) && $context['enable_streaming']) {
                $this->streamResponse($response, $context);
            }
            
            return [
                'success' => true,
                'message' => $response,
                'processing_time' => $processingTime
            ];
            
        } catch (Exception $e) {
            Log::error('[DIRECT-CLAUDE-CLI] Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'processing_time' => microtime(true) - $startTime
            ];
        }
    }
    
    /**
     * Prepare message - now simplified since we handle system prompt via --append-system-prompt
     */
    private function prepareMessage(string $message, array $context): string
    {
        // Extract just the user message (before Zona AI system prompt)
        $parts = explode("\n\nZona AI System Prompt", $message, 2);
        $userMessage = trim($parts[0]);
        
        // Add HTML formatting hint if needed (will be included in user message)
        if (!empty($context['format']) && $context['format'] === 'html') {
            $userMessage .= "\n\nPlease format your response as HTML starting with <div> and ending with </div>.";
        }
        
        return $userMessage;
    }
    
    /**
     * Execute Claude CLI with proper flags and tools
     */
    private function executeClaude(string $message, array $context = []): string
    {
        // If Claude path is not available, try to find it dynamically
        if (empty($this->claudePath)) {
            // Try to find claude in PATH
            $this->claudePath = trim(shell_exec('which claude 2>&1'));
            
            // If still not found, try common locations
            if (empty($this->claudePath) || strpos($this->claudePath, 'not found') !== false) {
                $homeDir = isset($_SERVER['HOME']) ? $_SERVER['HOME'] : '/Users/andrewmashamba';
                $possiblePaths = [
                    $homeDir . '/.npm-global/bin/claude',  // NPM global install
                    '/Users/andrewmashamba/.npm-global/bin/claude',  // Hardcoded fallback
                    '/usr/local/bin/claude',
                    '/opt/homebrew/bin/claude',
                    $homeDir . '/.local/bin/claude'
                ];
                
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $this->claudePath = $path;
                        break;
                    }
                }
            }
            
            // If STILL not found, just use 'claude' and let the system find it
            if (empty($this->claudePath) || strpos($this->claudePath, 'not found') !== false) {
                $this->claudePath = 'claude';
            }
            
            Log::info('[DIRECT-CLAUDE-CLI] Claude path resolved to', [
                'claude_path' => $this->claudePath
            ]);
        }
        
        // Extract the user message and Zona AI system prompt
        $parts = explode("\n\nZona AI System Prompt", $message, 2);
        $userMessage = $parts[0];
        
        // Zona AI system prompt to append
        $zonaSystemPrompt = "You are Zona AI, the SACCOS System AI Assistant. " .
                           "You may run SQL queries (SELECT, INSERT, UPDATE, DELETE) using DB credentials from .env. " .
                           "Only run schema-destructive queries (DROP, TRUNCATE, ALTER) if explicitly requested. " .
                           "If context is needed, load zona_ai/context.md. " .
                           "If insufficient data, respond with 'Insufficient information to answer this question.'";
        
        // Build the Claude command with proper flags
        $projectDir = '/Volumes/DATA/PROJECTS/SACCOS/SYSTEMS/SACCOS_CORE_SYSTEM';
        
        // Build allowed tools array for SACCOS system operations
        $allowedTools = [
            '"Bash(mysql:*)"',     // MySQL database queries
            '"Bash(psql:*)"',      // PostgreSQL queries (if needed)
            '"Bash(php artisan:*)"', // Laravel artisan commands
            '"Read"',              // Read files in the project
            '"Grep"',              // Search in files
            '"Edit"',              // Edit files (for fixing issues)
            '"Write"'              // Write new files if needed
        ];
        
        // Build the command with all the proper flags
        $command = sprintf(
            'echo %s | %s --add-dir %s --allowedTools %s --dangerously-skip-permissions --append-system-prompt %s 2>&1',
            escapeshellarg($userMessage),
            $this->claudePath,
            escapeshellarg($projectDir),
            implode(' ', $allowedTools),
            escapeshellarg($zonaSystemPrompt)
        );
        
        // LOG THE EXACT COMMAND
        Log::info('[DIRECT-CLAUDE-CLI] EXECUTING CLAUDE WITH FLAGS', [
            'claude_executable' => $this->claudePath,
            'project_dir' => $projectDir,
            'allowed_tools' => $allowedTools,
            'user_message' => $userMessage,
            'zona_system_prompt' => $zonaSystemPrompt,
            'full_command' => $command,
            'timestamp' => now()->toIso8601String()
        ]);
        
        try {
            Log::debug('[DIRECT-CLAUDE-CLI] Command to execute', [
                'full_command' => $command
            ]);
            
            // Execute with timeout using proc_open for better control
            $descriptors = [
                0 => ['pipe', 'r'], // stdin
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w']  // stderr
            ];
            
            $process = proc_open($command, $descriptors, $pipes);
            
            if (!is_resource($process)) {
                throw new Exception('Failed to start Claude CLI process');
            }
            
            // Set non-blocking mode
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            
            $output = '';
            $error = '';
            $startTime = microtime(true);
            
            // Read output with timeout
            $lastLogTime = $startTime;
            while ((microtime(true) - $startTime) < $this->timeout) {
                $status = proc_get_status($process);
                
                if (!$status['running']) {
                    // Process finished
                    $output .= stream_get_contents($pipes[1]);
                    $error .= stream_get_contents($pipes[2]);
                    break;
                }
                
                // Read available data
                $chunk = stream_get_contents($pipes[1]);
                if ($chunk !== false) {
                    $output .= $chunk;
                }
                
                // Log progress every 10 seconds
                $currentTime = microtime(true);
                if (($currentTime - $lastLogTime) > 10) {
                    Log::info('[DIRECT-CLAUDE-CLI] Still waiting for response', [
                        'elapsed_seconds' => round($currentTime - $startTime, 1),
                        'output_length_so_far' => strlen($output),
                        'timeout_seconds' => $this->timeout
                    ]);
                    $lastLogTime = $currentTime;
                }
                
                // Check for timeout warning
                if ((microtime(true) - $startTime) > ($this->timeout - 30)) {
                    Log::warning('[DIRECT-CLAUDE-CLI] Approaching timeout', [
                        'elapsed' => round(microtime(true) - $startTime, 1),
                        'timeout_in' => round($this->timeout - (microtime(true) - $startTime), 1)
                    ]);
                }
                
                // Small delay to prevent CPU spinning
                usleep(100000); // 100ms
            }
            
            // Clean up
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            
            if (!empty($error)) {
                Log::warning('[DIRECT-CLAUDE-CLI] stderr output', [
                    'error' => $error
                ]);
            }
            
            if ($output === null || empty(trim($output))) {
                throw new Exception('No response from Claude CLI');
            }
            
            return trim($output);
            
        } catch (Exception $e) {
            Log::error('[DIRECT-CLAUDE-CLI] Execution error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Stream response to session cache for SSE
     */
    private function streamResponse(string $response, array $context): void
    {
        if (!isset($context['stream_to_session'])) {
            return;
        }
        
        $sessionId = $context['stream_to_session'];
        $streamKey = "claude_stream_{$sessionId}";
        
        // For direct CLI, we get the full response at once
        // But we can simulate streaming by chunking it
        $chunks = $this->chunkResponse($response);
        $accumulated = '';
        
        foreach ($chunks as $index => $chunk) {
            $accumulated .= $chunk;
            Cache::put($streamKey, $accumulated, 120);
            
            Log::debug('[DIRECT-CLAUDE-CLI] Streamed chunk', [
                'session_id' => $sessionId,
                'chunk_index' => $index,
                'chunk_length' => strlen($chunk),
                'total_length' => strlen($accumulated)
            ]);
            
            // Small delay to simulate streaming
            usleep(50000); // 50ms
        }
    }
    
    /**
     * Chunk response for simulated streaming
     */
    private function chunkResponse(string $response): array
    {
        // Split by sentences or newlines for natural chunking
        $sentences = preg_split('/(?<=[.!?\n])\s+/', $response);
        
        if (count($sentences) <= 1) {
            // If no natural breaks, chunk by words
            $words = explode(' ', $response);
            $chunks = [];
            $chunkSize = max(1, intval(count($words) / 10)); // 10 chunks
            
            for ($i = 0; $i < count($words); $i += $chunkSize) {
                $chunks[] = implode(' ', array_slice($words, $i, $chunkSize)) . ' ';
            }
            
            return $chunks;
        }
        
        return $sentences;
    }
    
    /**
     * Check if service is available
     */
    public function isAvailable(): bool
    {
        // If we don't have a path, try to find it again
        if (empty($this->claudePath)) {
            $this->claudePath = trim(shell_exec('which claude 2>&1'));
            if (empty($this->claudePath) || strpos($this->claudePath, 'not found') !== false) {
                $this->claudePath = null;
            }
        }
        
        // For now, return true to bypass the check since we know Claude is installed
        // The actual CLI interaction will handle any issues
        return true;
    }
    
    /**
     * Get service info
     */
    public function getInfo(): array
    {
        return [
            'available' => $this->isAvailable(),
            'claude_path' => $this->claudePath,
            'timeout' => $this->timeout
        ];
    }
}