<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\Investment;
use App\Services\BalanceSheetItemIntegrationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Services\TransactionPostingService;
use Livewire\WithPagination;

class Investiments extends Component
{


    public $investmentTypeOr = null;
    public $principalAmount;
    public $investmentDate;

    // Specific fields for each investment type
    public $numberOfShares;
    public $sharePrice;
    public $brokerageFees;
    public $dividendRate;
    public $salePrice;

    public $interestRate;
    public $tenure;
    public $maturityDate;
    public $penalty;

    public $bondType;
    public $couponRate;
    public $bondYield;

    public $fundName;
    public $fundManager;
    public $expenseRatio;
    public $nav;
    public $unitsPurchased,$customer_name;

    public $propertyValue;
    public $location;
    public $purchaseDate,$liability_accounts_payable_code;
    public $annualPropertyTaxes;
    public $rentalIncome;
    public $maintenanceCosts;
    
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create investment account under
    public $other_account_id; // The other account for double-entry (Cash/Bank - credit side)

    public $description;
    public $interestDividendRate;

    public $investmentsPerPage = 10;

    public $account_table_name = 'asset_accounts';
    public $next_code_no = 1104;
    public $category_code_of_account = 1103;
    public $source;
    public $narration;
    public $categoryx;
    public $category = 'investments';
    public $inv_narration;
    public $category_code;
           public $sub_category_code;




    public $investmentsByType = [];

    public $selectedInvestmentId;
    public $editMode = false;

    public function mount()
    {
        $this->investmentsByType = $this->getAllInvestments();


    }

    public function getAllInvestments()
    {
        // Fetch and group investments by type.
        return Investment::all()->groupBy('investment_type')->toArray();
    }

    public function editInvestment($id){
        $this->editMode = true;
        $this->selectedInvestmentId = $id;
    }

    public function submitx()
    {

        if ($this->editMode) {
            $this->edit();
        } else {
            $this->submit();
        }
    }

