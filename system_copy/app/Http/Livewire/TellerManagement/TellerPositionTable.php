<?php

namespace App\Http\Livewire\TellerManagement;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use App\Models\TellerEndOfDayPositions;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class TellerPositionTable extends LivewireDatatable
{
    public $selectedDate;

    public $exportable=true;
    public $searchable=['id','til_account','til_balance',
                        'tiller_cash_at_hand','business_date',
                          'message','status','updated_at'];

    protected $listeners=['updateDate'=>'getDate',
                          'refreshTellerPosition'=>'$refresh'
        ];


    public function getDate($date){
        $this->selectedDate=$date;
        $this->emit('refreshTellerPosition');
    }

    public function builder(){

        if($this->selectedDate){

            return \App\Models\TellerEndOfDayPositions::query()->where('business_date',$this->selectedDate);

        }
        else{
            return \App\Models\TellerEndOfDayPositions::query();
        }
    }

    public function columns():array
    {
        return [
            Column::callback('employee_id',function($employeeId){
                return Employee::where('id', $employeeId)
                    ->selectRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) as name")
                    ->value('name');
            })->label('account  number'),
            Column::name('til_account')->label('account  number'),
            Column::name('til_balance')->label('balance'),
            Column::name('tiller_cash_at_hand')->label('cash at hand'),
            Column::name('business_date')->label('business date'),
            Column::name('message')->label('message'),
            Column::name('status')->label('status'),
            Column::name('updated_at')->label('last update'),
            Column::callback(['id'],function ($id){
       $status=DB::table('teller_end_of_day_positions')->where('id',$id)->value('status');


                $html='  <style>
            .hoverable:hover .hidden {
                display: block;
            }
        </style>
         <button wire:click="approveCloseTill('.$id.')" type="button" class="hoverable text-white bg-gray-100 hover:bg-red-100 hover:text-blue focus:ring-4 focus:outline-none focus:ring-red-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">

            <svg xmlns="http://www.w3.org/2000/svg" fill="red" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="hidden text-blue-800 m-1">CLOSE</span>
        </button>';
                if($status!="BALANCED"){
                    return $html;
                }
                else{
                    return null;
                }

            })->label('force close'),
        ];
    }


    public function approveCloseTill($id){
        //get id of the till
        $this->emit('approveCloseTill',$id);
    }
}
