<?php
//
//namespace App\Http\Livewire\TellerManagement;
//
//use App\Models\Branches;
//use App\Models\Employee;
//use Illuminate\Support\Facades\Config;
//use Illuminate\Support\Facades\DB;
//use Kdion4891\LaravelLivewireTables\Column;
//use Livewire\Component;
//use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
//use function PHPUnit\Framework\isEmpty;
//
//class TellerTable extends LivewireDatatable
//{
//    public $exportable=true;
//
//
//
//    public function builder(){
//        return \App\Models\Teller::query();
//    }
//
//    public function columns():array{
//        return [
//            \Mediconesystems\LivewireDatatables\Column::callback('account_id',function($id){
//                return DB::table('accounts')->where('id',$id)->value('account_number');
//            })->label('Till account number')->searchable(),
//
//            \Mediconesystems\LivewireDatatables\Column::callback(['account_id','id'],function($account_id,$id){
//                return DB::table('accounts')->where('id',$account_id)->value('account_name');
//            })->label(' Till account name')->searchable(),
//
//            \Mediconesystems\LivewireDatatables\Column::callback(['employee_id','progress_status'],function($empleyeeId,$progress_status){
//                $assigned_to="<div class='text-indigo-400'>Not available...</div>";
////                dd($assigned_to);
//
//                if(!$assigned_to==null ){
//                    $assigned_to= Employee::where('id',$empleyeeId)->value('first_name').'   '.Employee::where('id',$empleyeeId)->value('middle_name').'   '.Employee::where('id',$empleyeeId)->value('last_name');
//                    $assigned_to=strtoupper($assigned_to);
//                }
//                if($progress_status=="PENDING" && $assigned_to==null){
//                    $assigned_to= "<div class='text-indigo-400'>onprogress...</div>";
//                }
//
//                return $assigned_to;
//            })->label('Assigned to'),
//            \Mediconesystems\LivewireDatatables\Column::callback('branch_id',function($branchId){
//                return Branches::where('id',$branchId)->value('name') .'    '.'('.Branches::where('id',$branchId)->value('region').','.Branches::where('id',$branchId)->value('wilaya').')';
//            })->label('Branch')->searchable(),
//
//            \Mediconesystems\LivewireDatatables\Column::callback('max_amount',function ($amount){
//                return number_format($amount);
//            })->label('maximum amount')->searchable(),
//
//            \Mediconesystems\LivewireDatatables\Column::name('status')->label('status')->searchable(),
//
//            \Mediconesystems\LivewireDatatables\Column::callback(['status','id'] , function($status,$id){
//
//                $html='  <style>
//            .hoverable:hover .hidden {
//                display: block;
//            }
//        </style>
//         <button wire:click="assignToTeller('.$id.')" type="button" class="hoverable text-white bg-gray-100 hover:bg-green-100 hover:text-green focus:ring-4 focus:outline-none focus:ring-green-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-green-200 dark:hover:bg-green-200 dark:focus:ring-green-200">
//
//            <svg xmlns="http://www.w3.org/2000/svg" fill="green" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
//                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
//            </svg>
//            <span class="hidden text-blue-800 m-2">Assign</span>
//        </button>';
//                return $html;
//            })->label('assign')->searchable(),
//
//            \Mediconesystems\LivewireDatatables\Column::callback(['id','status'],function($id,$status){
//                return view('livewire.teller-management.teller-action',['id'=>$id]);
//            })->label('action'),
//
//        ];
//    }
//
//    public function editTeller($id){
//        session()->put('editTellerId',$id);
//        $this->emitTo('teller-management.teller-register','editTeller');
//    }
//
//    public function deleteTeller($id){
//        session()->put('editTellerId',$id);
//        $this->emitTo('teller-management.teller-register','deleteTeller');
//    }
//
//
//    public function assignToTeller($id){
//        $this->emitTo('teller-management.teller-register','assignTeller',$id);
//    }
//}


namespace App\Http\Livewire\TellerManagement;

use App\Models\Branches;
use App\Models\Employee;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Kdion4891\LaravelLivewireTables\Column;
use Livewire\Component;
use function PHPUnit\Framework\isEmpty;
use App\Models\ClientsModel;
use App\Models\loans_schedules;
use Carbon\Carbon;


use App\Models\issured_shares;
use App\Models\LoansModel;
use App\Models\BranchesModel;
use App\Models\Clients;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;


class TellerTable extends LivewireDatatable
{
    public $exportable = true;

    public function builder()
    {
        return \App\Models\Teller::query()
                ->leftJoin('accounts', 'tellers.account_id', '=', 'accounts.id');
    }

    public function columns(): array
    {

        return [


            \Mediconesystems\LivewireDatatables\Column::name('accounts.account_number')->label('Till account number')->searchable(),

            \Mediconesystems\LivewireDatatables\Column::name('accounts.account_name')->label(' Till account name')->searchable(),

            \Mediconesystems\LivewireDatatables\Column::callback(['employee_id'], function ($empleyeeId) {
                $assigned_to = "<div class='text-indigo-400'>Not available...</div>";
//                dd($assigned_to);

             $progress_status= DB::table('tellers')->where('employee_id',$empleyeeId)->value('progress_status');

                if (!$assigned_to == null) {
                    $assigned_to = Employee::where('id', $empleyeeId)->value('first_name') . '   ' . Employee::where('id', $empleyeeId)->value('middle_name') . '   ' . Employee::where('id', $empleyeeId)->value('last_name');
                }
                if ($progress_status == "PENDING" && $assigned_to == null) {
                    $assigned_to = "<div class='text-indigo-400'>onprogress...</div>";
                }

                return $assigned_to;
            })->label('Assigned to'),


            \Mediconesystems\LivewireDatatables\Column::callback('branch_id', function ($branchId) {
                return Branches::where('id', $branchId)->value('name') . '    ' . '(' . Branches::where('id', $branchId)->value('region') . ',' . Branches::where('id', $branchId)->value('wilaya') . ')';
            })->label('Branch')->searchable(),

            \Mediconesystems\LivewireDatatables\Column::callback('max_amount', function ($amount) {
                return number_format($amount, 2, '.', ',') . 'TZS';
            })->label('maximum amount')->searchable(),

            \Mediconesystems\LivewireDatatables\Column::name('status')->label('status')->searchable(),

            \Mediconesystems\LivewireDatatables\Column::callback(['id'], function ( $id) {

                $html = '  <style>
            .hoverable:hover .hidden {
                display: block;
            }
        </style>
         <button wire:click="assignToTeller(' . $id . ')" type="button" class="hoverable text-white bg-gray-100 hover:bg-blue-100 hover:text-blue focus:ring-4 focus:outline-none focus:ring-blue-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">

            <svg xmlns="http://www.w3.org/2000/svg" fill="blue" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="hidden text-blue-800 m-2">Assign</span>
        </button>';
                return $html;
            })->label('assign')->searchable(),


            \Mediconesystems\LivewireDatatables\Column::callback(['account_id'], function ($id) {
                $id=DB::table('tellers')->where('account_id',$id)->value('id');
                return view('livewire.teller-management.teller-action', ['id' => $id]);
            })->label('action'),

        ];
    }

    public function editTeller($id)
    {
        session()->put('editTellerId', $id);
        $this->emitTo('teller-management.teller-register', 'editTeller');
    }

    public function deleteTeller($id)
    {
        session()->put('editTellerId', $id);
        $this->emitTo('teller-management.teller-register', 'deleteTeller');
    }


    public function assignToTeller($id)
    {
        $this->emitTo('teller-management.teller-register', 'assignTeller', $id);
    }
}
