<?php

namespace App\Http\Livewire\Accounting;



use App\Models\AccountsModel;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Insurances;
use App\Services\BalanceSheetItemIntegrationService;

class Insurance extends Component
{
    public $insurances;
    public $name;
    public $category;
    public $coverage_amount;
    public $premium;
    public $monthly_rate;
    public $insuranceId;
    public $categoryx;
    public $account_table_name;
    public $next_code_no;
    public $category_code_of_account;
    public $account_number;
    
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create insurance account under
    public $other_account_id; // The other account for double-entry (Cash/Bank - debit side)

    public $categories = ['employees', 'members', 'loans'];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:employees,members,loans',
            'coverage_amount' => 'required_if:category,!=,loans|numeric|min:0',
            'premium' => 'required_if:category,!=,loans|numeric|min:0',
            'monthly_rate' => 'required_if:category,loans|numeric|min:0|max:100', // For loans
        ];
    }

    public function mount()
    {
        $this->categoryx='insurance_payable';
        $this->account_table_name='liability_accounts';
        $this->next_code_no=2002;
        $this->category_code_of_account=2001;

        $this->loadInsurances();
    }

    public function resetFields()
    {
        $this->name = '';
        $this->category = '';
        $this->coverage_amount = '';
        $this->premium = '';
        $this->monthly_rate = '';
        $this->insuranceId = null;
    }

    public function createInsurance()
    {
        //$this->validate();



        //dd($GN_account_code);

        // Fetch accounts for the category
        $get_accounts = DB::table($this->categoryx)->get();

        if ($get_accounts->isEmpty()) {
            // If no accounts exist, start with the default next code
            $next_code = $this->next_code_no;
        } else {
            // Extract the category_code of the first account
            $category_code = $get_accounts->first()->category_code;

            // Get all existing category codes in the range
            $existing_codes = $get_accounts->pluck('category_code')->toArray();

            // Set the range for new codes
            $range_start = intval($category_code) + 1;
            $range_limit = intval($category_code) + 999;

            // Initialize the next code within the range
            $next_code = $range_start;

            // Loop until a unique code is found or the limit is exceeded
            while (in_array(strval($next_code), $existing_codes) && $next_code <= $range_limit) {
                $next_code++;
            }

            // Validate that the next code does not exceed the range limit
            if ($next_code > $range_limit) {
                session()->flash('error', 'Unable to generate a unique code within the range.');
                return;
            }

            // Check if the next_code already exists in the `sub_category_code` column of the `accounts` table
            while (DB::table('accounts')->where('sub_category_code', $next_code)->exists()) {
                // Increment next_code and ensure it does not exceed the range limit
                $next_code++;
                if ($next_code > $range_limit) {
                    session()->flash('error', 'Unable to generate a unique sub_category_code within the range.');
                    return;
                }
            }
        }

    // $next_code is now a unique code within the desired range and ready for use



        $this->account_number = $this->generate_account_number(auth()->user()->branch, $next_code);

        $this->createNewAccount($next_code);

        $insurance = Insurances::create([
            'name' => $this->name,
            'account_number' => $this->account_number,
            'category' => $this->category,
            'coverage_amount' => $this->category != 'loans' ? $this->coverage_amount : null,
            'premium' => $this->category != 'loans' ? $this->premium : null,
            'monthly_rate' => $this->category == 'loans' ? $this->monthly_rate : null,
        ]);

        // Use Balance Sheet Integration Service to create accounts and post to GL
        $integrationService = new BalanceSheetItemIntegrationService();
        
        try {
            $integrationService->createInsuranceLiabilityAccount(
                $insurance,
                $this->parent_account_number,  // Parent account to create insurance account under
                $this->other_account_id        // The other account for double-entry (Cash/Bank - debit side)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to integrate insurance with accounts table: ' . $e->getMessage());
        }

        $this->resetFields();
        session()->flash('message', 'Insurance created successfully.');
        $this->loadInsurances();
    }

    public function editInsurance($id)
    {
        $insurance = Insurances::findOrFail($id);
        $this->insuranceId = $insurance->id;
        $this->name = $insurance->name;
        $this->category = $insurance->category;
        $this->coverage_amount = $insurance->coverage_amount;
        $this->premium = $insurance->premium;
        $this->monthly_rate = $insurance->monthly_rate;
    }

    public function updateInsurance()
    {
        $this->validate();

        if ($this->insuranceId) {
            $insurance = Insurances::findOrFail($this->insuranceId);
            $insurance->update([
                'name' => $this->name,
                'category' => $this->category,
                'coverage_amount' => $this->category != 'loans' ? $this->coverage_amount : null,
                'premium' => $this->category != 'loans' ? $this->premium : null,
                'monthly_rate' => $this->category == 'loans' ? $this->monthly_rate : null,
            ]);
            session()->flash('message', 'Insurance updated successfully.');
        }

        $this->resetFields();
        $this->loadInsurances();
    }

    public function deleteInsurance($id)
    {
        $insurance = Insurances::findOrFail($id);
        $insurance->delete();
        session()->flash('message', 'Insurance deleted successfully.');
        $this->loadInsurances();
    }

    public function loadInsurances()
    {
        $this->insurances = Insurances::all();
    }



    public function render()
    {
        // Get bank accounts for other account selection
        $otherAccounts = DB::table('bank_accounts')
            ->select('internal_mirror_account_number', 'bank_name', 'account_number')
            ->where('status', 'ACTIVE')
            ->orderBy('bank_name')
            ->get();
            
        $creditAccounts = DB::table('accounts')
            ->where('major_category_code', '2000') // Liability accounts
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%Insurance%')
                      ->orWhere('account_name', 'LIKE', '%Liability%')
                      ->orWhere('account_name', 'LIKE', '%Payable%');
            })
            ->orderBy('account_name')
            ->get();

        return view('livewire.accounting.insurance', [
            'otherAccounts' => $otherAccounts,
            'creditAccounts' => $creditAccounts
        ]);
    }




    public function createNewAccount($next_code)


    {
        //dd();








        // Format the account name
        $formattedAccountName = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $this->name)));
        $formattedAccountName = str_replace(' ', '_', $formattedAccountName);
