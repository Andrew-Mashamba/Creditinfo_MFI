<?php

namespace App\Http\Livewire\Users;

use Livewire\Component;
use App\Models\Branches;
use App\Models\ChannelsModel;
use App\Models\Committee;
use App\Models\Nodes;
use App\Models\NodesList;
use App\Models\servicesModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\ServicesList;
use Livewire\WithFileUploads;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class LoanCommitteeList extends LivewireDatatable
{
    public function builder(): \Illuminate\Database\Eloquent\Builder
    {

        return Committee::query();


    }

    public function columns(): array
    {
        return [

            Column::name('name')
                ->label('Committee Name'),

            Column::callback('id', function ($id) {
                return view('livewire.users.employee-list', ['id' => $id, 'move' => false]);
            })->unsortable()->label('Associated Permissions'),

            Column::name('status')
                ->label('Status'),


            Column::callback(['id'], function ($id) {
                return view('livewire.users.department-list-action-delete', ['id' => $id, 'move' => false]);
            })->unsortable()->label('Delete'),

        ];
    }
}
