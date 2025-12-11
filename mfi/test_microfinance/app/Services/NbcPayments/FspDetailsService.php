<?php

namespace App\Services\NbcPayments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FspDetailsService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $clientId;

    public function __construct()
    {
        Log::debug('Initializing FspDetailsService...');

        $this->baseUrl = config('services.nbc_payments.base_url');
        $this->apiKey = config('services.nbc_payments.api_key');
        $this->clientId = config('services.nbc_payments.client_id', 'APP_IOS');

        Log::debug('Service Configurations Loaded', [
            'baseUrl' => $this->baseUrl,
            'clientId' => $this->clientId,
        ]);
    }

    public function fetchFspDetails(): array
    {
        $endpoint = '/domestix/api/v2/fsp-details';

        Log::info('Preparing FSP Details API call...');
        $clientRef = $this->generateClientRef();
        $engineRef = $this->generateEngineRef();
        $timestamp = now()->toIso8601String();

        $payload = [
            'serviceName' => 'FSP_DETAILS_CHECK',
            'clientId' => $this->clientId,
            'clientRef' => $clientRef,
            'engineRef' => $engineRef,
            'requestTimestamp' => $timestamp,
        ];

        Log::debug('Constructed payload for FSP Details', $payload);

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Api-Key' => $this->apiKey,
        ];

        Log::debug('Prepared headers for request', $headers);
        Log::info("Sending POST request to: {$this->baseUrl}{$endpoint}");

        try {
            $response = Http::withHeaders($headers)->withOptions(['verify' => false])->post($this->baseUrl . $endpoint, $payload);

            Log::info('Received response from FSP Details API', [
                'http_status' => $response->status(),
                'response_body' => $response->json(),
            ]);

            $statusCode = $response->json('statusCode');
            if ($response->successful() && $statusCode === 600) {
                Log::info("FSP Details retrieved successfully for clientRef: $clientRef");
                return $response->json('body');
            }

            Log::warning("FSP Details API returned an error for clientRef: $clientRef", [
                'statusCode' => $statusCode,
                'message' => $response->json('message'),
                'details' => $response->json('body'),
            ]);

            return [
                'error' => true,
                'message' => $response->json('message') ?? 'Unknown error from API.',
                'details' => $response->json('body') ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Exception occurred while calling FSP Details API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => true,
                'message' => 'Internal error occurred during FSP Details retrieval.',
                'details' => [],
            ];
        }
    }

    protected function generateClientRef(): string
    {
        $ref = $this->clientId . now()->format('YmdHis') . strtoupper(Str::random(4));
        Log::debug("Generated clientRef: $ref");
        return $ref;
    }

    protected function generateEngineRef(): string
    {
        $engine = 'ENG' . now()->format('YmdHis') . rand(1000, 9999);
        Log::debug("Generated engineRef: $engine");
        return $engine;
    }
}
