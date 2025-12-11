<?php

namespace App\Http\Livewire\Accounting;

use App\Models\UnearnedDeferredRevenue;
use App\Services\CreditAndDebitService;
use Livewire\Component;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\Action;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;
use App\Models\loans_schedules;
use App\Models\issured_shares;
use App\Models\LoansModel;
use App\Models\BranchesModel;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class UnearnedTable extends LivewireDatatable
{

    public $exportStyles=true;
    public $exportWidths=true;

    function builder(){

     return    UnearnedDeferredRevenue::query();


    }

    public function columns(): array
    {
        return [
            Column::callback(['user_id'], function ($user_id) {
               return DB::table('users')->where('id', $user_id)->value('name') ? : null;
            })->label('initiator')->searchable(),

            Column::callback(['source_account_id'], function ($source_account_id) {
                return DB::table('accounts')->where('id', $source_account_id)->value('account_name');
            })->label('Source Account'),

            Column::callback(['destination_account_id'], function ($destination_account_id) {
              //  return DB::table('accounts')->where('id', $destination_account_id)->value('account_name');
            })->label('Destination Account'),

            Column::callback(['status'], function ($status) {
                return $status;
            })->label('Status')->searchable(),

            Column::callback(['amount'], function ($amount) {
                return $amount??0;
            })->label('amount'),

            BooleanColumn::name('is_recognized')
                ->label('Recognized')

                ->filterable(),
            BooleanColumn::name('is_delivery')
            ->label('Delivery')

            ->filterable(),

            Column::callback(['description'], function ($description) {
                return $description;
            })->label('Description')->searchable(),

            Column::callback(['name'], function ($name) {
                return $name;
            })->label('Name')->searchable(),

            Column::callback(['address'], function ($address) {
                return $address;
            })->label('Address'),

            Column::callback(['phone'], function ($phone) {
                return $phone;
            })->label('Phone'),

            Column::callback(['email'], function ($email) {
                return $email;
            })->label('Email'),

            Column::callback(['id'], function ($id) {

                $data=UnearnedDeferredRevenue::find($id);
                $delivery1=false; $recognize1=false;
                $delivery1=$data->is_delivery;
                $recognize1=$data->is_recognized;



                $recognize=' <button wire:click="recognize('.$id.')" type="button" class="text-white mt-4 mb-4 bg-[#3b5998] hover:bg-[#3b5998]/90 focus:ring-4 focus:outline-none focus:ring-[#3b5998]/50 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:focus:ring-[#3b5998]/55 me-2 mb-2">
                <svg class="w-4 h-4 " data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
              </svg>
                Recognize
                </button>';

                $delivery=' <button wire:click="delivery('.$id.')" type="button" class="text-white mt-4 mb-4 bg-[#3b5998] hover:bg-[#3b5998]/90 focus:ring-4 focus:outline-none focus:ring-[#3b5998]/50 font-medium rounded-lg text-sm px-2 py-2.5 text-center inline-flex items-center dark:focus:ring-[#3b5998]/55 me-2 mb-2">
                <svg class="w-4 h-4 " data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
              </svg>

                Confirm
                </button>';

                if($delivery1 && $recognize1){
                    return 'Completed';
                }elseif($delivery1==false && $recognize1==false){

                    return  $recognize;
                }elseif($delivery1==false && $recognize1==true){

                    return $delivery;
                }

            })->label('Action'),

            // Column::callback(['updated_at'], function ($updated_at) {
            //     return \Carbon\Carbon::parse($updated_at)->format('Y-m-d H:i:s');
            // })->label('Updated At'),
        ];
    }


    function recognize($id){

        $data=UnearnedDeferredRevenue::find($id);
        $destination_accounts=DB::table('accounts')->where('id',$data->source_account_id)->value('account_number');
      $credit= new    CreditAndDebitService ();
      $credit->CreditAndDebit($destination_accounts,$data->amount,
              1,"Unearned / deferred revenue");
       // CreditAndDebit($source_account, $amount, $destination_accounts, $narration)

       $data->update([
        'is_recognized' =>true,
       ]);

    }

    function delivery($id){

        $data=UnearnedDeferredRevenue::find($id);
        $destination_accounts=DB::table('accounts')->where('id',$data->source_account_id)->value('account_number');
      $credit= new    CreditAndDebitService ();
      $credit->CreditAndDebit(1,$data->amount,
      $destination_accounts,"Unearned / deferred revenue");
       // CreditAndDebit($source_account, $amount, $destination_accounts, $narration)

       $data->update([
        'is_delivery' =>true,
       ]);


    }

    public function buildActions()
    {
        return [



            Action::groupBy('Export Options', function () {
                return [
                    Action::value('csv')->label('Export CSV')->export('unearnedReport.csv'),
                    Action::value('html')->label('Export HTML')->export('unearnedReport.html'),
                    Action::value('xlsx')->label('Export XLSX')->export('unearnedReport.xlsx')->styles($this->exportStyles)->widths($this->exportWidths)
                ];
            }),
        ];
    }


}
