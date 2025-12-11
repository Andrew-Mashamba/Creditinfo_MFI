<?php

namespace App\Http\Controllers;

use App\Commands\GenerateBillCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class BillController extends Controller
{
    public function createBill(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'service_id' => 'required|exists:services,id',
            'amount' => 'required|numeric|min:0',
            'is_recurring' => 'required|in:1,2',
            'payment_mode' => 'required|in:1,2,3,4,5',
            'due_date' => 'required|date|after:today',
            'is_mandatory' => 'boolean'
        ]);

        try {
            // Create the command
            $command = new GenerateBillCommand(
                member_id: $validated['member_id'],
                service_id: $validated['service_id'],
                amount: $validated['amount'],
                is_recurring: $validated['is_recurring'],
                payment_mode: $validated['payment_mode'],
                due_date: $validated['due_date'],
                is_mandatory: $validated['is_mandatory'] ?? false
            );

            // Dispatch the command
            $bill = Bus::dispatch($command);

            return response()->json([
                'status' => 'success',
                'message' => 'Bill created successfully',
                'data' => $bill
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create bill: ' . $e->getMessage()
            ], 500);
        }
    }
} 