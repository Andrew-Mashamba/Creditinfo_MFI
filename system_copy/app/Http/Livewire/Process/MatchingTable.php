<?php

namespace App\Http\Livewire\Process;

use App\Models\Transactions;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class MatchingTable extends LivewireDatatable
{
    use WithFileUploads;

    protected $listeners = ['refreshTables' => '$refresh'];
    public $exportable = true;
    //public $searchable="DB_TABLE_REFERENCE, NODE_DB_TABLE_DESCRIPTION";
    public $ordernumber;
    public $moveTo;
    public $change_value_date;
    public $institution;
    public $thisIsCB = false;
    public $thisIsOther;
    public $deleteEntrySet;
    public $comment;


    public function builder(): \Illuminate\Database\Eloquent\Builder
    {

        $timestamp = strtotime(Session::get('sessionValueDate'));
        $formatted_date = date('Y-m-d', $timestamp);
        return Transactions::query()
            ->where('RECON_RESULTS','PASSED')
            ->where('VALUE_DATE',$formatted_date);


    }



    public function deleteEntry($value)
    {

        $this->deleteEntrySet = $value;
        $this->moveTo = null;

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

           
                Column::callback(['NODE_DB_TABLE_AMOUNT'], function ($amount) {
                   return  number_format((float)$amount);
                })->label('Amount'),

            Column::name('CHANNEL')
                ->label('Channel'),

            Column::name('POSTILION_DB_TABLE_TRANSACTION_TYPE')
                ->label('Service'),


            Column::name('NODE_DB_TABLE_STATUS')
                ->label('Source Status'),

            Column::name('NODE_DB_TABLE_DESCRIPTION')
                ->label('Source Description'),

            Column::name('POSTILION_DB_TABLE_STATUS')
                ->label('POST Status'),


            Column::name('PROCESSOR_NODE')
                ->label('3rd Processor'),

            Column::name('PROCESSOR_TABLE_STATUS')
                ->label('3rd Pro Status'),

            Column::name('PROCESSOR_TABLE_DESCRIPTION')
                ->label('3rd Pro Description'),





        ];
    }




}
