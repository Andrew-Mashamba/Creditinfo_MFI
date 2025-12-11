<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LukuGatewayService;

class LukuCallbackController extends Controller
{
    protected $lukuGatewayService;
    protected $channelId;
    protected $channelName;

    public function __construct(LukuGatewayService $lukuGatewayService)
    {
        $this->lukuGatewayService = $lukuGatewayService;
        $this->channelId = config('services.luku_gateway.channel_id');
        $this->channelName = config('services.luku_gateway.channel_name');
    }

    public function handleCallback(Request $request)
    {
        Log::channel('luku')->info('Luku Callback: Received callback request', [
            'content' => $request->getContent()
        ]);

        $xmlContent = $request->getContent();

        try {
            $data = simplexml_load_string($xmlContent, "SimpleXMLElement", LIBXML_NOCDATA);
            $dataArray = json_decode(json_encode($data), true);

            Log::channel('luku')->info('Luku Callback: Parsed XML data', [
                'data' => $dataArray
            ]);

            if (!isset($dataArray['sgGepgVendResp'])) {
                throw new \Exception('Invalid callback structure: Missing sgGepgVendResp');
            }

            $respHdr = $dataArray['sgGepgVendResp']['RespHdr'] ?? [];
            $respInf = $dataArray['sgGepgVendResp']['RespInf'] ?? [];

            $reference = $respHdr['ChannelRef'] ?? null;
            $statusCode = $respHdr['StsCode'] ?? null;
            $statusDesc = $respHdr['StsDesc'] ?? 'Unknown status';

            if (!$reference) {
                Log::error('Luku Callback: Missing ChannelRef in callback data');
                return response('Missing ChannelRef', 400);
            }

            // Process successful payment
            if ($statusCode === '7101') {
                $transactionData = [
                    'status' => 'success',
                    'status_message' => $statusDesc,
                    'third_party_reference' => $respInf['GepgRcptNum'] ?? null,
                    'token' => $respInf['Token'] ?? null,
                    'units' => $respInf['Units'] ?? null,
                    'currency' => $respInf['Currency'] ?? 'TZS',
                    'cost' => $respInf['Cost'] ?? null,
                    'paid_amount' => $respInf['PaidAmount'] ?? null,
                    'message' => $respInf['Message'] ?? null,
                    'charges' => $respInf['Charges'] ?? [],
                    'updated_at' => now(),
                ];

                Log::info('Luku Callback: Processing successful payment', [
                    'reference' => $reference,
                    'transaction_data' => $transactionData
                ]);

                DB::table('transactions')->where('reference_number', $reference)->update($transactionData);
            } else {
                // Process failed payment
                $errorData = [
                    'status' => 'failed',
                    'status_message' => $statusDesc,
                    'error' => $statusCode,
                    'error_message' => $statusDesc,
                    'updated_at' => now(),
                ];

                Log::info('Luku Callback: Processing failed payment', [
                    'reference' => $reference,
                    'error_data' => $errorData
                ]);

                DB::table('transactions')->where('reference_number', $reference)->update($errorData);
            }

            Log::info('Luku Callback: Successfully processed callback', [
                'reference' => $reference,
                'status' => $statusCode
            ]);

            // Send acknowledgment response
            $ackResponse = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<GepgGateway>
  <GepgGatewayFinRespACK>
    <GepgGatewayHdr>
      <ChannelID>{$this->channelId}</ChannelID>
      <ChannelName>{$this->channelName}</ChannelName>
      <Service>LUKU_PAY</Service>
    </GepgGatewayHdr>
    <pmtSubResAck>
      <CbpGwRef>{$reference}</CbpGwRef>
      <AckId>{$this->generateAckId()}</AckId>
      <PayStsCode>7101</PayStsCode>
    </pmtSubResAck>
  </GepgGatewayFinRespACK>
</GepgGateway>
XML;

            return response($ackResponse, 200)
                    ->header('Content-Type', 'application/xml');

        } catch (\Exception $e) {
            Log::channel('luku')->error('Luku Callback: Error processing callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'xmlContent' => $xmlContent
            ]);
            return response('Callback Error', 500);
        }
    }

    /**
     * Generate a unique acknowledgment ID
     *
     * @return string
     */
    protected function generateAckId(): string
    {
        return 'ACK_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
}
