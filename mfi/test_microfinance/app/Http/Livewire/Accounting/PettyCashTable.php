<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\Branches;
use App\Models\Role;
use App\Models\Employee;
use App\Models\general_ledger;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class PettyCashTable extends LivewireDatatable
{
    public function builder()
    {

        $accounts=AccountsModel::where('sub_category_code',1020)->pluck('account_number')->toArray();

        return general_ledger::query()->whereIn('record_on_account_number',$accounts);

    }



    public function columns(): array
    {

        return [
            Column::name('created_at')
                ->label('Date')->searchable(),

            Column::name('sender_name')
                ->label('Sender')->searchable(),

            Column::name('beneficiary_name')
                ->label('Receiver')->searchable(),

            Column::name('record_on_account_number')
                ->label('Account Number')->searchable(),

            Column::name('narration')
                ->label('Narration')->searchable(),

            Column::name('credit')
                ->label('Credit')->searchable(),

            Column::name('debit')
                ->label('Debit')->searchable(),

            Column::name('trans_status')
                ->label('status')->searchable(),


        ];
    }


}
