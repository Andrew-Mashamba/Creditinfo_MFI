<?php

namespace App\Http\Livewire\Procurement;
use App\Http\Livewire\Users\DepartmentList;
use App\Models\ContractManagement;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\PurchaseRequisition;
use App\Models\Vendor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Livewire\WithFileUploads;
class ContractTable extends LivewireDatatable
{
    public $exportable=true;




    public function builder()
    {
        return ContractManagement::query();
    }

    public function columns():array{
       return [

           Column::name('contract_name')->label('contract name')->searchable(),
           Column::name('contract_description')->label('contract description')->searchable(),
           Column::callback('vendorId',function($vendorId){
             return   Vendor::where('id',$vendorId)->value('organization_name');
           })->label('vendor name')->searchable(),

           Column::name('startDate')->label('start date')->searchable(),
           Column::name('endDate')->label('end date')->searchable(),

           Column::callback(['id','status'],function($id,$status){
               return '<div class="flex items-center space-x-4 flex-lg-row">
                                    <button  type="button"  wire:click="download('.$id.')" class="hoverable text-white bg-gray-100 hover:bg-blue-100 hover:text-blue-200 focus:ring-4 focus:outline-none focus:ring-blue-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="blue"  viewBox="0 0 24 24" stroke-width="1.5" stroke="blue" class="w-8 h-8">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                     </svg>

                     <span class="hidden text-blue-800 m-1">Download</span>
                     </button>
                     </div>';


           })->label('Download file'),


           Column::name('status')->label('status'),
           Column::callback('id',function($id){
               return  view('livewire.procurement.table-action',['id'=>$id]);
           })->label('action')
       ];
    }


    public function downloadFile($file){
        return Response::download($file);
    }

    public function edit($id){

//        session()->put('editVendorId',$id);
        $this->emit('editVendor',$id);
    }



    public function download($id){

        $get_microfinance=ContractManagement::where('id',$id)->value('contract_file_path');
        $filePath = storage_path('app/public/' .$get_microfinance);
        return response()->download($filePath);
    }

    public function delete($id){
        $this->emit('actionModal',$id);
    }

}
