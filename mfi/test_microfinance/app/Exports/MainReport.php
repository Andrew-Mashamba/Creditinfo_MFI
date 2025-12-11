<?php

namespace App\Exports;

use App\Models\LoansModel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\ContractData;
use App\Exports\IndividualData;
use App\Exports\SubjectRelation;
use App\Exports\Company;

class MainReport implements FromArray, WithMultipleSheets
{
    protected $sheets;
    public $loanId;

    public function __construct( $loanId)
    {
        $this->loanId = $loanId;
    }

    public function array(): array
    {
        return $this->sheets;
    }

    public function sheets(): array
    {
        $clientId=LoansModel::whereIn('id',$this->loanId)->pluck('client_number');

        $guarantor=LoansModel::whereIn('id',$this->loanId)->where('guarantor','!=',null)->pluck('guarantor');




        $sheets = [
            new ContractData($this->loanId),
            new IndividualData($clientId),
            new SubjectRelation($this->loanId),
            new Company($this->loanId),
        ];

        return $sheets;
}
}
