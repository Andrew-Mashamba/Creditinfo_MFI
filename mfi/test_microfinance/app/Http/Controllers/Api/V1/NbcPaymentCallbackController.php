<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NbcPaymentCallbackController extends Controller
{
    /**
     * Handle NBC payment callback notifications
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        $requestId = uniqid('nbc_callback_');
        $startTime = microtime(true);

        Log::info('Received NBC payment callback', [
            'request_id' => $requestId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            // Validate required fields
            $requiredFields = [
                'serviceName',
                'clientId',
                'clientRef',
                'engineRef',
                'hostRef',
                'status',
                'remarks',
                'completedAt'
            ];

            Log::debug('Starting field validation', [
                'request_id' => $requestId,
                'required_fields' => $requiredFields
            ]);

            foreach ($requiredFields as $field) {
                if (!$request->has($field)) {
                    Log::error('Missing required field in callback', [
                        'request_id' => $requestId,
                        'field' => $field,
                        'received_fields' => array_keys($request->all())
                    ]);
                    return response()->json([
                        'engineRef' => $request->input('engineRef'),
                        'status' => 'REJECTED',
                        'datetime' => Carbon::now()->format('Y-m-d\TH:i:s'),
                        'message' => "Missing required field: {$field}"
                    ], 400);
                }
            }

            Log::debug('Field validation completed successfully', [
                'request_id' => $requestId
            ]);

            // Validate service name
            $serviceName = $request->input('serviceName');
            Log::debug('Validating service name', [
                'request_id' => $requestId,
                'service_name' => $serviceName
            ]);

            if ($serviceName !== 'NOTIFICATION_CALLBACK') {
                Log::error('Invalid service name in callback', [
                    'request_id' => $requestId,
                    'service_name' => $serviceName,
                    'expected_service_name' => 'NOTIFICATION_CALLBACK'
                ]);
                return response()->json([
                    'engineRef' => $request->input('engineRef'),
                    'status' => 'REJECTED',
                    'datetime' => Carbon::now()->format('Y-m-d\TH:i:s'),
                    'message' => 'Invalid service name'
                ], 400);
            }

            Log::debug('Service name validation completed successfully', [
                'request_id' => $requestId
            ]);

            // TODO: Implement your business logic here
            // For example:
            // - Update transaction status in your database
            // - Send notifications to users
            // - Trigger any necessary follow-up actions

            $processingTime = round((microtime(true) - $startTime) * 1000, 2); // in milliseconds

            Log::info('Successfully processed NBC payment callback', [
                'request_id' => $requestId,
                'engine_ref' => $request->input('engineRef'),
                'client_ref' => $request->input('clientRef'),
                'host_ref' => $request->input('hostRef'),
                'status' => $request->input('status'),
                'remarks' => $request->input('remarks'),
                'completed_at' => $request->input('completedAt'),
                'processing_time_ms' => $processingTime
            ]);

            return response()->json([
                'engineRef' => $request->input('engineRef'),
                'status' => 'RECEIVED',
                'datetime' => Carbon::now()->format('Y-m-d\TH:i:s'),
                'message' => 'Notification has been acknowledged successfully'
            ]);

        } catch (\Exception $e) {
            $processingTime = round((microtime(true) - $startTime) * 1000, 2); // in milliseconds

            Log::error('Error processing NBC payment callback', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'processing_time_ms' => $processingTime,
                'payload' => $request->all()
            ]);

            return response()->json([
                'engineRef' => $request->input('engineRef'),
                'status' => 'REJECTED',
                'datetime' => Carbon::now()->format('Y-m-d\TH:i:s'),
                'message' => 'Error processing callback: ' . $e->getMessage()
            ], 500);
        }
    }
}
