<?php

namespace App\Exports;

use App\Models\Accounting;
use App\Models\AccountsModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\BranchesModel;
use App\Models\Loan_sub_products;
use App\Models\loans_schedules;
use App\Models\LoansModel;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Support\Facades\DB;

class LoanReport implements  FromArray, WithHeadings
{
    use Exportable;

    public $value;


    public function __construct($value)
    {
        $this->value=$value;
    }



    public function array(): array
    {
        $array = [];
        $client_numbers = $this->value;

        foreach ($client_numbers as $number) {
            $loanData = LoansModel::where('id', $number)->first();



            $array[] = ['PRODUCT_TYPE' => $loanData->loanProduct->sub_product_name ?? null,
            'LOAN_ACCOUNT' => $loanData->loan_account_number ?? null,
            'CASA_ACCOUNT' => $loanData->casa_account ?? null,

            'CUSTOMER_ID' => $loanData->client_number ?? null,
            'CUSTOMER_NAME' => $loanData->clientName->first_name.' '.$loanData->clientName->middle_name.' '.$loanData->clientName->last_name,

            'ACCRUAL_INTEREST_STATUS' =>  'NO', //$loanData->interest_status ?? null,
            'BRANCH_CODE' => $loanData->loanBranch->branch_number ?? null,
            'BRANCH_NAME' =>$loanData->loanBranch->name ?? null,

            'TOTAL_MORATORIUM_DAYS' => $loanData->grace_days ?? null , // $loanData->moratorium_days ?? null,

            'NEXT_DUE_DATE' => $this->getEarliestDisbursementDate($loanData->id) , //
            'OUTSTANDING_EMI' => $this->getOutstandingEMI($loanData->id), //

            'OVERDUE_EMI' => $this->getOverdueEMI($loanData->id), //
            'OPEN_DATE' => $loanData->created_at->format('Y-M-d') ?? null,
            'LIMIT_DISBURSED_DATE' => $loanData->updated_at->format('Y-M-d') ?? null, //

            'LIMIT_MATURITY_DATE' => $this->getLimitMaturityDate($loanData->id) ?? null, //
            'LAST_PAID_DATE' => $this->getLastPaidDate($loanData->id) ?? null, //
            'LAST_PAID_AMT_TZS' => $this->getLastPaidAmount($loanData->id) ?? 0, //
            'PAYMENT_FREQUENCY' => $loanData->payment_frequency ?? 'Monthly', //
            'LOAN_OD_TENURE' => $loanData->tenure ?? null, //

            'MONTH_ON_BOOK' => $loanData->month_on_book ?? null, //TODO
            'DAYS_OVERLINE' => $loanData->days_overline ?? null, //TODO

            'PAYMENT_CYCLE' => $this->getPaymentCycle($loanData->id)?? null,

            'CYCLE_LASTMONTH_2' => $loanData->cycle_lastmonth_2 ?? null, //TODO
            'CYCLE_LASTMONTH_1' => $loanData->cycle_lastmonth_1 ?? null, //TODO

            'DEL_CYCLE' => $loanData->del_cycle ?? null, //TODO
            'FX_RATE' => $loanData->interest ?  $loanData->interest /$loanData->tenure :null ,
            'CURRENCY' => $loanData->currency ?? 'TZS',

            'LIMIT_DISBURSED_TZS' => $loanData->principle ?? null,
            'AMT_PROFIT_SHARE_TZS' => $loanData->amt_profit_share_tzs ?? null, //TODO

            'BALANCE_TZS' => $this->getClosingBalance($loanData->id) ?? null,

            'INTEREST_IN_SUSPENSE_TZS' => $this->getUnpaidInterest($loanData->id)?? null,
            'PRINCIPAL_BALANCE_TZS' =>$this->getOutstandingEMI($loanData->id)?? null,
            'INSTALMENT_AMOUNT_TZS' => $this->getInstallmentAmount($loanData->id) ?? null,
            'ARREARS_EXCESS_TZS' => $loanData->arrears_excess ?? null, //TODO
            'INTEREST_RATE' => $loanData->interest." %" ?? null,
            'PROFIT_RATE' => $loanData->interest ?? null,
            'PREV_SEGMENT_CODE' => $loanData->prev_segment_code ?? null, //TODO
            'ACCOUNT_SEGMENT_CODE' => $loanData->account_segment_code ?? null, //TODO
            'SEGMENT_NAMING' => $loanData->segment_naming ?? null, //TODO
            'END_DATE' => $loanData->end_date ?? null, //TODO
            'NET_EXPOSURE_TZS' => $loanData->net_exposure_tzs ?? null, //TODO
            'NTD' => $loanData->ntd ?? null, //TODO
            'STRAIGHT_ROLLER' => $loanData->straight_roller ?? null, //TODO
            'BUCKET_JUMP' => $loanData->bucket_jump ?? null, //TODO
            'MOVEMENT' => $loanData->movement ?? null, //TODO
            'FLOW' => $loanData->flow ?? null, //TODO
            'CURRENT_INT_RATE' => $loanData->current_int_rate ?? null, //TODO
            'FINAL_INSTALMENT_DATE' => $loanData->final_instalment_date ?? null, //TODO
            'CURRENT_INSTALMENT_TZS' => $loanData->current_instalment_tzs ?? null, //TODO
        ];
        }

        return $array;
    }


