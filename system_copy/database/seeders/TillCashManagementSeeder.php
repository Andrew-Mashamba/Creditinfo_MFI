<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Till;
use App\Models\Teller;
use App\Models\User;
use App\Models\StrongroomLedger;
use App\Models\CashMovement;
use App\Models\TillTransaction;
use App\Models\TillReconciliation;
use Carbon\Carbon;

class TillCashManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users or create if they don't exist
        $adminUser = User::where('email', 'admin@example.com')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'email' => 'admin@example.com',
                'name' => 'System Administrator',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]);
        }

        $teller1 = User::where('email', 'teller1@example.com')->first();
        if (!$teller1) {
            $teller1 = User::create([
                'email' => 'teller1@example.com',
                'name' => 'John Teller',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]);
        }

        $teller2 = User::where('email', 'teller2@example.com')->first();
        if (!$teller2) {
            $teller2 = User::create([
                'email' => 'teller2@example.com',
                'name' => 'Jane Teller',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]);
        }

        $supervisor = User::where('email', 'supervisor@example.com')->first();
        if (!$supervisor) {
            $supervisor = User::create([
                'email' => 'supervisor@example.com',
                'name' => 'Mike Supervisor',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]);
        }

        // Create tellers
        $teller1Record = Teller::firstOrCreate(
            ['employee_id' => 1001],
            [
                'user_id' => $teller1->id,
                'branch_id' => 1, // Assuming branch ID 1 exists
                'employee_id' => 1001,
                'status' => 'active',
                'max_amount' => 100000.00,
                'transaction_limit' => 100000.00,
                'registered_by_id' => 1,
                'assigned_at' => now()]
        );

        $teller2Record = Teller::firstOrCreate(
            ['employee_id' => 1002],
            [
                'user_id' => $teller2->id,
                'branch_id' => 1, // Assuming branch ID 1 exists
                'employee_id' => 1002,
                'status' => 'active',
                'max_amount' => 100000.00,
                'transaction_limit' => 100000.00,
                'registered_by_id' => 1,
                'assigned_at' => now()]
        );

        // Create tills
        $till1 = Till::firstOrCreate(
            ['till_number' => 'TILL001'],
            [
                'name' => 'Main Till',
                'till_number' => 'TILL001',
                'assigned_to' => $teller1->id,
                'branch_id' => 1,
                'status' => 'open',
                'opening_balance' => 50000.00,
                'current_balance' => 75000.00,
                'opened_at' => now()->subHours(8),
                'opened_by' => $adminUser->id,
                'description' => 'Primary till for main branch operations']
        );

        $till2 = Till::firstOrCreate(
            ['till_number' => 'TILL002'],
            [
                'name' => 'Secondary Till',
                'till_number' => 'TILL002',
                'assigned_to' => $teller2->id,
                'branch_id' => 1,
                'status' => 'closed',
                'opening_balance' => 30000.00,
                'current_balance' => 30000.00,
                'opened_at' => now()->subDays(1),
                'closed_at' => now()->subHours(2),
                'opened_by' => $adminUser->id,
                'closed_by' => $teller2->id,
                'description' => 'Secondary till for backup operations']
        );


        $this->command->info('Till and Cash Management sample data created successfully!');
        $this->command->info('You can now access the system at: /till-cash-management');
        $this->command->info('Login with any of these accounts:');
        $this->command->info('- admin@example.com / password');
        $this->command->info('- teller1@example.com / password');
        $this->command->info('- teller2@example.com / password');
        $this->command->info('- supervisor@example.com / password');
    }
}
