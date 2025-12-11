<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\BranchesModel;
use App\Models\ChequeBookModel;
use App\Models\institutions;
use App\Models\MembersModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ChequeBook extends LivewireDatatable
{


    public  $exportable=true;
    public function builder(): \Illuminate\Database\Eloquent\Builder
    {



            return ChequeBookModel::query();// You can modify the ordering as per your requirement


    }



    public function columns(): array
    {
        return [
            Column::name('id')->label('id')->searchable(),
            Column::name('chequeBook_id')->label('cheque book id'),
            Column::callback('branch_id',function ($branch_id){
                return BranchesModel::where('id',$branch_id)->value('name').'('.BranchesModel::where('id',$branch_id)->value('region').','.BranchesModel::where('id',$branch_id)->value('wilaya').')';
            })->label('branch'),


            Column::name('remaining_leaves')->label('remaining leaves'),

              Column::callback('bank',function ($bank){
                return  AccountsModel::where('id',$bank)->value('account_name').'('. AccountsModel::where('id',$bank)->value('account_number').')';
            })->label('bank'),


            Column::name('status')->label('status'),

        ];
    }



}
