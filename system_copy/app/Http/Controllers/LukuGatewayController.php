<?php

namespace App\Http\Controllers;

use App\Services\LukuGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LukuGatewayController extends Controller
{
    protected $lukuGatewayService;

    public function __construct(LukuGatewayService $lukuGatewayService)
    {
        $this->lukuGatewayService = $lukuGatewayService;
    }

    /**
     * Handle meter lookup request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function meterLookup(Request $request)
    {
        try {
            $validated = $request->validate([
                'meter_number' => 'required|string',
                'debit_account_no' => 'required|string',
                'channel_ref' => 'required|string'
            ]);

            $response = $this->lukuGatewayService->meterLookup(
                $validated['meter_number'],
                $validated['debit_account_no'],
                $validated['channel_ref']
            );

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Meter Lookup Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process meter lookup request'
            ], 500);
        }
    }

    /**
     * Handle payment request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'channel_ref' => 'required|string',
                'cbp_gw_ref' => 'required|string',
                'result_url' => 'required|url',
                'channel_trx_id' => 'required|string',
                'meter_number' => 'required|string',
                'debit_account_no' => 'required|string',
                'amount' => 'required|numeric',
                'credit_account_no' => 'required|string',
                'transaction_datetime' => 'required|date',
                'payment_channel' => 'required|string',
                'third_party' => 'required|string',
                'customer_msisdn' => 'required|string',
                'customer_name' => 'required|string',
                'customer_tin' => 'nullable|string',
                'customer_nin' => 'nullable|string',
                'customer_email' => 'nullable|email'
            ]);

            $response = $this->lukuGatewayService->processPayment($validated);

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Payment Processing Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment request'
            ], 500);
        }
    }

    /**
     * Handle token status check request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTokenStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
                'channel_ref' => 'required|string'
            ]);

            $response = $this->lukuGatewayService->checkTokenStatus(
                $validated['token'],
                $validated['channel_ref']
            );

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Token Status Check Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check token status'
            ], 500);
        }
    }

    /**
     * Handle payment callback
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentCallback(Request $request)
    {
        try {
            $xmlData = $request->getContent();
            $data = $this->lukuGatewayService->xmlToArray($xmlData);

            // Process the callback data
            // TODO: Implement your callback processing logic here

            return response()->json([
                'status' => 'success',
                'message' => 'Callback received successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Payment Callback Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process callback'
            ], 500);
        }
    }
} 