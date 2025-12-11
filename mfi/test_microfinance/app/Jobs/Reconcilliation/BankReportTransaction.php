<?php

namespace App\Jobs\Reconcilliation;

use App\Models\Accounting;
use App\Models\AccountsModel;
use App\Models\Transaction;
use App\Service\DisbursementService;
use App\Services\SuspenseTransactionsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BankReportTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

     protected $SuspenseTransactionsService;
     protected $DisbursementService;

    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle( SuspenseTransactionsService $SuspenseTransactionsService, DisbursementService $DisbursementService)
    {

        $this->SuspenseTransactionsService=$SuspenseTransactionsService;
        $this->DisbursementService=$DisbursementService;
        try{

            // do matching

        }catch(\Exception $error){


        }

    }
    function getDepositAccount($member_number){

        return AccountsModel::where('category_code',);

    }

    function runReconcilliation(){

        $transactions= $this->getNewNonMatchingTransaction();

        foreach($transactions as $transaction){
            // read description
        $outPut=$this->readDescriptions($transaction->description);

          // validate descriptions
         $validate=$this->validateResult($outPut);

         //if fails report as suspense
         if($validate){
            // check names if exists or not
            $member=$this->validateClientNames($outPut['first_name'],$outPut['middle_name'],$outPut['last_name'] );
            if($member=="INVALID"){

                $this->manageSuspenseTransactions($transaction->debit ? : $transaction->credit,
                $this->getMirrorAccountId(2), $transaction->reference_number);

            }else{
                // if valid do transaction to default deposit account
                $member_number=$member->client_number;
                $source_account=12345678;
                $amount=$transaction->debit ? : $transaction->credit ;
                $destination_accounts=123456789;
                $narration=$transaction->description;

                $this->DisbursementService->makeTransaction($source_account, $amount, $destination_accounts, $narration);

            }



         }else{

           $this->manageSuspenseTransactions($transaction->debit ? : $transaction->credit,
        $this->getMirrorAccountId(2), $transaction->reference_number);


         }



        }
    }


    function validateClientNames($firstName, $middleName, $lastName)
    {
        // Build an array with the provided names
        $names = array_filter([
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
        ]);

        if (count($names) === 3) {
            $client = DB::table('clients')
                ->where(function ($query) use ($firstName, $middleName, $lastName) {
                    $query->where([
                        ['first_name', $firstName],
                        ['middle_name', $middleName],
                        ['last_name', $lastName],
                    ])->orWhere([
                        ['first_name', $middleName],
                        ['middle_name', $firstName],
                        ['last_name', $lastName],
                    ])->orWhere([
                        ['first_name', $firstName],
                        ['middle_name', $lastName],
                        ['last_name', $middleName],
                    ]);
                })
                ->first();

            return $client ? $client : 'INVALID';
        }

        if (count($names) === 2) {
            $client = DB::table('clients')
                ->where(function ($query) use ($names) {
                    foreach ($names as $column => $name) {
                        $query->where($column, $name);
                    }
                })
                ->first();

            return $client ? $client : 'INVALID';
        }

        return 'INVALID';
    }


    function getMirrorAccountId($bank_id){
        return Accounting::where('id',10)->value('account_number');
    }


    function getNewNonMatchingTransaction(){
        return Transaction::where('status','NEW_TRANSACTION')->get();
    }

    function updateNonMatchingTransaction($id){
        Transaction::where('id',$id)->update(['status',"NON_MATCHING"]);
    }

    function updateMatchingTransaction($id){
        Transaction::where('id',$id)->update(['status',"MATCHING"]);
    }


    function getAccount($account_id){
        return Accounting::where('id',$account_id)->value('account_number');
    }


    function readDescriptions($statement){

    $keywords = ['VAT', 'Commission', 'Outgoing', 'Incoming'];

    $foundKeywords = [];
    foreach ($keywords as $keyword) {
        if (stripos($statement, $keyword) !== false) {
            $foundKeywords[] = $keyword;
        }
    }

    preg_match('/to ([A-Z ]+)/i', $statement, $matches);

    if (isset($matches[1])) {
        $nameParts = explode(' ', trim($matches[1]));

        $firstName = isset($nameParts[0]) ? $nameParts[0] : null;
        $middleName = isset($nameParts[1]) ? $nameParts[1] : null;
        $lastName = isset($nameParts[2]) ? $nameParts[2] : null;
    } else {
        $firstName = $middleName = $lastName = null;
    }

    return [
        'description' => $foundKeywords,
        'first_name' => $firstName,
        'middle_name' => $middleName,
        'last_name' => $lastName
    ];


    }

    function validateResult($data) {
        $descriptionExists = !empty($data['description']);
        $nameExists = !is_null($data['first_name']) || !is_null($data['middle_name']) || !is_null($data['last_name']);
        if ($descriptionExists && $nameExists) {
            return true;
        }

        return false;
    }



    function manageSuspenseTransactions($amount, $mirror_account,$reference){
        $this->SuspenseTransactionsService->recordSuspenseTransaction($amount, $mirror_account,$reference);
    }

}
