<?php

namespace App\Exports;

use App\Models\ClientsModel;
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

class IndividualData implements FromArray,WithHeadings, WithStyles, ShouldAutoSize, WithEvents,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public $client_number;


    public function __construct($value)
    {
        $this->client_number=$value;
    }


    public function array():array
    {

        $array=[];

        $client_numbers=$this->client_number;



        foreach ($client_numbers as $number){
               $clientData=ClientsModel::where('client_number',$number)->first();

            $array[]=[

                'CustomerCode'=>$clientData->client_number,
                'PresentSurname'=>$clientData->present_surname,
                'BirthSurname'=>$clientData->birth_surname,
                'FirstName'=>$clientData->first_name,
                'MiddleNames'=>$clientData->middle_name,
                'FullName'=>$clientData->first_name.' '.$clientData->middle_name.' '.$clientData->last_name,
                'NumberofSpouse'=>$clientData->number_of_spouse,
                'NumberOfChildrens'=>$clientData->number_of_children,
                'ClassificationOfIndividual'=>$clientData->classification_of_individual,
                'DateOfBirth'=>$clientData->date_of_birth,
                'CountryOfBirth'=>$clientData->country_of_birth,
                'MaritalStatus'=>$clientData->marital_status,
                'FateStatus'=>$clientData->fate_status,
                'Socialstatus'=>$clientData->social_status,
                'Residency'=>$clientData->residency,
                'Citizenship'=>$clientData->citizenship,
                'Nationality'=>$clientData->nationality,
                'Employment'=>$clientData->employment,
                'EmployerName'=>$clientData->employer_name,
                'Education'=>$clientData->education,
                'BusinessName'=>$clientData->business_name,
                'IncomeAvailable'=>$clientData->income_available,
                'MonthlyExpenses'=>$clientData->monthly_expenses,
                'NegativeStatusofanIndividual'=>$clientData->negative_status_of_individual,
                'TaxIdentificationNumber'=>$clientData->tax_identification_number,
                'NationalID'=>$clientData->national_id,
                'PassportNumber'=>$clientData->passport_number,
                'PassportIssuerCountry'=>$clientData->passport_issuer_country,
                'DrivingLicenseNumber'=>$clientData->driving_license_number,
                'VotersID'=>$clientData->voters_id,
                'ForeignUniqueID'=>$clientData->foreign_unique_id,
                'CustomIDNumber1'=>$clientData->custom_id_number_1,
                'CustomIDNumber2'=>$clientData->custom_id_number_2,
                'Mainaddress'=>$clientData->main_address,
                'Street'=>$clientData->street,
                'NumberofBuilding'=>$clientData->number_of_building,
                'PostalCode'=>$clientData->postal_code,
                'Region'=>$clientData->region,
                'District'=>$clientData->district,
                'Country'=>$clientData->country,
                'MobilePhone'=>$clientData->mobile_phone,
                'Fixedline'=>$clientData->fixed_line,
                'E-mail'=>$clientData->email,
                'Web Page'=>$clientData->web_page,
            ];
        }

        return $array;
    }


    public function title(): string
    {
        return "INDIVIDUAL";
    }

    public function headings(): array
    {
        return [
            'Customer Code',
            'Present Surname',
            'Birth Surname',
            'First Name',
            'Middle Names',
            'Full Name',
            'Number of Spouse',
            'Number of Childrens',
            'Classification of Individual',
            'Date of Birth',
            'Country of Birth',
            'Marital Status',
            'Fate Status',
            'Social status',
            'Residency',
            'Citizenship',
            'Nationality',
            'Employment',
            'Employer Name',
            'Education',
            'Business Name',
            'Income Available',
            'Monthly Expenses',
            'Negative Status of an Individual',
            'Tax Identification Number',
            'National ID',
            'Passport Number',
            'Passport Issuer Country',
            'Driving License Number',
            'Voters ID',
            'Foreign Unique ID',
            'Custom ID Number 1',
            'Custom ID Number 2',
            'Main address',
            'Street',
            'Number of Building',
            'Postal Code',
            'Region',
            'District',
            'Country',
            'Mobile Phone',
            'Fixed line',
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
