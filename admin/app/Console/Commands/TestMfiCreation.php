<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Livewire\Mfi\CreateNewMfi;
use App\Models\User;
use App\Models\MfiInstitution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class TestMfiCreation extends Command
{
    protected $signature = 'test:mfi-creation {--fresh : Delete existing test data and create fresh}';
    protected $description = 'Test MFI creation workflow by simulating form submissions';

    public function handle()
    {
        $this->info('Starting MFI Creation Simulation...');
        $this->newLine();

        // Create or get a test admin user
        $testUser = User::where('email', 'admin@example.com')->first();
        if (!$testUser) {
            $testUser = User::create([
                'name' => 'Test Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $this->info('Created test admin user: admin@example.com');
        } else {
            $this->info('Using existing test admin user: admin@example.com');
        }

        // Authenticate the user
        Auth::login($testUser);
        $this->newLine();

        // Handle fresh option
        if ($this->option('fresh')) {
            $this->info('Cleaning up existing test data...');
            $this->cleanupTestData();
            $this->newLine();
        }

        // Sample test data
        $testData = [
            [
                'mfi_name' => 'Kilimanjaro SACCOS',
                'mfi_code' => 'kilimanjaro_saccos',
                'contact_person' => 'John Mwalimu',
                'contact_email' => 'admin@kilimanjaro-saccos.co.tz',
                'contact_phone' => '+255712345678',
                'address' => 'Moshi, Kilimanjaro Region, Tanzania',
                'license_number' => 'SACCOS-KLM-001',
                'admin_first_name' => 'John',
                'admin_last_name' => 'Mwalimu',
                'admin_email' => 'john.mwalimu@kilimanjaro-saccos.co.tz',
                'admin_password' => 'SecurePassword123!',
                'admin_password_confirmation' => 'SecurePassword123!',
            ],
            [
                'mfi_name' => 'Dar es Salaam Microfinance',
                'mfi_code' => 'dar_microfinance',
                'contact_person' => 'Maria Kimario',
                'contact_email' => 'admin@dar-microfinance.co.tz',
                'contact_phone' => '+255713456789',
                'address' => 'Kariakoo, Dar es Salaam, Tanzania',
                'license_number' => 'MFI-DSM-002',
                'admin_first_name' => 'Maria',
                'admin_last_name' => 'Kimario',
                'admin_email' => 'maria.kimario@dar-microfinance.co.tz',
                'admin_password' => 'StrongPassword456!',
                'admin_password_confirmation' => 'StrongPassword456!',
            ],
            [
                'mfi_name' => 'Arusha Community Bank',
                'mfi_code' => 'arusha_community',
                'contact_person' => 'Grace Mollel',
                'contact_email' => 'admin@arusha-community.co.tz',
                'contact_phone' => '+255714567890',
                'address' => 'Arusha City, Arusha Region, Tanzania',
                'license_number' => 'CB-ARS-003',
                'admin_first_name' => 'Grace',
                'admin_last_name' => 'Mollel',
                'admin_email' => 'grace.mollel@arusha-community.co.tz',
                'admin_password' => 'ComplexPass789!',
                'admin_password_confirmation' => 'ComplexPass789!',
            ],
        ];

        foreach ($testData as $index => $data) {
            $this->info("=== Testing MFI Creation " . ($index + 1) . " ===");
            $this->line("MFI Name: {$data['mfi_name']}");
            $this->line("MFI Code: {$data['mfi_code']}");
            $this->line("Contact Person: {$data['contact_person']}");
            $this->line("Admin Email: {$data['admin_email']}");
            $this->newLine();

            try {
                // Check if MFI already exists
                $existingMfi = MfiInstitution::where('code', $data['mfi_code'])->first();
                if ($existingMfi) {
                    $this->warn("MFI with code '{$data['mfi_code']}' already exists. Skipping...");
                    $this->newLine();
                    continue;
                }

                // Create a new instance of the Livewire component
                $component = new CreateNewMfi();

                // Set the component properties
                foreach ($data as $key => $value) {
                    $component->{$key} = $value;
                }

                $this->comment("✓ Component properties set");

                // Validate the data
                $component->validate();
                $this->comment("✓ Validation passed");

                // Call the createMfi method
                $result = $component->createMfi();
                
                $this->info("✓ MFI creation completed successfully!");
                $this->line("✓ Database: {$data['mfi_code']}_db");
                $this->line("✓ Folder: /mfi/{$data['mfi_code']}");
                $this->line("✓ Admin user created: {$data['admin_email']}");
                $this->line("✓ Auth database entry created");

            } catch (Exception $e) {
                $this->error("✗ ERROR: " . $e->getMessage());
                if ($this->option('verbose')) {
                    $this->line("Stack trace:");
                    $this->line($e->getTraceAsString());
                }
            }

            $this->newLine();
            $this->line(str_repeat("-", 50));
            $this->newLine();

            // Add a small delay between creations
            sleep(1);
        }

        // Verification section
        $this->info("=== Verification ===");
        $this->info("Checking created MFI institutions...");
        $this->newLine();

        try {
            $institutions = MfiInstitution::all();
            $this->info("Total institutions in database: " . $institutions->count());
            $this->newLine();

            foreach ($institutions as $institution) {
                $this->line("Institution: {$institution->name}");
                $this->line("Code: {$institution->code}");
                $this->line("Database: {$institution->database_name}");
                $this->line("Folder: {$institution->folder_path}");
                $this->line("Status: {$institution->status}");
                $this->line("Created: {$institution->created_at}");
                $this->newLine();
            }

            // Check auth database
            $this->info("=== Auth Database Verification ===");
            $authUsers = DB::connection('pgsql_auth')
                ->table('mfi_users')
                ->get();

            $this->info("Total MFI users in auth database: " . $authUsers->count());
            $this->newLine();

            foreach ($authUsers as $user) {
                $this->line("User: {$user->name}");
                $this->line("Email: {$user->email}");
                $this->line("MFI Code: {$user->mfi_code}");
                $this->line("MFI Folder: {$user->mfi_folder_path}");
                $this->line("MFI Database: {$user->mfi_database}");
                $this->line("Created: {$user->created_at}");
                $this->newLine();
            }

        } catch (Exception $e) {
            $this->error("✗ Verification ERROR: " . $e->getMessage());
        }

        $this->info("=== Simulation Complete ===");
        return 0;
    }

    private function cleanupTestData()
    {
        try {
            // Get all test MFI codes and emails
            $testData = [
                ['code' => 'kilimanjaro_saccos', 'email' => 'john.mwalimu@kilimanjaro-saccos.co.tz'],
                ['code' => 'dar_microfinance', 'email' => 'maria.kimario@dar-microfinance.co.tz'],
                ['code' => 'arusha_community', 'email' => 'grace.mollel@arusha-community.co.tz'],
            ];
            
            foreach ($testData as $mfi) {
                $this->line("Cleaning up MFI: {$mfi['code']}");
                
                // Remove from MFI institutions table
                MfiInstitution::where('code', $mfi['code'])->delete();
                
                // Remove from main users table
                User::where('email', $mfi['email'])->delete();
                $this->line("  Removed user: {$mfi['email']}");
                
                // Remove from auth database
                DB::connection('pgsql_auth')->table('mfi_users')
                    ->where('mfi_code', $mfi['code'])
                    ->delete();
                
                // Drop database if exists
                $databaseName = $mfi['code'] . '_db';
                $this->line("  Dropping database: $databaseName");
                shell_exec("psql -U postgres -h 127.0.0.1 -p 5432 -c 'DROP DATABASE IF EXISTS $databaseName;' 2>/dev/null");
                
                // Remove folder if exists
                $folderPath = base_path('../mfi/' . $mfi['code']);
                if (is_dir($folderPath)) {
                    $this->line("  Removing folder: $folderPath");
                    shell_exec("rm -rf '$folderPath'");
                }
            }
            
            $this->info("Cleanup completed successfully!");
            
        } catch (Exception $e) {
            $this->error("Cleanup failed: " . $e->getMessage());
        }
    }
}