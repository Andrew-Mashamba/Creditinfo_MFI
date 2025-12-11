<?php

namespace App\Http\Livewire\Accounting;

use App\Models\StandingInstruction;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class StandingInstructionTable extends LivewireDatatable
{

    public $exportable=true;

    public function builder(){
   return StandingInstruction::query();
  }

    public function columns(): array
    {
        return [
            Column::name('id')
                ->label('id')->searchable(),

            Column::name('created_at')
                ->label('run date'),

                Column::callback('member_id',function($member_number){
                  $member= DB::table('clients')->where('id',$member_number)->first();
                  if($member){
                    return $member->first_name.' '.$member->middle_name.' '.$member->last_name;
                  }else{
                    return "INTERNAL ACCOUNT ORDER";
                  }
                })
                ->label('Affected Account Name'),

            Column::name('status')
                ->label('status'),

           Column::callback('amount',function($amount){
            return number_format($amount,2).'TZS';
           })
                ->label('Amount'),

            Column::name('source_account_number')
                ->label('source account number'),

            Column::name('destination_account_id')
                ->label('destination account number'),



            Column::name('description')
            ->label('narration'),
        ];
            }

}
