<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('bills')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'service_id' => 5,
                'control_number' => '100011000252',
                'amount_due' => 50000.00,
                'amount_paid' => 0.00,
                'is_mandatory' => false,
                'is_recurring' => false,
                'due_date' => '2025-08-01',
                'member_id' => 10002,
                'created_by' => 1,
                'client_number' => 10002,
                'payment_mode' => 2,
                'status' => 'PENDING',
                'credit_account_number' => '011000230018',
                'debit_account_number' => '0101100010001010',
                'created_at' => '2025-07-18 04:00:30',
                'updated_at' => '2025-07-18 04:00:30',
            ],
            [
                'id' => 2,
                'service_id' => 1,
                'control_number' => '100011000212',
                'amount_due' => 100000.00,
                'amount_paid' => 0.00,
                'is_mandatory' => false,
                'is_recurring' => false,
                'due_date' => '2025-08-01',
                'member_id' => 10002,
                'created_by' => 1,
                'client_number' => 10002,
                'payment_mode' => 2,
                'status' => 'PENDING',
                'credit_account_number' => '0101300030003003',
                'debit_account_number' => '0101100010001010',
                'created_at' => '2025-07-18 04:00:30',
                'updated_at' => '2025-07-18 04:00:30',
            ],
            [
                'id' => 3,
                'service_id' => 4,
                'control_number' => '100011000242',
                'amount_due' => 1000.00,
                'amount_paid' => 0.00,
                'is_mandatory' => false,
                'is_recurring' => false,
                'due_date' => '2025-08-01',
                'member_id' => 10002,
                'created_by' => 1,
                'client_number' => 10002,
                'payment_mode' => 2,
                'status' => 'PENDING',
                'credit_account_number' => null,
                'debit_account_number' => null,
                'created_at' => '2025-07-18 04:10:46',
                'updated_at' => '2025-07-18 04:10:46',
            ],
            [
                'id' => 4,
                'service_id' => 1,
                'control_number' => '100011000212',
                'amount_due' => 100000.00,
                'amount_paid' => 0.00,
                'is_mandatory' => false,
                'is_recurring' => false,
                'due_date' => '2025-08-01',
                'member_id' => 10002,
                'created_by' => 1,
                'client_number' => 10002,
                'payment_mode' => 2,
                'status' => 'PENDING',
                'credit_account_number' => '0101300030003003',
                'debit_account_number' => '0101100010001010',
                'created_at' => '2025-07-18 04:10:46',
                'updated_at' => '2025-07-18 04:10:46',
            ],
            [
                'id' => 5,
                'service_id' => 11,
                'control_number' => '1000110002122',
                'amount_due' => 10.00,
                'amount_paid' => 0.00,
                'is_mandatory' => false,
                'is_recurring' => false,
                'due_date' => '2025-08-01',
                'member_id' => 10002,
                'created_by' => 1,
                'client_number' => 10002,
                'payment_mode' => 2,
                'status' => 'PENDING',
                'credit_account_number' => '0101200021002120',
                'debit_account_number' => '0101100010001010',
                'created_at' => '2025-07-18 04:10:46',
                'updated_at' => '2025-07-18 04:10:46',
            ],
            [
                'id' => 6,
                'service_id' => 5,
                'control_number' => '100011000352',
                'amount_due' => 50000.00,
                'amount_paid' => 0.00,
                'is_mandatory' => false,
                'is_recurring' => false,
                'due_date' => '2025-08-01',
                'member_id' => 10003,
                'created_by' => 1,
                'client_number' => 10003,
                'payment_mode' => 2,
                'status' => 'PENDING',
                'credit_account_number' => '011000330017',
                'debit_account_number' => '0101100010001010',
                'created_at' => '2025-07-18 06:47:37',
                'updated_at' => '2025-07-18 06:47:37',
            ],
            [
                'id' => 7,
                'service_id' => 1,
                'control_number' => '100011000312',
                'amount_due' => 100000.00,
                'amount_paid' => 0.00,
                'is_mandatory' => false,
                'is_recurring' => false,
                'due_date' => '2025-08-01',
                'member_id' => 10003,
                'created_by' => 1,
                'client_number' => 10003,
                'payment_mode' => 2,
                'status' => 'PENDING',
                'credit_account_number' => '0101300030003003',
                'debit_account_number' => '0101100010001010',
                'created_at' => '2025-07-18 06:47:37',
                'updated_at' => '2025-07-18 06:47:37',
            ],
            [
                'id' => 8,
                'service_id' => 4,
                'control_number' => '100011000342',
                'amount_due' => 1000.00,
                'amount_paid' => 0.00,
                'is_mandatory' => false,
                'is_recurring' => false,
                'due_date' => '2025-08-01',
                'member_id' => 10003,
                'created_by' => 1,
                'client_number' => 10003,
                'payment_mode' => 2,
                'status' => 'PENDING',
                'credit_account_number' => null,
                'debit_account_number' => null,
                'created_at' => '2025-07-18 06:47:50',
                'updated_at' => '2025-07-18 06:47:50',
            ],
            [
                'id' => 9,
                'service_id' => 1,
                'control_number' => '100011000312',
                'amount_due' => 100000.00,
                'amount_paid' => 0.00,
                'is_mandatory' => false,
                'is_recurring' => false,
                'due_date' => '2025-08-01',
                'member_id' => 10003,
                'created_by' => 1,
                'client_number' => 10003,
                'payment_mode' => 2,
                'status' => 'PENDING',
                'credit_account_number' => '0101300030003003',
                'debit_account_number' => '0101100010001010',
                'created_at' => '2025-07-18 06:47:50',
                'updated_at' => '2025-07-18 06:47:50',
            ],
            [
                'id' => 10,
                'service_id' => 11,
                'control_number' => '1000110003122',
                'amount_due' => 10.00,
                'amount_paid' => 0.00,
                'is_mandatory' => false,
                'is_recurring' => false,
                'due_date' => '2025-08-01',
                'member_id' => 10003,
                'created_by' => 1,
                'client_number' => 10003,
                'payment_mode' => 2,
                'status' => 'PENDING',
                'credit_account_number' => '0101200021002120',
                'debit_account_number' => '0101100010001010',
                'created_at' => '2025-07-18 06:47:50',
                'updated_at' => '2025-07-18 06:47:50',
            ],
        ];

        foreach ($data as $row) {
            // Check if service exists
            $serviceExists = DB::table('services')->where('id', $row['service_id'])->exists();
            if (!$serviceExists) {
                // Use first available service or skip
                $firstService = DB::table('services')->first();
                if ($firstService) {
                    $row['service_id'] = $firstService->id;
                } else {
                    if ($this->command) $this->command->warn("Skipping bill - no services found");
                    continue;
                }
            }
            
            // Check if client exists
            $clientExists = DB::table('clients')->where('client_number', $row['client_number'])->exists();
            if (!$clientExists) {
                $firstClient = DB::table('clients')->first();
                if ($firstClient) {
                    $row['client_number'] = $firstClient->client_number;
                    $row['member_id'] = $firstClient->client_number;
                } else {
                    if ($this->command) $this->command->warn("Skipping bill - no clients found");
                    continue;
                }
            }
            
            DB::table('bills')->insert($row);
        }
    }
}