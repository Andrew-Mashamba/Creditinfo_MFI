<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;

use App\Models\AccountsModel;
use App\Models\ChargesList;
use App\Services\CreditAndDebitService;
use Illuminate\Support\Facades\DB;


class LoanCharges extends Component
{
    public $intangibleAssets;
    public $name, $type, $calculating_type, $value;
    public $assetId;
    public $isEdit = false;
    public $category_code;
    public $parent_account_code;

    protected $rules = [
        'name' => 'required|string',
        'type'  => 'required|string',
        'calculating_type' => 'required',
        'value' => 'required',
        'parent_account_code' => 'required|string',

    ];
    private CreditAndDebitService $creditAndDebitService;
    private int $newInitialAmount;
    public $source;


    public function mount()
    {
        $this->fetchAssets();
    }


    public function fetchAssets()
    {
        $this->intangibleAssets = ChargesList::all();
    }


    /**
     * @throws \Exception
     */
    public function store()
    {

        $this->validate();

        $asset_parent_account = AccountsModel::where("sub_category_code", $this->category_code)->first();

        //assets
        // $asset_parent_account= DB::table('accounts')->where('sub_category_code',$this->debit_category_code)->first();
        $asset_account_number= $this->createNewAccountNumber($asset_parent_account->major_category_code,
            $asset_parent_account->category_code,
            $asset_parent_account->sub_category_code,
            $asset_parent_account->account_number);


        ChargesList::create([
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'calculating_type' => $this->calculating_type,
            'source' => $asset_account_number,
        ]);

        $this->resetInputFields();
        $this->fetchAssets();
        session()->flash('message', 'Asset Registered Successfully.');
    }


    public function createNewAccountNumber($major_category_code,$category_code, $sub_category_code,$parent_account){

        // Format the account name
        $formattedAccountName = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $this->name)));
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
            'notes' => $this->name,
            'account_level' => '3',
            'parent_account_number'=>$parent_account,
            'type' => 'asset_account'
        ]);

        return $account_number;
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

      public function generate_account_number($branch_code, $product_code): string
      {

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





    public function edit($id)
    {
        $asset = ChargesList::findOrFail($id);
        $this->assetId = $asset->id;
        $this->name = $asset->name;
        $this->type = $asset->type;
        $this->value = $asset->value;
        $this->calculating_type = $asset->calculating_type;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate();

        if ($this->assetId) {
            $asset = ChargesList::find($this->assetId);
            $asset->update([
                'name' => $this->name,
                'type' => $this->type,
                'value' => $this->value,
                'calculating_type' => $this->calculating_type,
            ]);

            $this->resetInputFields();
            $this->fetchAssets();
            session()->flash('message', 'Asset Updated Successfully.');
            $this->isEdit = false;
        }
    }

    public function delete($id)
    {
        ChargesList::find($id)->delete();
        $this->fetchAssets();
        session()->flash('message', 'Asset Deleted Successfully.');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->type = 'Intangible';
        $this->value = '';
        $this->acquisition_date = '';
        $this->assetId = '';
    }

    public function render()
    {
        return view('livewire.accounting.loan-charges');
    }








    public function CreditAndDebit($source_account, $amount, $destination_accounts, $narration)
    {

        // get source account details
        $reference_number = time();

        // Handle cases based on source and destination accounts
        $source_account_details = AccountsModel::where("account_number", $source_account)->first();
        $destination_account_details = AccountsModel::where("account_number", $destination_accounts)->first();
        $destination_account_name = $destination_account_details->account_name;

        $this->debit($reference_number, null, $destination_accounts, $amount, $narration, $destination_account_details->balance - $amount, null, $destination_account_name);

        $source_account_name = $source_account_details->account_name;
        $destination_account_name = $destination_account_details->account_name;
        $destination_account_prev_balance = $destination_account_details->balance;
        $destination_account_new_balance = (float)($destination_account_prev_balance + $amount);
        $this->credit($reference_number, $source_account, $destination_accounts, $amount, $narration, $destination_account_new_balance, $source_account_name, $destination_account_name);


    }



}
