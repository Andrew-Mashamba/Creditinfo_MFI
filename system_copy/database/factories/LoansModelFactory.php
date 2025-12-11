<?php

namespace Database\Factories;

use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\Loan_sub_products;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoansModelFactory extends Factory
{
    protected $model = LoansModel::class;

    public function definition()
    {
        return [
            'loan_id' => 'LOAN' . $this->faker->unique()->numberBetween(1000, 9999),
            'loan_account_number' => 'ACC' . $this->faker->unique()->numberBetween(10000, 99999),
            'loan_sub_product' => Loan_sub_products::factory(),
            'client_number' => ClientsModel::factory(),
            'guarantor' => $this->faker->optional()->name,
            'institution_id' => $this->faker->numberBetween(1, 10),
            'branch_id' => $this->faker->numberBetween(1, 5),
            'principle' => $this->faker->numberBetween(100000, 5000000),
            'interest' => $this->faker->randomFloat(2, 8, 24),
            'business_name' => $this->faker->company,
            'business_age' => $this->faker->numberBetween(1, 20),
            'business_category' => $this->faker->randomElement(['RETAIL', 'WHOLESALE', 'MANUFACTURING', 'SERVICES', 'AGRICULTURE', 'CONSTRUCTION']),
            'business_type' => $this->faker->word,
            'business_licence_number' => $this->faker->optional()->numerify('LIC###'),
            'business_tin_number' => $this->faker->optional()->numerify('TIN####'),
            'business_inventory' => $this->faker->optional()->numberBetween(50000, 1000000),
            'cash_at_hand' => $this->faker->optional()->numberBetween(10000, 500000),
            'daily_sales' => $this->faker->optional()->numberBetween(1000, 50000),
            'cost_of_goods_sold' => $this->faker->optional()->numberBetween(50000, 500000),
            'available_funds' => $this->faker->optional()->numberBetween(50000, 300000),
            'operating_expenses' => $this->faker->optional()->numberBetween(20000, 200000),
            'monthly_taxes' => $this->faker->optional()->numberBetween(5000, 50000),
            'other_expenses' => $this->faker->optional()->numberBetween(10000, 100000),
            'collateral_value' => $this->faker->optional()->numberBetween(100000, 2000000),
            'collateral_location' => $this->faker->optional()->address,
            'collateral_description' => $this->faker->optional()->sentence,
            'collateral_type' => $this->faker->optional()->randomElement(['REAL_ESTATE', 'VEHICLE', 'EQUIPMENT', 'INVENTORY', 'CASH', 'SECURITIES']),
            'tenure' => $this->faker->numberBetween(6, 60),
            'principle_amount' => $this->faker->optional()->numberBetween(100000, 5000000),
            'interest_method' => $this->faker->randomElement(['flat', 'reducing', 'compound']),
            'bank_account_number' => $this->faker->optional()->numerify('BANK####'),
            'bank' => $this->faker->optional()->company,
            'LoanPhoneNo' => $this->faker->optional()->numerify('07########'),
            'status' => $this->faker->randomElement(['PENDING', 'APPROVED', 'REJECTED', 'ACTIVE', 'DEFAULTED', 'PAID']),
            'loan_status' => $this->faker->randomElement(['NORMAL', 'OVERDUE', 'DEFAULTED']),
            'restructure_loanId' => $this->faker->optional()->numerify('REST####'),
            'heath' => $this->faker->randomElement(['GOOD', 'FAIR', 'POOR']),
            'phone_number' => $this->faker->optional()->numerify('07########'),
            'pay_method' => $this->faker->optional()->randomElement(['CASH', 'BANK_TRANSFER', 'MOBILE_MONEY']),
            'days_in_arrears' => $this->faker->optional()->numberBetween(0, 365),
            'total_days_in_arrears' => $this->faker->optional()->numberBetween(0, 1000),
            'arrears_in_amount' => $this->faker->optional()->numberBetween(0, 100000),
            'supervisor_id' => $this->faker->optional()->numberBetween(1, 10),
            'supervisor_name' => $this->faker->optional()->name,
            'client_id' => $this->faker->optional()->numberBetween(1, 100),
            'relationship' => $this->faker->optional()->randomElement(['SPOUSE', 'PARENT', 'SIBLING', 'FRIEND']),
            'loan_type' => $this->faker->optional()->randomElement(['PERSONAL', 'BUSINESS', 'AGRICULTURE']),
            'future_interest' => $this->faker->optional()->numberBetween(0, 100000),
            'total_principle' => $this->faker->optional()->numberBetween(100000, 5000000),
            'loan_type_2' => $this->faker->randomElement(['New', 'Top Up', 'Refinance']),
            'stage_id' => $this->faker->optional()->numberBetween(1, 10),
            'stage' => $this->faker->optional()->randomElement(['PENDING', 'BRANCH COMMITTEE', 'CREDIT ANALYST', 'HQ COMMITTEE', 'CREDIT ADMINISTRATION']),
            'loan_type_3' => $this->faker->optional()->randomElement(['Individual', 'Group', 'Corporate']),
            'take_home' => $this->faker->optional()->numberBetween(50000, 500000),
            'approved_loan_value' => $this->faker->optional()->numberBetween(100000, 5000000),
            'approved_term' => $this->faker->optional()->numberBetween(6, 60),
            'amount_to_be_credited' => $this->faker->optional()->numberBetween(100000, 5000000),
            'disbursement_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'bank_account' => $this->faker->optional()->numerify('ACC####'),
            'interest_account_number' => $this->faker->optional()->numerify('INT####'),
            'charge_account_number' => $this->faker->optional()->numerify('CHG####'),
            'insurance_account_number' => $this->faker->optional()->numerify('INS####'),
            'selectedLoan' => $this->faker->optional()->numerify('SEL####'),
            'client_type' => $this->faker->optional()->randomElement(['Individual', 'Corporate', 'Group']),
            'group_number' => $this->faker->optional()->numerify('GRP####'),
            'group_id' => $this->faker->optional()->numberBetween(1, 50),
            'parent_loan_id' => $this->faker->optional()->numerify('PAR####'),
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'PENDING',
            ];
        });
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'APPROVED',
            ];
        });
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'ACTIVE',
                'disbursement_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            ];
        });
    }

    public function defaulted()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'DEFAULTED',
                'loan_status' => 'DEFAULTED',
                'days_in_arrears' => $this->faker->numberBetween(90, 365),
            ];
        });
    }

    public function highRisk()
    {
        return $this->state(function (array $attributes) {
            return [
                'heath' => 'POOR',
                'days_in_arrears' => $this->faker->numberBetween(30, 90),
                'collateral_value' => $attributes['principle'] * 0.5, // Low collateral
            ];
        });
    }

    public function lowRisk()
    {
        return $this->state(function (array $attributes) {
            return [
                'heath' => 'GOOD',
                'days_in_arrears' => 0,
                'collateral_value' => $attributes['principle'] * 1.5, // High collateral
                'business_age' => $this->faker->numberBetween(5, 20),
            ];
        });
    }
} 