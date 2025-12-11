<?php

namespace App\Http\Livewire\Procurement;

use App\Models\ContractManagement;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Vendor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class AssetTable extends LivewireDatatable
{



    public function builder()
    {
        if(session()->has('assigned')){
            return Inventory::query()->where('employee_id',auth()->user()->employeeId);
        }else{
            return Inventory::query();

        }
    }


    public function columns():array{
        return [
            Column::name('item_name')->label('Iterm name')->searchable(),
            Column::name('item_id')->label('item number'),
            Column::callback('employee_id',function($employee_id){
                if($employee_id){
                    return  Employee::where('id',$employee_id)->raw("CONCAT('first_name','middle_name','last_name') as name")->value('name');
                }else{
                    return '<div class="text-red-400"  >Not Assigned </div>';
                }
            })->label('current owner'),
            Column::name('status')->label(' status'),
            Column::callback('id',function($id){
                return '
                <div class="flex w-full">
<bottom  class="w-1/2 space-x-2 " wire:click="view('.$id.')">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-200">
  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
</svg>
</bottom>

<button wire:click="assign('.$id.')" class="w-1/2 space-x-2">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
</svg>


</button>
</div>
';
            })->label('action')
        ];
    }


    public function view($id){
        $this->emit('viewEmployeeList',$id);

        // emit the  functions

    }

    public function assign($id){
        dd($id);
    }

}
