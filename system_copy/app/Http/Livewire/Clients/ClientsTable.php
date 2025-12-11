<?php

namespace App\Http\Livewire\Clients;

use App\Models\BranchesModel;

use App\Models\LoansModel;
use Illuminate\Support\Facades\DB;


use App\Models\ClientsModel;

use Mediconesystems\LivewireDatatables\Column;

use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;


class ClientsTable extends LivewireDatatable
{

    protected $listeners = ['refreshClientsTable' => '$refresh'];
    public $exportable = true;


    public function builder()
    {

        return ClientsModel::query()->whereNot("client_status","PENDING");
    }

    public function viewClient($memberId){
        Session::put('memberToViewId',$memberId);


        $this->emit('refreshClientsListComponent');
    }
    public function editClient($memberId,$name){
        Session::put('memberToEditId',$memberId);
        Session::put('memberToEditName',$name);
        $this->emit('refreshClientsListComponent');
    }

    /**
     * Write code on Method
     *
     * @return array()
     */
    public function columns(): array
    {
        return [

                Column::name('first_name')
                ->label('first name')->searchable(),

                Column::name('middle_name')
                ->label('middle name')->searchable(),

                Column::name('last_name')
                ->label('last name')->searchable(),


                // Column::callback('client_number',function($client_number){

                // })
                // ->label('Shares')->searchable(),

                // Column::name(['id'])
                // ->label('Deposits')->searchable(),

                // Column::name('last_name')
                // ->label('Savings')->searchable(),



            Column::callback('branch',function($id){
                return BranchesModel::where('id',$id)->value('name');
            })
                ->label('branch'),



            Column::callback(['client_number'],function ($member_number){
                return LoansModel::where('client_number',$member_number)->count() ."(".LoansModel::where('client_number',$member_number)->where('status','ACTIVE')->count().")";
            })
                ->label('Total loans(active)'),

            // Column::callback(['branch','member_number'],function ($branch,$member_number){
            //     return LoansModel::where('member_number',$member_number)->where('status','ACTIVE')->count();
            // })
            //     ->label('active loans'),


                Column::name('client_number')
                ->label('client number')->searchable(),

                Column::callback(['client_status'], function ($status) {
                    return view('livewire.branches.table-status', ['status' => $status, 'move' => false]);
                })->label('status'),


                Column::callback('id', function ($id) {
                    return view('livewire.clients.action-buttons', ['id' => $id, 'move' => false]);
                })->unsortable()->label('Action'),

               Column::callback(['id'], function ($id) {
                   if($id){

                   $html=' <a wire:click="viewLoans('.$id.')" target="_blank" class=" cursor-pointer text-white bg-gray-100 hover:bg-blue-100 hover:text-blue focus:ring-4 focus:outline-none focus:ring-blue-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">
            <svg class="w-6 h-6" stroke-width="1.5" stroke="gray" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path></svg>
        </a>';

                    return $html;
                   }else{
                       return null;
                   }

                })->unsortable()->label('loan info'),

        ];
    }


    public function viewLoans($id){
        $member_number=DB::table('clients')->where('id',$id)->value('client_number');
      //  dd($member_number);
        $this->emit('viewClientLoans',$member_number);
        $this->emit('viewMemberLoan',$member_number);

    }


    public function edit($id){
        $this->emitUp('editClient',$id);
        }
        public function block($id){
        $this->emitUp('blockClient',$id);
        }
        public function viewClientes($id){

        $this->emitUp('viewClientDetails',$id);
        }


}
