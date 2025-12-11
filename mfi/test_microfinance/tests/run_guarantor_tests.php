<?php

/**
 * Test Runner for Guarantor and Collateral Functionality
 * 
 * This script runs all the tests related to guarantor and collateral functionality
 * and provides a comprehensive report.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use Illuminate\Support\Facades\DB;
use Tests\Feature\GuarantosComponentTest;
use Tests\Feature\GuarantorDatabaseTest;
use Tests\Feature\GuarantorDataRetrievalTest;

class GuarantorTestRunner
{
    private $results = [];
    private $startTime;

    public function run()
    {
        $this->startTime = microtime(true);
        
        echo "🧪 Starting Guarantor and Collateral Tests...\n";
        echo "=============================================\n\n";

        // Run component tests
        $this->runComponentTests();
        
        // Run database tests
        $this->runDatabaseTests();
        
        // Run data retrieval tests
        $this->runDataRetrievalTests();
        
        // Generate report
        $this->generateReport();
    }

    private function runComponentTests()
    {
        echo "📱 Testing Guarantos Component...\n";
        echo "--------------------------------\n";
        
        $tests = [
            'it_can_load_existing_guarantor_data' => 'Loading existing guarantor data',
            'it_can_save_guarantor_and_collateral_data' => 'Saving guarantor and collateral data',
            'it_validates_required_fields' => 'Validating required fields',
            'it_validates_third_party_guarantor_fields' => 'Validating third party guarantor fields',
            'it_validates_physical_collateral_fields_when_enabled' => 'Validating physical collateral fields',
            'it_can_get_all_member_accounts' => 'Getting all member accounts',
            'it_can_commit_collateral' => 'Committing collateral',
            'it_can_refresh_collateral_data' => 'Refreshing collateral data',
            'it_can_toggle_debug_info' => 'Toggling debug info',
            'it_can_refresh_debug_info' => 'Refreshing debug info',
            'it_handles_restructure_loans_correctly' => 'Handling restructure loans',
            'it_handles_missing_loan_data' => 'Handling missing loan data',
            'it_handles_missing_client_data' => 'Handling missing client data',
            'it_handles_missing_account_data' => 'Handling missing account data',
            'it_correctly_maps_account_types_to_collateral_types' => 'Mapping account types to collateral types',
            'it_handles_debug_info_properties' => 'Handling debug info properties',
        ];

        foreach ($tests as $testMethod => $description) {
            $this->runTest(GuarantosComponentTest::class, $testMethod, $description);
        }
        
        echo "\n";
    }

    private function runDatabaseTests()
    {
        echo "🗄️ Testing Database Operations...\n";
        echo "--------------------------------\n";
        
        $tests = [
            'it_can_save_guarantor_data_to_database' => 'Saving guarantor data to database',
            'it_can_save_collateral_data_to_database' => 'Saving collateral data to database',
            'it_can_query_guarantor_data_by_loan_id' => 'Querying guarantor data by loan ID',
            'it_handles_restructure_loan_relationships' => 'Handling restructure loan relationships',
            'it_validates_loan_id_data_types' => 'Validating loan ID data types',
            'it_can_retrieve_all_guarantor_loan_ids' => 'Retrieving all guarantor loan IDs',
            'it_handles_empty_guarantor_tables' => 'Handling empty guarantor tables',
            'it_can_map_account_types_to_collateral_types' => 'Mapping account types to collateral types',
        ];

        foreach ($tests as $testMethod => $description) {
            $this->runTest(GuarantorDatabaseTest::class, $testMethod, $description);
        }
        
        echo "\n";
    }

    private function runDataRetrievalTests()
    {
        echo "🔍 Testing Data Retrieval Issues...\n";
        echo "----------------------------------\n";
        
        $tests = [
            'it_can_identify_missing_guarantor_data_for_specific_loan' => 'Identifying missing guarantor data for specific loan',
            'it_can_verify_loan_id_data_type_consistency' => 'Verifying loan ID data type consistency',
            'it_can_verify_correct_loan_id_usage' => 'Verifying correct loan ID usage',
            'it_can_simulate_the_exact_debug_scenario' => 'Simulating the exact debug scenario',
            'it_can_verify_table_structure_matches_debug_output' => 'Verifying table structure matches debug output',
        ];

        foreach ($tests as $testMethod => $description) {
            $this->runTest(GuarantorDataRetrievalTest::class, $testMethod, $description);
        }
        
        echo "\n";
    }

    private function runTest($testClass, $testMethod, $description)
    {
        try {
            echo "  ✓ {$description}... ";
            
            // Create test instance
            $test = new $testClass();
            $test->setUp();
            
            // Run the test method
            $test->$testMethod();
            
            echo "PASSED ✅\n";
            $this->results[] = [
                'class' => $testClass,
                'method' => $testMethod,
                'description' => $description,
                'status' => 'PASSED',
                'error' => null
            ];
            
        } catch (Exception $e) {
            echo "FAILED ❌\n";
            echo "    Error: " . $e->getMessage() . "\n";
            $this->results[] = [
                'class' => $testClass,
                'method' => $testMethod,
                'description' => $description,
                'status' => 'FAILED',
                'error' => $e->getMessage()
            ];
        }
    }

    private function generateReport()
    {
        $endTime = microtime(true);
        $duration = round($endTime - $this->startTime, 2);
        
        $totalTests = count($this->results);
        $passedTests = count(array_filter($this->results, fn($r) => $r['status'] === 'PASSED'));
        $failedTests = $totalTests - $passedTests;
        
        echo "📊 Test Results Summary\n";
        echo "=======================\n";
        echo "Total Tests: {$totalTests}\n";
        echo "Passed: {$passedTests} ✅\n";
        echo "Failed: {$failedTests} ❌\n";
        echo "Duration: {$duration}s\n";
        echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n\n";

        if ($failedTests > 0) {
            echo "❌ Failed Tests:\n";
            echo "----------------\n";
            foreach ($this->results as $result) {
                if ($result['status'] === 'FAILED') {
                    echo "  • {$result['description']}\n";
                    echo "    Error: {$result['error']}\n\n";
                }
            }
        }

        echo "✅ All tests completed!\n";
    }
}

// Run the tests
$runner = new GuarantorTestRunner();
$runner->run();
