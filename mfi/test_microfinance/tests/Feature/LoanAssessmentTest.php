<?php

namespace Tests\Feature;

use App\Models\LoansModel;
use App\Models\User;
use App\Models\ClientsModel;
use App\Models\Loan_sub_products;
use App\Services\LoanAssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoanAssessmentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $client;
    protected $loanProduct;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'branch_id' => 12345
        ]);
        
        $this->client = ClientsModel::factory()->create([
            'client_number' => 'CLI001',
            'branch_id' => 12345
        ]);
        
        $this->loanProduct = Loan_sub_products::factory()->create([
            'sub_product_id' => 'PROD001',
            'principle_min_value' => 100000,
            'principle_max_value' => 10000000,
            'min_term' => 6,
            'max_term' => 60,
            'interest_value' => 12
        ]);
    }

    public function test_loan_officer_can_assess_loan()
    {
        $loan = LoansModel::factory()->create([
            'client_number' => $this->client->client_number,
            'branch_id' => $this->user->branch_id,
            'loan_sub_product' => $this->loanProduct->sub_product_id,
            'principle' => 500000,
            'tenure' => 12,
            'interest' => 12,
            'status' => 'PENDING'
        ]);

        $this->actingAs($this->user)
             ->post("/loans/{$loan->id}/assess", [
                 'principle' => 500000,
                 'tenure' => 12,
                 'collateral_value' => 750000,
                 'business_income' => 200000,
                 'monthly_expenses' => 100000,
                 'business_age' => 3,
                 'daily_sales' => 5000,
                 'cost_of_goods_sold' => 100000,
                 'operating_expenses' => 50000,
                 'monthly_taxes' => 10000,
                 'other_expenses' => 20000,
                 'available_funds' => 150000,
                 'business_name' => 'Test Business',
                 'business_category' => 'RETAIL',
                 'business_type' => 'General Store',
                 'interest_method' => 'flat',
                 'amortization_method' => 'equal_installments'
             ])
             ->assertStatus(200);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'principle' => 500000,
            'tenure' => 12
        ]);
    }

    public function test_loan_approval_workflow()
    {
        $loan = LoansModel::factory()->create([
            'client_number' => $this->client->client_number,
            'branch_id' => $this->user->branch_id,
            'loan_sub_product' => $this->loanProduct->sub_product_id,
            'principle' => 500000,
            'tenure' => 12,
            'interest' => 12,
            'status' => 'PENDING'
        ]);

        $this->actingAs($this->user)
             ->post("/loans/{$loan->id}/approve", [
                 'health' => 'GOOD',
                 'comments' => 'Loan approved after thorough assessment'
             ])
             ->assertStatus(200);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'APPROVED',
            'heath' => 'GOOD'
        ]);
    }

    public function test_loan_rejection_workflow()
    {
        $loan = LoansModel::factory()->create([
            'client_number' => $this->client->client_number,
            'branch_id' => $this->user->branch_id,
            'loan_sub_product' => $this->loanProduct->sub_product_id,
            'principle' => 500000,
            'tenure' => 12,
            'interest' => 12,
            'status' => 'PENDING'
        ]);

        $this->actingAs($this->user)
             ->post("/loans/{$loan->id}/reject", [
                 'reason' => 'Insufficient collateral',
                 'comments' => 'Collateral value does not meet requirements'
             ])
             ->assertStatus(200);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'REJECTED'
        ]);
    }

    public function test_loan_disbursement_workflow()
    {
        $loan = LoansModel::factory()->create([
            'client_number' => $this->client->client_number,
            'branch_id' => $this->user->branch_id,
            'loan_sub_product' => $this->loanProduct->sub_product_id,
            'principle' => 500000,
            'tenure' => 12,
            'interest' => 12,
            'status' => 'APPROVED'
        ]);

        $this->actingAs($this->user)
             ->post("/loans/{$loan->id}/disburse", [
                 'disbursement_date' => now()->format('Y-m-d'),
                 'bank_account' => '1234567890',
                 'disbursement_method' => 'BANK_TRANSFER'
             ])
             ->assertStatus(200);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'ACTIVE'
        ]);
    }

    public function test_affordability_validation()
    {
        $loan = LoansModel::factory()->create([
            'client_number' => $this->client->client_number,
            'branch_id' => $this->user->branch_id,
            'loan_sub_product' => $this->loanProduct->sub_product_id,
            'principle' => 2000000, // High amount
            'tenure' => 12,
            'interest' => 12,
            'status' => 'PENDING'
        ]);

        $this->actingAs($this->user)
             ->post("/loans/{$loan->id}/assess", [
                 'principle' => 2000000,
                 'tenure' => 12,
                 'available_funds' => 100000, // Low income
                 'business_income' => 150000,
                 'monthly_expenses' => 100000,
                 'business_age' => 3,
                 'daily_sales' => 5000,
                 'cost_of_goods_sold' => 100000,
                 'operating_expenses' => 50000,
                 'monthly_taxes' => 10000,
                 'other_expenses' => 20000,
                 'business_name' => 'Test Business',
                 'business_category' => 'RETAIL',
                 'business_type' => 'General Store',
                 'interest_method' => 'flat',
                 'amortization_method' => 'equal_installments'
             ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['principle']);
    }

    public function test_collateral_validation()
    {
        $loan = LoansModel::factory()->create([
            'client_number' => $this->client->client_number,
            'branch_id' => $this->user->branch_id,
            'loan_sub_product' => $this->loanProduct->sub_product_id,
            'principle' => 1000000,
            'tenure' => 12,
            'interest' => 12,
            'status' => 'PENDING'
        ]);

        $this->actingAs($this->user)
             ->post("/loans/{$loan->id}/assess", [
                 'principle' => 1000000,
                 'tenure' => 12,
                 'collateral_value' => 500000, // Low collateral
                 'business_income' => 200000,
                 'monthly_expenses' => 100000,
                 'business_age' => 3,
                 'daily_sales' => 5000,
                 'cost_of_goods_sold' => 100000,
                 'operating_expenses' => 50000,
                 'monthly_taxes' => 10000,
                 'other_expenses' => 20000,
                 'available_funds' => 150000,
                 'business_name' => 'Test Business',
                 'business_category' => 'RETAIL',
                 'business_type' => 'General Store',
                 'interest_method' => 'flat',
                 'amortization_method' => 'equal_installments'
             ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['collateral_value']);
    }

    public function test_risk_assessment_calculation()
    {
        $assessmentService = app(LoanAssessmentService::class);
        
        $loan = LoansModel::factory()->create([
            'client_number' => $this->client->client_number,
            'branch_id' => $this->user->branch_id,
            'loan_sub_product' => $this->loanProduct->sub_product_id,
            'principle' => 500000,
            'tenure' => 12,
            'interest' => 12,
            'status' => 'PENDING',
            'collateral_value' => 750000,
            'available_funds' => 150000,
            'business_age' => 3,
            'daily_sales' => 5000,
            'cost_of_goods_sold' => 100000,
            'operating_expenses' => 50000,
            'monthly_taxes' => 10000,
            'other_expenses' => 20000
        ]);

        $assessment = $assessmentService->assessLoan($loan->id);

        $this->assertArrayHasKey('risk_score', $assessment);
        $this->assertArrayHasKey('affordability', $assessment);
        $this->assertArrayHasKey('recommendation', $assessment);
        $this->assertArrayHasKey('conditions', $assessment);
    }

    public function test_audit_log_creation()
    {
        $loan = LoansModel::factory()->create([
            'client_number' => $this->client->client_number,
            'branch_id' => $this->user->branch_id,
            'loan_sub_product' => $this->loanProduct->sub_product_id,
            'principle' => 500000,
            'tenure' => 12,
            'interest' => 12,
            'status' => 'PENDING'
        ]);

        $this->actingAs($this->user)
             ->post("/loans/{$loan->id}/approve", [
                 'health' => 'GOOD'
             ]);

        $this->assertDatabaseHas('loan_audit_logs', [
            'loan_id' => $loan->id,
            'action' => 'LOAN_APPROVED',
            'user_id' => $this->user->id
        ]);
    }

    public function test_unauthorized_access_prevention()
    {
        $loan = LoansModel::factory()->create([
            'client_number' => $this->client->client_number,
            'branch_id' => 99999, // Different branch
            'loan_sub_product' => $this->loanProduct->sub_product_id,
            'principle' => 500000,
            'tenure' => 12,
            'interest' => 12,
            'status' => 'PENDING'
        ]);

        $this->actingAs($this->user)
             ->post("/loans/{$loan->id}/approve", [
                 'health' => 'GOOD'
             ])
             ->assertStatus(403);
    }
} 