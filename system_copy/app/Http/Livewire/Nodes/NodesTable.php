<?php

namespace App\Http\Livewire\Nodes;


use Livewire\WithFileUploads;
use App\Models\NodesList;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;


class NodesTable extends LivewireDatatable
{
    use WithFileUploads;

    public $selectedOption;
    public $exportable = true;
    public $searchable="NODE_NAME
      ,NODE_DB_HOST
      ,NODE_DB_PORT
      ,NODE_DB_DATABASE
      ,NODE_DB_USERNAME
      ,NODE_DB_PASSWORD
      ,DATA_SOURCE_TYPE
      ,DB_TABLE_TRANSACTION_TYPE
      ,DB_TABLE_CLIENT_IDENTIFIER
      ,DB_TABLE_SERVICE_IDENTIFIER
      ,DB_TABLE_STATUS
      ,DB_TABLE_DESCRIPTION
      ,DB_TABLE_SENDER
      ,DB_TABLE_RECEIVER
      ,DB_TABLE_AMOUNT
      ,DB_TABLE_DATE
      ,DB_TABLE_REFERENCE
      ,DB_TABLE_SECONDARY_REFERENCE
      ,DB_TABLE_NAME
      ,NODE_TYPE
      ,NODE_STATUS";

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {

        return NodesList::query()->whereIn('NODE_STATUS',['ACTIVE', 'INACTIVE','PENDING']);


    }


    public function columns(): array
    {
        return [

            Column::name('NODE_NAME')
                ->label('Node Name'),

            Column::name('NODE_DB_HOST')
                ->label('Host'),

            Column::name('NODE_DB_DATABASE')
                ->label('Database Name'),

            Column::name('NODE_TYPE')
                ->label('Type Of Node'),


            Column::name('QUERY')
                ->label('Query String'),



                Column::callback(['ID'], function ($id) {
                    $node = NodesList::where('ID',$id)->get()->first();
                    return view('livewire.nodes.table-source-buttons', [
                        'dataSource' => $node->NODE_DATA_SOURCE,
                        'id' => $id,
                        'move' => false,
                    ]);
                })->label('Data Source'),


            Column::callback(['NODE_STATUS'], function ($status) {
                return view('livewire.nodes.table-action-buttons', ['status' => $status, 'move' => false]);
            })->label('Node Status'),




        ];
    }

    public function selectedOption($dataSource,$id){
        NodesList::where('ID', $id)->update(['NODE_DATA_SOURCE' => $dataSource]);
    }


}