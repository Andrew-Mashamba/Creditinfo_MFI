<?php

namespace App\Http\Livewire\Services;

use App\Models\ServicesList;
use Livewire\Component;


use Livewire\WithFileUploads;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;


class ServicesTable extends LivewireDatatable
{

    use WithFileUploads;
    public $exportable = true;

    public $searchable="name,status";


    public function builder(): \Illuminate\Database\Eloquent\Builder
    {

        return ServicesList::query()->whereNot('status','DELETED');


    }


    public function columns(): array
    {
        return [

            Column::name('name')
                ->label('Service Name'),


            Column::callback(['nodes'], function ($nodes) {


                $json = $nodes;

                // Decode the JSON string into an array
                $array = json_decode($json, true);

                // Loop through the array and extract the node_id values
                $nodeIds = [];
                foreach ($array as $item) {
                    $nodeIds[] = $item['node_id'];
                }

                // Output the node_id values
                $nodeslisting = implode(", ", $nodeIds);

                return view('livewire.services.list-of-nodes', ['nodeslisting' => $nodeIds, 'move' => true]);
            })->unsortable()->label('Service Nodes'),


            Column::callback(['status'], function ($status) {
                return view('livewire.users.table-status', ['status' => $status, 'move' => false]);
            })->label('Service Status'),




        ];
    }


}
