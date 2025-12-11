<?php

namespace App\Http\Livewire\Channels;


use App\Models\ChannelsModel;
use Livewire\Component;


use Livewire\WithFileUploads;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;


class ChannelsTable extends LivewireDatatable
{
    use WithFileUploads;

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {

        return ChannelsModel::query();


    }


    public function columns(): array
    {
        return [

            Column::name('NAME')
                ->label('Channel Name'),

            Column::callback(['SERVICES'], function ($services) {


                $json = $services;

                // Decode the JSON string into an array
                $array = json_decode($json, true);

                // Loop through the array and extract the node_id values
                $serviceIds = [];
                foreach ($array as $item) {
                    $serviceIds[] = $item;
                }

                return view('livewire.channels.list-of-services', ['servicesListing' => $serviceIds, 'move' => true]);
            })->unsortable()->label('SERVICES'),


            Column::callback(['STATUS'], function ($status) {
                return view('livewire.users.table-status', ['status' => $status, 'move' => false]);
            })->label('Channel Status'),




        ];
    }


}
