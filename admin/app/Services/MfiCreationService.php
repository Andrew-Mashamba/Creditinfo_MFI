<?php

namespace App\Services;

use App\Models\MfiInstitution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class MfiCreationService
{
    public function createMfiInstance($data)
    {
        DB::beginTransaction();
        
        try {
            // 1. Create MFI institution record
            $mfiInstitution = MfiInstitution::create([
                'name' => $data['mfi_name'],
                'code' => $data['mfi_code'],
                'contact_person' => $data['contact_person'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'],
                'address' => $data['address'],
                'license_number' => $data['license_number'],
                'database_name' => $data['mfi_code'] . '_db',
                'folder_path' => '/mfi/' . $data['mfi_code'],
                'admin_email' => $data['admin_email'],
                'status' => 'active',
                'configuration' => [
                    'created_by' => auth()->user()->id,
                    'admin_user' => [
                        'first_name' => $data['admin_first_name'],
                        'last_name' => $data['admin_last_name'],
                    ]
                ]
            ]);

            // 2. Create database for the MFI
            $this->createMfiDatabase($data['mfi_code']);

            // 3. Create folder structure and copy files
            $this->createMfiFolderStructure($data['mfi_code']);

            // 4. Update .env configuration
            $this->updateMfiConfiguration($data['mfi_code']);

            // 5. Restore database from backup
            $this->restoreMfiDatabase($data['mfi_code']);

            // 6. Assign port and start MFI server
            $port = $this->assignPortAndStartServer($data['mfi_code']);
            $mfiInstitution->port = $port;
            $mfiInstitution->save();

            // 7. Create admin user (this would be in the MFI's database later)
            $this->createMfiAdminUser($data, $port);

            // 8. Send notification emails (implement later)
            // $this->sendNotificationEmails($mfiInstitution, $data);

            DB::commit();

            Log::info('MFI Instance created successfully', [
                'mfi_code' => $data['mfi_code'],
                'mfi_name' => $data['mfi_name']
            ]);

            return $mfiInstitution;

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create MFI instance', [
                'error' => $e->getMessage(),
                'mfi_code' => $data['mfi_code'] ?? null
            ]);

            // Cleanup any partially created resources
            $this->cleanup($data['mfi_code'] ?? null);

            throw $e;
        }
    }

    private function createMfiDatabase($mfiCode)
    {
        $databaseName = $mfiCode . '_db';
        
        // Validate database name for PostgreSQL (alphanumeric and underscores only)
        if (!preg_match('/^[a-z0-9_]+$/', $databaseName)) {
            throw new Exception("Invalid database name: $databaseName");
        }
        
        // Create PostgreSQL database
        $command = sprintf(
            'psql -U postgres -h 127.0.0.1 -p 5432 -c "CREATE DATABASE %s;"',
            $databaseName
        );

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception("Failed to create database: $databaseName");
        }

        Log::info("Database created: $databaseName");
    }

    private function createMfiFolderStructure($mfiCode)
    {
        $systemCopyPath = base_path('../system_copy');
        $targetPath = base_path('../mfi/' . $mfiCode);

        // Create target directory
        if (!File::exists($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
        }

        // Copy files from system_copy to new MFI folder
        if (File::exists($systemCopyPath)) {
            File::copyDirectory($systemCopyPath, $targetPath);
            Log::info("Files copied to: $targetPath");
        } else {
            throw new Exception("System copy template not found at: $systemCopyPath");
        }
    }

    private function updateMfiConfiguration($mfiCode)
    {
        $envPath = base_path('../mfi/' . $mfiCode . '/.env');
        
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            
            // Update database configuration
            $envContent = preg_replace(
                '/DB_DATABASE=.*/',
                'DB_DATABASE=' . $mfiCode . '_db',
                $envContent
            );

            File::put($envPath, $envContent);
            Log::info("Configuration updated for: $mfiCode");
        }
    }

    private function restoreMfiDatabase($mfiCode)
    {
        $databaseName = $mfiCode . '_db';
        $backupFile = base_path('../system_copy/backup_database/nbc_saccos_db_full_backup_20251210_092828.sql');

        if (!File::exists($backupFile)) {
            throw new Exception("Backup file not found: $backupFile");
        }

        $command = sprintf(
            'psql -U postgres -h 127.0.0.1 -p 5432 -d %s -f %s',
            escapeshellarg($databaseName),
            escapeshellarg($backupFile)
        );

        $output = [];
        $returnVar = 0;
        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            Log::warning("Database restore had issues for: $databaseName", ['output' => $output]);
        } else {
            Log::info("Database restored successfully: $databaseName");
        }
    }

    private function assignPortAndStartServer($mfiCode)
    {
        // Find an available port starting from 8005
        $port = $this->findAvailablePort();
        
        // Start the MFI Laravel server
        $mfiPath = base_path('../mfi/' . $mfiCode);
        $command = "cd '$mfiPath' && php artisan serve --port=$port --host=0.0.0.0 > /dev/null 2>&1 &";
        
        exec($command);
        
        Log::info("MFI server started for $mfiCode on port $port");
        
        return $port;
    }

    private function findAvailablePort($startPort = 8005)
    {
        $port = $startPort;
        
        // Check if port is already used by other MFI instances
        while ($this->isPortInUse($port)) {
            $port++;
        }
        
        return $port;
    }

    private function isPortInUse($port)
    {
        // Check if port is used by existing MFI instances
        $existingMfi = MfiInstitution::where('port', $port)->first();
        if ($existingMfi) {
            return true;
        }
        
        // Check if port is actually listening
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
        if ($connection) {
            fclose($connection);
            return true;
        }
        
        return false;
    }

    private function createMfiAdminUser($data, $port = null)
    {
        try {
            // Create user in the main admin database
            User::create([
                'name' => $data['admin_first_name'] . ' ' . $data['admin_last_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'email_verified_at' => now(),
            ]);

            // Also create user in the auth database for MFI access
            $this->createMfiAuthUser($data, $port);

            Log::info("Admin user created: " . $data['admin_email']);
        } catch (Exception $e) {
            Log::warning("Could not create admin user", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function createMfiAuthUser($data, $port)
    {
        $authDbConnection = DB::connection('pgsql_auth');
        
        $authDbConnection->table('mfi_users')->insert([
            'name' => $data['admin_first_name'] . ' ' . $data['admin_last_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'mfi_code' => $data['mfi_code'],
            'mfi_folder_path' => '/mfi/' . $data['mfi_code'],
            'mfi_database' => $data['mfi_code'] . '_db',
            'port' => $port,
            'created_at' => now(),
            'updated_at' => now(),
            'email_verified_at' => now(),
        ]);

        Log::info("MFI auth user created: " . $data['admin_email'] . " for MFI: " . $data['mfi_code']);
    }

    private function cleanup($mfiCode)
    {
        if (!$mfiCode) return;

        try {
            // Remove database if created
            $databaseName = $mfiCode . '_db';
            $command = sprintf(
                'psql -U postgres -h 127.0.0.1 -p 5432 -c "DROP DATABASE IF EXISTS %s;"',
                $databaseName
            );
            exec($command);

            // Remove folder if created
            $targetPath = base_path('../mfi/' . $mfiCode);
            if (File::exists($targetPath)) {
                File::deleteDirectory($targetPath);
            }

            // Remove user from auth database if exists
            try {
                DB::connection('pgsql_auth')->table('mfi_users')
                    ->where('mfi_code', $mfiCode)
                    ->delete();
            } catch (Exception $e) {
                Log::warning("Could not cleanup auth user for MFI: $mfiCode", ['error' => $e->getMessage()]);
            }

            Log::info("Cleanup completed for: $mfiCode");
        } catch (Exception $e) {
            Log::error("Cleanup failed for: $mfiCode", ['error' => $e->getMessage()]);
        }
    }
}