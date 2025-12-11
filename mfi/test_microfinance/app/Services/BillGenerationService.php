<?php

namespace App\Services;

use App\Commands\GenerateBillCommand;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class BillGenerationService
{
    /**
     * Generate a bill for a member
     *
     * @param array $data
     * @return \App\Models\Bill
     * @throws \Exception
     */
    public function generateBill(array $data)
    {
        try {
            $command = new GenerateBillCommand(
                member_id: $data['member_id'],
                service_id: $data['service_id'],
                amount: $data['amount'],
                is_recurring: $data['is_recurring'],
                payment_mode: $data['payment_mode'],
                due_date: $data['due_date'],
                is_mandatory: $data['is_mandatory'] ?? false
            );

            $bill = Bus::dispatch($command);

            Log::info('Bill generated successfully', [
                'bill_id' => $bill->id,
                'member_id' => $data['member_id'],
                'service_id' => $data['service_id']
            ]);

            return $bill;

        } catch (\Exception $e) {
            Log::error('Failed to generate bill', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            throw $e;
        }
    }

    /**
     * Generate bills for multiple members
     *
     * @param array $members
     * @param array $serviceData
     * @return array
     */
    public function generateBulkBills(array $members, array $serviceData)
    {
        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($members as $member) {
            try {
                $bill = $this->generateBill([
                    'member_id' => $member['id'],
                    'service_id' => $serviceData['service_id'],
                    'amount' => $serviceData['amount'],
                    'is_recurring' => $serviceData['is_recurring'],
                    'payment_mode' => $serviceData['payment_mode'],
                    'due_date' => $serviceData['due_date'],
                    'is_mandatory' => $serviceData['is_mandatory'] ?? false
                ]);

                $results['success'][] = [
                    'member_id' => $member['id'],
                    'bill_id' => $bill->id
                ];

            } catch (\Exception $e) {
                $results['failed'][] = [
                    'member_id' => $member['id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
} 