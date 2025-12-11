<?php

namespace App\Exports;

use App\Models\ClientsModel;
use App\Models\LoansModel;
use App\Models\User;
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

class SubjectRelation implements FromArray,WithHeadings, WithStyles, ShouldAutoSize, WithEvents,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;

    public $values;


    public function __construct($values)
    {
        $this->values=$values;
    }


    public function array():array
    {

        $array=[];

     //  $clientId=LoansModel::whereIn('id',$this->values)->pluck('client_number');




        $client_numbers=$this->values;


        foreach ($client_numbers as $number){

           // $guarantor=LoansModel::where('id',)->where('guarantor','!=',null)->pluck('guarantor');

            $clientId=LoansModel::where('id',$number)->first();

            $clientData=ClientsModel::where('client_number',$clientId->client_number)->first();

            $array[]=[
                'CustomerCodeofPrimarySubject'=>$clientId->client_number,
                'RelationType'=>$clientId->relationship ? : null,
                'CustomerCodeofSecondarySubject'=>null,
                'TaxIdentificationNumber'=>$clientData->tax_identification_number,
                'NationalID'=>$clientData->national_id,
                'RegistrationNumber'=>$clientData->registration_number,
                'FullName'=>$clientData->first_name.' '.$clientData->middle_name.' '.$clientData->last_name,
                'Phone'=>$clientData->phone_number,
                'AdditionalInformation'=>null,
                'Street'=>$clientData->street,
                'NumberofBuilding'=>$clientData->number_of_building,
                'PostalCode'=>$clientData->postal_code,
                'Region'=>$clientData->region,
                'District'=>$clientData->district,
                'Country'=>$clientData->country,
            ];
        }

        return $array;
    }

    public function headings(): array
    {
        return [
            'Customer Code of Primary Subject',
            'Relation Type',
            'Customer Code of Secondary Subject',
            'Tax Identification Number',
            'National ID',
            'Registration Number',
            'Full Name',
            'Phone',
            'Additional Information',
            'Street',
            'Number of Building',
            'Postal Code',
            'Region',
            'District',
            'Country'
        ];
    }

    public function styles(Worksheet|\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFF00']],
            ],
        ];
    }

    public function title(): string
    {
        return "SUBJECTRELATION";
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
