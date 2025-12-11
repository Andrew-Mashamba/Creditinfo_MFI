<?php

namespace App\Http\Livewire\Procurement;
use App\Http\Livewire\Users\DepartmentList;
use App\Models\ContractManagement;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\PurchaseRequisition;
use App\Models\Tender;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Livewire\WithFileUploads;
class TenderTable extends LivewireDatatable
{    public $exportable=true;

    
    public function builder()
    {
        return Tender::query();
    }


    public function columns():array{
        return [
            Column::name('tender_number')->label('tender number')->searchable(),
            Column::name('tender_name')->label('tender name'),
            Column::name('tender_description')->label('tender description'),
            Column::name('status')->label(' status'),
            Column::callback('id',function($id){
               return view('livewire.procurement.tender-action',['id'=>$id]);
            })->label('action')
        ];
    }


    public function edit($id){
        $this->emit('editTenderModal',$id);
    }

    public function delete($id){
        $this->emit('deleteTenderModal',$id);
    }



}
