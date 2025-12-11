<?php

namespace App\Http\Livewire\Reports;

use App\Models\approvals;
use Livewire\Component;

use App\Models\CashBookMatchingStore;
use App\Models\cashbooknonmatchingstore;
use App\Models\Transactions;
use Illuminate\Support\Facades\Session;

use Livewire\WithFileUploads;

use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class MainReport extends LivewireDatatable
{
    use WithFileUploads;


    public $exportable = true;
    public $searchable="DB_TABLE_REFERENCE, NODE_DB_TABLE_DESCRIPTION";

    public $endDate = "2025-07-31";
    public $startDate = "2022-09-30";
    public $nodes;
    public $services;
    public $channels;
    public $type;

    public $thequery;

    public $processortype;

    // Register the updated event listeners
    protected $listeners = [
        'parametersChanged' => 'reloadTable',
        'updatedStartDate' => 'updatedStartDate',
        'updatedEndDate' => 'updatedEndDate',
        'updatedNodes' => 'updatedNodes',
        'updatedServices' => 'updatedServices',
        'updatedChannels' => 'updatedChannels',
        'updatedType' => 'updatedType',
        'updatedProcessorType' => 'updatedProcessorType',
    ];

    // Update the start date variable and refresh the table
    public function updatedStartDate($value): void
    {
        $this->startDate = $value;
        $this->reloadTable();
    }

    // Update the end date variable and refresh the table
    public function updatedEndDate($value): void
    {
        $this->endDate = $value;
        $this->reloadTable();
    }

    // Update the nodes variable and refresh the table
    public function updatedNodes($value): void
    {
        $this->nodes = $value;
        $this->reloadTable();
    }

    // Update the services variable and refresh the table
    public function updatedServices($value): void
    {
        $this->services = $value;
        $this->reloadTable();
    }

    // Update the channels variable and refresh the table
    public function updatedChannels($value): void
    {
        $this->channels = $value;
        $this->reloadTable();
    }

    // Update the type variable and refresh the table
    public function updatedType($value): void
    {
        $this->type = $value;
        $this->reloadTable();
    }

    public function updatedProcessorType($value){
        $this->processortype = $value;
        $this->reloadTable();
    }

    // Refresh the table with new data
    public function reloadTable(): void
    {
        $this->resetPage();
    }



    public function builder(): \Illuminate\Database\Eloquent\Builder
    {

        // Define the base query
        $query = Transactions::query();


        // Add conditions to the query based on the variables
        if (!empty($this->endDate)) {
           
            $query->where('VALUE_DATE', '<=', $this->endDate);
        }
        if (!empty($this->startDate)) {
            $query->where('VALUE_DATE', '>=', $this->startDate);
        }
        if (!empty($this->nodes)) {
            
            if($this->nodes == 'All'){

            }else{
                $query->where('CHANNEL', $this->nodes);
            }
            
        }
        if (!empty($this->services)) {
            $query->where('PROCESSOR_TABLE_SERVICE_IDENTIFIER', $this->services);
        }
        if (!empty($this->channels)) {
            $query->where('CHANNEL', $this->channels);
        }
        if (!empty($this->type)) {

            if($this->type == 'All'){

            }else{
                if($this->type == 'NULL'){
                    $query->where('RECON_RESULTS', null);
                }else{
                    $query->where('RECON_RESULTS', $this->type);
                }
               
            }
            
        }

        if (!empty($this->processortype)) {

            if($this->processortype == 'All'){

            }else{
                $query->where('PROCESSOR_NODE', $this->processortype);
               
            }
            
        }

  


        $this->thequery = $query->toSql(); 
        
        // Return the final query
        return $query;
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

            Column::name('NODE_DB_TABLE_AMOUNT')
                ->label('Amount'),

            Column::name('CHANNEL')
                ->label('node'),

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


                Column::callback(['ID'], function ($id) {
                    if(Transactions::find($id)->RECON_RESULTS == null){
                        return view('livewire.reports.decision', ['id' => $id, 'move' => false]);
                    }
                    return null;
                })->unsortable()->label('Action'),

                Column::name('COMMENTS')
                ->label('Comments'),


        ];
    }

    public function resolve($id){
        $this->emitUp('resolveModal',$id);
    }




}
