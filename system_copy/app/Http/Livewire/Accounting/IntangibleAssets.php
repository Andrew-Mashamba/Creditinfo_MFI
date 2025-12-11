<?php

namespace App\Http\Livewire\Accounting;



use App\Models\AccountsModel;
use App\Models\ARModel;
use App\Models\general_ledger;
use App\Models\IntangibleAsset;

use App\Services\BalanceSheetItemIntegrationService;
use App\Services\CreditAndDebitService;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class IntangibleAssets extends Component
{
    public $intangibleAssets;
    public $name, $type = 'Intangible', $value, $acquisition_date;
    public $assetId;
    public $isEdit = false;
    public $category_code;
    public $asset_parent_account_code;
    public $asset_sub_category_code;

    protected $rules = [
        'name' => 'required|string',
        'type'  => 'required|string',
        'value' => 'required|numeric|min:0',
        'acquisition_date' => 'required|date',
        'asset_parent_account_code' => 'required|string',

    ];
    private CreditAndDebitService $creditAndDebitService;
    private int $newInitialAmount;
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create intangible asset account under
    public $other_account_id; // The other account for double-entry (Cash/Bank - credit side)

    public function mount()
    {
        $this->fetchAssets();
    }

    public function fetchAssets()
    {
        $this->intangibleAssets = IntangibleAsset::all();
    }

    /**
     * @throws \Exception
     */
    public function store()
    {
        $this->validate();

        try {
            // Create intangible asset record
            $intangibleAsset = IntangibleAsset::create([
                'name' => $this->name,
                'type' => $this->type,
                'value' => $this->value,
                'acquisition_date' => $this->acquisition_date,
            ]);

            // Use Balance Sheet Integration Service to create accounts and post to GL
            $integrationService = new BalanceSheetItemIntegrationService();
            
            try {
                $assetObj = (object) [
                    'id' => $intangibleAsset->id,
                    'name' => $this->name,
                    'asset_type' => $this->type,
                    'cost' => $this->value,
                    'acquisition_date' => $this->acquisition_date
                ];
                
                $integrationService->createIntangibleAssetAccount(
                    $assetObj,
                    $this->parent_account_number,  // Parent account to create asset account under
                    $this->other_account_id        // The other account for double-entry (Cash/Bank - credit side)
                );
                
            } catch (\Exception $e) {
                \Log::error('Failed to integrate intangible asset with accounts table: ' . $e->getMessage());
            }

            $this->resetInputFields();
            $this->fetchAssets();
            session()->flash('message', 'Intangible Asset created successfully.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating intangible asset: ' . $e->getMessage());
        }
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



    public function createNewAccount()
    {
        $GN_account_code = DB::table('GL_accounts')->where('account_name', $this->account_table_name)->value('account_code');
        if (!$GN_account_code) {
            // Handle the case where the account does not exist
        }

        $get_accounts = DB::table($this->category)->get();
        $next_code = $get_accounts->isEmpty() ? $this->next_code_no : intval($get_accounts->first()->category_code) + 1;

        // Ensure unique sub_category_code
        while (DB::table('accounts')->where('sub_category_code', $next_code)->exists()) {
            $next_code++;
        }

        // Format the account name
        $formattedAccountName = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $this->name)));
        $formattedAccountName = str_replace(' ', '_', $formattedAccountName);
        $formattedAccountName = strtoupper($formattedAccountName);

        // Create a new account in the category
        DB::table($this->category)->insert([
            'category_code' => $this->category_code_of_account,
            'sub_category_code' => $next_code,
            'sub_category_name' => $formattedAccountName,
        ]);

        // Generate account number
        $account_number = $this->generate_account_number(auth()->user()->branch, $next_code);

        // Create an entry in the AccountsModel
        AccountsModel::create([
            'account_use' => 'internal',
            'institution_number' => auth()->user()->institution_id,
            'branch_number' => auth()->user()->branch,
            'major_category_code' => $GN_account_code,
            'category_code' => $this->category_code_of_account,
            'sub_category_code' => $next_code,
            'account_name' => $this->name,
            'account_number' => $account_number,
            'notes' => $this->name,
            'account_level' => '3',
        ]);

        return $account_number;
    }







    public function edit($id)
    {
        $asset = IntangibleAsset::findOrFail($id);
        $this->assetId = $asset->id;
        $this->name = $asset->name;
        $this->type = $asset->type;
        $this->value = $asset->value;
        $this->acquisition_date = $asset->acquisition_date;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate();

        if ($this->assetId) {
            $asset = IntangibleAsset::find($this->assetId);
            $asset->update([
                'name' => $this->name,
                'type' => $this->type,
                'value' => $this->value,
                'acquisition_date' => $this->acquisition_date,
            ]);

            $this->resetInputFields();
            $this->fetchAssets();
            session()->flash('message', 'Asset Updated Successfully.');
            $this->isEdit = false;
        }
    }

    public function delete($id)
    {
        IntangibleAsset::find($id)->delete();
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
        // Get accounts for account selection
        $parentAccounts = DB::table('accounts')
            ->where('major_category_code', '1000') // Asset accounts
            ->where('account_level', '<=', 2) // Parent level accounts only
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%INTANGIBLE%')
                      ->orWhere('account_name', 'LIKE', '%ASSET%')
                      ->orWhere('account_name', 'LIKE', '%INTELLECTUAL%');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        // Get bank accounts from bank_accounts table
        $otherAccounts = DB::table('bank_accounts')
            ->select('internal_mirror_account_number', 'bank_name', 'account_number')
            ->where('status', 'ACTIVE')
            ->orderBy('bank_name')
            ->get();

        return view('livewire.accounting.intangible-assets', [
            'parentAccounts' => $parentAccounts,
            'otherAccounts' => $otherAccounts
        ]);
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




    public function debit($reference, $source_account_number, $destination_account_number, $credit, $narration, $running_balance, $source_account_name, $destinantion_account_name)
    {


        /**
         * @var mixed prepare sender data
         */

        $sender_branch_id='';
        $sender_product_id='';
        $sender_sub_product_id='';
        $sender_id='';
        $sender_name='';


        $senderInfo=  DB::table('clients')->where('client_number', DB::table('accounts')
            ->where('account_number', $destinantion_account_name)->value('client_number'))->first();
        if($senderInfo){
            $accounts=DB::table('accounts')->where('account_number',$source_account_number)->first();
            $sender_branch_id=$senderInfo->branch_id;
            $sender_product_id=$accounts->category_code;
            $sender_sub_product_id=$accounts->sub_category_code;
            $sender_id=$senderInfo->client_number;
            $sender_name=$senderInfo->first_name.' '.$senderInfo->middle_name.' .'.$senderInfo->last_name;

        }

        //DEBIT RECORD MEMBER
        $beneficiary_branch_id='';
        $beneficiary_product_id='';
        $beneficiary_sub_product_id='';
        $beneficiary_id='';
        $beneficiary_name='';

        $receiverInfo= DB::table('clients')->where('client_number', DB::table('accounts')
            ->where('account_number', $destinantion_account_name)->value('client_number'))->first();
        if($receiverInfo){

//            $accounts=DB::table('accounts')->where('account_number',$source_account_number)->first();
//            $beneficiary_branch_id=$senderInfo->branch_id;
//            $beneficiary_product_id=$accounts->category_code;
//            $beneficiary_sub_product_id=$accounts->sub_category_code;
//            $beneficiary_id=$senderInfo->client_number;
//            $beneficiary_name=$senderInfo->first_name.' '.$senderInfo->middle_name.' '.$senderInfo->last_name;


            $beneficiary_id=$senderInfo->client_number;
            $beneficiary_name=$senderInfo->first_name.' '.$senderInfo->middle_name.' '.$senderInfo->last_name;
        }

        $accounts=DB::table('accounts')->where('account_number',$source_account_number)->first();

        $major_category_code=$accounts->major_category_code;
        $category_code=$accounts->category_code;
        $sub_category_code=$accounts->sub_category_code;


        general_ledger::create([
            'record_on_account_number' => $source_account_number  ? :0,
            'record_on_account_number_balance' => $running_balance  ? :0,
            'major_category_code' =>$major_category_code  ? :0,
            'category_code' => $category_code  ? :0,
            'sub_category_code' => $sub_category_code  ? :0,
            'sender_sub_product_id' =>  null,
            'beneficiary_product_id' => null,
            'beneficiary_sub_product_id' => null,
            'sender_id' =>  $sender_id  ?:1,
            'beneficiary_id' => $beneficiary_id  ?:1,
            'sender_name' => $sender_name,
            'beneficiary_name' => $beneficiary_name,
            'sender_account_number' => $source_account_number,
            'beneficiary_account_number' => $destination_account_number,
            'transaction_type' => 'IFT',
            'sender_account_currency_type' => 'TZS',
            'beneficiary_account_currency_type' => 'TZS',
            'narration' => $narration,
            'credit'  => 0,
            'debit' => (double)$credit,
            'reference_number' => $reference,
            'trans_status' => 'Successful',
            'trans_status_description' => 'Successful',
            'swift_code' => '',
            'destination_bank_name' => '',
            'destination_bank_number' => '',
            'payment_status' => 'Successful',
            'recon_status' => 'Pending',
            // 'partner_bank' => AccountsModel::where('account_number', $this->bank1)->value('institution_number'),
            // 'partner_bank_name' => AccountsModel::where('account_number', $this->bank1)->value('account_name'),
            // 'partner_bank_account_number' => $this->bank1,
            'partner_bank_transaction_reference_number' => '0000',

        ]);



    }


    public function credit($reference, $source_account_number, $destination_account_number, $credit, $narration, $running_balance, $source_account_name, $destinantion_account_name)
    {


        /**
         * @var mixed prepare sender data
         */

        $sender_branch_id='';
        $sender_product_id='';
        $sender_sub_product_id='';
        $sender_id='';
        $sender_name='';


        $senderInfo=  DB::table('clients')->where('client_number', DB::table('accounts')
            ->where('account_number', $destinantion_account_name)->value('client_number'))->first();
        if($senderInfo){
            $accounts=DB::table('accounts')->where('account_number',$source_account_number)->first();
            $sender_branch_id=$senderInfo->branch_id;
            $sender_product_id=$accounts->category_code;
            $sender_sub_product_id=$accounts->sub_category_code;
            $sender_id=$senderInfo->client_number;
            $sender_name=$senderInfo->first_name.' '.$senderInfo->middle_name.' .'.$senderInfo->last_name;

        }

        //DEBIT RECORD MEMBER
        $beneficiary_branch_id='';
        $beneficiary_product_id='';
        $beneficiary_sub_product_id='';
        $beneficiary_id='';
        $beneficiary_name='';

        $receiverInfo= DB::table('clients')->where('client_number', DB::table('accounts')
            ->where('account_number', $destinantion_account_name)->value('client_number'))->first();
        if($receiverInfo){


            $beneficiary_id=$senderInfo->client_number;
            $beneficiary_name=$senderInfo->first_name.' '.$senderInfo->middle_name.' '.$senderInfo->last_name;
        }

        $accounts=DB::table('accounts')->where('account_number',$destination_account_number)->first();

        $major_category_code=$accounts->major_category_code;
        $category_code=$accounts->category_code;
        $sub_category_code=$accounts->sub_category_code;


        general_ledger::create([
            'record_on_account_number' => $destination_account_number ? :0,
            'record_on_account_number_balance' => $running_balance ? :0,
            'major_category_code' =>$major_category_code  ? :0,
            'category_code' => $category_code  ? :0,
            'sub_category_code' => $sub_category_code  ? :0,
            'sender_sub_product_id' =>  null,
            'beneficiary_product_id' => null,
            'beneficiary_sub_product_id' => null,
            'sender_id' =>  $sender_id  ?:1,
            'beneficiary_id' => $beneficiary_id  ?:1,
            'sender_name' => $sender_name,
            'beneficiary_name' => $beneficiary_name,
            'sender_account_number' => $source_account_number,
            'beneficiary_account_number' => $destination_account_number,
            'transaction_type' => 'IFT',
            'sender_account_currency_type' => 'TZS',
            'beneficiary_account_currency_type' => 'TZS',
            'narration' => $narration,
            'credit'  => (double)$credit,
            'debit' => 0,
            'reference_number' => $reference,
            'trans_status' => 'Successful',
            'trans_status_description' => 'Successful',
            'swift_code' => '',
            'destination_bank_name' => '',
            'destination_bank_number' => '',
            'payment_status' => 'Successful',
            'recon_status' => 'Pending',
            'partner_bank_transaction_reference_number' => '0000',

        ]);



    }

}
