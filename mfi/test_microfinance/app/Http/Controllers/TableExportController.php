<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;

class TableExportController extends Controller
{
    protected $tableData;

    public function exportTableData(Request $request)
    {
        // Capture table data from request
        $this->tableData = $request->input('data');

        // Create a new export class instance with the table data
        $export = new class($this->tableData) implements FromArray {
            private $data;
            public function __construct(array $data) {
                $this->data = $data;
            }
            public function array(): array {
                return $this->data;
            }
        };

        // Return a stream download with customized headers
        return response()->streamDownload(function () use ($export) {
            Excel::store($export, 'php://output');
        }, 'table_data.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="table_data.xlsx"',
        ]);
    }
}
