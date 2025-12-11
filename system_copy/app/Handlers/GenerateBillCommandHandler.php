<?php

namespace App\Handlers;

use App\Commands\GenerateBillCommand;
use App\Models\Bill;
use App\Models\ClientsModel;
use App\Models\Service;
use App\Events\BillCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateBillCommandHandler
{
    public function handle(GenerateBillCommand $command)
    {
        try {
            DB::beginTransaction();

            $client = ClientsModel::where('client_number', $command->client_number)->firstOrFail();
            $service = Service::findOrFail($command->service_id);

            // Generate control number
            $controlNumber = '1' .
                str_pad('0001', 4, '0', STR_PAD_LEFT) . // Default NBC code
                str_pad($client->client_number, 5, '0', STR_PAD_LEFT) .
                $service->code .
                $command->is_recurring .
                $command->payment_mode;

            // Create bill
            $bill = Bill::create([
                'client_number' => $client->client_number,
                'service_id' => $service->id,
                'amount_due' => $command->amount,
                'amount_paid' => 0,
                'control_number' => $controlNumber,
                'is_mandatory' => $command->is_mandatory,
                'is_recurring' => $command->is_recurring,
                'payment_mode' => $command->payment_mode,
                'due_date' => $command->due_date,
                'status' => 'PENDING',
                'created_by' => auth()->id()
            ]);

            // Dispatch event
            event(new BillCreated($bill));

            DB::commit();
            return $bill;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bill generation failed: ' . $e->getMessage());
            throw $e;
        }
    }
} 