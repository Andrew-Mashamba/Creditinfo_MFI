<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;
use App\Models\LoansModel;

class Dashboard extends Component
{

    public $loan_summary=[];
    public $total_new_loan;
    public $total_new_amount;
    public $total_topup_amount;
    public $total_topup_loan;
    public $total_restructure_amount;
    public $total_restructure_loan;
    public $total_deviation_queue_amount;
    public $total_deviation_queue_loan;
    public function visit($id){

    }

    function getNewLoan(){
        return LoansModel::query()->where('loan_type_2','New')->whereNull('loan_type_3');
    }
    function loanSummary(){

        $loan=LoansModel::query();


        $this->loan_summary=[
            'loan_officer'=>100,
            'portfolio_manager'=>568,
            'loans_management_committee'=>9,
            'signatories'=>24,
            'accounting'=>233,
        ];


    }



    public function render()
    {

        // new loan
        $this->total_new_amount=$this->getNewLoan()->sum('principle');
         $this->total_new_loan=$this->getNewLoan()->count();

         //topup loan
        $this->total_topup_amount=$this->getTopUpLoan()->sum('principle');
        $this->total_topup_loan=$this->getTopUpLoan()->count();

        //restructure
         $this->total_restructure_amount=$this->getRestructureLoan()->sum('principle');
         $this->total_restructure_loan=$this->getRestructureLoan()->count();

         //deviation queue
         $this->total_deviation_queue_amount=$this->getDeviationQueueLoan()->sum('principle');
         $this->total_deviation_queue_loan=$this->getDeviationQueueLoan()->count();



        $this->loanSummary();
        return view('livewire.loans.dashboard');
    }


    function getTopUpLoan(){
        return LoansModel::query()->where('loan_type_2','Top Up')->whereNull('loan_type_3');
    }



    function getRestructureLoan(){
        return LoansModel::query()->where('loan_type_2','Restructure')->whereNull('loan_type_3');
    }


    function getDeviationQueueLoan(){
        return LoansModel::query()->where('loan_type_3','Exception');
    }

    /**
     * Navigate to loans filtered by product and approval stage
     */
    public function viewLoansByStage($productId, $stageName)
    {
        // Map stage names to appropriate filters
        $filterParams = [
            'product' => $productId,
        ];
        
        // Add stage-specific filters
        switch ($stageName) {
            case 'Inputter':
                $filterParams['approval_stage'] = 'Inputter';
                break;
            case 'First Checker':
                $filterParams['approval_stage'] = 'first_checker';
                break;
            case 'Second Checker':
                $filterParams['approval_stage'] = 'second_checker';
                break;
            case 'Approver':
                $filterParams['approval_stage'] = 'approver';
                break;
            case 'Approved':
                $filterParams['approval_stage'] = 'approved';
                break;
            case 'Rejected':
                $filterParams['approval_stage'] = 'rejected';
                break;
            default:
                // Check if it's a direct approval stage value
                if (in_array($stageName, ['Inputter', 'first_checker', 'second_checker', 'approver', 'approved', 'rejected'])) {
                    $filterParams['approval_stage'] = $stageName;
                } else {
                    $filterParams['status'] = $stageName; // Fallback for direct status
                }
                break;
        }
        
        // Redirect to loans table with filters
        return redirect()->route('loans.index', $filterParams);
    }

}
