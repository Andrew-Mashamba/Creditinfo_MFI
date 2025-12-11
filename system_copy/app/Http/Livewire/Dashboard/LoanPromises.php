<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\ClientsModel;
use App\Models\loans_schedules;
use App\Models\LoansModel;
use Carbon\Carbon;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class LoanPromises extends LivewireDatatable
{
    public function builder()
    {

        // get loan id
        // dd(auth()->user()->employeeId);
        // $employeeId='';



        // get yesterday's date
        $yesterday=Carbon::yesterday()->format('Y-m-d');

        // Get today's date in 'Y-m-d' format
        $today = Carbon::today()->format('Y-m-d');



        // Get tomorrow's date in 'Y-m-d' format
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        // Get the day after tomorrow's date in 'Y-m-d' format
        $dayAfterTomorrow = Carbon::tomorrow()->addDay()->format('Y-m-d');

        if(auth()->user()->id){
            $loanId=LoansModel::where('supervisor_id',auth()->user()->id)->pluck('loan_id');

            // Query to get records where the date is today, tomorrow, or day after tomorrow
            $loanSchedule = loans_schedules::query()->whereIn('loan_id',$loanId)->
            whereDate('promise_date', $today)
                ->orWhereDate('promise_date', $tomorrow)
                ->orWhereDate('promise_date', $yesterday)
                ->orWhereDate('promise_date', $dayAfterTomorrow)->orderBy('promise_date','ASC');
        }
        else{



            // Query to get records where the date is today, tomorrow, or day after tomorrow
            $loanSchedule = loans_schedules::query()-> whereDate('installment_date', $today)
                ->orWhereDate('installment_date', $tomorrow)
                ->orWhereDate('installment_date', $yesterday)
                ->orWhereDate('installment_date', $dayAfterTomorrow)->orderBy('installment_date','ASC');
        }

        return  $loanSchedule;
    }

    public function columns(): array
    {



        return [


            Column::callback('loan_id', function ($member_number) {
                $member_number=LoansModel::where('loan_id',$member_number)->value('client_number');
                return ClientsModel::where('client_number', $member_number)->value('first_name') . ' ' . ClientsModel::where('client_number', $member_number)->value('middle_name') . ' ' . ClientsModel::where('client_number', $member_number)->value('last_name');
            })->label('Client name'),

            Column::callback(['loan_id','id'], function ($member_number,$id) {
                $member_number=LoansModel::where('loan_id',$member_number)->value('client_number');
                return ClientsModel::where('client_number',$member_number)->value('phone_number');
            })->label('contacts'),


            Column::callback(['promise_date','installment','payment'],function ($date,$installment,$payment){

                $payment=(double)$payment;
                $installment=(double)$installment;
                if(round($payment,2)>= round($installment,2)){
                    return '<div class=" p-2 rounded-lg flex text-align-center item-center bg-orange-500"> '.$date->format('Y-m-d').'Paid </div>';

                }else{


                    $date=Carbon::parse($date);

                    if($date->isToday()){
                        return '<div class=" p-2 rounded-lg flex text-align-center item-center bg-yellow-500"> '.$date->format('Y-m-d').'   - Today </div>';
                    }elseif($date->isTomorrow()){
                        return '<div class=" p-2 rounded-lg flex text-align-center item-center bg-purple-500"> '.$date->format('Y-m-d').' -  Tomorrow  </div>';
                    }elseif($date->isYesterday()){
                        return '<div class=" p-2 rounded-lg flex text-align-center item-center bg-red-500"> '.$date->format('Y-m-d').'   - Yesterday </div>';
                    }else{
                        return '<div class=" p-2 rounded-lg text-align-center item-center bg-green-500"> '.$date->format('Y-m-d').' -  After Tomorrow </div>';

                    }
                }

            })->label('promise date')->searchable(),

            Column::name('comment')->label('officer comment'),
            Column::name('installment_date')->label('actual date'),


            Column::callback('installment',function ($installment){
                return number_format($installment,2);
            })->label('amount (TZS)'),
            Column::callback('payment',function ($amount){
                if($amount){
                    return number_format($amount,2);
                }
                else{
                    return 0.00;
                }
            })->label('amount paid (TZS)'),

            Column::callback('id',function ($id){
                $html='<button wire:click="edit('.$id.')" type="button" class="hoverable text-white bg-gray-100 hover:bg-blue-100 hover:text-blue focus:ring-4 focus:outline-none focus:ring-blue-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="blue" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"></path>
            </svg>
            <span class="hidden text-blue-800 m-2">Edit</span>
        </button>';
                return $html;
            })->label('status'),


        ];
    }

    public function edit($id){
        $this->emit('installmentPromises',$id);
    }

}
