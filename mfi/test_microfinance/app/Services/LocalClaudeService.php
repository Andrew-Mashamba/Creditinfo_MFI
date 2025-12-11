<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;
use App\Services\TmuxClaudeService;

class LocalClaudeService
{
    private $processManager;
    private $queryQueue;
    private $tmuxService;
    private $contextFile;
    private $useTmux = true; // Use tmux-based Claude by default
    private $usePersistentProcess = false; // Disabled in favor of tmux
    private $useQueue = false; // Queue is optional
    
    public function __construct()
    {
        // Set context file path
        $this->contextFile = base_path('zona_ai/context.md');
        
        // Initialize tmux service first
        if ($this->useTmux) {
            try {
                $this->tmuxService = new TmuxClaudeService();
            } catch (Exception $e) {
                Log::warning('[LOCAL-CLAUDE] Failed to initialize TmuxClaudeService', [
                    'error' => $e->getMessage()
                ]);
                $this->useTmux = false;
                $this->usePersistentProcess = true;
            }
        }
        
        // Initialize managers if not using tmux
        if (!$this->useTmux && $this->usePersistentProcess) {
            $this->processManager = ClaudeProcessManager::getInstance();
        }
        
        if ($this->useQueue) {
            $this->queryQueue = ClaudeQueryQueue::getInstance();
        }
    }
    
