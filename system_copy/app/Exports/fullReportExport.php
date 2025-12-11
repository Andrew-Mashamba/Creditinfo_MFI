<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\getSheet;

class fullReportExport implements WithMultipleSheets
{
use Exportable;

protected $page;

    private $startDate;
    private $endDatex;
    private $thirdPart;

    public function __construct($startDate,$endDatex,$thirdPart)
    {
        $this->startDate = $startDate;
        $this->endDatex = $endDatex;
        $this->thirdPart = $thirdPart;
    }

    /**
    * @return array
    */
    public function sheets(): array
    {

    $sheets = [];

    for ($page = 1; $page <= 9; $page++) {
    $sheets[] = new getSheet($page,$this->startDate,$this->endDatex,$this->thirdPart);
    }

    return $sheets;
    }
}
