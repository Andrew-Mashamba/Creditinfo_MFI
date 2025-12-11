<?php

namespace App\Http\Livewire\Investment;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class InvestmentTable extends LivewireDatatable
{

    public function builder(){
        return \App\Models\Investment::query();
    }

    public function columns(): array
             {
        return [

            Column::name('id')
                ->label('id')->searchable(),

            Column::name('investment_name')
                ->label('investment name')->searchable(),
        Column::callback('investment_type',function($investment_types){
            $nvestment_type=DB::table('investment_types')->where('id',$investment_types)->value('investment_type').'    ('.DB::table('investment_types')->where('id',$investment_types)->value('investment_name').')';
            return $nvestment_type;
        })
                ->label('investment type'),
            Column::name('investment_amount')
                ->label('investment amount'),
         Column::callback('status',function ($status){
             return view('livewire.branches.table-status', ['status' => $status, 'move' => false]);
         })->label('status')

        ];
       }
}
