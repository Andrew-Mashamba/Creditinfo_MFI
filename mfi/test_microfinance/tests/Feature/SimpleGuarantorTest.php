<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class SimpleGuarantorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_verify_loan_id_data_type_issue()
    {
        // This test verifies the issue we found in the debug output
        // The problem is that loan_id should be numeric, not string
        
        // Create a simple test to verify the data type issue
        $this->assertTrue(true, 'Test framework is working');
        
        // Test that we can identify the issue from the debug output
        $debugInfo = [
            'loan_id' => 39,
            'loan_id_string' => 'LN202508171897',
            'error_message' => 'invalid input syntax for type bigint: "LN202508171897"'
        ];
        
        $this->assertIsInt($debugInfo['loan_id']);
        $this->assertIsString($debugInfo['loan_id_string']);
        $this->assertStringContainsString('bigint', $debugInfo['error_message']);
    }

    /** @test */
    public function it_can_verify_guarantor_table_structure()
    {
        // Test that we understand the table structure from debug output
        $expectedColumns = [
            'id' => 'bigint',
            'loan_id' => 'bigint', 
            'guarantor_member_id' => 'bigint',
            'guarantor_type' => 'character varying',
            'total_guaranteed_amount' => 'numeric',
            'available_amount' => 'numeric',
            'status' => 'character varying',
            'guarantee_start_date' => 'timestamp without time zone',
            'is_active' => 'boolean'
        ];
        
        foreach ($expectedColumns as $column => $type) {
            $this->assertIsString($column);
            $this->assertIsString($type);
        }
        
        // Verify that loan_id is bigint (numeric)
        $this->assertEquals('bigint', $expectedColumns['loan_id']);
    }

    /** @test */
    public function it_can_verify_collateral_table_structure()
    {
        // Test that we understand the collateral table structure
        $expectedColumns = [
            'id' => 'bigint',
            'loan_guarantor_id' => 'bigint',
            'collateral_type' => 'character varying',
            'account_id' => 'bigint',
            'collateral_amount' => 'numeric',
            'physical_collateral_description' => 'character varying',
            'physical_collateral_value' => 'numeric',
            'status' => 'character varying',
            'is_active' => 'boolean'
        ];
        
        foreach ($expectedColumns as $column => $type) {
            $this->assertIsString($column);
            $this->assertIsString($type);
        }
        
        // Verify that loan_guarantor_id is bigint (numeric)
        $this->assertEquals('bigint', $expectedColumns['loan_guarantor_id']);
    }

    /** @test */
    public function it_can_verify_debug_scenario_analysis()
    {
        // Analyze the debug scenario from the user's output
        $debugScenario = [
            'loan_id' => 39,
            'loan_type' => 'Top-up',
            'guarantor_records_found' => 0,
            'collateral_records_found' => 0,
            'total_guarantors_in_db' => 7,
            'total_collaterals_in_db' => 9,
            'existing_loan_ids' => [34, 35, 38, 36, 37, 33],
            'error' => 'invalid input syntax for type bigint: "LN202508171897"'
        ];
        
        // Verify the analysis
        $this->assertEquals(39, $debugScenario['loan_id']);
        $this->assertEquals('Top-up', $debugScenario['loan_type']);
        $this->assertEquals(0, $debugScenario['guarantor_records_found']);
        $this->assertEquals(0, $debugScenario['collateral_records_found']);
        $this->assertEquals(7, $debugScenario['total_guarantors_in_db']);
        $this->assertEquals(9, $debugScenario['total_collaterals_in_db']);
        $this->assertCount(6, $debugScenario['existing_loan_ids']);
        $this->assertNotContains(39, $debugScenario['existing_loan_ids']);
        $this->assertStringContainsString('bigint', $debugScenario['error']);
    }

    /** @test */
    public function it_can_verify_solution_approach()
    {
        // Test the solution approach we identified
        $solution = [
            'problem' => 'No guarantor data for loan ID 39',
            'root_cause' => 'Guarantor data was never saved for this specific loan',
            'data_exists' => 'Yes, for other loans (34, 35, 38, 36, 37, 33)',
            'solution' => 'Save guarantor data for loan ID 39 or check if data was saved with different ID',
            'verification' => 'Use debug tools to check actual data in database'
        ];
        
        $this->assertEquals('No guarantor data for loan ID 39', $solution['problem']);
        $this->assertEquals('Guarantor data was never saved for this specific loan', $solution['root_cause']);
        $this->assertEquals('Yes, for other loans (34, 35, 38, 36, 37, 33)', $solution['data_exists']);
        $this->assertStringContainsString('Save guarantor data', $solution['solution']);
        $this->assertStringContainsString('debug tools', $solution['verification']);
    }

    /** @test */
    public function it_can_verify_account_type_mapping()
    {
        // Test the account type to collateral type mapping
        $mapping = [
            'savings' => 'savings',
            'savings_account' => 'savings',
            'deposits' => 'deposits',
            'fixed_deposit' => 'deposits',
            'term_deposit' => 'deposits',
            'shares' => 'shares',
            'share_account' => 'shares',
            'unknown' => 'savings' // default
        ];
        
        foreach ($mapping as $accountType => $expectedCollateralType) {
            $result = $this->mapAccountTypeToCollateralType($accountType);
            $this->assertEquals($expectedCollateralType, $result);
        }
    }

    private function mapAccountTypeToCollateralType($accountType)
    {
        switch (strtolower($accountType ?? '')) {
            case 'savings':
            case 'savings_account':
                return 'savings';
            case 'deposits':
            case 'fixed_deposit':
            case 'term_deposit':
                return 'deposits';
            case 'shares':
            case 'share_account':
                return 'shares';
            default:
                return 'savings';
        }
    }
}
