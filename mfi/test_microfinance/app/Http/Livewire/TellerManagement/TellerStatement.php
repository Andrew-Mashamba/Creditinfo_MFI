<?php
//
//namespace App\Http\Livewire\TellerManagement;
//
//use App\Models\Branches;
//use App\Models\BranchesModel;
//use App\Models\Department;
//use App\Models\Employee;
//use App\Models\general_ledger;
//use Illuminate\Support\Facades\Config;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Session;
//use Livewire\Component;
//use Mediconesystems\LivewireDatatables\Column;
//use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
//
//class TellerStatement extends LivewireDatatable
//{
//
//    protected $listeners = ['refreshTilTable' => '$refresh'];
//    public $exportable = true;
//
//
//    public function boot(): void
//    {
//
//        $this->branches = BranchesModel::get();
//
//    }
//
//
//    public function builder()
//    {
//
//        $businessDate = Session::get('businessDate');
//        $sessionTillId = Session::get('sessionTillId');
//        $tilAccountId = DB::table('tellers')->where('id', $sessionTillId)->value('account_id');
//        $tilAccountNumber = DB::table('accounts')->where('id', $tilAccountId)->value('account_number');
//        //dd($businessDate, $sessionTillId, $tilAccountId, $tilAccountNumber);
//        return general_ledger::query()
//            ->where('institution_id', auth()->user()->institution_id)
//            ->whereDate('created_at', $businessDate)
//            ->where('record_on_account_number', $tilAccountNumber)
//            ->orderBy('created_at', 'asc');
//
//
//
//    }
//
//    /**
//     * Write code on Method
//     *
//     * @return array()
//     */
//    public function columns(): array
//    {
//
//        return [
//            Column::name('created_at')
//                ->label('Date')->searchable(),
//
//            Column::name('sender_account_number')
//                ->label('Sender')->searchable(),
//
//            Column::name('beneficiary_account_number')
//                ->label('Beneficiary')->searchable(),
//
//            Column::name('narration')
//                ->label('Narration')->searchable(),
//
//
//            Column::callback(['id','credit'],function($id,$credit){
//                return number_format($credit,2);
//            })->label('Credit'),
//
//
//            Column::callback(['id','debit'],function($id,$debit){
//                return number_format($debit,2);
//            })->label('Debit'),
//
//            Column::callback(['id','record_on_account_number_balance'],function($id,$record_on_account_number_balance){
//                return number_format($record_on_account_number_balance,2);
//            })->label('Balance'),
//
//        ];
//    }
//}


namespace App\Http\Livewire\TellerManagement;

use App\Models\Branches;
use App\Models\BranchesModel;
use App\Models\Role;
use App\Models\Employee;
use App\Models\general_ledger;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class TellerStatement extends LivewireDatatable
{

    protected $listeners = ['refreshTilTable' => '$refresh'];
    public $exportable = true;



    public function builder()
    {

        $businessDate = Session::get('businessDate');
        $sessionTillId = Session::get('sessionTillId');
        $tilAccountId = DB::table('tellers')->where('id', $sessionTillId)->value('account_id');
        $tilAccountNumber = DB::table('accounts')->where('id', $tilAccountId)->value('account_number');
        //dd($businessDate, $sessionTillId, $tilAccountId, $tilAccountNumber);
        return general_ledger::query()
            ->where('institution_id', auth()->user()->institution_id)
            ->whereDate('created_at', $businessDate)
            ->where('record_on_account_number', $tilAccountNumber)
            ->orderBy('created_at', 'asc');


    }

    /**
     * Write code on Method
     *
     * @return array()
     */
    public function columns(): array
    {

        return [
            Column::name('created_at')
                ->label('Date')->searchable(),

            Column::name('sender_account_number')
                ->label('Sender')->searchable(),

            Column::name('beneficiary_account_number')
                ->label('Beneficiary')->searchable(),

            Column::name('narration')
                ->label('Narration')->searchable(),


            Column::callback([ 'credit'], function ( $credit) {
                return number_format($credit, 2);
            })->label('Credit'),


            Column::callback(['debit'], function ( $debit) {
                return number_format($debit, 2);
            })->label('Debit'),

            Column::callback([ 'record_on_account_number_balance'], function ( $record_on_account_number_balance) {
                return number_format($record_on_account_number_balance, 2);
            })->label('Balance'),

        ];
    }
}
