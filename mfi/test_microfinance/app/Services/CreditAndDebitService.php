<?php

namespace App\Services;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use Illuminate\Support\Facades\DB;

class CreditAndDebitService
{

    public function CreditAndDebit($source_account, $amount, $destination_accounts, $narration)
    {
        // get source account details
        $reference_number = time();

        // Handle cases based on source and destination accounts
        $source_account_details = AccountsModel::where("account_number", $source_account)->first();
        $destination_account_details = AccountsModel::where("account_number", $destination_accounts)->first();

        // Case 1: Both source_account and destination_account are provided
        if ($source_account_details && $destination_account_details) {
            // Perform the debit transaction from source account
            $source_account_prev_balance = $source_account_details->balance;
            $source_account_new_balance = (float)($source_account_prev_balance - $amount);

            // Update the source account balance
            AccountsModel::where("account_number", $source_account_details->account_number)->update(['balance' => $source_account_new_balance]);
            $source_account_name = $source_account_details->account_name;
            $destination_account_name = $destination_account_details->account_name;

            // Record on debit
            $this->debit($reference_number, $source_account, $destination_accounts, $amount, $narration, $source_account_new_balance, $source_account_name, $destination_account_name);

            // Perform the credit transaction to destination account
            $destination_account_prev_balance = $destination_account_details->balance;
            $destination_account_new_balance = (float)($destination_account_prev_balance + $amount);

            // Update destination account balance
            AccountsModel::where("account_number", $destination_account_details->account_number)->update(['balance' => $destination_account_new_balance]);

            // Record on credit
            $this->credit($reference_number, $source_account, $destination_accounts, $amount, $narration, $destination_account_new_balance, $source_account_name, $destination_account_name);
        }
        // Case 2: Only source_account is provided (credit only)
        elseif ($source_account_details && !$destination_account_details) {
            // Only perform credit action
            $source_account_name = $source_account_details->account_name;

            // Record credit transaction
            $this->credit($reference_number, $source_account, null, $amount, $narration, $source_account_details->balance + $amount, $source_account_name, null);
        }
        // Case 3: Only destination_account is provided (debit only)
        elseif (!$source_account_details && $destination_account_details) {
            // Only perform debit action
            $destination_account_name = $destination_account_details->account_name;

            // Record debit transaction
            $this->debit($reference_number, null, $destination_accounts, $amount, $narration, $destination_account_details->balance - $amount, null, $destination_account_name);
        }
        // Case 4: Both accounts are null
        else {
            throw new \Exception('Both source and destination accounts cannot be null.');
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
            'record_on_account_number' => $source_account_number  ? :0,
            'record_on_account_number_balance' => $running_balance  ? :0,
            'sender_branch_id' =>$sender_branch_id  ? :1,
            'beneficiary_branch_id' => $beneficiary_branch_id  ? :1,
            'sender_product_id' => $sender_sub_product_id  ? :1,
            'sender_sub_product_id' =>  $sender_product_id  ? :1,
            'beneficiary_product_id' => $beneficiary_product_id ?:0,
            'beneficiary_sub_product_id' => $beneficiary_sub_product_id ?:1,
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

            $accounts=DB::table('accounts')->where('account_number',$source_account_number)->first();
            $beneficiary_branch_id=$senderInfo->branch_id;
            $beneficiary_product_id=$accounts->category_code;
            $beneficiary_sub_product_id=$accounts->sub_category_code;
            $beneficiary_id=$senderInfo->client_number;
            $beneficiary_name=$senderInfo->first_name.' '.$senderInfo->middle_name.' '.$senderInfo->last_name;
        }


        general_ledger::create([
            'record_on_account_number' => $destination_account_number ? :0,
            'record_on_account_number_balance' => $running_balance ? :0,
            'sender_branch_id' =>$sender_branch_id  ? :0,
            'beneficiary_branch_id' => $beneficiary_branch_id  ? :0,
            'sender_product_id' => $sender_sub_product_id  ? :0,
            'sender_sub_product_id' =>  $sender_product_id  ? :0,
            'beneficiary_product_id' => $beneficiary_product_id  ?:1,
            'beneficiary_sub_product_id' => $beneficiary_sub_product_id  ?:1,
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
