<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure we're not in a transaction
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        
        // Disable foreign key checks to avoid constraint issues during seeding
        DB::statement('SET session_replication_role = replica;');
        
        // Initialize counters
        $totalSeeders = 0;
        $successfulSeeders = 0;
        $failedSeeders = [];
        
        try {
            // Log start of seeding
            Log::info('=== DATABASE SEEDING STARTED ===');
            echo "\n=== DATABASE SEEDING STARTED ===\n";
            
            $seeders = [
                // Core System
                InstitutionsSeeder::class,
                BranchesSeeder::class,
                DepartmentsSeeder::class,

                // User and Role Management
                // RolesSeeder::class,
                // RoleSeeder::class,
                SACCOSRolesSeeder::class,
                SubrolesSeeder::class,
                SubRoleSeeder::class,
                SACCOSSubRolesSeeder::class,
                PermissionsSeeder::class,
                UsersSeeder::class,
                UserrolesSeeder::class,
                UserRoleSeeder::class,
                UserpermissionsSeeder::class,
                SubrolepermissionsSeeder::class,
                RolepermissionsSeeder::class,
                UsersubmenusSeeder::class,
                UsersubrolesSeeder::class,
                UserprofilesSeeder::class,
                UsersecurityprofilesSeeder::class,
                PasswordpoliciesSeeder::class,
                PasswordPolicySeeder::class,

                // Menu System
                // MenulistSeeder::class, // File doesn't exist
                // ConsolidatedMenuSeeder::class, // File deleted
                MenuSeeder::class, // Populates menus table
                SubMenusSeeder::class,
                MenuActionsSeeder::class,
                RoleMenuActionSeeder::class, // Role menu action mappings
                RoleMenuActionsSeeder::class, // Additional role menu actions

                // Member Management
                ClientsSeeder::class,
                ExampleMembersSeeder::class,
                MembercategoriesSeeder::class,
                ClientdocumentsSeeder::class,
                PendingregistrationsSeeder::class,
                WebportalusersSeeder::class,

                // Financial Products and Services
                ServicesSeeder::class,
                SubProductsSeeder::class,
                LoanSubProductsSeeder::class,
                ProducthaschargesSeeder::class,
                ProducthasinsuranceSeeder::class,
                SavingstypesSeeder::class,
                DeposittypesSeeder::class,

                // Accounting and GL
                AccountsSeeder::class,
                // GLaccountsSeeder::class, // Table doesn't exist
                GeneralledgerSeeder::class,
                // GLaccountsSeeder::class, // Table GL_accounts doesn't exist
                SubaccountsSeeder::class,
                AssetaccountsSeeder::class,
                CapitalaccountsSeeder::class,
                ExpenseaccountsSeeder::class,
                IncomeaccountsSeeder::class,
                LiabilityaccountsSeeder::class,
                BudgetaccountsSeeder::class,
                AccounthistoricalbalancesSeeder::class,
                HistoricalbalancesSeeder::class,
                SetupAccountsSeeder::class,
                // Institution1AccountsSeeder_2025_07_18_025945::class, // Uses GL_accounts table which doesn't exist

                // Loans
                LoansSeeder::class,
                LoanssummarySeeder::class,
                LoansschedulesSeeder::class,
                LoansarreasSeeder::class,
                LoansoriginatedSeeder::class,
                LoanapprovalsSeeder::class,
                LoanauditlogsSeeder::class,
                LoanstagesSeeder::class,
                CurrentloansstagesSeeder::class,
                LoanprocessprogressSeeder::class,
                LoanproductchargesSeeder::class,
                LoanprovisionsettingsSeeder::class,
                LoanimagesSeeder::class,
                LoancollateralSeeder::class,
                LoancollateralsSeeder::class,
                LoanguarantorsSeeder::class,
                GuarantorsSeeder::class,
                CollateralsSeeder::class,
                CollateralTypesSeeder::class,
                MaincollateraltypesSeeder::class,
                CustomcollateralsSeeder::class,
                SampleLoanCollateralSeeder::class,
                // GrouploansSeeder::class, // Table Group_loans doesn't exist
                MaendeleoloansSeeder::class,
                SettledloansSeeder::class,
                ShortlongtermloansSeeder::class,
                ApproversofloansstagesSeeder::class,

                // Banking and Transactions
                BanksSeeder::class,
                BankaccountsSeeder::class,
                BanktransactionsSeeder::class,
                BanktransfersSeeder::class,
                BankstatementsstagingtableSeeder::class,
                TransactionsSeeder::class,
                TransactionauditlogsSeeder::class,
                // TransactionreconciliationsSeeder::class, // Table transaction_reconciliations doesn't exist
                // TransactionretrylogsSeeder::class, // Table transaction_retry_logs doesn't exist
                TransactionreversalsSeeder::class,
                ReconciledtransactionsSeeder::class,
                ReconciliationstagingtableSeeder::class,
                ImbanktransactionsSeeder::class,
                GepgtransactionsSeeder::class,
                InternaltransfersSeeder::class,

                // Cash Management
                TillsSeeder::class,
                TilltransactionsSeeder::class,
                TillreconciliationsSeeder::class,
                // TillCashManagementSeeder::class, // User creation conflicts
                TellerendofdaypositionsSeeder::class,
                TellersSeeder::class,
                CashmovementsSeeder::class,
                CashflowconfigurationsSeeder::class,
                CashInTransitProvidersSeeder::class,
                VaultsSeeder::class,
                StrongroomledgersSeeder::class,
                SecuritytransportlogsSeeder::class,

                // Payments and Billing
                PaymentmethodsSeeder::class,
                PaymentsSeeder::class,
                PaymentnotificationsSeeder::class,
                BillsSeeder::class,
                BillingcyclesSeeder::class,
                OrdersSeeder::class,
                ChequesSeeder::class,
                ChequebooksSeeder::class,

                // Shares and Investments
                // SharesSeeder::class, // Table shares doesn't exist
                ShareownershipSeeder::class,
                SharetransfersSeeder::class,
                SharewithdrawalsSeeder::class,
                IssuedsharesSeeder::class,
                InvestmentslistSeeder::class,
                InvestmenttypesSeeder::class,
                DividendsSeeder::class,
                // ContributionsSeeder::class, // Table Contributions doesn't exist

                // Expenses and Budget
                ExpensesSeeder::class,
                ExpenseapprovalsSeeder::class,
                BudgetmanagementsSeeder::class,
                BudgetapproversSeeder::class,
                MainbudgetSeeder::class,
                MainbudgetpendingSeeder::class,

                // HR and Employees
                EmployeesSeeder::class,
                EmployeerolesSeeder::class,
                EmployeerequestsSeeder::class,
                EmployeefilesSeeder::class,
                PayrollsSeeder::class,
                LeavesSeeder::class,
                LeavemanagementSeeder::class,
                BenefitsSeeder::class,
                HiresapprovalsSeeder::class,
                InterviewsSeeder::class,
                JobpostingsSeeder::class,

                // Approvals and Committees
                ApprovalsSeeder::class, // Basic approval data
                ApprovalactionsSeeder::class,
                ApprovalcommentsSeeder::class,
                ApprovalmatrixconfigsSeeder::class,
                CommitteesSeeder::class,
                CommitteemembersSeeder::class,
                CommitteeSeeder::class, // Committee data
                // CommitteemembershipsSeeder::class, // Table doesn't exist
                CommitteeapprovalsSeeder::class,

                // Complaints and Support
                ComplaintCategoriesSeeder::class,
                ComplaintStatusesSeeder::class,
                ComplaintsSeeder::class,

                // Reports and Analytics
                ReportsSeeder::class,
                ScheduledreportsSeeder::class,
                DatafeedsSeeder::class,
                FinancialdataSeeder::class,
                FinancialpositionSeeder::class,
                FinancialratiosSeeder::class,
                AnalysissessionsSeeder::class,
                ScoresSeeder::class,

                // Insurance and Charges
                ChargesSeeder::class,
                ChargeslistSeeder::class,
                InsurancesSeeder::class,
                InsurancelistSeeder::class,
                InterestpayablesSeeder::class,

                // Assets and Inventory
                AssetslistSeeder::class,
                AsseturlSeeder::class,
                PpesSeeder::class,
                // PpePermissionsSeeder::class, // Permissions ID conflict
                InventoriesSeeder::class,
                LandedpropertytypesSeeder::class,
                MovablepropertytypesSeeder::class,

                // Vendors and Contracts
                VendorsSeeder::class,
                ContractmanagementsSeeder::class,
                TendersSeeder::class,
                PurchasesSeeder::class,

                // Notifications and Communication
                NotificationsSeeder::class,
                NotificationlogsSeeder::class,
                EmailsSeeder::class, // Email templates
                MandatorysavingsnotificationsSeeder::class,
                QueryresponsesSeeder::class,

                // System and Audit
                AuditlogsSeeder::class,
                UseractionlogsSeeder::class,
                ApikeysSeeder::class,
                ProcessCodeConfigsSeeder::class,
                CallbackSeeder::class,
                OnboardingSeeder::class,
                AiinteractionsSeeder::class,

                // Locations (commented out - no tables exist)
                // RegionsSeeder::class, // Location data
                // DistrictsSeeder::class, // Location data
                // WardsSeeder::class, // Location data

                // Other Configurations
                CurrenciesSeeder::class,
                DocumenttypesSeeder::class,
                MobileNetworksSeeder::class,
                MnosSeeder::class,
                TaxesSeeder::class,
                StandinginstructionsSeeder::class,
                LockedamountsSeeder::class,
                LossreservesSeeder::class,
                MandatorySavingsSettingsSeeder::class,
                MandatorysavingstrackingSeeder::class,
                UnearneddeferredrevenueSeeder::class,
                PayablesSeeder::class,
                ReceivablesSeeder::class,
                // DashboardTableSeeder::class, // Duplicate DataFeed entries
                TemppermissionsSeeder::class,

                // Specific Account Seeders - Tables don't exist
                // ADMINISTRATIVEEXPENSESSeeder::class,
                // ASSETRECEIVABLESSeeder::class,
                // AUDITANDCOMPLIANCESeeder::class,
                // CASHANDCASHEQUIVALENTSSeeder::class,
                // DEPRECIATIONSeeder::class,
                // GENERALRESERVESSeeder::class,
                // INCOMEACCOUNTRECEIVABLESeeder::class,
                // INCOMEFROMINVESTMENTSSeeder::class,
                // INCOMEFROMLOANSSeeder::class,
                // LIABILITYSUSPENSEACCOUNTSSeeder::class,
                // LOANPORTFOLIOSeeder::class,
                // MEMBERSSAVINGSANDDEPOSITSSeeder::class,
                // OTHERINCOMESeeder::class,
                // RETAINEDEARNINGSSeeder::class,
                // REVALUATIONRESERVESeeder::class,
                // SALARIESANDWAGESSeeder::class,
                // SHARECAPITALSeeder::class,
                // STATUTORYRESERVESSeeder::class,
                // TRADEANDOTHERPAYABLESSeeder::class,

                // Organization Structure
                // SaccosOrganizationalStructureSeeder::class, // Duplicate departments
                // SaccosStructureSeeder::class, // Duplicate departments
                GroupsSeeder::class,
                MeetingsSeeder::class,
                MeetingattendanceSeeder::class,
                MeetingdocumentsSeeder::class,
                LeadershipsSeeder::class,
                ApplicantsSeeder::class,
                InstitutionfilesSeeder::class,
                ProjectSeeder::class,
                EntriesSeeder::class,
                EntriesamountSeeder::class,
                
                // Verification seeder - runs last to ensure everything is set up correctly
                VerifySetupSeeder::class,
                
                // Super Admin Permissions - ALWAYS RUN LAST
                // Assigns all permissions to User ID 1 (IT Manager)
                AssignSuperAdminPermissionsSeeder::class,
            ];
            
            $totalSeeders = count($seeders);
            
            // Run each seeder individually with error handling
            foreach ($seeders as $index => $seederClass) {
                $seederNumber = $index + 1;
                $seederName = class_basename($seederClass);
                
                try {
                    // Log and display progress
                    $message = "[{$seederNumber}/{$totalSeeders}] Running {$seederName}...";
                    Log::info($message);
                    echo $message . "\n";
                    
                    // Run the seeder
                    $this->call($seederClass);
                    
                    $successfulSeeders++;
                    
                    // Log success
                    $successMessage = "✅ [{$seederNumber}/{$totalSeeders}] {$seederName} completed successfully";
                    Log::info($successMessage);
                    echo $successMessage . "\n";
                    
                    // Check critical tables after key seeders
                    if (in_array($seederClass, [BranchesSeeder::class, UsersSeeder::class])) {
                        $this->verifyData($seederName);
                    }
                    
                } catch (\Exception $e) {
                    // Capture the error
                    $errorMessage = "❌ [{$seederNumber}/{$totalSeeders}] {$seederName} FAILED: " . $e->getMessage();
                    $failedSeeders[] = [
                        'seeder' => $seederName,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ];
                    
                    // Log the error
                    Log::error($errorMessage);
                    Log::error("Stack trace: " . $e->getTraceAsString());
                    
                    // Display the error
                    echo $errorMessage . "\n";
                    echo "Stack trace logged to: storage/logs/laravel.log\n";
                    
                    // Continue with next seeder instead of stopping
                    continue;
                }
            }
            
            // Re-enable foreign key checks
            DB::statement('SET session_replication_role = DEFAULT;');
            
            // Final summary
            $this->displaySummary($totalSeeders, $successfulSeeders, $failedSeeders);
            
            // Verify final data state
            $this->verifyFinalData();
            
            // If there were failures, throw exception to prevent silent failure
            if (!empty($failedSeeders)) {
                $errorDetails = json_encode($failedSeeders, JSON_PRETTY_PRINT);
                throw new \Exception("Database seeding completed with {$successfulSeeders}/{$totalSeeders} successful. Failed seeders:\n{$errorDetails}");
            }
            
        } catch (\Exception $e) {
            // Re-enable foreign key checks
            DB::statement('SET session_replication_role = DEFAULT;');
            
            // Log the final error
            Log::error('=== DATABASE SEEDING FAILED ===');
            Log::error($e->getMessage());
            
            // Display the error
            echo "\n=== DATABASE SEEDING FAILED ===\n";
            echo $e->getMessage() . "\n";
            echo "Check storage/logs/laravel.log for detailed error information\n";
            
            // Re-throw to ensure the command fails with non-zero exit code
            throw $e;
        }
    }
    
    /**
     * Verify data after critical seeders
     */
    protected function verifyData($seederName)
    {
        try {
            $branchCount = DB::table('branches')->count();
            $userCount = DB::table('users')->count();
            
            $message = "Data check after {$seederName}: Branches={$branchCount}, Users={$userCount}";
            Log::info($message);
            echo "  → {$message}\n";
        } catch (\Exception $e) {
            Log::warning("Could not verify data: " . $e->getMessage());
        }
    }
    
    /**
     * Display seeding summary
     */
    protected function displaySummary($total, $successful, $failed)
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "DATABASE SEEDING SUMMARY\n";
        echo str_repeat('=', 60) . "\n";
        echo "Total seeders: {$total}\n";
        echo "Successful: {$successful}\n";
        echo "Failed: " . count($failed) . "\n";
        
        if (!empty($failed)) {
            echo "\nFailed seeders:\n";
            foreach ($failed as $failure) {
                echo "  - {$failure['seeder']}: {$failure['error']}\n";
            }
        }
        
        echo str_repeat('=', 60) . "\n";
        
        // Log the summary
        Log::info("Seeding Summary: {$successful}/{$total} successful, " . count($failed) . " failed");
    }
    
    /**
     * Verify final data state
     */
    protected function verifyFinalData()
    {
        try {
            echo "\nFinal Database State:\n";
            echo str_repeat('-', 40) . "\n";
            
            $tables = [
                'users' => 'Users',
                'branches' => 'Branches',
                'institutions' => 'Institutions',
                'clients' => 'Clients',
                'accounts' => 'Accounts',
                'loans' => 'Loans',
                'transactions' => 'Transactions'
            ];
            
            foreach ($tables as $table => $label) {
                try {
                    $count = DB::table($table)->count();
                    $message = "{$label}: {$count}";
                    echo "{$message}\n";
                    Log::info("Final count - {$message}");
                } catch (\Exception $e) {
                    echo "{$label}: Error counting\n";
                }
            }
            
            echo str_repeat('-', 40) . "\n";
            
            // Check if critical data exists
            $branchCount = DB::table('branches')->count();
            $userCount = DB::table('users')->count();
            
            if ($branchCount == 0) {
                Log::warning("WARNING: No branches in database after seeding!");
                echo "⚠️  WARNING: No branches in database after seeding!\n";
            }
            
            if ($userCount == 0) {
                Log::warning("WARNING: No users in database after seeding!");
                echo "⚠️  WARNING: No users in database after seeding!\n";
            }
            
        } catch (\Exception $e) {
            Log::error("Error verifying final data: " . $e->getMessage());
            echo "Error verifying final data\n";
        }
    }
}