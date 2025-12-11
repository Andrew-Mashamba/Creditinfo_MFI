<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // List of tables that should have branch_id
        $tables = [
            // Core entities
            'users',
            'clients',
            'employees',
            'applicants',
            
            // Financial accounts
            'accounts',
            'sub_accounts',
            'asset_accounts',
            'capital_accounts',
            'expense_accounts',
            'income_accounts',
            'liability_accounts',
            'budget_accounts',
            
            // Loans
            'loans',
            'loans_summary',
            'loans_originated',
            'loan_approvals',
            'maendeleo_loans',
            'settled_loans',
            'short_long_term_loans',
            'loan_stages',
            'current_loans_stages',
            'loan_process_progress',
            'loan_collateral',
            'loan_collaterals',
            'loan_guarantors',
            'loan_images',
            'loan_audit_logs',
            'loan_product_charges',
            'loan_provision_settings',
            
            // Transactions
            'transactions',
            'bank_transactions',
            'till_transactions',
            'internal_transfers',
            'cash_movements',
            'bank_transfers',
            'im_bank_transactions',
            'gepg_transactions',
            'transaction_reversals',
            'transaction_audit_logs',
            
            // Tills and Vaults
            'tills',
            'tellers',
            'vaults',
            'strongroom_ledgers',
            'teller_end_of_day_positions',
            'till_reconciliations',
            'security_transport_logs',
            
            // Savings and deposits
            'savings_types',
            'deposit_types',
            
            // Services and products
            'services',
            'sub_products',
            'loan_sub_products',
            'product_has_charges',
            'product_has_insurance',
            
            // Shares
            'share_ownership',
            'issued_shares',
            'share_transfers',
            'share_withdrawals',
            'dividends',
            
            // Payments and bills
            'payments',
            'bills',
            'orders',
            'payment_notifications',
            'billing_cycles',
            
            // Cheques
            'cheques',
            'chequebooks',
            
            // Expenses and budget
            'expenses',
            'expense_approvals',
            'budget_managements',
            'main_budget',
            'main_budget_pending',
            'budget_approvers',
            
            // HR
            'payrolls',
            'leaves',
            'leave_management',
            'benefits',
            'interviews',
            'job_postings',
            'hires_approvals',
            'employee_requests',
            
            // Complaints
            'complaints',
            
            // Notifications
            'notifications',
            'notification_logs',
            'mandatory_savings_notifications',
            
            // Vendors and contracts
            'vendors',
            'contract_managements',
            'tenders',
            'purchases',
            
            // Assets
            'assets_list',
            'ppes',
            'inventories',
            
            // Groups and meetings
            'groups',
            'meetings',
            'meeting_attendance',
            'meeting_documents',
            
            // Approvals
            'approvals',
            'approval_actions',
            'approval_comments',
            'committee_approvals',
            'approval_matrix_configs',
            
            // Standing instructions
            'standing_instructions',
            
            // Locked amounts
            'locked_amounts',
            
            // Mandatory savings
            'mandatory_savings_tracking',
            'mandatory_savings_settings',
            
            // Others
            'guarantors',
            'collaterals',
            'custom_collaterals',
            'insurances',
            'insurance_list',
            'charges',
            'charges_list',
            'interest_payables',
            'loss_reserves',
            'unearned_deferred_revenue',
            'payables',
            'receivables',
            'investments_list',
            'investment_types',
            'onboarding',
            'ai_interactions',
            'reports',
            'scheduled_reports',
            'financial_data',
            'financial_position',
            'financial_ratios',
            'analysis_sessions',
            'scores',
            'pending_registrations',
            'webportal_users',
            'queries',
            'query_responses',
            'cashflow_configurations',
            'cash_in_transit_providers',
            'payment_methods',
            'member_categories',
            'client_documents',
            'taxes',
            'process_code_configs',
            'user_action_logs',
            'audit_logs',
            'temp_permissions',
            'dasboard_table',
            'entries',
            'entries_amount',
            'projects',
            'institution_files',
            'committee_members',
            'committees',
            'leaderships',
            'approvers_of_loans_stages',
            'loans_schedules',
            'loans_arreas',
            'historical_balances',
            'account_historical_balances',
            'setup_accounts',
            'general_ledger',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                // Check if table already has branch_id
                if (!Schema::hasColumn($table, 'branch_id')) {
                    Schema::table($table, function (Blueprint $table) {
                        // Add branch_id column after id
                        $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                        
                        // Add index for performance
                        $table->index('branch_id');
                        
                        // Add foreign key constraint
                        $table->foreign('branch_id')
                            ->references('id')
                            ->on('branches')
                            ->onDelete('restrict');
                    });
                    
                    echo "✅ Added branch_id to table: {$table}\n";
                } else {
                    echo "⏭️  Table {$table} already has branch_id\n";
                }
            } else {
                echo "⚠️  Table {$table} does not exist\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users', 'clients', 'employees', 'applicants', 'accounts', 'sub_accounts',
            'asset_accounts', 'capital_accounts', 'expense_accounts', 'income_accounts',
            'liability_accounts', 'budget_accounts', 'loans', 'loans_summary',
            'loans_originated', 'loan_approvals', 'maendeleo_loans', 'settled_loans',
            'short_long_term_loans', 'loan_stages', 'current_loans_stages',
            'loan_process_progress', 'loan_collateral', 'loan_collaterals',
            'loan_guarantors', 'loan_images', 'loan_audit_logs', 'loan_product_charges',
            'loan_provision_settings', 'transactions', 'bank_transactions',
            'till_transactions', 'internal_transfers', 'cash_movements', 'bank_transfers',
            'im_bank_transactions', 'gepg_transactions', 'transaction_reversals',
            'transaction_audit_logs', 'tills', 'tellers', 'vaults', 'strongroom_ledgers',
            'teller_end_of_day_positions', 'till_reconciliations', 'security_transport_logs',
            'savings_types', 'deposit_types', 'services', 'sub_products', 'loan_sub_products',
            'product_has_charges', 'product_has_insurance', 'share_ownership', 'issued_shares',
            'share_transfers', 'share_withdrawals', 'dividends', 'payments', 'bills', 'orders',
            'payment_notifications', 'billing_cycles', 'cheques', 'chequebooks', 'expenses',
            'expense_approvals', 'budget_managements', 'main_budget', 'main_budget_pending',
            'budget_approvers', 'payrolls', 'leaves', 'leave_management', 'benefits',
            'interviews', 'job_postings', 'hires_approvals', 'employee_requests', 'complaints',
            'notifications', 'notification_logs', 'mandatory_savings_notifications', 'vendors',
            'contract_managements', 'tenders', 'purchases', 'assets_list', 'ppes', 'inventories',
            'groups', 'meetings', 'meeting_attendance', 'meeting_documents', 'approvals',
            'approval_actions', 'approval_comments', 'committee_approvals',
            'approval_matrix_configs', 'standing_instructions', 'locked_amounts',
            'mandatory_savings_tracking', 'mandatory_savings_settings', 'guarantors',
            'collaterals', 'custom_collaterals', 'insurances', 'insurance_list', 'charges',
            'charges_list', 'interest_payables', 'loss_reserves', 'unearned_deferred_revenue',
            'payables', 'receivables', 'investments_list', 'investment_types', 'onboarding',
            'ai_interactions', 'reports', 'scheduled_reports', 'financial_data',
            'financial_position', 'financial_ratios', 'analysis_sessions', 'scores',
            'pending_registrations', 'webportal_users', 'queries', 'query_responses',
            'cashflow_configurations', 'cash_in_transit_providers', 'payment_methods',
            'member_categories', 'client_documents', 'taxes', 'process_code_configs',
            'user_action_logs', 'audit_logs', 'temp_permissions', 'dasboard_table',
            'entries', 'entries_amount', 'projects', 'institution_files',
            'committee_members', 'committees', 'leaderships', 'approvers_of_loans_stages',
            'loans_schedules', 'loans_arreas', 'historical_balances',
            'account_historical_balances', 'setup_accounts', 'general_ledger',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'branch_id')) {
                Schema::table($table, function (Blueprint $table) {
                    // Drop foreign key constraint
                    $table->dropForeign(['branch_id']);
                    
                    // Drop index
                    $table->dropIndex(['branch_id']);
                    
                    // Drop column
                    $table->dropColumn('branch_id');
                });
            }
        }
    }
};