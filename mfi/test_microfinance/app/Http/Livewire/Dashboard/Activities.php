<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;

use App\Models\approvals;
use App\Models\Branches;
use App\Models\ChannelsModel;
use App\Models\Nodes;
use App\Models\NodesList;
use App\Models\servicesModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\ServicesList;
use Livewire\WithFileUploads;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;



class Activities extends LivewireDatatable
{
    public function builder(): \Illuminate\Database\Eloquent\Builder
    {

        return approvals::query();


    }

    public function columns(): array
    {
        return [

            Column::callback(['user_id'], function ($id) {

                    if($id){
                        if(User::find($id)){
                            return User::find($id)->name;
                        }else{
                            return '';
                        }

                    }else{

                        return '';
                    }




            })->unsortable()->label('Initiator'),


            Column::name('process_description')
                ->label('Action Description'),

            Column::name('approval_status')
                ->label('Approval Status'),



        ];
    }
}
