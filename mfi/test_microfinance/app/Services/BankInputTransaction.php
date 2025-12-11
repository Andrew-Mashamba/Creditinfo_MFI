<?php

namespace App\Services;

use App\Models\ApprovalAction;
use App\Livewire\Accounting\Account;
use App\Models\Account as Accounts;
use App\Models\AccountsModel;
use App\Models\Activity;
use App\Models\general_ledger;
use App\Models\GeneralLedger;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
class BankInputTransaction
{

    public function validateTransactionsDetails(){

        // get source account, client number 
    }

    public function makeTransaction($source_account, $amount, $destination_accounts, $narration,$member_number=null)
    {

        DB::beginTransaction();
        try {
           // $this->disburse($source_account, $amount, $destination_accounts, $narration);
            DB::commit();
            return "successfully ";
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
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

            $accounts=DB::table('accounts')->where('account_number',$source_account_number)->first();
            $beneficiary_branch_id=$senderInfo->branch_id;
            $beneficiary_product_id=$accounts->category_code;
            $beneficiary_sub_product_id=$accounts->sub_category_code;
            $beneficiary_id=$senderInfo->client_number;
            $beneficiary_name=$senderInfo->first_name.' '.$senderInfo->middle_name.' '.$senderInfo->last_name;
        }


        general_ledger::create([
            'record_on_account_number' => $source_account_number,
            'record_on_account_number_balance' => $running_balance,
            'sender_branch_id' =>$sender_branch_id,
            'beneficiary_branch_id' => $beneficiary_branch_id,
           'sender_product_id' => $sender_sub_product_id,
            'sender_sub_product_id' =>  $sender_product_id,
           'beneficiary_product_id' => $beneficiary_product_id,
            'beneficiary_sub_product_id' => $beneficiary_sub_product_id,
            'sender_id' =>  $sender_id,
            'beneficiary_id' => $beneficiary_id,
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

            $accounts=DB::table('accounts')->where('account_number',$source_account_number)->first();
            $beneficiary_branch_id=$senderInfo->branch_id;
            $beneficiary_product_id=$accounts->category_code;
            $beneficiary_sub_product_id=$accounts->sub_category_code;
            $beneficiary_id=$senderInfo->client_number;
            $beneficiary_name=$senderInfo->first_name.' '.$senderInfo->middle_name.' '.$senderInfo->last_name;
        }


        general_ledger::create([
            'record_on_account_number' => $source_account_number,
            'record_on_account_number_balance' => $running_balance,
            'sender_branch_id' =>$sender_branch_id,
            'beneficiary_branch_id' => $beneficiary_branch_id,
           'sender_product_id' => $sender_sub_product_id,
            'sender_sub_product_id' =>  $sender_product_id,
           'beneficiary_product_id' => $beneficiary_product_id,
            'beneficiary_sub_product_id' => $beneficiary_sub_product_id,
            'sender_id' =>  $sender_id,
            'beneficiary_id' => $beneficiary_id,
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
            // 'partner_bank' => AccountsModel::where('account_number', $this->bank1)->value('institution_number'),
            // 'partner_bank_name' => AccountsModel::where('account_number', $this->bank1)->value('account_name'),
            // 'partner_bank_account_number' => $this->bank1,
            'partner_bank_transaction_reference_number' => '0000',

        ]);



    }

    public function disburse($source_account, $amount, $destination_accounts, $narration)
    {
        // get source account details
        $reference_number = time();

        $accounts = AccountsModel::where("account_number", $source_account)->first();



        $destination_account = AccountsModel::where("account_number", $destination_accounts)->first();

        if ($accounts && $destination_account) {

            $source_account_prev_balance = $accounts->balance;
            $source_account_new_balance = (float) ($source_account_prev_balance - $amount);

            // update the balance
            AccountsModel::where("account_number", $accounts->account_number)->update(['balance' => $source_account_new_balance]);
            $source_account_name =  $accounts->account_name;
            $destinantion_account_name = $destination_account->account_name;


            // record on debit
            $this->debit($reference_number, $source_account, $destination_accounts, $amount, $narration, $source_account_new_balance, $source_account_name, $destinantion_account_name);

            // credit process
            $destination_account = AccountsModel::where("account_number", $destination_accounts)->first();

            $destination_account_prev_balance = $destination_account->balance;
            $destination_account_new_balance = (float) ($destination_account_prev_balance + $amount);
            // update account balance
            AccountsModel::where('account_number', $destination_account->account_number)->update(['balance' => $destination_account_new_balance]);

            $this->credit($reference_number, $source_account, $destination_accounts, $amount, $narration, $destination_account_new_balance, $source_account_name, $destinantion_account_name);

        }
    }

}
