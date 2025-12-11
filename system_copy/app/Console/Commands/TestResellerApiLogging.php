<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResellerApiLogger;

class TestResellerApiLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reseller-api:test-logging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Reseller API logging system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing Reseller API Logging System...');
        
        $logger = new ResellerApiLogger();
        
        // Test request logging
        $this->info('Testing request logging...');
        $requestId = $logger->logRequest('POST', 'https://api.example.com/test', [
            'action' => 'testDomain',
            'domainName' => 'example.tz',
            'api_key' => 'test_example_key'
        ], [
            'X-API-KEY' => 'test_example_key',
            'Content-Type' => 'application/json'
        ]);
        
        $this->line("Request ID: {$requestId}");
        
        // Test response logging
        $this->info('Testing response logging...');
        $logger->logResponse($requestId, 200, [
            'status' => 'success',
            'data' => [
                'available' => true,
                'domain' => 'example.tz',
                'price' => 50000
            ]
        ], 150.5);
        
        // Test error logging
        $this->info('Testing error logging...');
        $logger->logError($requestId, 'Test error message', [
            'error_code' => 'TEST_ERROR',
            'additional_info' => 'This is a test error'
        ]);
        
        // Test domain operation logging
        $this->info('Testing domain operation logging...');
        $logger->logDomainOperation('test_operation', 'example.tz', [
            'test_param' => 'test_value'
        ], [
            'success' => true,
            'result' => 'Test completed successfully'
        ]);
        
        // Test rate limit logging
        $this->info('Testing rate limit logging...');
        $logger->logRateLimit($requestId, 100, 95, time() + 3600);
        
        $this->info('Logging test completed!');
        $this->line('');
        $this->info('You can now run: php artisan reseller-api:logs view --limit=10');
        $this->info('Or: php artisan reseller-api:logs stats');
        
        return 0;
    }
}