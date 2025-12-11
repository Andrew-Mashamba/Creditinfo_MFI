<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get some users
        $users = DB::table('users')->limit(5)->get();
        
        if ($users->count() < 2) {
            $this->command->info('Not enough users to seed emails. Skipping...');
            return;
        }

        $emails = [
            [
                'sender_id' => $users[0]->id,
                'recipient_id' => $users[1]->id,
                'recipient_email' => $users[1]->email,
                'subject' => 'Welcome to our Email System',
                'body' => 'Hello! Welcome to our new email system. This is a test email to demonstrate the functionality of our Gmail MVP implementation. Feel free to explore all the features including compose, reply, forward, and folder management.',
                'folder' => 'inbox',
                'is_read' => false,
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'sender_id' => $users[1]->id,
                'recipient_id' => $users[0]->id,
                'recipient_email' => $users[0]->email,
                'subject' => 'Meeting Tomorrow at 2 PM',
                'body' => 'Hi, Just a reminder about our meeting tomorrow at 2 PM. We will be discussing the new project requirements and timeline. Please prepare your updates. Thanks!',
                'folder' => 'inbox',
                'is_read' => true,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'sender_id' => $users[0]->id,
                'recipient_id' => $users[2]->id ?? $users[1]->id,
                'recipient_email' => $users[2]->email ?? $users[1]->email,
                'subject' => 'Project Update - Phase 1 Complete',
                'body' => 'Good news! We have successfully completed Phase 1 of the project. All deliverables have been submitted and approved by the client. Moving on to Phase 2 next week.',
                'folder' => 'sent',
                'is_read' => true,
                'is_sent' => true,
                'sent_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'sender_id' => $users[1]->id,
                'recipient_id' => $users[0]->id,
                'recipient_email' => $users[0]->email,
                'subject' => 'Draft: Quarterly Report',
                'body' => 'This is a draft of the quarterly report. Need to add financial figures and team performance metrics before sending to management.',
                'folder' => 'drafts',
                'is_read' => false,
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'sender_id' => null,
                'recipient_id' => $users[0]->id,
                'recipient_email' => $users[0]->email,
                'subject' => 'You have won $1,000,000!',
                'body' => 'Congratulations! You have been selected as the winner of our lottery. Click here to claim your prize. This is definitely not spam!',
                'folder' => 'spam',
                'is_read' => false,
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7),
            ],
            [
                'sender_id' => $users[2]->id ?? $users[0]->id,
                'recipient_id' => $users[1]->id,
                'recipient_email' => $users[1]->email,
                'subject' => 'Important: System Maintenance Notice',
                'body' => 'Dear User, We will be performing scheduled maintenance on our servers this weekend from 2 AM to 6 AM. During this time, the email service may be temporarily unavailable. We apologize for any inconvenience.',
                'folder' => 'inbox',
                'is_read' => false,
                'is_read' => true,
                'created_at' => Carbon::now()->subHours(12),
                'updated_at' => Carbon::now()->subHours(12),
            ],
            [
                'sender_id' => $users[0]->id,
                'recipient_id' => $users[1]->id,
                'recipient_email' => $users[1]->email,
                'subject' => 'Re: Meeting Tomorrow at 2 PM',
                'body' => 'Thanks for the reminder! I will be there. I have prepared my updates and will share them during the meeting. See you tomorrow!',
                'folder' => 'sent',
                'is_read' => true,
                'is_sent' => true,
                'sent_at' => Carbon::now()->subDays(2)->subHours(3),
                'created_at' => Carbon::now()->subDays(2)->subHours(3),
                'updated_at' => Carbon::now()->subDays(2)->subHours(3),
            ],
            [
                'sender_id' => $users[1]->id,
                'recipient_id' => $users[0]->id,
                'recipient_email' => $users[0]->email,
                'subject' => 'Invoice #12345',
                'body' => 'Please find attached the invoice for the services rendered last month. Total amount due: $5,000. Payment terms: Net 30 days. Thank you for your business!',
                'folder' => 'inbox',
                'is_read' => true,
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            [
                'sender_id' => $users[0]->id,
                'recipient_id' => $users[3]->id ?? $users[1]->id,
                'recipient_email' => $users[3]->email ?? $users[1]->email,
                'subject' => 'Happy Birthday!',
                'body' => 'Wishing you a very happy birthday! Hope you have a wonderful day filled with joy and celebration. Best wishes!',
                'folder' => 'sent',
                'is_read' => true,
                'is_sent' => true,
                'sent_at' => Carbon::now()->subDays(15),
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'sender_id' => $users[0]->id,
                'recipient_id' => $users[1]->id,
                'recipient_email' => $users[1]->email,
                'subject' => 'Old Email - To be deleted',
                'body' => 'This is an old email that has been moved to trash. It will be permanently deleted after 30 days.',
                'folder' => 'trash',
                'is_read' => true,
                'deleted_at' => Carbon::now()->subDays(20),
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(20),
            ],
        ];

        DB::table('emails')->insert($emails);

        $this->command->info('Email data seeded successfully!');
    }
}