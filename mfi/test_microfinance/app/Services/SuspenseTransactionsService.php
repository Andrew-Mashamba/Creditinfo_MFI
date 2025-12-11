<?php

namespace App\Services;

use App\Models\AccountsModel;
use App\Models\ApprovalAction;

class SuspenseTransactionsService
{


    protected $debitService;
    protected $creditService;

    function __construct(DebitService $debitService, CreditService $creditService ){

        $this->debitService= $debitService;
        $this->creditService= $creditService;
    }


    function recordSuspenseTransaction($amount, $mirror_account,$reference){

        // source account mirror account
        $account= AccountsModel::where('mirrow_account',$mirror_account)->first();

        $suspense_account_number =$account->suspense_account;
        $suspense_account=AccountsModel::where('account_number',$suspense_account_number)->first();

        $account_prev_balance=   $suspense_account->balance;
        $account_new_balance = (double) ($account_prev_balance + (double) $amount );

        // credit suspense account

        AccountsModel::where('id',  $suspense_account->id)->update(['balance'=>$account_new_balance]);

        // record on ledger
        $this->creditService->credit($reference,
        $account->account_number,
        $account->suspense_account,
        '','Suspense Transaction from'.$account->account_name,
        $account_new_balance ,
        $account->account_name,
         $suspense_account->account_name);

    }

}
