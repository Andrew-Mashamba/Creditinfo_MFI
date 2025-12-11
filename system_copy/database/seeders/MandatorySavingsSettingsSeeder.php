<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MandatorySavingsSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('mandatory_savings_settings')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'institution_id' => '1',
                'mandatory_savings_account' => '10000001', // Sample account number
                'monthly_amount' => 50000.00, // 50,000 monthly savings
                'due_day' => 5, // Due on 5th of each month
                'grace_period_days' => 5,
                'enable_notifications' => true,
                'first_reminder_days' => 7, // 7 days before due date
                'second_reminder_days' => 3, // 3 days before due date
                'final_reminder_days' => 1, // 1 day before due date
                'enable_sms_notifications' => true,
                'enable_email_notifications' => true,
                'sms_template' => 'Dear {name}, your mandatory savings of {amount} is due on {due_date}. Account: {account_number}',
                'email_template' => 'Dear {name},\n\nThis is a reminder that your mandatory savings payment of {amount} is due on {due_date}.\n\nAccount Number: {account_number}\n\nThank you.',
                'additional_settings' => json_encode(['auto_deduct' => false, 'penalty_rate' => 2.5]),
                'created_at' => '2025-07-23 10:38:39',
                'updated_at' => '2025-07-23 10:38:39',
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('mandatory_savings_settings')->insert($row);
    }
}
}