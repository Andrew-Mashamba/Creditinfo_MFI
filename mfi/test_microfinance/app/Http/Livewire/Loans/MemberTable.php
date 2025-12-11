<?php

namespace App\Http\Livewire\Loans;

use App\Models\ClientsModel;
use App\Models\general_ledger;
use App\Models\Loan_sub_products;
use App\Models\loans_schedules;
use App\Models\LoansModel;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Exports\LoanSchedule;
use Maatwebsite\Excel\Facades\Excel;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class MemberTable extends LivewireDatatable
{

    public $member_number;
    public $loanId;
    public $selectPaymentSchedule=false;
    public $repayment;
    public $product_name;


    public function closeInstallment(){

        $this->loanId=null;
        $this->installments=null;
        $this->emit('refreshPage');
    }

    public function viewInstallment($loan_id){
        $this->loanId=$loan_id;
        session()->put('memberLoanId',$loan_id);
        $this->totalInstallment=loans_schedules::where('loan_id',$loan_id)->sum('installment');
        $this->totalinterest=loans_schedules::where('loan_id',$loan_id)->sum('interest');
        $this->totalPrinciple=loans_schedules::where('loan_id',$loan_id)->sum('principle');
        $this->totalPenalties=loans_schedules::where('loan_id',$loan_id)->sum('penalties');
        $this->installments=loans_schedules::where('loan_id',$loan_id)->get();

    }



    public function builder()
    {

        return LoansModel::query()->where('member_number',session()->get('viewMemberLoan'));
    }




        public function columns(): array
    {

        return [
            Column::name('id')->label('id'),
            Column::name('loan_id')->label('loan id'),
            Column::callback('loan_sub_product',function ($number){
              return   Loan_sub_products::where('sub_product_id',$number)->value('sub_product_name');
            })->label('loan product'),
            Column::name('status')->label('status'),
            Column::callback(['loan_id','id'],function ($loan_id,$id){
                   $value=LoansModel::where('loan_id',$loan_id)->get();
                return view('livewire.loans.member-history',['values'=>$value]);
            })->label('action'),
        ];
    }

    public function downloadExcel($loandId){

        return    Excel::download(new  LoanSchedule([$loandId]) , 'loanSchedule.xlsx');
    }

    public function Installment(){
        $this->selectPaymentSchedule=false;
        $this->emit('refreshPage');
    }

    public function LoanRepayment(){
        $this->selectPaymentSchedule=true;

        $this->repayment=general_ledger::where('loan_id',session()->get('memberLoanId'))->get();

         $this->product_name=DB::table('loan_sub_products')->where('id',LoansModel::where('loan_id',$this->loanId)->value('loan_sub_product') )->value('sub_product_name');
         $this->emit('refreshPage');
    }

}
