<?php

  namespace App\Http\Services;

  use Illuminate\Support\Facades\Http;

 class BankTransactionService {

    public function sendTransactionData(string $transactionType, array $data): array
    {
        // API endpoint base URL - use environment variable
        $baseUrl = env('NBC_INTERNAL_FUND_TRANSFER_BASE_URL', 'http://cbpuat.intra.nbc.co.tz:6666/api/nbc-sg/internal_ft');

        // Determine the transaction type and set additional data or endpoint
        switch ($transactionType) {
            case 'IFT': // Intra-Financial Transaction
                $url = $baseUrl . '/ift-transaction';
                $data['transaction_type'] = 'IFT';
                break;

            case 'EFT': // Electronic Funds Transfer
                $url = $baseUrl . '/eft-transaction';
                $data['transaction_type'] = 'EFT';
                break;

            case 'MOBILE': // Mobile Network Transaction
                $url = $baseUrl . '/mobile-transaction';
                $data['mobile_network'] = 'MTN'; // Example: MTN, Vodafone, etc.
                $data['transaction_type'] = 'MOBILE';
                break;

            default:
                return [
                    'status' => 'error',
                    'message' => 'Invalid transaction type.',
                ];
        }

        // Make the POST request
        $response = Http::post($url, $data);

        // Handle the response
        if ($response->successful()) {
            return [
                'status' => 'success',
                'message' => 'Transaction data sent successfully.',
                'data' => $response->json(),
            ];
        } elseif ($response->failed()) {
            return [
                'status' => 'error',
                'message' => 'Failed to send transaction data.',
                'data' => $response->json(),
            ];
        } elseif ($response->clientError()) {
            return [
                'status' => 'error',
                'message' => 'Client error occurred.',
                'data' => $response->json(),
            ];
        } elseif ($response->serverError()) {
            return [
                'status' => 'error',
                'message' => 'Server error occurred.',
                'data' => $response->json(),
            ];
        }
    }
}
