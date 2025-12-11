<?php

namespace App\Http\Controllers;

use App\Models\GepgTransaction;
use App\Services\NbcPayments\GepgLoggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GepgCallbackController extends Controller
{
    protected $logger;

    public function __construct(GepgLoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function handleCallback(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            $xml = $request->getContent();
            $data = simplexml_load_string($xml);
            $json = json_encode($data);
            $response = json_decode($json, true);

            $this->logger->logCallback('GEPG_CALLBACK', [
                'raw_xml' => $xml,
                'parsed_data' => $response
            ]);

            // Find the transaction by reference
            $transaction = GepgTransaction::where('control_number', $response['CustCtrNum'] ?? '')
                ->latest()
                ->first();

            if ($transaction) {
                $transaction->update([
                    'response_code' => $response['PayStsCode'] ?? $response['QtStsCode'] ?? '7201',
                    'response_description' => $response['PayStsDesc'] ?? $response['QtStsDesc'] ?? 'Callback received',
                    'payload' => $response,
                ]);

                $this->logger->logTransaction($transaction->id, [
                    'transaction' => $transaction->toArray(),
                    'callback_data' => $response
                ]);
            } else {
                $this->logger->logError('GEPG_CALLBACK', new \Exception('Transaction not found'), [
                    'control_number' => $response['CustCtrNum'] ?? '',
                    'callback_data' => $response
                ]);
            }

            $acknowledgment = [
                'GepgGatewayFinRespACK' => [
                    'GepgGatewayHdr' => [
                        'ChannelID' => config('gepg.channel_id'),
                        'ChannelName' => config('gepg.channel_name'),
                        'Service' => 'GEPG_PAY',
                    ],
                    'pmtSubResAck' => [
                        'CbpGwRef' => $response['CbpGwRef'] ?? '',
                        'AckId' => uniqid('ack_'),
                        'PayStsCode' => '7101',
                    ]
                ]
            ];

            $duration = (microtime(true) - $startTime) * 1000;
            $this->logger->logResponse('GEPG_CALLBACK', $acknowledgment, $duration);

            return response()->xml($acknowledgment);
        } catch (\Exception $e) {
            $this->logger->logError('GEPG_CALLBACK', $e, [
                'request_data' => $request->all(),
                'raw_content' => $request->getContent()
            ]);

            return response()->xml([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}