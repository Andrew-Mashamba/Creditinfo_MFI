<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCallback;
use App\Mail\InstitutionRegistrationConfirmationMail;
use App\Models\institutions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InstitutionInformationApi extends Controller
{

    public function getInstitution(Request $institution_id)
    {


        $id=$institution_id->institution_id;
        // get institution by id
        $institution=institutions::where('id',$id)->get();
        $institution_product=DB::table('loan_sub_products')->where('institution_id',$id)->get();

        $array=["institution"=>$institution,
            "institution_product"=>$institution_product,
        ];

        return $array;

    }

    public function internalBankTransfer(Request $body){
//        return $id ;
        $account_number= $body->account_number;
        $amount=$body->amount;
        $currency=$body->currency;
        $destination_account=$body->destination_account;
        $callback_url=$body->call_back_url;
        $reference_number=$body->reference_number;
        $error_code='';



            $response = [
                'error'=>$error_code,
                'status' => 'Request received',
                'message' => 'Function X will call back later.',
            ];
            response()->json($response)->send();


            ProcessCallback::dispatch($reference_number,$callback_url);



        }





}
