<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\BranchesModel;
use App\Models\ClientsModel;
use App\Models\Employee;
use App\Models\LoansModel;
use App\Models\sub_products;
use App\Models\loans_schedules;
use App\Exports\LoanSchedule;
use Illuminate\Support\Facades\DB;
use App\Exports\LoanScheduleReport;

use Maatwebsite\Excel\Facades\Excel;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class PortifolioAtRiskTable extends LivewireDatatable
{
    public $exportable=true;
    public $par,$parAbove;

    protected $listeners=['setRangeBetween','setAbove'];



    public function setRangeBetween($value){

        $this->par=$value;
        $this->parAbove=null;
    }

    public function setAbove($value){
        $this->parAbove=$value;
        $this->par=null;

    }


    public function builder()
    {

        $query =LoansModel::query()->where('status','ACTIVE');

        if(!empty($this->par)){
          $query->whereBetween('days_in_arrears',$this->par);
        }


        if(!empty($this->parAbove)){
        $query->where('days_in_arrears','>=',$this->parAbove);

        }



        return $query;
          //  return ClientsModel::query()->where('branch_id', auth()->user()->branch);
    }




    public function columns(): array
    {

        return [

            Column::index($this),

          //  Column::name('loan_id')->label('loan Id'),

            Column::callback(['client_number'], function( $client_number) {
                $clientName = ClientsModel::where('client_number', $client_number)
                    ->selectRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) as client_name")
                    ->value('client_name');

                return $clientName;
            })->label('client name')->searchable(),

            Column::name('principle')
                ->label('Loan Amount')->searchable(),



                Column::callback('id', function ($id) {
                    // Check if $id is valid and numeric
                    if (!is_numeric($id) || empty($id)) {
                        return 'N/A'; // Return default if $id is invalid
                    }

                    // Retrieve the oldest installment date for this loan_id
                    $installmentDate = loans_schedules::where('loan_id', $id)->oldest()->value('installment_date');

                    // If there's no matching entry in loans_schedules, return 'N/A'
                    return $installmentDate ?? 'N/A';
                })->label('start date')->searchable(),

                Column::callback(['loan_id'], function ($loan_id) {
                    // Find the loan by loan_id in LoansModel
                    $loan = LoansModel::where('loan_id', $loan_id)->first();

                    // If no loan found, return 'N/A'
                    if (!$loan) {
                        return 'N/A';
                    }

                    $id = $loan->id;

                    // Retrieve the latest installment date for this loan_id
                    $dueDate = loans_schedules::where('loan_id', $id)->latest()->value('installment_date');

                    // If there's no matching entry or no installment_date, return 'N/A'
                    return $dueDate ?? 'N/A';
                })->label('Due Date')->searchable(),


            Column::name('interest')->label('interest'),

            Column::callback('loan_id',function ($loan_id){

                $loan = LoansModel::where('loan_id', $loan_id)->first();

                $query=loans_schedules::query()->where('loan_id',$loan->id);
                $amount=  $query->sum('principle') - ($query->sum('payment') ? $query->sum('payment') -  $query->sum('interest') : 0);

                return number_format($amount,1);
                //return

              //  $payment= loans_schedules::where('loan_id', $loan->id)->sum('payment');

            })->label('OutStanding Amount')->searchable(),


            Column::name('days_in_arrears')->label('days in arrears'),


            Column::name('status')->label('status'),



            Column::callback(['id'],function ($id){
            // $ids=DB::table('loans')->pluck('id')->toArray();
            // return Excel::download(new LoanScheduleReport($id ),'LoanScheduleReport.xlsx');
                 $html = '<div class="w-8 h-8 bg-blue-900 rounded-full cursor-pointer" wire:click="download('.$id.')">';
                    $html .= '<svg data-slot="icon" fill="white" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">';
                    $html .= '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v6.75m0 0-3-3m3 3 3-3m-8.25 6a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z"></path>';
                    $html .= '</svg></div>';
                    return $html;


            })->label('Download schedule')->searchable()


        ];

}



function download($id){

 return Excel::download(new LoanScheduleReport($id ),'LoanScheduleReport.xlsx');

}


}
