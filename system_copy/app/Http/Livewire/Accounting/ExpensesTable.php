<?php

namespace App\Http\Livewire\Accounting;

use App\Models\ExpensesModel;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\MembersModel;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ExpensesTable extends LivewireDatatable
{
public $exportable=true;


protected $listeners = ['refreshExpensesTable' => '$refresh'];


    public function builder()
    {

        return ExpensesModel::query()->where('status',"ACTIVE")->orWhere('status',"PAID");
        //->leftJoin('branches', 'branches.id', 'members.branch')
    }

    /**
     * Write code on Method
     *
     * @return array()
     */
    public function columns(): array
    {
        return [



            Column::name('id')->label('id')->searchable(),
            Column::callback('amount', function ($amount)  {
                return number_format($amount,2);
            })->label('amount')->searchable(),
            Column::name('notes')->label('description ')->searchable(),
            Column::callback('member_number',function ($member_number){
                return MembersModel::where('client_number',$member_number)->
                            selectRaw("CONCAT( first_name,'  ',middle_name,'  ', last_name) as name")->value('name');
            })->label('member name')->searchable(),

            Column::name('created_at')->label('submission date'),
            Column::name('status')->label('Status'),

            Column::callback('id', function ($id)  {
                //$status = 1;
                $status = ExpensesModel::where('id',$id)->value('status');





                $html = '<div class="flex items-center space-x-4 flex-lg-row">

                    <button wire:click="approve('.$id.')" type="button" class="hoverable text-white bg-gray-100 hover:bg-blue-100 hover:text-blue focus:ring-4 focus:outline-none focus:ring-blue-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="blue" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                    <span class="hidden text-blue-800 m-2">Pay</span>
                    </button>
                            </div> ';

                $html2='        <span class="bg-green-400 text-green-800 text-sm font-medium mr-2 p-2 rounded dark:bg-green-900 dark:text-green-300">"APPROVED"</span>
';



                 $html3 = '<div class="flex items-center space-x-4 flex-lg-row">

                <button  type="button" class="hoverable text-white bg-gray-100 hover:bg-red-200 hover:text-red focus:ring-4 focus:outline-none focus:ring-red-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center dark:bg-red-600 dark:hover:bg-red-200 dark:focus:ring-red-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="red" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
         <span class="hidden text-red-600 m-2">Rejected</span>
        </button>
                                    </div> ';

                if($status=='ACTIVE'){

                    return $html;
                }
                else if($status=="PAID"){
                    return "" ;
                }
                return "";




            })->label('Action'),


        ];
    }

    public function approve($id){

        $this->emit('approveExpenses',$id);

    }



}
