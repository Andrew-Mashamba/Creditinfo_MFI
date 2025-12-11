<?php

namespace App\Services;

use App\Models\ApprovalAction;

use App\Models\Account as Accounts;
use App\Models\AccountsModel;
use App\Models\Activity;
use App\Models\general_ledger;
use App\Models\GeneralLedger;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreditService
{



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


}
