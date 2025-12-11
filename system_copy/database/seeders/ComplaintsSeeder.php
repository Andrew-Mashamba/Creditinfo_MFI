<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplaintsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('complaints')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'client_id' => 1,
                'category_id' => 5,
                'status_id' => 1,
                'title' => 'Account Access Issues',
                'description' => 'Unable to access my savings account online. Getting error message when trying to log in.',
                'resolution_notes' => null,
                'resolved_at' => null,
                'assigned_to' => null,
                'resolved_by' => null,
                'priority' => 2,
                'reference_number' => null,
                'attachments' => null,
                'created_at' => '2025-07-12 16:25:52',
                'updated_at' => '2025-07-12 16:25:52',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'client_id' => 2,
                'category_id' => 3,
                'status_id' => 1,
                'title' => 'Loan Application Delayed',
                'description' => 'My loan application has been pending for over 2 weeks. Need urgent response.',
                'resolution_notes' => null,
                'resolved_at' => null,
                'assigned_to' => null,
                'resolved_by' => null,
                'priority' => 3,
                'reference_number' => null,
                'attachments' => null,
                'created_at' => '2025-07-07 16:25:52',
                'updated_at' => '2025-07-09 16:25:52',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'client_id' => 2,
                'category_id' => 6,
                'status_id' => 3,
                'title' => 'Unexpected Charges',
                'description' => 'I was charged maintenance fees that were not explained to me when I opened the account.',
                'resolution_notes' => null,
                'resolved_at' => '2025-07-04 16:25:52',
                'assigned_to' => null,
                'resolved_by' => null,
                'priority' => 2,
                'reference_number' => null,
                'attachments' => null,
                'created_at' => '2025-07-02 16:25:52',
                'updated_at' => '2025-07-05 16:25:52',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'client_id' => 1,
                'category_id' => 6,
                'status_id' => 4,
                'title' => 'Poor Customer Service',
                'description' => 'The staff at the branch was rude and unhelpful when I asked for assistance.',
                'resolution_notes' => null,
                'resolved_at' => null,
                'assigned_to' => null,
                'resolved_by' => null,
                'priority' => 1,
                'reference_number' => null,
                'attachments' => null,
                'created_at' => '2025-07-14 16:25:52',
                'updated_at' => '2025-07-14 16:25:52',
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'client_id' => 2,
                'category_id' => 4,
                'status_id' => 5,
                'title' => 'System Downtime',
                'description' => 'Online banking system was down for 3 hours yesterday. This caused inconvenience.',
                'resolution_notes' => null,
                'resolved_at' => null,
                'assigned_to' => null,
                'resolved_by' => null,
                'priority' => 2,
                'reference_number' => null,
                'attachments' => null,
                'created_at' => '2025-07-10 16:25:52',
                'updated_at' => '2025-07-11 16:25:52',
                'deleted_at' => null,
            ],
            [
                'id' => 6,
                'client_id' => 1,
                'category_id' => 6,
                'status_id' => 1,
                'title' => 'Security Concern',
                'description' => 'Received suspicious SMS about my account. Worried about potential fraud.',
                'resolution_notes' => null,
                'resolved_at' => null,
                'assigned_to' => null,
                'resolved_by' => null,
                'priority' => 4,
                'reference_number' => null,
                'attachments' => null,
                'created_at' => '2025-07-15 16:25:52',
                'updated_at' => '2025-07-16 16:25:52',
                'deleted_at' => null,
            ],
            [
                'id' => 7,
                'client_id' => 1,
                'category_id' => 7,
                'status_id' => 2,
                'title' => 'Withdrawal Issues',
                'description' => 'Unable to withdraw money from ATM. Card is working but transaction fails.',
                'resolution_notes' => null,
                'resolved_at' => null,
                'assigned_to' => null,
                'resolved_by' => null,
                'priority' => 3,
                'reference_number' => null,
                'attachments' => null,
                'created_at' => '2025-07-05 16:25:52',
                'updated_at' => '2025-07-07 16:25:52',
                'deleted_at' => null,
            ],
            [
                'id' => 8,
                'client_id' => 1,
                'category_id' => 4,
                'status_id' => 3,
                'title' => 'Statement Errors',
                'description' => 'My monthly statement shows incorrect balance. Need correction.',
                'resolution_notes' => null,
                'resolved_at' => '2025-06-28 16:25:52',
                'assigned_to' => null,
                'resolved_by' => null,
                'priority' => 2,
                'reference_number' => null,
                'attachments' => null,
                'created_at' => '2025-06-27 16:25:52',
                'updated_at' => '2025-06-29 16:25:52',
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('complaints')->insert($row);
    }
}
}