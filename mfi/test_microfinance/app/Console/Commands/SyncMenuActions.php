<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MenuAction;
use App\Models\Menu;

class SyncMenuActions extends Command
{
    protected $signature = 'menu-actions:sync';
    protected $description = 'Sync menu actions with the database';

    public function handle()
    {
        $this->info('Starting menu actions sync...');

        $menus = Menu::all();
        foreach ($menus as $menu) {
            $this->info("Processing menu: {$menu->name}");
            
            // Define default actions for each menu
            $actions = ['view', 'create', 'edit', 'delete'];
            
            foreach ($actions as $action) {
                MenuAction::firstOrCreate([
                    'menu_id' => $menu->id,
                    'action' => $action
                ]);
            }
        }

        $this->info('Menu actions sync completed successfully!');
    }
} 