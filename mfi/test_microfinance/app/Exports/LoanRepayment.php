<?php

namespace App\Exports;

use App\Models\ClientsModel;
use App\Models\general_ledger;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LoanRepayment implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithDrawings
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public $value;
    public $institutionName;
    public $logoPath; // Path to the institution logo
    public $accountName;
    public $openingBalance;
    public $closingBalance;
    public $reportGenerationDate;
    public $sheet;

    public function __construct($value, $institutionName, $logoPath, $accountName, $openingBalance, $closingBalance, $reportGenerationDate)
    {
        $this->value = $value;
        $this->institutionName = $institutionName;
        $this->logoPath = $logoPath;
        $this->accountName = $accountName;
        $this->openingBalance = $openingBalance;
        $this->closingBalance = $closingBalance;
        $this->reportGenerationDate = $reportGenerationDate;
    }

    public function array(): array
    {
        $data = [];
        return $data;
    }

    public function headings(): array
    {
        return [
        ];
    }

    public function styles(Worksheet|\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        return [
        ];
    }

    /**
     * @throws Exception
     */
    public function drawings(): array
    {

        $drawing = new Drawing();
        $drawing->setName('Institution Logo');
        $drawing->setDescription('Institution Logo');
        $drawing->setPath(public_path($this->logoPath)); // Set the path to your institution logo
        $drawing->setHeight(75);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(350);
        $drawing->setOffsetY(50);
        $drawing->setWorksheet($this->sheet);


        return [$drawing];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $event->sheet->mergeCells('A1:E5');
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Add additional information
                $event->sheet->mergeCells('A6:E6');
                $event->sheet->setCellValue('A6', $this->institutionName);
                $event->sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Add account details
                //$event->sheet->mergeCells('A2:B2');
                $event->sheet->setCellValue('A7', 'Account Name:');
                $event->sheet->setCellValue('B7', $this->accountName);
                $event->sheet->getStyle('B7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $event->sheet->setCellValue('A8', 'Account Number:');
                $event->sheet->setCellValue('B8', $this->accountName);
                $event->sheet->getStyle('B8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                //$event->sheet->mergeCells('A3:B3');
                $event->sheet->setCellValue('A9', 'Opening Balance:');
                $event->sheet->setCellValue('B9', number_format((float)$this->openingBalance));
                $event->sheet->getStyle('B9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                //$event->sheet->mergeCells('A4:B4');
                $event->sheet->setCellValue('A10', 'Closing Balance:');
                $event->sheet->setCellValue('B10', number_format((float)$this->closingBalance));
                $event->sheet->getStyle('B10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                //$event->sheet->mergeCells('A5:B5');
                $event->sheet->setCellValue('A11', 'Report Generation Date:');
                $event->sheet->setCellValue('B11', $this->reportGenerationDate);
                $event->sheet->getStyle('B11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);


                $event->sheet->setCellValue('A' . 14, 'Created At');
                $event->sheet->setCellValue('B' . 14, 'Narration');
                $event->sheet->setCellValue('C' . 14, 'Debit (TZS)');
                $event->sheet->setCellValue('D' . 14, 'Credit (TZS)');
                $event->sheet->setCellValue('E' . 14, 'Balance(TZS)');

                $event->sheet->getStyle('A14:E14')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'DDDDDD']],
                ]);
                // Start adding transactions from A7
                $rowIndex = 15;
                    foreach (general_ledger::where('record_on_account_number', $this->value)->get() as $transaction) {
                        $event->sheet->setCellValue('A' . $rowIndex, $transaction->created_at);
                        $event->sheet->setCellValue('B' . $rowIndex, $transaction->narration);
                        $event->sheet->setCellValue('C' . $rowIndex, number_format((float)$transaction->debit) ?: "00");
                        $event->sheet->getStyle('C' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $event->sheet->setCellValue('D' . $rowIndex, number_format((float)$transaction->credit) ?: "00");
                        $event->sheet->getStyle('D' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $event->sheet->setCellValue('E' . $rowIndex, number_format((float)$transaction->record_on_account_number_balance) ?: "00");
                        $event->sheet->getStyle('E' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $rowIndex++;
                    }

            },
        ];
    }

}
