<?php

namespace App\Http\Livewire\Accounting;

use App\Models\BranchesModel;
use App\Models\institutions;
use App\Models\Clients;
use App\Models\ClientsModel;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use function PHPUnit\Framework\isEmpty;

class ClientTable extends LivewireDatatable
{
    protected $listeners = ['refreshClientsTable' => '$refresh'];
    public $exportable = true;


    public function builder()
    {
        return ClientsModel::query()->where('institution_id',auth()->user()->institution_id);
        //->leftJoin('branches', 'branches.id', 'clients.branch')
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

    public function columns(): array
    {
        return [

            Column::name('first_name')
                ->label('first name')->searchable(),

            Column::name('middle_name')
                ->label('middle name'),

            Column::callback('branch',function($id){
                return BranchesModel::where('id',$id)->value('name');
            })
                ->label('branch'),

            Column::name('phone_number')
                ->label('phone number'),

            Column::name('client_number')
                ->label('membership number')->searchable(),

            Column::callback(['member_status'], function ($status) {
                return view('livewire.branches.table-status', ['status' => $status, 'move' => false]);
            })->label('status'),
            Column::callback('id',function($id){
                return view('livewire.accounting.client-action',['id'=>$id]);
            })->label('action')

            ];
}


public function  financeViewClientes($id){

        $this->emitTo('accounting.accounting','financeViewClient',$id);

    }

public function   financeReject ($id){
        dd("reject member");
}



}
