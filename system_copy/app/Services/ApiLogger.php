<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * API Request/Response Logger Service
 * 
 * Provides comprehensive logging for all external API calls
 * with request/response tracking and performance metrics
 */
class ApiLogger
{
    private static $instance = null;
    private $currentRequestId;
    private $logChannel;
    
    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct()
    {
        $this->logChannel = config('logging.api_channel', 'api');
        $this->currentRequestId = uniqid('api_', true);
    }
    
    /**
     * Generate a new request ID
     */
    public function generateRequestId()
    {
        $this->currentRequestId = uniqid('api_', true);
        return $this->currentRequestId;
    }
    
    /**
     * Log API request
     * 
     * @param string $service Service name (e.g., 'BankTransaction', 'GEPG')
     * @param string $operation Operation name (e.g., 'IFT Transfer', 'Bill Query')
     * @param string $method HTTP method
     * @param string $url Full URL
     * @param array $headers Request headers
     * @param mixed $body Request body
     * @param array $metadata Additional metadata
     * @return string Request ID for tracking
     */
    public function logRequest($service, $operation, $method, $url, $headers = [], $body = null, $metadata = [])
    {
        $requestId = $this->generateRequestId();
        
        $logData = [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'service' => $service,
            'operation' => $operation,
            'request' => [
                'method' => $method,
                'url' => $url,
                'headers' => $this->sanitizeHeaders($headers),
                'body' => $this->sanitizeBody($body),
            ],
            'metadata' => array_merge($metadata, [
                'user_id' => auth()->id() ?? null,
                'session_id' => session()->getId() ?? null,
                'ip_address' => request()->ip() ?? null,
            ])
        ];
        
        // Log to file
        Log::channel($this->logChannel)->info('API Request', $logData);
        
        // Store detailed log
        $this->storeDetailedLog('requests', $logData);
        
        return $requestId;
    }
    
    /**
     * Log API response
     * 
     * @param string $requestId Request ID from logRequest
     * @param int|null $statusCode HTTP status code
     * @param array $headers Response headers
     * @param mixed $body Response body
     * @param float $responseTime Response time in seconds
     * @param string|null $error Error message if failed
     * @param array $metadata Additional metadata
     */
    public function logResponse($requestId, $statusCode = null, $headers = [], $body = null, $responseTime = 0, $error = null, $metadata = [])
    {
        $responseReceived = $error === null && $statusCode !== null;
        
        $logData = [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'response_received' => $responseReceived,
            'response' => [
                'status_code' => $statusCode,
                'headers' => $this->sanitizeHeaders($headers),
                'body' => $this->sanitizeBody($body),
                'response_time_ms' => round($responseTime * 1000, 2),
            ],
            'error' => $error,
            'metadata' => $metadata
        ];
        
        // Determine log level based on response
        $logLevel = 'info';
        if ($error !== null) {
            $logLevel = 'error';
        } elseif ($statusCode >= 400) {
            $logLevel = 'warning';
        }
        
        // Log to file
        Log::channel($this->logChannel)->$logLevel('API Response', $logData);
        
        // Store detailed log
        $this->storeDetailedLog('responses', $logData);
        
        // Track metrics
        $this->trackMetrics($requestId, $statusCode, $responseTime, $responseReceived);
        
        return $logData;
    }
    
    /**
     * Log API error
     * 
     * @param string $requestId Request ID
     * @param \Exception $exception Exception that occurred
     * @param array $context Additional context
     */
    public function logError($requestId, \Exception $exception, $context = [])
    {
        $errorData = [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'error' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ],
            'context' => $context
        ];
        
        Log::channel($this->logChannel)->error('API Error', $errorData);
        
        $this->storeDetailedLog('errors', $errorData);
        
