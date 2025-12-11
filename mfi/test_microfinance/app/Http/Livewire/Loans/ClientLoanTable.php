<?php

namespace App\Http\Livewire\Loans;

use App\Models\BranchesModel;
use App\Models\ClientsModel;
use App\Models\Employee;
use App\Models\LoansModel;
use App\Models\sub_products;
use App\Models\loans_schedules;
use App\Exports\LoanSchedule;

use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ClientLoanTable extends LivewireDatatable
{

    public $loanId;
    public $installments;
    public $exportable=true;
    public $totalinterest;
    public $totalInstallment;
    public $totalPrinciple;
    public $totalPenalties;
    public $repayment;

    protected $listeners=[
      'refreshPage'=>'$refresh'
    ];

    public function closeInstallment(){

        $this->loanId=null;
        $this->installments=null;
        $this->emit('refreshPage');
    }

    public function viewInstallment($loan_id){

        $this->loanId=$loan_id;
        $this->totalInstallment=loans_schedules::where('loan_id',$loan_id)->sum('installment');
        $this->totalinterest=loans_schedules::where('loan_id',$loan_id)->sum('interest');
        $this->totalPrinciple=loans_schedules::where('loan_id',$loan_id)->sum('principle');
        $this->totalPenalties=loans_schedules::where('loan_id',$loan_id)->sum('penaties');
        $this->installments=loans_schedules::where('loan_id',$loan_id)->get();

    }

    public function builder()
     {

         if(ClientsModel::where('branch_id', auth()->user()->branch)->value('branch_id') == 25){
             return ClientsModel::query();

         }else{
             return ClientsModel::query()->where('branch_id', auth()->user()->branch);
         }


     }

    public function columns(): array
    {

        return [
            Column::callback(['id','client_number','client_number'], function($id, $firstName, $middleName) {
                $clientName = ClientsModel::where('id', $id)
                    ->selectRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) as client_name")
                    ->value('client_name');

                return $clientName;
            })->label('client name')->searchable(),

            Column::name('client_number')
                ->label('client number')->searchable(),

            Column::callback(['client_number','id'],function ($client_number,$id){
                       $loan_data=LoansModel::where('client_number',$client_number)->count();
                return $loan_data;

            })->label('Total Loans')->searchable(),

            Column::callback(['client_number','client_status'],function ($client_number,$status){
                       $activeLoan=LoansModel::where('client_number',$client_number)->where('status',"ACTIVE")->count();
                return $activeLoan;

            })->label('Active loan')->searchable(),

          Column::callback(['client_number','client_status','id'],function ($client_number,$status,$id){
                     $htmlCode=' <div class="flex justify-center item-center">
  <button wire:click="viewMemberLoans('.$client_number.')" >
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
</svg>

</button>
</div>
';
                return $htmlCode ;

            })->label('action')->searchable(),

        ];





}

    public function viewMemberLoans($client_number){
        session()->put('viewMemberLoan',$client_number);
       $this->emitUp('viewMemberLoans',$client_number);
}

    public function downloadExcel($loandId){

      return    Excel::download(new  LoanSchedule($loandId) , 'loanSchedule.xlsx');
    }

    public function Installment(){
        $this->selectPaymentSchedule=false;
        $this->emit('refreshPage');
    }

    public function LoanRepayment(){
        $this->selectPaymentSchedule=true;
//        $this->repayment=
        $this->emit('refreshPage');
    }

}
