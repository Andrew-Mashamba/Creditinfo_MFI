<?php

namespace App\Exports;

use App\Models\BranchesModel;
use App\Models\Loan_sub_products;
use App\Models\loans_schedules;
use App\Models\LoansModel;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet;

class ContractData implements FromArray,WithHeadings, WithStyles, ShouldAutoSize, WithEvents,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;

    public $value;


    public function __construct($value)
    {
        $this->value=$value;
    }


    public function array():array
    {

        $array=[];
        $users=User::get();

        $client_numbers=$this->value;



        foreach ($client_numbers as $number){

            $loanData=  LoansModel::where('id',$number)->first();
            $startDate = Carbon::now()->startOfMonth(); // Get the first day of the current month
            $endDate = Carbon::now()->endOfMonth();


            $array[]=[
                'report_date'=>$loanData->created_at->format('Y-m-d'),
                'contract_code'=>$loanData->loan_id,
                'customer_code'=>$loanData->client_number,
                'Branch'=>BranchesModel::where('id',$loanData->branch_id)->value('name'),
                'phase_of_contract'=>null,
                'TransferStatus'=>'NO',
                'TypeofContract'=>'Individual',
                'PurposeofFinancing'=>Loan_sub_products::where('sub_product_id',$loanData->loan_sub_product)->value('sub_product_name'),
                'InterestRate'=>$loanData->interest,
                'TotalAmount'=>$loanData->principle,
                'TotalTakenAmount'=>(double)$loanData->principle -(double)$loanData->total_principle-(double)$loanData->future_interest,
                'InstallmentAmount'=>loans_schedules::where('loan_id',$loanData->loan_id)->sum('installment'),
                'NumberofInstallments'=>loans_schedules::where('loan_id',$loanData->loan_id)->count(),
                'NumberofOutstandingInstallments'=>(loans_schedules::where('loan_id',$loanData->loan_id)->count()) - (loans_schedules::where('loan_id',$loanData->loan_id) ->where('completion_status','CLOSED')->count()),
                'OutstandingAmount'=>loans_schedules::where('loan_id',$loanData->loan_id) ->where('completion_status','CLOSED')->sum('installment'),
                'Past Due Amount'=>null,
                'PastDueDays'=>$loanData->days_in_arrears,
                'NumberOfDueInstallments'=>null,
                'AdditionalFeesSum'=>loans_schedules::where('loan_id',$loanData->loan_id)->sum('penalties'),
                'AdditionalFeesPaid'=>loans_schedules::where('loan_id',$loanData->loan_id)->where('completion_status','CLOSED')->sum('penalties'),
                'DateofLastPayment'=>loans_schedules::where('loan_id',$loanData->loan_id)->where('completion_status','CLOSED')->max('updated_at'),
                'TotalMonthlyPayment'=>loans_schedules::where('loan_id', $loanData->loan_id)
                                            ->where('completion_status', 'CLOSED')
                                            ->whereBetween('updated_at', [$startDate, $endDate])->sum('payment'),
                'PaymentPeriodicity'=>null,
                'CreditUsageinLast30Days'=>null,
                'StartDate'=>loans_schedules::where('loan_id',$loanData->loan_id)->min('installment_date'),
                'ExpectedEndDate'=>loans_schedules::where('loan_id',$loanData->loan_id)->max('installment_date'),
                'RealEndDate'=>loans_schedules::where('loan_id',$loanData->loan_id)->max('installment_date') < loans_schedules::where('loan_id',$loanData->loan_id)->max('updated_at')  ? : null,
                'NegativeStatusoftheContract'=>'constant',
                'CollateralType'=>$loanData->collateral_type,
                'CollateralValue'=>$loanData->collateral_value,
                'RoleofCustomer'=>null,
                'CurrencyofContract'=>'TZS'







            ];
        }


        return $array;
    }


    public function title(): string
    {
        return "CONTRACT";
    }


    public function headings(): array
    {
        return [
            ' Reporting Date',
            'Contract code',
            'Customer Code',
            'Branch',
            'Phase of Contract',
            'Transfer Status',
            'Type of Contract',
            'Purpose of Financing',
            'Interest Rate',
             'Total Amount',
            'Total Taken Amount',
            'Installment Amount',
             'Number of Installments',
             'Number of Outstanding Installments',
            'Outstanding Amount',
            'Past Due Amount',
            'Past Due Days',
            'Number of Due Installments',
            'Additional Fees Sum',
            'Additional Fees Paid',
            'Date of Last Payment',
            'Total Monthly Payment',
            'Payment Periodicity',
            'Credit Usage in Last 30 Days',
            'Start Date',
            'Expected End Date',
            'Real End Date',
            'Negative Status of the Contract',
            'Collateral Type',
            'Collateral Value',
            'Role of Customer',
            'Currency of Contract',


        ];
    }

    public function styles(Worksheet|\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'ADD8E6']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:F1')->applyFromArray([
                    'font' => [
                        'color' => ['argb' => 'FF0000'],
                    ],
                ]);
            },
        ];
    }

}
