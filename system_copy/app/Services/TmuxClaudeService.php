<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Service for managing Claude CLI through tmux sessions
 * This provides a persistent Claude session that maintains context
 */
class TmuxClaudeService
{
    private $sessionName = 'claude_chat';
    private $isInitialized = false;
    private $lastActivity = null;
    private $timeout = 30; // seconds to wait for response
    
    public function __construct()
    {
        $this->ensureSessionExists();
    }
    
    /**
     * Ensure tmux session exists
     */
    private function ensureSessionExists(): bool
    {
        try {
            // Check if tmux session exists
            $checkCommand = "tmux has-session -t {$this->sessionName} 2>&1";
            exec($checkCommand, $output, $returnCode);
            
            if ($returnCode !== 0) {
                Log::info('[TMUX-CLAUDE] Creating new tmux session', [
                    'session' => $this->sessionName
                ]);
                
                // Create new tmux session
                $createCommand = "tmux new-session -d -s {$this->sessionName} 2>&1";
                exec($createCommand, $createOutput, $createReturn);
                
                if ($createReturn !== 0) {
                    Log::error('[TMUX-CLAUDE] Failed to create tmux session', [
                        'error' => implode("\n", $createOutput)
                    ]);
                    return false;
                }
                
                // Initialize Claude in the session
                $this->initializeClaude();
            } else {
                Log::debug('[TMUX-CLAUDE] Session already exists', [
                    'session' => $this->sessionName
                ]);
            }
            
            return true;
            
        } catch (Exception $e) {
            Log::error('[TMUX-CLAUDE] Error ensuring session', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Initialize Claude in tmux session
     */
    private function initializeClaude(): void
    {
        try {
            Log::info('[TMUX-CLAUDE] Initializing Claude CLI');
            
            // Get the Claude CLI path
            $claudePath = trim(shell_exec('which claude 2>&1'));
            
            if (empty($claudePath)) {
                throw new Exception('Claude CLI not found in PATH');
            }
            
            Log::info('[TMUX-CLAUDE] Found Claude at: ' . $claudePath);
            
            // Send claude command to start the CLI
            $this->sendToTmux($claudePath);
            
            // Wait for Claude to initialize
            sleep(2);
            
            // Capture initial output to verify it started
            $output = $this->captureOutput();
            
            Log::info('[TMUX-CLAUDE] Claude initialized', [
                'output_preview' => substr($output, 0, 200)
            ]);
            
            $this->isInitialized = true;
            
        } catch (Exception $e) {
            Log::error('[TMUX-CLAUDE] Failed to initialize Claude', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Send message to Claude via tmux
     */
    public function sendMessage(string $message, array $context = []): array
    {
        $startTime = microtime(true);
        
        Log::info('[TMUX-CLAUDE] Sending message', [
            'message_length' => strlen($message),
            'session' => $this->sessionName,
            'has_context' => !empty($context)
        ]);
        
        try {
            // Ensure session exists
            if (!$this->ensureSessionExists()) {
                throw new Exception('Failed to ensure tmux session');
            }
            
            // If not initialized, initialize Claude
            if (!$this->isInitialized) {
                $this->initializeClaude();
            }
            
            // Capture output before sending message (to get baseline)
            $beforeOutput = $this->captureOutput();
            $beforeLines = count(explode("\n", $beforeOutput));
            
            // Send the message
            $this->sendToTmux($message);
            
            // Wait for response with streaming support
            if (isset($context['enable_streaming']) && $context['enable_streaming']) {
                $response = $this->streamResponse($beforeLines, $context);
            } else {
                $response = $this->waitForResponse($beforeLines);
            }
            
            $processingTime = microtime(true) - $startTime;
            
            Log::info('[TMUX-CLAUDE] Response received', [
                'response_length' => strlen($response),
                'processing_time' => round($processingTime, 2)
            ]);
            
            return [
                'success' => true,
                'message' => $response,
                'processing_time' => $processingTime,
                'session' => $this->sessionName
            ];
            
        } catch (Exception $e) {
            Log::error('[TMUX-CLAUDE] Error sending message', [
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
     * Send text to tmux session
     */
    private function sendToTmux(string $text): void
    {
        $safeText = escapeshellarg($text);
        $command = "tmux send-keys -t {$this->sessionName} {$safeText} C-m";
        
        Log::debug('[TMUX-CLAUDE] Sending to tmux', [
            'command' => substr($command, 0, 100),
            'text_length' => strlen($text)
        ]);
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Failed to send to tmux: ' . implode("\n", $output));
        }
        
        $this->lastActivity = microtime(true);
    }
    
    /**
     * Capture current output from tmux session
     */
    private function captureOutput(): string
    {
        $command = "tmux capture-pane -t {$this->sessionName} -p";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Failed to capture tmux output');
        }
        
        return implode("\n", $output);
    }
    
    /**
     * Wait for Claude's response
     */
    private function waitForResponse(int $baselineLines): string
    {
        $startTime = microtime(true);
        $lastOutput = '';
        $stableCount = 0;
        $requiredStableChecks = 3; // Output must be stable for 3 checks
        
        while ((microtime(true) - $startTime) < $this->timeout) {
            usleep(500000); // Wait 500ms
            
            $currentOutput = $this->captureOutput();
            $lines = explode("\n", $currentOutput);
            
            // Get only new lines (response)
            if (count($lines) > $baselineLines) {
                $newLines = array_slice($lines, $baselineLines);
                $newOutput = implode("\n", $newLines);
                
                // Check if output has stabilized
                if ($newOutput === $lastOutput) {
                    $stableCount++;
                    if ($stableCount >= $requiredStableChecks) {
                        // Output has been stable, response is complete
                        return $this->cleanResponse($newOutput);
                    }
                } else {
                    $stableCount = 0;
                    $lastOutput = $newOutput;
                }
            }
        }
        
        // Timeout reached, return what we have
        return $this->cleanResponse($lastOutput);
    }
    
    /**
     * Stream response with callback support
     */
    private function streamResponse(int $baselineLines, array $context): string
    {
        $startTime = microtime(true);
        $fullResponse = '';
        $lastLength = 0;
        $stableCount = 0;
        
        // Get stream callback if provided
        $streamCallback = isset($context['stream_callback']) ? $context['stream_callback'] : null;
        $sessionId = $context['stream_to_session'] ?? null;
        
        while ((microtime(true) - $startTime) < $this->timeout) {
            usleep(200000); // Wait 200ms
            
            $currentOutput = $this->captureOutput();
            $lines = explode("\n", $currentOutput);
            
            // Get only new lines
            if (count($lines) > $baselineLines) {
                $newLines = array_slice($lines, $baselineLines);
                $newOutput = implode("\n", $newLines);
                
                // Check for new content
                if (strlen($newOutput) > $lastLength) {
                    $chunk = substr($newOutput, $lastLength);
                    
                    // Stream the chunk
                    if ($streamCallback) {
                        $streamCallback($chunk);
                    }
                    
                    // Store in cache for SSE
                    if ($sessionId) {
                        $this->streamToSession($sessionId, $chunk);
                    }
                    
                    $lastLength = strlen($newOutput);
                    $fullResponse = $newOutput;
                    $stableCount = 0;
                } else {
                    $stableCount++;
                    if ($stableCount >= 5) {
                        // Output stable for 1 second, response complete
                        break;
                    }
                }
            }
        }
        
        return $this->cleanResponse($fullResponse);
    }
    
    /**
     * Stream chunk to session cache
     */
    private function streamToSession(string $sessionId, string $chunk): void
    {
        $streamKey = "claude_stream_{$sessionId}";
        $currentStream = Cache::get($streamKey, '');
        Cache::put($streamKey, $currentStream . $chunk, 120);
        
        Log::debug('[TMUX-CLAUDE] Streamed chunk to session', [
            'session_id' => $sessionId,
            'chunk_length' => strlen($chunk),
            'total_length' => strlen($currentStream . $chunk)
        ]);
    }
    
    /**
     * Clean response text
     */
    private function cleanResponse(string $response): string
    {
        // Remove any tmux artifacts or control characters
        $response = preg_replace('/\x1b\[[0-9;]*m/', '', $response); // Remove ANSI colors
        $response = preg_replace('/\r/', '', $response); // Remove carriage returns
        
        // Trim empty lines from start and end
        $response = trim($response);
        
        return $response;
    }
    
    /**
     * Check if service is available
     */
    public function isAvailable(): bool
    {
        // Check if tmux is installed
        $tmuxPath = trim(shell_exec('which tmux 2>&1'));
        
        if (empty($tmuxPath) || strpos($tmuxPath, 'not found') !== false) {
            Log::warning('[TMUX-CLAUDE] tmux not installed');
            return false;
        }
        
        Log::debug('[TMUX-CLAUDE] tmux found at: ' . $tmuxPath);
        
        // Check if claude CLI exists
        $claudePath = trim(shell_exec('which claude 2>&1'));
        
        if (empty($claudePath) || strpos($claudePath, 'not found') !== false) {
            Log::warning('[TMUX-CLAUDE] claude CLI not found');
            return false;
        }
        
        Log::debug('[TMUX-CLAUDE] claude found at: ' . $claudePath);
        
        return true;
    }
    
    /**
     * Kill tmux session
     */
    public function killSession(): void
    {
        $command = "tmux kill-session -t {$this->sessionName} 2>&1";
        exec($command);
        
        $this->isInitialized = false;
        
        Log::info('[TMUX-CLAUDE] Session killed', [
            'session' => $this->sessionName
        ]);
    }
    
    /**
     * Get session info
     */
    public function getSessionInfo(): array
    {
        $hasSession = false;
        $output = '';
        
        // Check if session exists
        exec("tmux has-session -t {$this->sessionName} 2>&1", $checkOutput, $returnCode);
        $hasSession = ($returnCode === 0);
        
        if ($hasSession) {
            $output = $this->captureOutput();
        }
        
        return [
            'session_name' => $this->sessionName,
            'exists' => $hasSession,
            'initialized' => $this->isInitialized,
            'last_activity' => $this->lastActivity,
            'output_length' => strlen($output),
            'output_preview' => substr($output, -500) // Last 500 chars
        ];
    }
}