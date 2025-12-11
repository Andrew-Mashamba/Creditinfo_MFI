<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncRoles extends Command
{
    protected $signature = 'roles:sync';
    protected $description = 'Sync all role-related data';

    public function handle()
    {
        $this->info('Starting role sync...');

        $this->call('menu-actions:sync');
        $this->call('user-roles:sync');
        $this->call('role-permissions:sync');

        $this->info('Role sync completed successfully.');
    }
} 