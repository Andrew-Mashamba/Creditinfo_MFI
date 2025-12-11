<?php

namespace App\Http\Livewire\Files;

use App\Exports\CustomExport;
use App\Models\NodesList;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Imports\ImportTransactions;

use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class Files extends Component
{
    use WithFileUploads;


    public $startDate;
    public $selectedDate;
    public $nodes;
    public $nodeName;
    public $excelFile;
    public $processedTransactions = null;
    /**
     * @var array|mixed
     */
    public $importErrors;

    public function boot(): void
    {

        $Cdate = now()->startOfDay()->format('Y-m-d');
        $this->startDate = $Cdate;
        $this->selectedDate = $Cdate;

        Session::put('sessionValueDate',$this->selectedDate);


    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $timestamp = strtotime($this->startDate);
        $formatted_date = date('Y-m-d', $timestamp);

              // Set initial date value to today's date
        if (!$this->selectedDate) {
            $this->selectedDate = date('Y-m-d');
        }

        $this->nodes = NodesList::where('NODE_DATA_SOURCE', 'File')->get();
        return view('livewire.files.files');
    }



    public function save()
    {

        $validatedData = $this->validate([
            'nodeName' => 'required|string|max:255',
            'startDate' => 'required|date',
            'excelFile' => 'required|file|mimes:xlsx,csv|max:50000'
        ]);

        Session::put('nodeName', $this->nodeName);
        Session::put('sessionID', Str::random(8));
        //Excel::import(new ImportTransactions, $this->excelFile->store('files'));

        $import = new ImportTransactions();
        $file = $this->excelFile;

        try {
            $result = Excel::import($import, $file);
            if($result){
                $this->importErrors = $import->errors;
                    $this->processedTransactions = DB::table($this->nodeName)
                                            ->where('SESSION_ID',Session::get('sessionID'))
                                            ->count();

                                            $export = new CustomExport($import->Excelrows);

                                            return Excel::download($export, $this->nodeName.'_Skipped_Transactions.xlsx');
                                            //Excel::download(new CustomExport($headers, $import->Excelrows), 'custom.xlsx');

            }else{
                $this->processedTransactions = 0;
            }
            $this->resetValues();
        } catch (ValidationException $e) {
            $failures = $e->failures();
            //dd($failures);

        }

        if($this->importErrors){
            Session::flash('message', 'Transactions have been processed with ERRORS!');
            Session::flash('alert-class', 'alert-warning');
        }else{
            Session::flash('message', 'Transactions have been succesifully processed!');
            Session::flash('alert-class', 'alert-success');
        }


    }

public function resetValues(){
    $this->excelFile = null;
    $this->dispatchBrowserEvent('clearFileInput');
    $this->startDate = now()->startOfDay()->format('Y-m-d');

}

public function updatedNodeName(){
    $this->excelFile = null;
    $this->dispatchBrowserEvent('clearFileInput');
    $this->processedTransactions = null;
    $this->importErrors = null;
    $this->startDate = now()->startOfDay()->format('Y-m-d');
}

public function updatedStartDate(){
    $this->excelFile = null;
    $this->dispatchBrowserEvent('clearFileInput');
    $this->processedTransactions = null;
    $this->importErrors = null;
    $this->nodeName = null;

}


}