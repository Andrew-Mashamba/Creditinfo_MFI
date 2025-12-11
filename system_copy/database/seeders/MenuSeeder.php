<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Generate array of numbers from 1 to 200 as strings
        $permissions = array_map('strval', range(1, 200));
        
        // Define menu items
        $menuItems = [
            [
                'id' => 1,
                'system_id' => 1,
                'menu_name' => 'Branches',
                'menu_description' => 'Manage all branch locations',
                'menu_title' => 'Branches',
                'status' => 'PENDING',
                'menu_number' => 1],
            [
                'id' => 2,
                'system_id' => 1,
                'menu_name' => 'Members',
                'menu_description' => 'Directory of all members',
                'menu_title' => 'Members',
                'status' => 'PENDING',
                'menu_number' => 2,
                'created_at' => '2023-09-23 11:57:57',
                'updated_at' => '2023-09-23 11:57:57'
            ],
            [
                'id' => 3,
                'system_id' => 1,
                'menu_name' => 'Shares',
                'menu_description' => 'Shareholder records and info',
                'menu_title' => 'Shares',
                'status' => 'PENDING',
                'menu_number' => 3,
                'created_at' => '2023-09-23 11:57:57',
                'updated_at' => '2023-09-23 11:57:57'
            ],
            [
                'id' => 4,
                'system_id' => 1,
                'menu_name' => 'Savings',
                'menu_description' => 'All savings account types',
                'menu_title' => 'Savings',
                'status' => 'PENDING',
                'menu_number' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'system_id' => 1,
                'menu_name' => 'Deposits',
                'menu_description' => 'Deposit and transaction history',
                'menu_title' => 'Deposits',
                'status' => 'PENDING',
                'menu_number' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'system_id' => 1,
                'menu_name' => 'Loans',
                'menu_description' => 'Manage all loan accounts',
                'menu_title' => 'Loans',
                'status' => 'PENDING',
                'menu_number' => 6,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'system_id' => 1,
                'menu_name' => 'Products Management',
                'menu_description' => 'All available product types',
                'menu_title' => 'Products Management',
                'status' => 'PENDING',
                'menu_number' => 8,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 8,
                'system_id' => 1,
                'menu_name' => 'Accounting',
                'menu_description' => 'Financial records and reports',
                'menu_title' => 'Accounting',
                'status' => 'PENDING',
                'menu_number' => 7,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 9,
                'system_id' => 1,
                'menu_name' => 'Expenses',
                'menu_description' => 'Track and manage expenses',
                'menu_title' => 'Expenses',
                'status' => 'PENDING',
                'menu_number' => 16,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 10,
                'system_id' => 1,
                'menu_name' => 'Payments',
                'menu_description' => 'Outgoing and incoming payments',
                'menu_title' => 'Payments',
                'status' => 'PENDING',
                'menu_number' => 9,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 11,
                'system_id' => 1,
                'menu_name' => 'Investments',
                'menu_description' => 'Manage investment portfolio',
                'menu_title' => 'Investments',
                'status' => 'PENDING',
                'menu_number' => 17,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 12,
                'system_id' => 1,
                'menu_name' => 'Procurement',
                'menu_description' => 'Purchase orders and suppliers',
                'menu_title' => 'Procurement',
                'status' => 'PENDING',
                'menu_number' => 14,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 13,
                'system_id' => 1,
                'menu_name' => 'Budget Management',
                'menu_description' => 'Plan and track budgets',
                'menu_title' => 'Budget Management',
                'status' => 'PENDING',
                'menu_number' => 17,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 14,
                'system_id' => 1,
                'menu_name' => 'Insurance',
                'menu_description' => 'All insurance policy details',
                'menu_title' => 'Insurance',
                'status' => 'PENDING',
                'menu_number' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 15,
                'system_id' => 1,
                'menu_name' => 'Teller Management',
                'menu_description' => 'Teller operations and logs',
                'menu_title' => 'Teller Management',
                'status' => 'PENDING',
                'menu_number' => 18,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 16,
                'system_id' => 1,
                'menu_name' => 'Reconciliation',
                'menu_description' => 'Account balancing and review',
                'menu_title' => 'Reconciliation',
                'status' => 'PENDING',
                'menu_number' => 12,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 17,
                'system_id' => 1,
                'menu_name' => 'Human Resources',
                'menu_description' => 'HR and staff management',
                'menu_title' => 'Human Resources',
                'status' => 'PENDING',
                'menu_number' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 18,
                'system_id' => 1,
                'menu_name' => 'Self Services',
                'menu_description' => 'User self-service options',
                'menu_title' => 'Self Services',
                'status' => 'PENDING',
                'menu_number' => 21,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 19,
                'system_id' => 1,
                'menu_name' => 'Approvals',
                'menu_description' => 'Approval workflows and status',
                'menu_title' => 'Approvals',
                'status' => 'PENDING',
                'menu_number' => 49,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 20,
                'system_id' => 1,
                'menu_name' => 'Reports Manager',
                'menu_description' => 'Generate and view reports',
                'menu_title' => 'Reports Manager',
                'status' => 'PENDING',
                'menu_number' => 13,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 21,
                'system_id' => 1,
                'menu_name' => 'Institution Settings',
                'menu_description' => 'System settings and config',
                'menu_title' => 'Institution Settings',
                'status' => 'PENDING',
                'menu_number' => 19,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 22,
                'system_id' => 1,
                'menu_name' => 'Users Manager',
                'menu_description' => 'Manage all user accounts',
                'menu_title' => 'Users Manager',
                'status' => 'PENDING',
                'menu_number' => 50,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 23,
                'system_id' => 1,
                'menu_name' => 'Active Loans',
                'menu_description' => 'View all current loans',
                'menu_title' => 'Active Loans',
                'status' => 'PENDING',
                'menu_number' => 24,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 24,
                'system_id' => 1,
                'menu_name' => 'Management Approval',
                'menu_description' => 'Manager approval requests',
                'menu_title' => 'Management Approval',
                'status' => 'PENDING',
                'menu_number' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 25,
                'system_id' => 1,
                'menu_name' => 'Groups',
                'menu_description' => 'User and staff groups',
                'menu_title' => 'Groups',
                'status' => 'PENDING',
                'menu_number' => 25,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 26,
                'system_id' => 1,
                'menu_name' => 'Cash Management',
                'menu_description' => 'Manage cash operations, tills, vaults, and cash movements',
                'menu_title' => 'Cash Management',
                'status' => 'PENDING',
                'menu_number' => 11,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 27,
                'system_id' => 1,
                'menu_name' => 'Billing',
                'menu_description' => 'Manage billing, invoices, payment processing, and billing cycles',
                'menu_title' => 'Billing',
                'status' => 'PENDING',
                'menu_number' => 15,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 28,
                'system_id' => 1,
                'menu_name' => 'Transactions',
                'menu_description' => 'View and manage all financial transactions, transfers, and transaction history',
                'menu_title' => 'Transactions',
                'status' => 'PENDING',
                'menu_number' => 20,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 29,
                'system_id' => 1,
                'menu_name' => 'Mobile & Web Portal',
                'menu_description' => 'Manage mobile and web portal settings',
                'menu_title' => 'Mobile & Web Portal',
                'status' => 'PENDING',
                'menu_number' => 29,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 30,
                'system_id' => 1,
                'menu_name' => 'Email',
                'menu_description' => 'Email configuration and management',
                'menu_title' => 'Email',
                'status' => 'PENDING',
                'menu_number' => 30,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 31,
                'system_id' => 1,
                'menu_name' => 'Subscriptions',
                'menu_description' => 'Manage system subscriptions and plans',
                'menu_title' => 'Subscriptions',
                'status' => 'PENDING',
                'menu_number' => 31,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insert or update each menu item
        foreach ($menuItems as $menuItem) {
            // Add the standard fields that the application expects
            $menuData = array_merge($menuItem, [
                'name' => $menuItem['menu_name'],  // Copy menu_name to name field
                'route' => strtolower(str_replace(' ', '_', $menuItem['menu_name'])),  // Generate route from menu name
                'icon' => 'fa fa-' . strtolower(str_replace(' ', '-', $menuItem['menu_name'])),  // Generate icon class
                'created_at' => $menuItem['created_at'] ?? now(),
                'updated_at' => $menuItem['updated_at'] ?? now()
            ]);
            
            DB::table('menus')->updateOrInsert(
                ['id' => $menuItem['id']],
                $menuData
            );
        }
        
        echo "MenuSeeder: Inserted " . count($menuItems) . " menus with names, routes, and icons\n";
    }
}