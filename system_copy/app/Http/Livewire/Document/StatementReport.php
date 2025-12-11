<?php

namespace App\Http\Livewire\Document;

use Livewire\Component;
use PDF;

class StatementReport extends Component
{

    protected $listeners=['downloadPDF'=>'Download'];


    public function Download($id){

        $data = [
            'key' => 'value', // Your view data
        ];
        $pdf = PDF::loadView('livewire.document.statement-report', ['data'=>$data]);
        $pdfFileName = 'document.pdf';

        return response()->stream(
            function () use ($pdf){
                echo $pdf->output();
            },
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $pdfFileName . '"',
            ]
        );
    }


    public function render()
    {
        return view('livewire.document.statement-report');
    }
}