$formattedAccountName = strtoupper($formattedAccountName);

        // Create a new account based on input fields
        DB::table( $this->categoryx)->insert([
            //'major_category_code' => $GN_account_code,
            'category_code' =>  $this->category_code_of_account,
            'sub_category_code' => $next_code,
            'sub_category_name' => $formattedAccountName,
        ]);

        // Check if the table for the new account exists
        // Schema
        // if (!Schema::hasTable($formattedAccountName)) {
        //     Schema::create($formattedAccountName, function ($table) {
        //         $table->increments('id');
        //         $table->string('category_code')->nullable();
        //         $table->string('sub_category_code')->nullable();
        //         $table->string('sub_category_name')->nullable();
        //         $table->dateTime('updated_at')->nullable();
        //         $table->dateTime('created_at')->nullable();
        //     });
        // }

        // Generate account number

        // Ensure category exists in GL_accounts
        $GN_account_code = DB::table('GL_accounts')->where('account_name',$this->account_table_name )->value('account_code');
        if (!$GN_account_code) {

        }
        // Create a new account entry in the AccountsModel
        $id = AccountsModel::create([
            'account_use' => 'internal',
            'institution_number' => auth()->user()->institution_id,
            'branch_number' => auth()->user()->branch,
            'major_category_code' => $GN_account_code,
            'category_code' => $next_code,
            'sub_category_code' => $next_code,
            'account_name' => $this->name,
            'account_number' => $this->account_number,
            'notes' => $this->name,
            'bank_id' => null,
            'mirror_account' => null,
            'account_level' => 'parent',
        ])->id;


        session()->flash('message', 'Account created successfully.');
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




}
