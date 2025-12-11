<?php

namespace App\Exports;
use App\Models\Accounting;
use App\Models\AccountsModel;
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
use Maatwebsite\Excel\Concerns\FromCollection;

class LoanScheduleReport implements  FromArray, WithHeadings

{
    /**
    * @return \Illuminate\Support\Collection
    */

    public $loan_id;

    public function __construct($value)
    {
        $this->loan_id=$value;
    }

    public function array(): array
    {
        $array = [];

        $loan_schedule=loans_schedules::where('loan_id',$this->loan_id)->get();
       $i=1;
        foreach ($loan_schedule as $data) {

            $array[] = [

            'S/N' => $i ,
            'DATE' => $data->installment_date ?? null,
            'INSTALLMENT' => $data->installment ?? null,
            'INTEREST' => $data->interest ?? null,
            'PRINCIPLE' => $data->principle ?? null,
            'PAYMENT' => $data->payment ?? null,
            'STATUS' => $data->completion_status ?? null,
            'PENALTIES' => $data->penalties ?? null,
        ];

        $i++;

    }
        return $array;

    }


    public function headings(): array
    {
        return [
            'S/N' ,
            'DATE' ,
            'INSTALLMENT',
            'INTEREST',
            'PRINCIPLE',
            'PAYMENT',
            'STATUS',
            'PENALTIES',
        ];

    }
}