    /**
     * Send a message with multiple strategies for optimal performance
     */
    public function sendMessage(string $message, array $context = []): array
    {
        $startTime = microtime(true);
        
        Log::info('[LOCAL-CLAUDE-START] Processing message', [
            'message_length' => strlen($message),
            'mode' => $this->useTmux ? 'tmux' : ($this->usePersistentProcess ? 'persistent' : 'per-request'),
            'has_context' => !empty($context),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        // Set execution time limit
        set_time_limit(120);
        
        try {
            // Choose processing strategy
            if ($this->useTmux && $this->tmuxService) {
                Log::debug('[LOCAL-CLAUDE] Using tmux-based Claude', [
                    'has_service' => $this->tmuxService !== null
                ]);
                $result = $this->tmuxService->sendMessage($message, $context);
            } else if ($this->usePersistentProcess) {
                Log::debug('[LOCAL-CLAUDE] Using persistent process', [
                    'has_manager' => $this->processManager !== null
                ]);
                $result = $this->sendWithPersistentProcess($message, $context);
            } else {
                Log::debug('[LOCAL-CLAUDE] Using new process per request');
                $result = $this->sendWithNewProcess($message, $context);
            }
            
            $totalTime = microtime(true) - $startTime;
            
            Log::info('[LOCAL-CLAUDE-SUCCESS] Message processed', [
                'success' => $result['success'] ?? false,
                'processing_time' => round($totalTime, 2),
                'response_length' => isset($result['message']) ? strlen($result['message']) : 0,
                'mode_used' => $this->usePersistentProcess ? 'persistent' : 'per-request'
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $totalTime = microtime(true) - $startTime;
            
            Log::error('[LOCAL-CLAUDE-ERROR] Processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'processing_time' => round($totalTime, 2),
                'mode' => $this->usePersistentProcess ? 'persistent' : 'per-request'
            ]);
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'error' => 'EXECUTION_ERROR',
                'processing_time' => round($totalTime, 2)
            ];
        }
    }
    
    /**
     * Send message using persistent process (maintains context)
     */
    private function sendWithPersistentProcess(string $message, array $context = []): array
    {
        Log::debug('[PERSISTENT-PROCESS] Starting persistent process handling', [
            'has_streaming' => isset($context['enable_streaming']) && $context['enable_streaming'],
            'use_queue' => $this->useQueue
        ]);
        
        // Add streaming callback if needed
        if (isset($context['enable_streaming']) && $context['enable_streaming']) {
            Log::debug('[PERSISTENT-PROCESS] Setting up streaming callback', [
                'stream_to_session' => $context['stream_to_session'] ?? null
            ]);
            
            $context['stream_callback'] = function($chunk) use ($context) {
                // Emit to frontend via websocket/SSE if configured
                if (isset($context['stream_to_session'])) {
                    $this->streamToSession($context['stream_to_session'], $chunk);
                }
            };
        }
        
        // Use queue if enabled
        if ($this->useQueue) {
            $queryId = $this->queryQueue->addQuery($message, $context);
            
            // For async mode, return query ID immediately
            if (isset($context['async']) && $context['async']) {
                return [
                    'success' => true,
                    'query_id' => $queryId,
                    'message' => 'Query queued for processing',
                    'async' => true
                ];
            }
            
            // Wait for completion
            $result = $this->queryQueue->waitForQuery($queryId, 60);
            
            if ($result) {
                return [
                    'success' => $result['status'] === 'completed',
                    'message' => $result['response'] ?? $result['error'] ?? 'No response',
                    'processing_time' => $result['processing_time'] ?? 0
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Query timeout',
                'error' => 'TIMEOUT'
            ];
        }
        
        // Direct processing with persistent process
        Log::debug('[PERSISTENT-PROCESS] Sending to process manager', [
            'message_preview' => substr($message, 0, 100)
        ]);
        
        $result = $this->processManager->sendMessage($message, $context);
        
        Log::debug('[PERSISTENT-PROCESS] Received response from process manager', [
            'success' => $result['success'] ?? false,
            'response_length' => isset($result['message']) ? strlen($result['message']) : 0,
            'processing_time' => $result['processing_time'] ?? null
        ]);
        
        return $result;
    }
    
    /**
     * Send message with new process (legacy mode for comparison)
     */
    private function sendWithNewProcess(string $message, array $context = []): array
    {
        $enhancedMessage = $this->buildMinimalMessage($message, $context);
        $claudePath = $this->getClaudePath();
        
        if (!$claudePath) {
            return [
                'success' => false,
                'message' => 'Claude CLI not found',
                'error' => 'CLAUDE_NOT_FOUND'
            ];
        }
        
        $response = $this->executeDirectPipe($enhancedMessage);
        
        if ($response !== null && !empty(trim($response))) {
            return [
                'success' => true,
                'message' => trim($response),
                'context' => ['method' => 'new_process']
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Empty response from Claude',
            'error' => 'EMPTY_RESPONSE'
        ];
    }
    
    /**
     * Stream response chunk to session/frontend
     */
    private function streamToSession(string $sessionId, string $chunk): void
    {
        // Store in cache for SSE/WebSocket pickup
        $streamKey = "claude_stream_{$sessionId}";
        $currentStream = Cache::get($streamKey, '');
        Cache::put($streamKey, $currentStream . $chunk, 60);
        
        // Emit event for real-time updates
        try {
            event(new \App\Events\ClaudeStreamUpdate($sessionId, $chunk));
        } catch (Exception $e) {
            // Event system might not be configured
            Log::debug('Stream event not configured', ['session' => $sessionId]);
        }
    }
    
    /**
     * Pre-warm Claude process for faster first response
     */
    public function prewarm(): bool
    {
        Log::info('[PREWARM-START] Beginning Claude pre-warm', [
            'persistent_mode' => $this->usePersistentProcess,
            'context_file' => $this->contextFile,
            'file_exists' => file_exists($this->contextFile)
        ]);
        
        if (!$this->usePersistentProcess) {
            Log::debug('[PREWARM] Skipping - not in persistent mode');
            return false;
        }
        
        try {
            $startTime = microtime(true);
            
            // Start the process
            Log::debug('[PREWARM] Starting process');
            $processStarted = $this->processManager->startProcess();
            
            if (!$processStarted) {
                Log::error('[PREWARM] Failed to start process');
                return false;
            }
            
            // Send a warmup query
            $warmupMessage = "Initialize. Respond with 'Ready' when you've loaded the context from {$this->contextFile}";
            
            Log::debug('[PREWARM] Sending warmup message');
            $response = $this->processManager->sendMessage($warmupMessage, [
                'warmup' => true
            ]);
            
            $totalTime = microtime(true) - $startTime;
            
            Log::info('[PREWARM-SUCCESS] Claude pre-warmed successfully', [
                'success' => $response['success'],
                'warmup_time' => round($totalTime, 2),
                'response_preview' => substr($response['message'] ?? '', 0, 100)
            ]);
            
            return $response['success'];
            
        } catch (Exception $e) {
            Log::error('[PREWARM-ERROR] Pre-warm failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Get conversation history from persistent session
     */
    public function getConversationHistory(): array
    {
        if ($this->usePersistentProcess) {
            return $this->processManager->getHistory();
        }
        return [];
    }
    
    /**
     * Clear conversation while keeping process alive
     */
    public function clearConversation(): void
    {
        if ($this->usePersistentProcess) {
            $this->processManager->clearHistory();
        }
    }
    
    /**
     * Get process status
     */
    public function getStatus(): array
    {
        $status = [
            'mode' => $this->usePersistentProcess ? 'persistent' : 'per-request',
            'queue_enabled' => $this->useQueue,
            'context_file' => file_exists($this->contextFile)
        ];
        
        if ($this->usePersistentProcess) {
            $status['process'] = $this->processManager->getStatus();
        }
        
        if ($this->useQueue) {
            $status['queue'] = $this->queryQueue->getStats();
        }
        
        return $status;
    }
    
    /**
     * Enable/disable persistent process mode
     */
    public function setPersistentMode(bool $enabled): void
    {
        $this->usePersistentProcess = $enabled;
        
        if ($enabled && !$this->processManager) {
            $this->processManager = ClaudeProcessManager::getInstance();
        }
    }
    
    /**
     * Enable/disable queue mode
     */
    public function setQueueMode(bool $enabled): void
    {
        $this->useQueue = $enabled;
        
        if ($enabled && !$this->queryQueue) {
            $this->queryQueue = ClaudeQueryQueue::getInstance();
        }
    }
    
    /**
     * Build minimal message with context file reference
     */
    private function buildMinimalMessage(string $message, array $context = []): string
    {
        $enhanced = "";
        
        if (!empty($context['user_name'])) {
            $enhanced .= "User: {$context['user_name']}\n";
        }
        if (!empty($context['session_id'])) {
            $enhanced .= "Session: {$context['session_id']}\n";
        }
        
        $enhanced .= "\n[QUESTION]\n{$message}\n\n";
        $enhanced .= "For system context, read: {$this->contextFile}\n";
        $enhanced .= "Provide accurate, business-oriented responses.\n";
        
        return $enhanced;
    }
    
    /**
     * Execute Claude with direct pipe (fallback method)
     */
    private function executeDirectPipe(string $message): ?string
    {
        $claudePath = $this->getClaudePath();
        $allowedTools = $this->getAllowedToolsFlags();
        $mcpConfig = $this->getMcpConfigPath();
        
        $command = "{$claudePath} {$allowedTools} --mcp-config {$mcpConfig}";
        
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],  
            2 => ['pipe', 'w']
        ];
        
        $process = proc_open($command, $descriptors, $pipes, base_path());
        
        if (!is_resource($process)) {
            throw new Exception('Failed to start Claude process');
        }
        
        fwrite($pipes[0], $message);
        fclose($pipes[0]);
        
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        
        $output = '';
        $error = '';
        $startTime = time();
        $timeout = 60;
        
        do {
            $status = proc_get_status($process);
            
            $chunk = stream_get_contents($pipes[1]);
            if ($chunk !== false) {
                $output .= $chunk;
            }
            
            $errorChunk = stream_get_contents($pipes[2]);
            if ($errorChunk !== false) {
                $error .= $errorChunk;
            }
            
            if (time() - $startTime > $timeout) {
                proc_terminate($process);
                throw new Exception('Command timeout');
            }
            
            usleep(100000); // 100ms
            
        } while ($status['running']);
        
        $output .= stream_get_contents($pipes[1]);
        $error .= stream_get_contents($pipes[2]);
        
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        
        if (!empty($error)) {
            Log::warning('Claude stderr', ['error' => substr($error, 0, 500)]);
        }
        
        return $output;
    }
    
    /**
     * Get cached Claude path
     */
    private function getClaudePath(): ?string
    {
        return Cache::remember('claude_cli_path', 3600, function() {
            // Check for the npm claude package (not Claude Desktop)
            $npmClaude = trim(shell_exec('which claude 2>&1'));
            
            // Check if it's the npm package by testing if it responds
            if (!empty($npmClaude) && strpos($npmClaude, 'claude') !== false) {
                // Test if it's the working CLI (not Claude Desktop app)
                // The npm claude package exists but may not be properly configured
                Log::info('[LOCAL-CLAUDE] Found claude at: ' . $npmClaude);
                
                // For now, return null since the npm claude package isn't configured
                // In production, you'd configure the actual Claude CLI here
                return null;
            }
            
            return null;
        });
    }
    
    /**
     * Get cached allowed tools flags
     */
    private function getAllowedToolsFlags(): string
    {
        return Cache::rememberForever('claude_allowed_tools', function() {
            $allowedTools = [
                '"Bash(psql*)"',
                '"Read(**)"',
                '"Write(public/reports/**)"',
                '"Bash(ls*)"',
                '"Bash(echo*)"',
                '"mcp__ide__getDiagnostics"',
                '"mcp__ide__executeCode"'
            ];
            
            $flags = '';
            foreach ($allowedTools as $tool) {
                $flags .= ' --allowed-tools ' . $tool;
            }
            return $flags;
        });
    }
    
    /**
     * Get cached MCP config path
     */
    private function getMcpConfigPath(): string
    {
        return escapeshellarg(base_path('.mcp.json'));
    }
    
    /**
     * Check if Claude CLI is available
     */
    public function isAvailable(): bool
    {
        // Check tmux service first
        if ($this->useTmux && $this->tmuxService) {
            if ($this->tmuxService->isAvailable()) {
                Log::debug('[LOCAL-CLAUDE] Available via tmux');
                return true;
            }
        }
        
        // Check regular Claude CLI
        $path = $this->getClaudePath();
        
        if ($path === null) {
            Log::info('[LOCAL-CLAUDE] Claude CLI not available');
            return false;
        }
        
        return true;
    }
    
    /**
     * Test the service
     */
    public function test(): array
    {
        try {
            // Pre-warm if using persistent mode
            if ($this->usePersistentProcess) {
                $this->prewarm();
            }
            
            $startTime = microtime(true);
            $testMessage = "What is 2 + 2?";
            $result = $this->sendMessage($testMessage, ['test' => true]);
            $endTime = microtime(true);
            
            return [
                'success' => $result['success'],
                'mode' => $this->usePersistentProcess ? 'persistent' : 'per-request',
                'response_time' => round($endTime - $startTime, 2) . 's',
                'response' => $result['message'] ?? 'No response',
                'status' => $this->getStatus()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}