<?php

namespace App\Http\Livewire\Procurement;

use App\Models\Role;
use App\Models\Employee;
use App\Models\PurchaseRequisition;
use App\Models\Tender;
use App\Models\Vendor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;



class POTable extends LivewireDatatable
{    public $exportable=true;
    public $statusId;


    protected $listeners=[
        'orderStatus'=>'filterByStatus',
        'refreshComponent'=>'$refresh'
    ];

    public function filterByStatus($id){
        $this->statusId=$id;


    }




    public function builder(){
       if($this->statusId==2){
            return PurchaseRequisition::query()->where('status','ACTIVE');
        }elseif($this->statusId==3){
            return PurchaseRequisition::query()->where('status',"COMPLETED");
        }
       else{
           return PurchaseRequisition::query()->where('status','PENDING');
       }


    }

    public function columns():array{

        return [
            Column::callback('employeeId',function($id){
                return Employee::where('id',$id)->value('first_name').'   '.Employee::where('id',$id)->value('middle_name').'  '.Employee::where('id',$id)->value('last_name');
            })->label('NAME')->sortable(),

            Column::name('requisition_description')->label('Description')->searchable(),

            Column::callback(['employeeId','id'],function($employeeId,$id){
                return  Role::where('id',Employee::where('id',$employeeId)->value('department'))->value('department_name') ;
            })->label('Department'),
            Column::name('quantity')->label('quantity')->searchable(),

            Column::callback('created_at',function ($date){
                return $date;
            })->label('issue date')->sortable(),

            Column::callback('vendorId',
            function ($vendorId){


                if($vendorId){
                    return Vendor::where('id',$vendorId)->value('organization_name');
                }else{
                    return "<div class='text-red-500'>  Not Assigned </div>";
                }
            })->label('vendor')->sortable(),
            Column::name('status')->label('status')->sortable(),
            Column::callback('invoice',function($invoice){
                if($invoice){
                    return "YES";
                }
                else{
                    return "<div class='text-red-500'>  Not Found </div>";
                }
            })->label('invoice'),

            Column::callback(['id','status'],function($id,$status){
                if($status=="PENDING"){

                    $html=' <div class="flex gap-4">


        <button wire:click="view('.$id.')" type="button" class="hoverable text-white bg-gray-100 hover:bg-blue-100 hover:text-blue focus:ring-4 focus:outline-none focus:ring-blue-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">

            <svg xmlns="http://www.w3.org/2000/svg" fill="blue" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
</svg>

        </button>

        <button wire:click="approve('.$id.')" type="button" class="hoverable text-white bg-gray-100 hover:bg-blue-100 hover:text-blue focus:ring-4 focus:outline-none focus:ring-blue-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">

            <svg xmlns="http://www.w3.org/2000/svg" fill="blue" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>


        </button>






    </div>';

//                    <button wire:click="reject('.$id.'}})" type="button" class="hoverable text-white bg-gray-100 hover:bg-red-200 hover:text-red focus:ring-4 focus:outline-none focus:ring-red-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center dark:bg-red-600 dark:hover:bg-red-200 dark:focus:ring-red-200">
//
//            <svg xmlns="http://www.w3.org/2000/svg" fill="red" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
//                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
//            </svg>
//
//
//        </button>
                    return $html;
                }
                else{
                    return null;
                }

            })->label('action')
        ];
    }


    public function approve($id){
        $this->emit('approvePo',$id);
    }

    public function reject($id){

        PurchaseRequisition::where('id',$id)->update(['status'=>"REJECTED"]);
    }

    public function view($id){

      $this->emit('viewPo',$id);
    }



}

