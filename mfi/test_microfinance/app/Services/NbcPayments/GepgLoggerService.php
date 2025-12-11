<?php

namespace App\Services\NbcPayments;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GepgLoggerService
{
    protected $logChannel = 'gepg';
    protected $logPath = 'logs/gepg';

    public function __construct()
    {
        // Create custom log channel for GEPG
        config(['logging.channels.gepg' => [
            'driver' => 'daily',
            'path' => storage_path($this->logPath . '/gepg.log'),
            'level' => 'debug',
            'days' => 30,
        ]]);
    }

    /**
     * Log GEPG request
     */
    public function logRequest(string $service, array $payload, array $headers = [])
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'service' => $service,
            'payload' => $payload,
            'headers' => $headers,
        ];

        Log::channel($this->logChannel)->info('GEPG Request', $logData);
        $this->saveToFile('requests', $logData);
    }

    /**
     * Log GEPG response
     */
    public function logResponse(string $service, array $response, float $duration = null)
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'service' => $service,
            'response' => $response,
            'duration_ms' => $duration,
        ];

        Log::channel($this->logChannel)->info('GEPG Response', $logData);
        $this->saveToFile('responses', $logData);
    }

    /**
     * Log GEPG error
     */
    public function logError(string $service, \Throwable $error, array $context = [])
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'service' => $service,
            'error' => [
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'trace' => $error->getTraceAsString(),
            ],
            'context' => $context,
        ];

        Log::channel($this->logChannel)->error('GEPG Error', $logData);
        $this->saveToFile('errors', $logData);
    }

    /**
     * Log GEPG transaction
     */
    public function logTransaction(string $transactionId, array $data)
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'transaction_id' => $transactionId,
            'data' => $data,
        ];

        Log::channel($this->logChannel)->info('GEPG Transaction', $logData);
        $this->saveToFile('transactions', $logData);
    }

    /**
     * Log GEPG callback
     */
    public function logCallback(string $transactionId, array $callbackData)
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'transaction_id' => $transactionId,
            'callback_data' => $callbackData,
        ];

        Log::channel($this->logChannel)->info('GEPG Callback', $logData);
        $this->saveToFile('callbacks', $logData);
    }

    /**
     * Save log to file
     */
    protected function saveToFile(string $type, array $data)
    {
        $date = now()->format('Y-m-d');
        $path = "{$this->logPath}/{$type}/{$date}.log";
        
        Storage::append($path, json_encode($data, JSON_PRETTY_PRINT));
    }
} 