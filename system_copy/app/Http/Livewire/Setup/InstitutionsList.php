<?php

namespace App\Http\Livewire\Setup;

use Livewire\Component;

use App\Models\institutions;
use App\Models\LoansModel;
use App\Models\Clients;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;

class InstitutionsList extends LivewireDatatable
{

    protected $listeners = ['refreshInstitutionsListComponent' => '$refresh'];
    public $exportable = true;


    public function builder()
    {
        return institutions::query();



    }

    public function viewClient($memberId)
    {

        Session::put('memberToViewId', $memberId);
        $this->emit('refreshClientsListComponent');
    }

    public function editClient($memberId, $name)
    {
        Session::put('memberToEditId', $memberId);
        Session::put('memberToEditName', $name);
        $this->emit('refreshClientsListComponent');
    }

    /**
     * Write code on Method
     *
     * @return array()
     */
    public function columns(): array
    {


        $html =null;
        return [


            Column::name('name')
                ->label('Institution name'),

            Column::raw('region')
                ->label('Region'),

            Column::name('wilaya')
                ->label('Wilaya'),

            Column::name('phone_number')
                ->label('Phone number'),

            Column::name('email')
                ->label('Email'),

            Column::name('status')
                ->label('Status'),

            Column::name('admin')
                ->label('Admin'),

            Column::callback(['id','institution_id','status'], function ($id,$member_number,$status) use ($html) {
                //$status = 1;

                if($status == 'Pending'){
                    $status = 1;
                }elseif ($status == 'Awaiting Approval'){
                    $status = 2;
                }
                elseif ($status == 'Approved'){
                    $status = 3;
                }
                elseif ($status == 'Restructured'){
                    $status = 4;
                }
                elseif ($status == 'Top Up'){
                    $status = 5;
                }
                elseif ($status == 'Active'){
                    $status = 6;
                }
                elseif ($status == 'Rejected'){
                    $status = 7;
                }
                elseif ($status == 'Recovery'){
                    $status = 8;
                }else{
                    $status = 1;
                }


                $html = '<div class="w-full">
                            <button wire:click="viewloan('.$id.','.$member_number.','.$status.')" class=" m-2 py-2 px-4 text-sm font-medium text-center text-gray-900
                            bg-white rounded-lg border border-gray-300 hover:bg-gray-100 focus:ring-4
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700
                            dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            </div> ';

                return $html;
            }),

        ];


    }


    public function viewloan($id,$member_number,$status){



        if($status == 1){
            Session::put('loanStatus','Pending');
        }elseif ($status == 2){
            Session::put('loanStatus','Awaiting Approval');
        }
        elseif ($status == 3){
            Session::put('loanStatus','Approved');
        }
        elseif ($status == 4){
            Session::put('loanStatus','Restructured');
        }
        elseif ($status == 5){
            Session::put('loanStatus','Top Up');
        }
        elseif ($status == 6){
            Session::put('loanStatus','Active');
        }
        elseif ($status == 7){
            Session::put('loanStatus','Rejected');
        }
        elseif ($status == 8){
            Session::put('loanStatus','Recovery');
        }else{
            Session::put('loanStatus','Pending');
        }


        if ($status == 1){
            Session::put('disableInputs',false);
        }else{
            Session::put('disableInputs',true);
        }

        Session::put('currentloanClient',$member_number);
        Session::put('currentloanID',$id);
        $this->emit('currentloanID');
    }



}
