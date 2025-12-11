<?php

namespace App\Exports;

use App\Models\ClientsModel;
use App\Models\general_ledger;
use App\Models\LoansModel;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet;

class Company implements FromArray,WithHeadings, WithStyles, ShouldAutoSize, WithEvents,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $value;



    public function __construct($value){
        $this->value=$value;
    }


    public function array():array
    {

     $array=[];


      $users=User::all();

        $client_numbers=$this->value;

        $loan_datas=LoansModel::whereIn('id',$client_numbers)->pluck('client_number');
        //guarantor
        $clientDatas=ClientsModel::whereIn('client_number',$loan_datas)->where('membership_type','!=','individual')->pluck('client_number');



        foreach ($clientDatas as $number){

            //guarantor
            $clientData=ClientsModel::where('client_number',$number)->first();

          $array[]=[

              'CustomerCode'=>$clientData->client_number,
              'CompanyName'=>$clientData->first_name,
              'TradeName'=>$clientData->trade_name,
              'LegalForm'=>$clientData->legal_form,
              'EstablishmentDate'=>$clientData->establishment_date,
              'RegistrationCountry'=>$clientData->registration_country,
              'IndustrySector'=>$clientData->industry_sector,
              'RegistrationNumber'=>$clientData->registration_number,
              'TaxIdentificationNumber'=>$clientData->tax_identification_number,
              'Street'=>$clientData->street,
              'NumberOfBuilding'=>$clientData->number_of_building,
              'PostalCode'=>$clientData->postal_code,
              'Region'=>$clientData->region,
              'District'=>$clientData->district,
              'Country'=>$clientData->country,
              'MobilePhone'=>$clientData->mobile_phone,
              'FixedLine'=>$clientData->fixed_line,
              'E-mail'=>$clientData->email,
              'WebPage'=>$clientData->web_page,
      ];
      }

      return $array;
    }


    public function title(): string
    {
        return "COMPANY";
    }

    public function headings(): array
    {
        return [
            'Customer Code',
            'Company Name',
            'Trade Name',
            'Legal Form',
            'Establishment Date',
            'Registration Country',
            'Industry Sector',
            'Registration Number ',
            'Tax Identification Number ',
            'Street ',
            'Number of Building ',
            'Postal Code ',
            'Region ',
            'District',
            'Country',
            'Mobile Phone',
            'Fixed Line',
            'E-mail',
            'Web Page',
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
