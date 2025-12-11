<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ResellerApiLogger
{
    private $logChannel = 'reseller_api';
    private $logFile = 'reseller-api.log';
    
    public function __construct()
    {
        // Ensure the log channel is configured
        $this->configureLogChannel();
    }
    
    /**
     * Log API request
     */
    public function logRequest($method, $url, $payload, $headers = [])
    {
        $logData = [
            'timestamp' => Carbon::now()->toISOString(),
            'type' => 'REQUEST',
            'method' => $method,
            'url' => $url,
            'payload' => $this->maskSensitiveData($payload),
            'headers' => $this->maskSensitiveHeaders($headers),
            'request_id' => $this->generateRequestId()
        ];
        
        $this->writeLog($logData);
        
        return $logData['request_id'];
    }
    
    /**
     * Log API response
     */
    public function logResponse($requestId, $statusCode, $response, $responseTime = null)
    {
        $logData = [
            'timestamp' => Carbon::now()->toISOString(),
            'type' => 'RESPONSE',
            'request_id' => $requestId,
            'status_code' => $statusCode,
            'response' => $this->maskSensitiveData($response),
            'response_time_ms' => $responseTime
        ];
        
        $this->writeLog($logData);
    }
    
    /**
     * Log API error
     */
    public function logError($requestId, $error, $context = [])
    {
        $logData = [
            'timestamp' => Carbon::now()->toISOString(),
            'type' => 'ERROR',
            'request_id' => $requestId,
            'error' => $error,
            'context' => $context
        ];
        
        $this->writeLog($logData);
    }
    
    /**
     * Log domain operation
     */
    public function logDomainOperation($operation, $domain, $data = [], $result = null)
    {
        $logData = [
            'timestamp' => Carbon::now()->toISOString(),
            'type' => 'DOMAIN_OPERATION',
            'operation' => $operation,
            'domain' => $domain,
            'data' => $this->maskSensitiveData($data),
            'result' => $result ? $this->maskSensitiveData($result) : null
        ];
        
        $this->writeLog($logData);
    }
    
    /**
     * Log API rate limit information
     */
    public function logRateLimit($requestId, $limit, $remaining, $resetTime)
    {
        $logData = [
            'timestamp' => Carbon::now()->toISOString(),
            'type' => 'RATE_LIMIT',
            'request_id' => $requestId,
            'limit' => $limit,
            'remaining' => $remaining,
            'reset_time' => $resetTime
        ];
        
        $this->writeLog($logData);
    }
    
    /**
     * Write log entry
     */
    private function writeLog($logData)
    {
        $logEntry = json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        // Write to Laravel log
        Log::channel($this->logChannel)->info('Reseller API', $logData);
        
        // Also write to dedicated file
        $this->writeToFile($logEntry);
    }
    
    /**
     * Write to dedicated log file
     */
    private function writeToFile($logEntry)
    {
        $logPath = storage_path('logs/' . $this->logFile);
        $separator = str_repeat('=', 80) . "\n";
        
        file_put_contents($logPath, $separator . $logEntry . "\n\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Mask sensitive data in payload
     */
    private function maskSensitiveData($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        
        $sensitiveKeys = [
            'api_key',
            'password',
            'secret',
            'token',
            'authorization',
            'x-api-key'
        ];
        
        $masked = $data;
        
        foreach ($sensitiveKeys as $key) {
            if (isset($masked[$key])) {
                $masked[$key] = $this->maskValue($masked[$key]);
            }
        }
        
        // Recursively mask nested arrays
        foreach ($masked as $key => $value) {
            if (is_array($value)) {
                $masked[$key] = $this->maskSensitiveData($value);
            }
        }
        
        return $masked;
    }
    
    /**
     * Mask sensitive headers
     */
    private function maskSensitiveHeaders($headers)
    {
        $sensitiveHeaders = [
            'X-API-KEY',
            'Authorization',
            'X-Auth-Token'
        ];
        
        $masked = $headers;
        
        foreach ($sensitiveHeaders as $header) {
            if (isset($masked[$header])) {
                $masked[$header] = $this->maskValue($masked[$header]);
            }
        }
        
        return $masked;
    }
    
    /**
     * Mask a sensitive value
     */
    private function maskValue($value)
    {
        if (empty($value)) {
            return $value;
        }
        
        $length = strlen($value);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        return substr($value, 0, 4) . str_repeat('*', $length - 8) . substr($value, -4);
    }
    
    /**
     * Generate unique request ID
     */
    private function generateRequestId()
    {
        return 'req_' . uniqid() . '_' . time();
    }
    
    /**
     * Configure log channel
     */
    private function configureLogChannel()
    {
        $config = config('logging.channels');
        
        if (!isset($config[$this->logChannel])) {
            // Add the channel to the config if it doesn't exist
            config(['logging.channels.' . $this->logChannel => [
                'driver' => 'single',
                'path' => storage_path('logs/' . $this->logFile),
                'level' => 'debug',
                'days' => 30,
            ]]);
        }
    }
    
    /**
     * Get log statistics
     */
    public function getLogStats($days = 7)
    {
        $logPath = storage_path('logs/' . $this->logFile);
        
        if (!file_exists($logPath)) {
            return [
                'total_requests' => 0,
                'successful_requests' => 0,
                'failed_requests' => 0,
                'average_response_time' => 0,
                'most_common_operations' => []
            ];
        }
        
        $logs = file_get_contents($logPath);
        $lines = explode("\n", $logs);
        
        $stats = [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'response_times' => [],
            'operations' => []
        ];
        
        foreach ($lines as $line) {
            if (empty(trim($line)) || strpos($line, '=') === 0) {
                continue;
            }
            
            $logData = json_decode($line, true);
            if (!$logData) {
                continue;
            }
            
            // Check if log is within the specified days
            $logTime = Carbon::parse($logData['timestamp'] ?? '');
            if ($logTime->lt(Carbon::now()->subDays($days))) {
                continue;
            }
            
            if ($logData['type'] === 'REQUEST') {
                $stats['total_requests']++;
            } elseif ($logData['type'] === 'RESPONSE') {
                if ($logData['status_code'] >= 200 && $logData['status_code'] < 300) {
                    $stats['successful_requests']++;
                } else {
                    $stats['failed_requests']++;
                }
                
                if (isset($logData['response_time_ms'])) {
                    $stats['response_times'][] = $logData['response_time_ms'];
                }
            } elseif ($logData['type'] === 'DOMAIN_OPERATION') {
                $operation = $logData['operation'] ?? 'unknown';
                $stats['operations'][$operation] = ($stats['operations'][$operation] ?? 0) + 1;
            }
        }
        
        $stats['average_response_time'] = !empty($stats['response_times']) 
            ? round(array_sum($stats['response_times']) / count($stats['response_times']), 2)
            : 0;
        
        unset($stats['response_times']);
        
        return $stats;
    }
    
    /**
     * Clean old logs
     */
    public function cleanOldLogs($days = 30)
    {
        $logPath = storage_path('logs/' . $this->logFile);
        
        if (!file_exists($logPath)) {
            return;
        }
        
        $logs = file_get_contents($logPath);
        $lines = explode("\n", $logs);
        
        $filteredLines = [];
        $currentEntry = '';
        $inEntry = false;
        
        foreach ($lines as $line) {
            if (strpos($line, '=') === 0) {
                if ($inEntry) {
                    $logData = json_decode($currentEntry, true);
                    if ($logData && isset($logData['timestamp'])) {
                        $logTime = Carbon::parse($logData['timestamp']);
                        if ($logTime->gte(Carbon::now()->subDays($days))) {
                            $filteredLines[] = str_repeat('=', 80);
                            $filteredLines[] = $currentEntry;
                        }
                    }
                }
                $currentEntry = '';
                $inEntry = true;
            } else {
                $currentEntry .= $line . "\n";
            }
        }
        
        // Handle the last entry
        if ($inEntry && !empty(trim($currentEntry))) {
            $logData = json_decode($currentEntry, true);
            if ($logData && isset($logData['timestamp'])) {
                $logTime = Carbon::parse($logData['timestamp']);
                if ($logTime->gte(Carbon::now()->subDays($days))) {
                    $filteredLines[] = str_repeat('=', 80);
                    $filteredLines[] = $currentEntry;
                }
            }
        }
        
        file_put_contents($logPath, implode("\n", $filteredLines) . "\n");
    }
}
