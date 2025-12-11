<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FundsTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;
    protected $apiUrl;

    /**
     * Create a new job instance.
     *
     * @param array $payload
     * @param string $apiUrl
     */
    public function __construct(array $payload, string $apiUrl)
    {
        // Validate and sanitize input before assigning
        $validator = Validator::make($payload, [
            'amount' => 'required|numeric|min:0',
            'account_number' => 'required|string|max:255',
            'beneficiary_name' => 'required|string|max:255',
            'currency' => 'required|string|size:3', // Ensure its a 3-letter currency code
            // Add more fields and validation rules as necessary
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid payload for funds transfer: ' . json_encode($validator->errors()));
        }

        // Assign the validated data
        $this->payload = $payload;
        $this->apiUrl = $apiUrl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client(); // GuzzleHttp client

        try {
            // Perform the API request
            $response = $client->post($this->apiUrl, [
                'json' => $this->payload,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    // Add authorization headers if required, e.g. Bearer token
                    'Authorization' => 'Bearer ' . config('services.api_token'),
                ],
                'timeout' => 30, // Set a reasonable timeout to prevent the request from hanging
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            // Log the successful transfer
            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info('Funds transfer successful', [
                    'url' => $this->apiUrl,
                    'payload' => $this->payload,
                    'response_status' => $statusCode,
                    'response_body' => $responseBody,
                ]);
            } else {
                Log::warning('Funds transfer completed with issues', [
                    'url' => $this->apiUrl,
                    'payload' => $this->payload,
                    'response_status' => $statusCode,
                    'response_body' => $responseBody,
                    'reason' => 'Non-2xx status code received',
                ]);
            }
        } catch (ConnectException $e) {
            // Network-related error (e.g., DNS failure, timeout)
            Log::error('Funds transfer failed due to network issues', [
                'url' => $this->apiUrl,
                'payload' => $this->payload,
                'error_message' => $e->getMessage(),
                'error_type' => 'Network error',
            ]);
        } catch (ClientException $e) {
            // Handle 4xx errors (Client errors)
            Log::error('Funds transfer failed due to client error', [
                'url' => $this->apiUrl,
                'payload' => $this->payload,
                'response_status' => $e->getResponse()->getStatusCode(),
                'response_body' => $e->getResponse()->getBody()->getContents(),
                'error_message' => $e->getMessage(),
                'error_type' => 'Client error (4xx)',
            ]);
        } catch (ServerException $e) {
            // Handle 5xx errors (Server errors)
            Log::error('Funds transfer failed due to server error', [
                'url' => $this->apiUrl,
                'payload' => $this->payload,
                'response_status' => $e->getResponse()->getStatusCode(),
                'response_body' => $e->getResponse()->getBody()->getContents(),
                'error_message' => $e->getMessage(),
                'error_type' => 'Server error (5xx)',
            ]);
        } catch (RequestException $e) {
            // General Guzzle request errors
            if ($e->hasResponse()) {
                Log::error('Funds transfer failed due to request error', [
                    'url' => $this->apiUrl,
                    'payload' => $this->payload,
                    'response_status' => $e->getResponse()->getStatusCode(),
                    'response_body' => $e->getResponse()->getBody()->getContents(),
                    'error_message' => $e->getMessage(),
                    'error_type' => 'Request exception',
                ]);
            } else {
                Log::error('Funds transfer failed with no response from API', [
                    'url' => $this->apiUrl,
                    'payload' => $this->payload,
                    'error_message' => $e->getMessage(),
                    'error_type' => 'Request exception without response',
                ]);
            }
        } catch (\Exception $e) {
            // Catch any other unforeseen exceptions
            Log::error('Funds transfer failed due to an unexpected error', [
                'url' => $this->apiUrl,
                'payload' => $this->payload,
                'error_message' => $e->getMessage(),
                'error_type' => 'Unexpected error',
            ]);
        }
    }
}
