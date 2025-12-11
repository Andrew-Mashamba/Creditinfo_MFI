<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\AssignSuperAdminPermissionsSeeder;

class AssignSuperAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:super-admin 
                            {--user=1 : The user ID to assign super admin permissions to}
                            {--force : Force reassignment even if permissions already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign all system permissions to the super admin user (default: User ID 1)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $force = $this->option('force');
        
        $this->info('========================================');
        $this->info('SUPER ADMIN PERMISSIONS ASSIGNMENT');
        $this->info('========================================');
        $this->info("Target User ID: {$userId}");
        
        if ($force) {
            $this->warn('Force mode enabled - will reassign all permissions');
        }
        
        try {
            // Run the seeder
            $seeder = new AssignSuperAdminPermissionsSeeder();
            $seeder->run();
            
            $this->info('');
            $this->info('✅ Super admin permissions assigned successfully!');
            
            // Show summary
            $this->showPermissionsSummary($userId);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed to assign super admin permissions:');
            $this->error($e->getMessage());
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Display a summary of assigned permissions
     */
    protected function showPermissionsSummary($userId)
    {
        try {
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                $this->warn("User with ID {$userId} not found");
                return;
            }
            
            $role = $user->roles()->first();
            
            if (!$role) {
                $this->warn("User has no roles assigned");
                return;
            }
            
            $permissionCount = $role->permissions()->count();
            
            $this->info('');
            $this->table(
                ['Property', 'Value'],
                [
                    ['User', $user->name],
                    ['Email', $user->email],
                    ['Role', $role->name],
                    ['Total Permissions', $permissionCount],
                    ['Status', $permissionCount > 0 ? '✅ Active' : '⚠️ No Permissions']
                ]
            );
            
            // Show sample permissions
            if ($permissionCount > 0) {
                $this->info('');
                $this->info('Sample Permissions (first 5):');
                $role->permissions()->limit(5)->pluck('name')->each(function($permission) {
                    $this->line("  ✓ {$permission}");
                });
                
                if ($permissionCount > 5) {
                    $this->line("  ... and " . ($permissionCount - 5) . " more");
                }
            }
            
        } catch (\Exception $e) {
            $this->warn('Could not display summary: ' . $e->getMessage());
        }
    }
}