        // Alert if critical
        if ($this->isCriticalError($exception)) {
            $this->sendAlert($errorData);
        }
    }
    
    /**
     * Log validation results
     * 
     * @param string $requestId Request ID
     * @param string $service Service name
     * @param string $operation Operation name
     * @param bool $passed Whether validation passed
     * @param array $details Validation details
     */
    public function logValidation($requestId, $service, $operation, $passed, $details = [])
    {
        $validationData = [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'service' => $service,
            'operation' => $operation,
            'validation_passed' => $passed,
            'details' => $details
        ];
        
        $logLevel = $passed ? 'info' : 'warning';
        Log::channel($this->logChannel)->$logLevel('API Validation', $validationData);
        
        $this->storeDetailedLog('validations', $validationData);
    }
    
    /**
     * Get request/response pair by request ID
     * 
     * @param string $requestId
     * @return array|null
     */
    public function getRequestResponsePair($requestId)
    {
        $date = now()->format('Y-m-d');
        
        $requestLog = $this->getLogEntry('requests', $date, $requestId);
        $responseLog = $this->getLogEntry('responses', $date, $requestId);
        
        if ($requestLog && $responseLog) {
            return [
                'request' => $requestLog,
                'response' => $responseLog,
                'pair_complete' => true
            ];
        }
        
        return [
            'request' => $requestLog,
            'response' => $responseLog,
            'pair_complete' => false
        ];
    }
    
    /**
     * Generate daily summary report
     * 
     * @param string|null $date Date in Y-m-d format
     * @return array
     */
    public function generateDailySummary($date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        
        $requests = $this->getLogsByDate('requests', $date);
        $responses = $this->getLogsByDate('responses', $date);
        $errors = $this->getLogsByDate('errors', $date);
        
        $summary = [
            'date' => $date,
            'total_requests' => count($requests),
            'total_responses' => count($responses),
            'total_errors' => count($errors),
            'success_rate' => 0,
            'average_response_time_ms' => 0,
            'services' => [],
            'status_codes' => [],
            'slowest_requests' => [],
            'failed_requests' => []
        ];
        
        // Calculate metrics
        $responseTimes = [];
        $statusCodes = [];
        $services = [];
        
        foreach ($responses as $response) {
            if (isset($response['response']['response_time_ms'])) {
                $responseTimes[] = $response['response']['response_time_ms'];
            }
            
            if (isset($response['response']['status_code'])) {
                $code = $response['response']['status_code'];
                $statusCodes[$code] = ($statusCodes[$code] ?? 0) + 1;
            }
        }
        
        foreach ($requests as $request) {
            if (isset($request['service'])) {
                $service = $request['service'];
                $services[$service] = ($services[$service] ?? 0) + 1;
            }
        }
        
        // Calculate averages
        if (!empty($responseTimes)) {
            $summary['average_response_time_ms'] = round(array_sum($responseTimes) / count($responseTimes), 2);
            $summary['min_response_time_ms'] = min($responseTimes);
            $summary['max_response_time_ms'] = max($responseTimes);
            
            // Get slowest requests
            arsort($responseTimes);
            $summary['slowest_requests'] = array_slice($responseTimes, 0, 5, true);
        }
        
        $summary['services'] = $services;
        $summary['status_codes'] = $statusCodes;
        
        // Calculate success rate
        $successCount = 0;
        foreach ($statusCodes as $code => $count) {
            if ($code >= 200 && $code < 300) {
                $successCount += $count;
            }
        }
        
        if ($summary['total_responses'] > 0) {
            $summary['success_rate'] = round(($successCount / $summary['total_responses']) * 100, 2);
        }
        
        // Store summary
        $this->storeDetailedLog('summaries', $summary);
        
        return $summary;
    }
    
    /**
     * Sanitize headers to remove sensitive information
     */
    private function sanitizeHeaders($headers)
    {
        $sensitiveHeaders = ['authorization', 'x-api-key', 'api-key', 'token', 'secret'];
        $sanitized = [];
        
        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $sensitiveHeaders)) {
                $sanitized[$key] = '***REDACTED***';
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize body to remove sensitive information
     */
    private function sanitizeBody($body)
    {
        if (is_string($body)) {
            // Try to parse as JSON
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->sanitizeBody($decoded);
            }
            return $body;
        }
        
        if (is_array($body)) {
            $sensitiveFields = ['password', 'pin', 'cvv', 'card_number', 'account_number', 'secret', 'token'];
            
            foreach ($body as $key => $value) {
                if (in_array(strtolower($key), $sensitiveFields)) {
                    $body[$key] = '***REDACTED***';
                } elseif (is_array($value)) {
                    $body[$key] = $this->sanitizeBody($value);
                }
            }
        }
        
        return $body;
    }
    
    /**
     * Store detailed log to file
     */
    private function storeDetailedLog($type, $data)
    {
        $date = now()->format('Y-m-d');
        $path = "api-logs/{$date}/{$type}.json";
        
        // Get existing logs
        $logs = [];
        if (Storage::exists($path)) {
            $content = Storage::get($path);
            $logs = json_decode($content, true) ?? [];
        }
        
        // Append new log
        $logs[] = $data;
        
        // Store updated logs
        Storage::put($path, json_encode($logs, JSON_PRETTY_PRINT));
    }
    
    /**
     * Get log entry by request ID
     */
    private function getLogEntry($type, $date, $requestId)
    {
        $path = "api-logs/{$date}/{$type}.json";
        
        if (!Storage::exists($path)) {
            return null;
        }
        
        $content = Storage::get($path);
        $logs = json_decode($content, true) ?? [];
        
        foreach ($logs as $log) {
            if (isset($log['request_id']) && $log['request_id'] === $requestId) {
                return $log;
            }
        }
        
        return null;
    }
    
    /**
     * Get all logs by date
     */
    private function getLogsByDate($type, $date)
    {
        $path = "api-logs/{$date}/{$type}.json";
        
        if (!Storage::exists($path)) {
            return [];
        }
        
        $content = Storage::get($path);
        return json_decode($content, true) ?? [];
    }
    
    /**
     * Track performance metrics
     */
    private function trackMetrics($requestId, $statusCode, $responseTime, $responseReceived)
    {
        // Store metrics for monitoring
        $metrics = [
            'request_id' => $requestId,
            'timestamp' => now()->timestamp,
            'status_code' => $statusCode,
            'response_time_ms' => round($responseTime * 1000, 2),
            'response_received' => $responseReceived,
            'success' => $statusCode >= 200 && $statusCode < 300
        ];
        
        // You can send this to monitoring service (e.g., CloudWatch, Datadog)
        // For now, just log it
        Log::channel('metrics')->info('API Metric', $metrics);
    }
    
    /**
     * Check if error is critical
     */
    private function isCriticalError(\Exception $exception)
    {
        // Define critical error conditions
        $criticalMessages = [
            'Connection refused',
            'Service unavailable',
            'Authentication failed',
            'Rate limit exceeded'
        ];
        
        foreach ($criticalMessages as $message) {
            if (stripos($exception->getMessage(), $message) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Send alert for critical errors
     */
    private function sendAlert($errorData)
    {
        // Send to notification channel (email, Slack, etc.)
        // For now, just log as critical
        Log::critical('Critical API Error Alert', $errorData);
        
        // You can implement actual alerting here
        // Example: Mail::to('admin@example.com')->send(new ApiErrorAlert($errorData));
    }
}