    public function edit()
    {


        $investmentId = $this->selectedInvestmentId;
        // Retrieve the investment record by ID
        $investment = Investment::findOrFail($investmentId);
        $this->salePrice = $investment->sale_price;

        // Validate the common fields
        $validatedData = $this->validate([
            'investmentTypeOr' => 'required|string',
            'principalAmount' => 'required|numeric|min:0',
            'investmentDate' => 'required|date',
            'inv_narration' => 'required|string',
        ]);

        // Add additional validation rules based on investment type
        switch ($this->investmentTypeOr) {
            case 'shares':
                $validatedData = array_merge($validatedData, $this->validate([
                    'numberOfShares' => 'required|integer|min:1',
                    'sharePrice' => 'required|numeric|min:0',
                    'brokerageFees' => 'required|numeric|min:0',
                    'dividendRate' => 'required|numeric|min:0',
                    'salePrice' => 'nullable|numeric|min:0',
                ]));
                $investmentData = [
                    'investment_type' => 'shares',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                    'number_of_shares' => $this->numberOfShares,
                    'share_price' => $this->sharePrice,
                    'brokerage_fees' => $this->brokerageFees,
                    'dividend_rate' => $this->dividendRate,
                    'sale_price' => $this->salePrice,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1104;
                break;

            case 'fdr':
                $validatedData = array_merge($validatedData, $this->validate([
                    'interestRate' => 'required|numeric|min:0',
                    'tenure' => 'required|integer|min:1',
                    'maturityDate' => 'required|date',
                    'penalty' => 'nullable|numeric|min:0',
                ]));
                $investmentData = [
                    'investment_type' => 'fdr',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                    'interest_rate' => $this->interestRate,
                    'tenure' => $this->tenure,
                    'maturity_date' => $this->maturityDate,
                    'penalty' => $this->penalty,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1105;
                break;

            case 'bonds':
                $validatedData = array_merge($validatedData, $this->validate([
                    'bondType' => 'required|string',
                    'couponRate' => 'required|numeric|min:0',
                    'maturityDate' => 'required|date',
                    'bondYield' => 'nullable|numeric|min:0',
                ]));
                $investmentData = [
                    'investment_type' => 'bonds',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                    'bond_type' => $this->bondType,
                    'coupon_rate' => $this->couponRate,
                    'maturity_date' => $this->maturityDate,
                    'bond_yield' => $this->bondYield,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1106;
                break;

            case 'mutual_funds':
                $validatedData = array_merge($validatedData, $this->validate([
                    'fundName' => 'required|string',
                    'fundManager' => 'required|string',
                ]));
                $investmentData = [
                    'investment_type' => 'mutual_funds',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                    'fund_name' => $this->fundName,
                    'fund_manager' => $this->fundManager,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1107;
                break;

            case 'real_estate':
                $investmentData = [
                    'investment_type' => 'real_estate',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1108;
                break;

            case 'other':
                $investmentData = [
                    'investment_type' => 'other',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1109;
                break;

            default:
                throw new \Exception('Invalid investment type');
        }



        // Update the investment record
        $investment->update($investmentData);





        // Display a success message
        session()->flash('message', 'Investment updated successfully.');
    }


    public function updateInvestment()
    {
        // Logic to update the investment with new values
        $investment = Investment::find($this->selectedInvestmentId);
        $investment->sale_price = $this->salePrice; // Update other fields as necessary
        $investment->save();

        $this->reset(['editMode', 'selectedInvestmentId', 'salePrice']);
        $this->investmentsByType = $this->getAllInvestments(); // Refresh the investments list
    }

    public function deleteInvestment($id)
    {
        Investment::destroy($id);
        $this->investmentsByType = $this->getAllInvestments(); // Refresh the investments list
    }



    public function createNewAccountNumber($major_category_code,$category_code, $sub_category_code,$parent_account){

        // Format the account name
        $formattedAccountName = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $this->customer_name)));
        $formattedAccountName = str_replace(' ', '_', $formattedAccountName);
        $formattedAccountName = strtoupper($formattedAccountName);

        // Create a new account in the category
        $account_number = $this->generate_account_number(auth()->user()->branch, $sub_category_code);

        // Create an entry in the AccountsModel
        AccountsModel::create([
            'account_use' => 'internal',
            'institution_number' => auth()->user()->institution_id,
            'branch_number' => auth()->user()->branch,
            'major_category_code' => $major_category_code,
            'category_code' => $category_code,
            'sub_category_code' => $sub_category_code,
            'account_name' => $formattedAccountName,
            'account_number' => $account_number,
            'notes' => $this->customer_name,
            'account_level' => '3',
            'parent_account_number'=>$parent_account
        ]);

        return $account_number;
    }






    public function submit()
    {

        // Validate the common fields
        $validatedData = $this->validate([
            'investmentTypeOr' => 'required|string',
            'principalAmount' => 'required|numeric|min:0',
            'investmentDate' => 'required|date',
           // 'inv_narration' => 'required|string',
        ]);

     //   dd('hi');

        // Add additional validation rules based on investment type
        $this->customer_name=$this->investmentTypeOr;
        switch ($this->investmentTypeOr) {
            case 'shares':
                $validatedData = array_merge($validatedData, $this->validate([
                    'numberOfShares' => 'required|integer|min:1',
                    'sharePrice' => 'required|numeric|min:0',
                    'brokerageFees' => 'required|numeric|min:0',
                    'dividendRate' => 'required|numeric|min:0',
                    'salePrice' => 'nullable|numeric|min:0',
                ]));

                $investmentData = [
                    'investment_type' => 'shares',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                    'number_of_shares' => $this->numberOfShares,
                    'share_price' => $this->sharePrice,
                    'brokerage_fees' => $this->brokerageFees,
                    'dividend_rate' => $this->dividendRate,
                    'sale_price' => $this->salePrice,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1104; // Corrected to 1104 for shares
                break;



            case 'fdr':
                $validatedData = array_merge($validatedData, $this->validate([
                    'interestRate' => 'required|numeric|min:0',
                    'tenure' => 'required|integer|min:1',
                    'maturityDate' => 'required|date',
                    'penalty' => 'nullable|numeric|min:0',
                ]));
                $investmentData = [
                    'investment_type' => 'fdr',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                    'interest_rate' => $this->interestRate,
                    'tenure' => $this->tenure,
                    'maturity_date' => $this->maturityDate,
                    'penalty' => $this->penalty,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1105; // FDR subcategory
                break;

            case 'bonds':
                $validatedData = array_merge($validatedData, $this->validate([
                    'bondType' => 'required|string',
                    'couponRate' => 'required|numeric|min:0',
                    'maturityDate' => 'required|date',
                    'bondYield' => 'nullable|numeric|min:0',
                ]));
                $investmentData = [
                    'investment_type' => 'bonds',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                    'bond_type' => $this->bondType,
                    'coupon_rate' => $this->couponRate,
                    'maturity_date' => $this->maturityDate,
                    'bond_yield' => $this->bondYield,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1106; // Bonds subcategory
                break;

            case 'mutual_funds':
                $validatedData = array_merge($validatedData, $this->validate([
                    'fundName' => 'required|string',
                    'fundManager' => 'required|string',
                ]));
                $investmentData = [
                    'investment_type' => 'mutual_funds',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                    'fund_name' => $this->fundName,
                    'fund_manager' => $this->fundManager,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1107; // Mutual funds subcategory
                break;

            case 'real_estate':
                $investmentData = [
                    'investment_type' => 'real_estate',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1108; // Real estate subcategory
                break;

            case 'other':
                $investmentData = [
                    'investment_type' => 'other',
                    'principal_amount' => $this->principalAmount,
                    'investment_date' => $this->investmentDate,
                ];

                // Assign category and subcategory codes
                $this->category_code = 1103;
                $this->sub_category_code = 1109; // Other financial instruments subcategory
                break;

            default:
                throw new \Exception('Invalid investment type');
        }



        $source_account=DB::table('accounts')->where('sub_category_code',$this->source)->first();


        $accounts=DB::table('accounts')->where('account_number',
          DB::table('setup_accounts')->where('item','assets')
              ->value('account_number'))->first();


        $investment_account= $this->createNewAccountNumber($accounts->major_category_code,$accounts->category_code, $accounts->sub_category_code,$accounts->account_number);

         $debited_account=AccountsModel::where('account_number',$investment_account)->first();

        $investmentData['cash_account']=$source_account->account_number;
        $investmentData['investment_account']=$investment_account;

        $investment = Investment::create($investmentData);

        // Use Balance Sheet Integration Service to create accounts and post to GL
        $integrationService = new BalanceSheetItemIntegrationService();
        
        try {
            $investmentObj = (object)[
                'id' => $investment->id,
                'purchase_amount' => $this->principalAmount,
                'current_value' => $this->principalAmount,
                'maturity_date' => $this->maturityDate,
                'description' => 'Investment - ' . $this->investmentTypeOr
            ];
            
            $integrationService->createInvestmentAccount(
                $investmentObj,
                $this->parent_account_number,  // Parent account to create investment account under
                $this->other_account_id        // The other account for double-entry (Cash/Bank - credit side)
            );
            
            Log::info('Investment integrated with accounts table', [
                'investment_id' => $investment->id,
                'type' => $this->investmentTypeOr,
                'amount' => $this->principalAmount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to integrate investment with accounts table: ' . $e->getMessage());
        }






        // Clear the form data after submission
        $this->reset([
            'investmentTypeOr',
            'principalAmount',
            'investmentDate',
            'numberOfShares',
            'sharePrice',
            'brokerageFees',
            'dividendRate',
            'salePrice',
            'interestRate',
            'tenure',
            'maturityDate',
            'penalty',
            'bondType',
            'couponRate',
            'bondYield',
            'fundName',
            'fundManager',
        ]);

        // Display a success message
        session()->flash('message', 'Investment submitted successfully.');
    }


    public function createNewAccount()
    {
        // DEPRECATED - Account creation now handled by BalanceSheetItemIntegrationService
        // which uses AccountCreationService as the ONLY authorized account creator
        Log::warning('Deprecated createNewAccount method called in Investiments component');
        return null;
    }

    function luhn_checksum($number) {
        $digits = str_split($number);
        $sum = 0;
        $alt = false;
        for ($i = count($digits) - 1; $i >= 0; $i--) {
            $n = $digits[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }
        return $sum % 10;
    }

    public function generate_account_number($branch_code, $product_code) {

        //sub_category_code
        do {
            // Generate a 5-digit random number for the unique account identifier
            $unique_identifier = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

            // Concatenate branch code, unique identifier, and product code
            $partial_account_number = $branch_code . $unique_identifier . $product_code;

            // Calculate the checksum digit
            $checksum = (10 - $this->luhn_checksum($partial_account_number . '0')) % 10;

            // Form the final 12-digit account number
            $full_account_number = $partial_account_number . $checksum;

            // Check for uniqueness using Laravel's Eloquent model
            $is_unique = !AccountsModel::where('account_number', $full_account_number)->exists();

        } while (!$is_unique);

        return $full_account_number;
    }


    private function debit($reference_number, $debited_account, $credited_account, $amount, $new_amount)
    {
        Log::info("Debit entry", [
            'debited_account' => $debited_account->account_number,
            'amount' => $amount
        ]);

        general_ledger::create([
            'record_on_account_number' => $debited_account->account_number,
            'record_on_account_number_balance' => $new_amount,
            'major_category_code' => $debited_account->major_category_code,
            'category_code' => $debited_account->category_code,
            'sub_category_code' => $debited_account->sub_category_code,
            'sender_name' => $debited_account->account_name,
            'beneficiary_name' => $credited_account->account_name,
            'sender_account_number' => $debited_account->account_number,
            'beneficiary_account_number' => $credited_account->account_number,
            'narration' => $this->narration,
            'credit' => 0,
            'debit' => $amount,  // Debit the actual amount
            'reference_number' => $reference_number,
            'trans_status' => 'Pending Approval', // Status updated once approved
            'trans_status_description' => 'Awaiting Approval',
            'payment_status' => 'Pending',
            'recon_status' => 'Pending',
        ]);
    }

    private function credit($reference_number, $debited_account, $credited_account, $amount, $new_amount)
    {
        Log::info("Credit entry", [
            'credited_account' => $credited_account->account_number,
            'amount' => $amount
        ]);

        general_ledger::create([
            'record_on_account_number' => $credited_account->account_number,
            'record_on_account_number_balance' => $new_amount,
            'major_category_code' => $credited_account->major_category_code,
            'category_code' => $credited_account->category_code,
            'sub_category_code' => $credited_account->sub_category_code,
            'sender_name' => $debited_account->account_name,
            'beneficiary_name' => $credited_account->account_name,
            'sender_account_number' => $debited_account->account_number,
            'beneficiary_account_number' => $credited_account->account_number,
            'narration' => $this->narration,
            'credit' => $amount,  // Credit the actual amount
            'debit' => 0,
            'reference_number' => $reference_number,
            'trans_status' => 'Pending Approval',
            'trans_status_description' => 'Awaiting Approval',
            'payment_status' => 'Pending',
            'recon_status' => 'Pending',
        ]);
    }








    public function updatedInvestmentType($field,$value)
    {

        //$this->investmentTypeOr = $field;
        // Reset the fields when the investment type is changed
        $this->resetSpecificFields();
    }

    public function resetSpecificFields()
    {
        $this->numberOfShares = null;
        $this->sharePrice = null;
        $this->brokerageFees = null;
        $this->dividendRate = null;
        $this->salePrice = null;

        $this->interestRate = null;
        $this->tenure = null;
        $this->maturityDate = null;
        $this->penalty = null;

        $this->bondType = null;
        $this->couponRate = null;
        $this->bondYield = null;

        $this->fundName = null;
        $this->fundManager = null;
        $this->expenseRatio = null;
        $this->nav = null;
        $this->unitsPurchased = null;

        $this->propertyValue = null;
        $this->location = null;
        $this->purchaseDate = null;
        $this->annualPropertyTaxes = null;
        $this->rentalIncome = null;
        $this->maintenanceCosts = null;

        $this->description = null;
        $this->interestDividendRate = null;

    }



    public function createInvestment()
    {
        $this->validate();

        Investment::create([
            'type' => $this->investmentType,
            'principal' => $this->principal,
            'interest_rate' => $this->interestRate,
            'investment_date' => $this->investmentDate,
            'number_of_shares' => $this->investmentType === 'shares' ? $this->numberOfShares : null,
            'share_price' => $this->investmentType === 'shares' ? $this->sharePrice : null,
            'status' => $this->status,
            'early_withdrawal_penalty' => $this->earlyWithdrawalPenalty,
            'description' => $this->investmentType === 'other' ? $this->otherDescription : null,
        ]);

        session()->flash('message', 'Investment created successfully.');
        $this->reset();
        $this->investments = Investment::all();
    }





//    public function mount()
//    {
//        $this->loadInvestments();
//    }

    // Load all investments
    public function loadInvestments()
    {
        $this->investments = Investment::all();
    }




    // Calculate accrued interest for FDRs
    public function calculateInterest($investmentId)
    {
        $investment = Investment::find($investmentId);

        if ($investment->type == 'fdr') {
            $daysElapsed = Carbon::now()->diffInDays(Carbon::parse($investment->investment_date));
            $accruedInterest = ($investment->principal * $investment->interest_rate * $daysElapsed) / 365;
            return round($accruedInterest, 2);
        }

        return 0;
    }



    // Generate report
    public function generateInvestmentReport()
    {
        // This method can gather all investment data and format it as a report.
        // For simplicity, we'll log the report here.
        foreach ($this->investments as $investment) {
            Log::info("Investment Report", [
                'type' => $investment->type,
                'principal' => $investment->principal,
                'accrued_interest' => $this->calculateInterest($investment->id),
                'status' => $investment->status,
                'number_of_shares' => $investment->number_of_shares ?? 0,
            ]);
        }

        session()->flash('message', 'Investment report generated and logged successfully.');
    }

    // Reset input fields
    private function resetInputFields()
    {
        $this->investmentId = null;
        $this->type = '';
        $this->principal = '';
        $this->interestRate = '';
        $this->investmentDate = '';
        $this->numberOfShares = '';
        $this->sharePrice = '';
        $this->status = '';
        $this->isEditing = false;
    }






    // Liquidate shares
    public function liquidateShares($investmentId, $salePrice)
    {
        $investment = Investment::find($investmentId);

        // Ensure the investment is of type shares
        if ($investment && $investment->type == 'shares') {
            // Calculate the total sale value
            $totalSaleValue = $investment->number_of_shares * $salePrice;

            // Use Balance Sheet Integration Service to process liquidation
            $integrationService = new BalanceSheetItemIntegrationService();
            
            try {
                // Process the liquidation through integration service
                $integrationService->processInvestmentMaturity($investment, $totalSaleValue, 'liquidation');
                
                // Update the investment record (remove shares)
                $investment->update([
                    'number_of_shares' => 0,
                    'status' => 'liquidated'
                ]);
                
                // Log the liquidation
                Log::info("Shares liquidated and GL updated", [
                    'investment_id' => $investment->id,
                    'total_sale_value' => $totalSaleValue
                ]);
                
                session()->flash('message', 'Shares liquidated successfully. GL entries posted.');
            } catch (\Exception $e) {
                Log::error('Failed to liquidate shares: ' . $e->getMessage());
                session()->flash('error', 'Failed to liquidate shares: ' . $e->getMessage());
            }

            // Refresh investment list
            $this->loadInvestments();
        } else {
            Log::warning("Attempted to liquidate non-existing or non-share investment", [
                'investment_id' => $investmentId
            ]);
        }
    }


// Liquidate FDR
    public function liquidateFdr($investmentId)
    {
        $investment = Investment::find($investmentId);

        // Ensure the investment is of type FDR
        if ($investment && $investment->type == 'fdr') {
            // Calculate accrued interest up to the current date
            $daysElapsed = Carbon::now()->diffInDays(Carbon::parse($investment->investment_date));
            $accruedInterest = ($investment->principal * $investment->interest_rate * $daysElapsed) / 365;

            // Penalty for early withdrawal (if applicable)
            $penalty = $investment->early_withdrawal_penalty ?? 0;

            // Calculate total proceeds
            $totalProceeds = $investment->principal + $accruedInterest - $penalty;

            // Use Balance Sheet Integration Service to process maturity/liquidation
            $integrationService = new BalanceSheetItemIntegrationService();
            
            try {
                // Process the FDR maturity through integration service
                $integrationService->processInvestmentMaturity($investment, $totalProceeds, 'maturity');
                
                // Mark the FDR as liquidated
                $investment->update(['status' => 'liquidated']);
                
                // Log the liquidation
                Log::info("FDR liquidated and GL updated", [
                    'investment_id' => $investment->id,
                    'total_proceeds' => $totalProceeds,
                    'interest_earned' => $accruedInterest,
                    'penalty' => $penalty
                ]);
                
                session()->flash('message', 'FDR matured successfully. GL entries posted.');
            } catch (\Exception $e) {
                Log::error('Failed to process FDR maturity: ' . $e->getMessage());
                session()->flash('error', 'Failed to process FDR maturity: ' . $e->getMessage());
            }

            // Refresh investment list
            $this->loadInvestments();
        } else {
            Log::warning("Attempted to liquidate non-existing or non-FDR investment", [
                'investment_id' => $investmentId
            ]);
        }
    }



// Liquidate Real Estate
    public function liquidateRealEstate($investmentId, $salePrice)
    {
        $investment = Investment::find($investmentId);

        // Ensure the investment is of type real estate
        if ($investment && $investment->type == 'real_estate') {
            // Calculate the total sale value
            $totalSaleValue = $salePrice; // Sale price is assumed to be the total value of real estate

            // Update the investment record (mark as liquidated)
            $investment->update([
                'status' => 'liquidated'
            ]);

            // Uncomment and implement cash account update if necessary
            /*
            $cashAccount = Account::where('account_type', 'cash')->first();
            $cashAccount->balance += $totalSaleValue;
            $cashAccount->save();
            */

            // Log the liquidation
            Log::info("Real Estate liquidated", [
                'investment_id' => $investment->id,
                'total_sale_value' => $totalSaleValue
            ]);

            // Refresh investment list
            $this->loadInvestments();
        } else {
            Log::warning("Attempted to liquidate non-existing or non-real estate investment", [
                'investment_id' => $investmentId
            ]);
        }
    }


// Liquidate Bonds
    public function liquidateBonds($investmentId)
    {
        $investment = Investment::find($investmentId);

        // Ensure the investment is of type bonds
        if ($investment && $investment->type == 'bonds') {
            // Calculate accrued interest up to the current date
            $daysElapsed = Carbon::now()->diffInDays(Carbon::parse($investment->investment_date));
            $accruedInterest = ($investment->principal * $investment->interest_rate * $daysElapsed) / 365;

            // Calculate total proceeds
            $totalProceeds = $investment->principal + $accruedInterest;

            // Mark the bonds as liquidated
            $investment->update(['status' => 'liquidated']);

            // Uncomment and implement cash account update if necessary
            /*
            $cashAccount = Account::where('account_type', 'cash')->first();
            $cashAccount->balance += $totalProceeds;
            $cashAccount->save();
            */

            // Log the liquidation
            Log::info("Bonds liquidated", [
                'investment_id' => $investment->id,
                'total_proceeds' => $totalProceeds
            ]);

            // Refresh investment list
            $this->loadInvestments();
        } else {
            Log::warning("Attempted to liquidate non-existing or non-bond investment", [
                'investment_id' => $investmentId
            ]);
        }
    }


    // Liquidate Mutual Funds
    public function liquidateMutualFunds($investmentId, $salePrice)
    {
        $investment = Investment::find($investmentId);

        // Ensure the investment is of type mutual fund
        if ($investment && $investment->type == 'mutual_fund') {
            // Calculate the total sale value
            $totalSaleValue = $investment->number_of_units * $salePrice;

            // Update the investment record (mark as liquidated)
            $investment->update([
                'number_of_units' => 0,
                'status' => 'liquidated'
            ]);

            // Uncomment and implement cash account update if necessary
            /*
            $cashAccount = Account::where('account_type', 'cash')->first();
            $cashAccount->balance += $totalSaleValue;
            $cashAccount->save();
            */

            // Log the liquidation
            Log::info("Mutual Funds liquidated", [
                'investment_id' => $investment->id,
                'total_sale_value' => $totalSaleValue
            ]);

            // Refresh investment list
            $this->loadInvestments();
        } else {
            Log::warning("Attempted to liquidate non-existing or non-mutual fund investment", [
                'investment_id' => $investmentId
            ]);
        }
    }







    public function render()
    {
        // Get accounts for account selection
        $parentAccounts = DB::table('accounts')
            ->where('major_category_code', '1000') // Asset accounts
            ->where('account_level', '<=', 2) // Parent level accounts only
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%INVESTMENT%')
                      ->orWhere('account_name', 'LIKE', '%ASSET%');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        $otherAccounts = DB::table('bank_accounts')
            
            


            
            ->select('internal_mirror_account_number', 'bank_name', 'account_number')
            ->where('status', 'ACTIVE')
            ->orderBy('bank_name')
            ->get();

        return view('livewire.accounting.investiments', [
            'parentAccounts' => $parentAccounts,
            'otherAccounts' => $otherAccounts
        ]);
    }
}