    function getLastPaidDate($loan_id)
{
    // Retrieve the latest payment date (payment_date) for the given loan_id
    $lastPaidDate = DB::table('loans_schedules')
        ->where('loan_id', $loan_id)
        ->whereIn('completion_status', ['ACTIVE', 'PARTIAL'])
        ->orderByDesc('installment_date') // Order by payment_date in descending order
        ->value('installment_date');
        // Get the value of the payment_date column

    return $lastPaidDate;
}


function getInstallmentAmount($loan_id)
{
    // Retrieve the latest payment date (payment_date) for the given loan_id
    $lastPaidDate = DB::table('loans_schedules')
        ->where('loan_id', $loan_id)
        ->orderByDesc('installment_date') // Order by payment_date in descending order
        ->value('installment');

    return $lastPaidDate;
}



function getUnpaidInterest($loan_id)
{
    // Retrieve the latest payment date (payment_date) for the given loan_id
    $lastPaidDate = DB::table('loans_schedules')
        ->where('loan_id', $loan_id)
        ->whereIn('completion_status', ['ACTIVE', 'PARTIAL'])
        ->sum('interest');

    return $lastPaidDate;
}

function getPaymentCycle($loan_id)
{
    // Retrieve the latest payment date (payment_date) for the given loan_id
    $getPaymentCycle = DB::table('loans_schedules')
        ->where('loan_id', $loan_id)
        ->whereIn('completion_status', ['CLOSED', 'PARTIAL'])
        ->count() // Order by payment_date in descending order
        ;
    return $getPaymentCycle;
}


function getLastPaidAmount($loan_id)
{
    // Retrieve the latest payment date (payment_date) for the given loan_id
    $lastPaidDate = DB::table('loans_schedules')
        ->where('loan_id', $loan_id)
        ->whereIn('completion_status', ['ACTIVE', 'PARTIAL'])
        ->orderByDesc('installment_date') // Order by payment_date in descending order
        ->value('payment');
        // Get the value of the payment_date column

    return $lastPaidDate;
}


function getClosingBalance($loan_id)
{
    // Retrieve the latest payment date (payment_date) for the given loan_id
    $lastPaidDate = DB::table('loans_schedules')
        ->where('loan_id', $loan_id)
        ->whereIn('completion_status', ['ACTIVE', 'PARTIAL'])
        ->orderByDesc('installment_date') // Order by payment_date in descending order
        ->value('closing_balance');
        // Get the value of the payment_date column

    return $lastPaidDate;
}



