<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Models\loans_schedules;
use App\Models\LoansModel;
use PDF;

class ViewInstallment extends Component
{
    public function createPDF(){
        try {
            $loanId = LoansModel::where('id', Session::get('currentloanID'))->value('loan_id');
            $getInstallments = loans_schedules::where('loan_id', $loanId)->get();
            $pdf = PDF::loadView('pdf_view', ['installments' => $getInstallments]);
            return $pdf->stream('loan_installments.pdf');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
    public function render()
    {
        $loanId = LoansModel::where('id',Session::get('currentloanID'))->value('loan_id');
        $getInstallments = loans_schedules::where('loan_id', $loanId)->get();
        return view('livewire.loans.view-installment',[
            'installments'=>$getInstallments
        ]);
    }
}
