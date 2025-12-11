<?php

namespace App\Jobs;

use App\Mail\InstitutionRegistrationConfirmationMail;
use App\Models\AccountsModel;
use App\Models\ClientsModel;
use App\Models\general_ledger;
use App\Models\Loan_sub_products;
use App\Models\loans_schedules;
use App\Models\LoansModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EndOfDay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to('percyegno@gmail.com')->send(new InstitutionRegistrationConfirmationMail('confirmation detail'));


    }


    public function loanRepayment($source_account_number,$destination_account_number,$interest_amount,$loan_id,$note,$id){


        $source_acc=AccountsModel::where('account_number',$source_account_number)->first();
        $source_prev_balance=$source_acc->balance;


        if($source_prev_balance<=0){

        }else{

            // debit amount
        if($interest_amount <= $source_prev_balance){

            // for paid amount
            $loanValues=loans_schedules::where('id',$id)->first();
            $prev_paid_mount=$loanValues->payment;
            $new_paid_amount=(double)$prev_paid_mount + (double)($interest_amount);
            loans_schedules::where('id',$id)->update(['payment'=>$new_paid_amount]);


            $source_new_balance=(double)$source_prev_balance -(double)$interest_amount;
            AccountsModel::where('account_number',$source_account_number)->update(['balance'=>$source_new_balance]);


        }else{

            // amount in arrears
            $loanValues=loans_schedules::where('id',$id)->first();
            // get prev amount
            $prev_amount=$loanValues->amount_in_arrears;
            $new_amount_inArrears=$prev_amount + ($interest_amount - $source_prev_balance);
            loans_schedules::where('id',$id)->update(['amount_in_arrears'=>$new_amount_inArrears]);


            //  paid amount
            $prev_paid_mount=$loanValues->payment;
            $new_paid_amount=(double)$prev_paid_mount + (double)($source_prev_balance);
            loans_schedules::where('id',$id)->update(['payment'=>$new_paid_amount]);




             // debit
            $interest_amount=$source_prev_balance;
            $source_new_balance=0;
            AccountsModel::where('account_number',$source_account_number)->update(['balance'=>$source_new_balance]);




        }

        // credit
        $destination_acc=AccountsModel::where('account_number',$destination_account_number)->fisrt();
        $destination_new_balance=(double)$destination_acc->balane + (double)$interest_amount;





        // record on the general ledger
        $record_on_gl=new general_ledger();
        $record_on_gl->debit($source_account_number,$source_new_balance,
            $destination_account_number,$interest_amount,$note,$loan_id);

        $record_on_gl->credit($destination_account_number,$destination_new_balance
            ,$source_account_number,$interest_amount,$note,$loan_id);
        }

    }


    public function loanPaymentWithNoArrears(){

        $TodayDate=now()->format('Y-m-d');
        $get_loan_id=loans_schedules::where('installment_date',$TodayDate)->pluck('loan_id');
        // get loan account


        foreach($get_loan_id as $id){

            //client information
            $client_loan_account=LoansModel::where('loan_id',$id)->first();
            $client_account_number=$client_loan_account->loan_account_number;
            $client_account_balance=AccountsModel::where('account_number',$client_account_number)->value('balance');


            // loan information
            $client_installment=loans_schedules::where('installment_date',$TodayDate)->where('loan_id',$id)->first();
            $interest_payable=$client_installment->interest;
            $principle_payable=$client_installment->principle;
            $penalties_payable=$client_installment->penaties;


            //interest sections
            $interest_account_id=Loan_sub_products::where('sub_product_id',$client_loan_account->loan_sub_product)->value('collection_account_loan_interest'); //collection_account_loan_principle;
            $interest_account_value=AccountsModel::where('id',$interest_account_id)->first();
            $interest_account=$interest_account_value->account_number;
            $interest_prev_balance=$interest_account_value->balance;


            // principles
            $principle_account_id=Loan_sub_products::where('sub_product_id',$client_loan_account->loan_sub_product)->value('collection_account_loan_principle'); //collection_account_loan_principle;
            $principle_account_value=AccountsModel::where('id',$principle_account_id)->first();
            $principle_account=$principle_account_value->account_number;
            $principle_prev_balance=$principle_account_value->balance;


            // charges/penalties
            $charge_account_id=Loan_sub_products::where('sub_product_id',$client_loan_account->loan_sub_product)->value('collection_account_loan_penalties'); //collection_account_loan_principle;
            $charge_account_value=AccountsModel::where('id',$charge_account_id)->first();
            $charge_account=$charge_account_value->account_number;
            $charge_prev_balance=$charge_account_value->balance;



            // check total balance
            $total_amount_payable=$interest_payable+$principle_payable+$penalties_payable;
            $actual_client_account_balance=$client_account_balance;

            if($actual_client_account_balance >= $total_amount_payable){

                // penalties
                $this->loanRepayment($client_account_number,$charge_account,$penalties_payable,$id,'penalty payment',$client_installment->id);

                // interest payment
               $this->loanRepayment($client_account_number,$interest_account,$interest_payable,$id,"interest payment",$client_installment->id);

               // principle payment
               $this->loanRepayment($client_account_number,$principle_account,$principle_payable,$id,"principle payment",$client_installment->id);


            }
            else{
                // penalties
                $this->loanPayment($client_account_number,$charge_account,$penalties_payable,$id,'penalty payment',$client_installment->id);

                // interest payment
                $this->loanPayment($client_account_number,$interest_account,$interest_payable,$id,"interest payment",$client_installment->id);

                // principle payment
                $this->loanPayment($client_account_number,$principle_account,$principle_payable,$id,"principle payment",$client_installment->id);


                // records on arrears
                $days_in_arrears=$client_installment->days_in_arrears;
                $new_day_in_arrears=$days_in_arrears +1;
                loans_schedules::where('id',$client_installment->id)->update(['days_in_arrears'=>$new_day_in_arrears]);


            }

          $loan_account_balane=  LoansModel::where('loan_id',$id)->value('principle');
          //  amount to be paid
            $loan_repay_amount=loans_schedules::where('loan_id',$id)->value('installment');
            $interest=loans_schedules::where('loan_id',$id)->value('interest');
            $total_amount_to_be_paid= (double)$loan_repay_amount+ (double)$interest;

            if($loan_account_balane >= $total_amount_to_be_paid){
                // loan repayment account new balanve

                $loan_account_new_balance= (double)$loan_account_balane-$total_amount_to_be_paid;

                // update balance
                LoansModel::where('loan_id',$id)->update(['principle'=>$loan_account_new_balance]);
                // disbursement
                  // interest money to profit account
                // mtaji to equity
            }
            elseif($loan_account_balane > 0){
                // reduce interest
                $new_balance=(double)$loan_account_balane-(double)$interest;
                if($new_balance > 0){
                    $remaining_amount_to_pay=(double)abs($new_balance-(double)$loan_repay_amount);
                    // update remaining amount
                    LoansModel::where('loan_id',$id)->update(['amount_in_arrears'=>$remaining_amount_to_pay]);
                    // record the next check date
                    loans_schedules::where('loan_id',$id)->where('installment_date',now())->update(['next_check_date'=>now() + 1,'days_in_arrears'=>1]);
                    // record days in arrears in loan table
                    $get_arrears=LoansModel::where('id',$id)->get();
                    // update loan table
                    LoansModel::where('loan_id',$id)->update(['days_in_arrears'=>$get_arrears +1, 'total_days_in_arrears'=>$get_arrears+1]);
                    // send money back to equity account
                }

                else{
                    // remaining amount
                    $remaining_amount=(double)$loan_repay_amount+ abs($new_balance);

                    // update remaining amount
                    LoansModel::where('loan_id',$id)->update(['amount_in_arrears'=>$remaining_amount]);

                    // record the next check date
                    loans_schedules::where('loan_id',$id)->where('installment_date',now())->update(['next_check_date'=>now() + 1,'days_in_arrears'=>1]);
                    // record days in arrears in loan table
                    $get_arrears=LoansModel::where('id',$id)->get();
                    // update loan table
                    LoansModel::where('loan_id',$id)->update(['days_in_arrears'=>$get_arrears +1, 'total_days_in_arrears'=>$get_arrears+1]);



                    // send money back to equity account


                }


            }
            else{

                // update remaining amount
                LoansModel::where('loan_id',$id)->update(['amount_in_arrears'=>$total_amount_to_be_paid]);

                // record the next check date
                loans_schedules::where('loan_id',$id)->where('installment_date',now())->update(['next_check_date'=>now() + 1,'days_in_arrears'=>1]);
                // record days in arrears in loan table
                $get_arrears=LoansModel::where('id',$id)->get();
                // update loan table
                LoansModel::where('loan_id',$id)->update(['days_in_arrears'=>$get_arrears +1, 'total_days_in_arrears'=>$get_arrears+1]);


            }
        }


    }

    public function loanPaymentWithArrears(){

    }



    public function clientMenu(){
        // get all clients

        // set in active clients and inactive clients
        $active_client_numbers=ClientsModel::where('client_status',"ACTIVE")->pluck('client_number');
        foreach ($active_client_numbers as $client_number){
           $active_status= LoansModel::where('client_number',$client_number)->max('status');
           if($active_status=="CLOSED"){
               ClientsModel::where('client_number',$client_number)->update(['client_status'=>"INACTIVE"]);
           }

        }

        $active_client_numbers=ClientsModel::where('client_status','!=',"ACTIVE")->pluck('client_number');
        foreach ($active_client_numbers as $client_number){
           $active_status= LoansModel::where('client_number',$client_number)->max('status');
           if($active_status !="CLOSED"){
               ClientsModel::where('client_number',$client_number)->update(['client_status'=>"ACTIVE"]);
           }

        }





    }
}