    function getEarliestDisbursementDate($loan_id)
{
    // Query the loans_schedule table
    $disbursement = DB::table('loans_schedules')
        ->where('loan_id', $loan_id)
        ->whereIn('completion_status', ['ACTIVE', 'PARTIAL'])
        ->orderBy('installment_date', 'asc') // Sort by date in ascending order
        ->first(['installment_date']); // Retrieve only the disbursement_date column

    // Return the date if found, or null if not found
    return $disbursement ? $disbursement->installment_date : null;
}

function getLimitMaturityDate($loan_id)
{
    // Query the loans_schedules table to get the latest installment_date by max id
    $maturity = DB::table('loans_schedules')
        ->where('loan_id', $loan_id)
        ->whereIn('completion_status', ['ACTIVE', 'PARTIAL'])
        ->orderBy('id', 'desc') // Order by id to get the latest entry
        ->first(['installment_date']); // Retrieve only the installment_date column

    // Return the date if found, or null if not found
    return $maturity ? $maturity->installment_date : null;
}




function getOverdueEMI($loan_id)
{
    // Get current date to compare against due dates
    $currentDate = Carbon::now();

    // Retrieve overdue installments for the given loan_id where payment_status is not CLOSED
    $overdueSchedules = DB::table('loans_schedules')
        ->where('loan_id', $loan_id)
        ->where('completion_status', '!=', 'CLOSED')
        ->whereDate('installment_date', '<', $currentDate) // Only overdue
        ->get();

    $overdueEMI = 0;

    foreach ($overdueSchedules as $schedule) {
        // Sum up the principal and interest for overdue installments
        $overdueEMI += $schedule->principle + $schedule->interest;
    }

    return $overdueEMI;
}




function getOutstandingEMI($loan_id)
{
    // Retrieve loan schedule entries for the given loan_id where payment_status is ACTIVE or PARTIAL
    $loanSchedules = DB::table('loans_schedules')
        ->where('loan_id', $loan_id)
        ->whereIn('completion_status', ['ACTIVE', 'PARTIAL'])
        ->get();

    $outstandingEMI = 0;

    foreach ($loanSchedules as $schedule) {
        // If payment status is ACTIVE, the full principal amount is outstanding
        if ($schedule->completion_status === 'ACTIVE') {
            $outstandingEMI += $schedule->principle;
        } elseif ($schedule->completion_status === 'PARTIAL') {
            // For PARTIAL, calculate the principal remaining after partial payments
            $principalPaid = max(0, $schedule->payment - $schedule->interest); // Ensure non-negative values
            $remainingPrincipal = $schedule->principle - $principalPaid;
            $outstandingEMI += max(0, $remainingPrincipal); // Add remaining principal, ensure non-negative
        }
    }

    return $outstandingEMI;
}



    public function headings(): array
    {
        return [
            'PRODUCT_TYPE', 'LOAN_ACCOUNT', 'CASA_ACCOUNT', 'CUSTOMER_ID', 'CUSTOMER_NAME',
            'ACCRUAL_INTEREST_STATUS', 'BRANCH_CODE', 'BRANCH_NAME', 'TOTAL_MORATORIUM_DAYS',
            'NEXT_DUE_DATE', 'OUTSTANDING_EMI', 'OVERDUE_EMI', 'OPEN_DATE', 'LIMIT_DISBURSED_DATE',
            'LIMIT_MATURITY_DATE', 'LAST_PAID_DATE', 'LAST_PAID_AMT_TZS', 'PAYMENT_FREQUENCY',
            'LOAN_OD_TENURE', 'MONTH_ON_BOOK', 'DAYS_OVERLINE', 'PAYMENT_CYCLE', 'CYCLE_LASTMONTH_2',
            'CYCLE_LASTMONTH_1', 'DEL_CYCLE', 'FX_RATE', 'CURRENCY', 'LIMIT_DISBURSED_TZS',
            'AMT_PROFIT_SHARE_TZS', 'BALANCE_TZS', 'INTEREST_IN_SUSPENSE_TZS', 'PRINCIPAL_BALANCE_TZS',
             'INSTALMENT_AMOUNT_TZS', 'ARREARS_EXCESS_TZS',
            'INTEREST_RATE', 'PROFIT_RATE', 'PREV_SEGMENT_CODE', 'ACCOUNT_SEGMENT_CODE',
            'SEGMENT_NAMING', 'END_DATE', 'NET_EXPOSURE_TZS', 'NTD', 'STRAIGHT_ROLLER',
            'BUCKET_JUMP', 'MOVEMENT', 'FLOW', 'CURRENT_INT_RATE', 'FINAL_INSTALMENT_DATE',
            'CURRENT_INSTALMENT_TZS'
        ];
    }



}
