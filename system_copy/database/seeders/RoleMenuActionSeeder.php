<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleMenuActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing records to avoid duplicates
        DB::table('role_menu_actions')->truncate();

        // Get role IDs by name
        $systemAdminRole = DB::table('roles')->where('name', 'System Administrator')->first();
        $institutionAdminRole = DB::table('roles')->where('name', 'Institution Administrator')->first();
        
        if (!$systemAdminRole || !$institutionAdminRole) {
            $this->command->warn('Required roles not found. Skipping RoleMenuActionSeeder.');
            return;
        }

        $data = [
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 1,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 2,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 3,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 4,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 5,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 6,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 7,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 8,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 9,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 10,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 11,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 12,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 13,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 14,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 15,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 16,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 17,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 18,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 19,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 20,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 21,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 22,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 23,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 24,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 25,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 26,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 27,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 28,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage","configure","audit"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 29,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 30,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $systemAdminRole->id,
                'sub_role' => 'System Administrator',
                'menu_id' => 31,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 1,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 2,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 3,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 4,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 5,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 6,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 7,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 8,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 9,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 10,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 11,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 12,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 13,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 14,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 15,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 16,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 17,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 18,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 19,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 20,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 21,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 22,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 23,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 24,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 25,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 26,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 27,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 28,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 29,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 30,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => $institutionAdminRole->id,
                'sub_role' => 'Institution Administrator',
                'menu_id' => 31,
                'allowed_actions' => json_encode(["view","create","edit","delete","approve","reject","manage"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        // Insert data one by one to ensure proper column mapping
        foreach ($data as $row) {
            DB::table('role_menu_actions')->insert($row);
    }
}
}