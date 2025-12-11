<?php

namespace App\Helper;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewMemberUpdateStatusHelper{


    function checkRegistrationFee($member_number){
        // registration fees

        $institution_data=DB::table('institutions')->where('id',1)->first();

        $registration_fee= $institution_data->registration_fees;

        $nida_number= DB::table('clients')->where('client_number',$member_number)->first()->nida_number;

      $amount=DB::table('pending_registrations')->where('nida_number',$nida_number)
         ->where('status',"INITIAL PAY")->value('amount');
        if($amount == $registration_fee){
         // \\// \\
        return "OKEY";
                              }
    }
    function  checkMandatoryShare($member_number){
        $institution_data=DB::table('institutions')->where('id',1)->first();
        $initial_mandatory_share= $institution_data->initial_shares * $institution_data->value_per_share ;

        $member_account_balance=DB::table('accounts')->where('client_number',$member_number)
                               ->where('sub_category_code','3003')->value('balance');

        if($member_account_balance == $initial_mandatory_share){

        // update the member status
        $status= DB::table('clients')->where('client_number',$member_number)->value('client_status');


        if($status == "ONPROGRESS"){

            return "OKEY";
        }


        }



    }

    function updateMemberStatus($client_number){

        try{
            // Check if payment processing is in progress to prevent automatic status updates
            if (config('payment.processing', false)) {
                Log::info('Skipping automatic client status update during payment processing', [
                    'client_number' => $client_number,
                    'reason' => 'Payment processing in progress'
                ]);
                return;
            }

            $status= $this->checkRegistrationFee($client_number);
            $status_two= $this->checkMandatoryShare($client_number);

            if($status==$status_two){
              DB::table('clients')->where('client_number',$client_number)->update(['client_status'=>'ACTIVE']);

            }
        }catch(\Exception $e){

            dd('gon'.$e->getMessage());
        }



    }

}


