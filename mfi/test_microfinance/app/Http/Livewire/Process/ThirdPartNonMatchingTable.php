<?php

namespace App\Http\Livewire\Process;

use App\Models\TigoTransactions;
use Livewire\Component;


use App\Models\CashBookMatchingStore;
use App\Models\cashbooknonmatchingstore;
use App\Models\Transactions;
use Illuminate\Support\Facades\Session;
use Livewire\WithFileUploads;

use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ThirdPartNonMatchingTable extends LivewireDatatable
{
    use WithFileUploads;

    protected $listeners = ['refreshTables' => '$refresh'];
    public $exportable = true;
    public $searchable="DB_TABLE_REFERENCE, NODE_DB_TABLE_DESCRIPTION";
    public $ordernumber;
    public $moveTo;
    public $change_value_date;
    public $institution;
    public $thisIsCB = false;
    public $thisIsOther;
    public $deleteEntrySet;
    public $comment;


    public function builder()
    {

       // dd(TigoTransactions::where('RECON_RESULTS',null)->get());

        $timestamp = strtotime(Session::get('sessionValueDate'));
        $formatted_date = date('Y-m-d', $timestamp);
        return TigoTransactions::query()
            ->where('RECON_RESULTS',null);


    }



    /**
     * Write code on Method
     *
     * @return array()
     */
    public function columns(): array
    {
        return [

            Column::name('DB_TABLE_REFERENCE')
                ->label('Reference Number'),

            Column::name('DB_TABLE_SECONDARY_REFERENCE')
                ->label('3rd Ref Number'),

            
                Column::callback(['DB_TABLE_AMOUNT'], function ($amount) {
                    return  number_format($amount);
                 })->label('Amount'),


                Column::callback(['DB_TABLE_SENDER'], function ($amount) {
                    return  'MOBILE BANKING';
                 })->label('CHANNEL'),


            Column::name('DB_TABLE_DESCRIPTION')
                ->label('Description'),


                Column::callback(['DB_TABLE_SERVICE_IDENTIFIER'], function ($amount) {
                    return  'TIGO';
                 })->label('3rd Processor'),


            Column::name('DB_TABLE_DATE')
                ->label('Date'),





        ];
    }
}
