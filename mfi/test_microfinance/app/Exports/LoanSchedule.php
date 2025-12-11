<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\loans_schedules;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet;


class LoanSchedule implements FromCollection,WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{

    /**
     * @return \Illuminate\Support\Collection
     */

    public $loanId;


    public function __construct($loanId)
    {
        $this->loanId=$loanId;
    }

    public function collection()
    {

        return loans_schedules::where('loan_id',$this->loanId)->select('installment','interest','principle','installment_date','payment','penalties','completion_status')->get();
    }

    public function headings(): array
    {
        return [
            'INSTALLMENT',
            'INTEREST',
            'PRINCIPLE',
            'INSTALLMENT DATE',
            'PAYMENT',
            'PENALTIES',
            'STATUS'
        ];
    }

    public function styles(Worksheet|\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC0CB']],
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
