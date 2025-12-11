<?php

namespace App\Http\Livewire\Payments;

use Livewire\Component;




use App\Models\Transactions;
use App\Models\Clients;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;

use Illuminate\Support\Facades\DB;

class ApprovalsTable extends LivewireDatatable
{

    protected $listeners = ['viewOrderToApprove' => '$refresh'];
    public $exportable = true;


    public function builder()
    {

        //dd(Session::get('orderNumber'));

        $first_authorizer_action = '';
        $second_authorizer_action = '';
        $third_authorizer_action = '';

        $role = '';

        $orders = DB::table('orders')->where('order_number', Session::get('orderNumber'))->get();
        foreach ($orders as $order) {
            $first_authorizer_action = $order->first_authorizer_action;
            $second_authorizer_action = $order->second_authorizer_action;
            $third_authorizer_action = $order->third_authorizer_action;
        }
        if ($role == 'firstAuthorizer') {
            if ($first_authorizer_action == 'Pending') {
                return Transactions::query()->where('order_number', Session::get('orderNumber'))
                    ->where('trans_status', 'Pending Approval')
                    ->whereNotNull('order_number');
            }

        }elseif ($role == 'secondAuthorizer') {

            if ($second_authorizer_action == 'Pending') {
                return Transactions::query()->where('order_number', Session::get('orderNumber'))
                    ->where('trans_status', 'Pending Approval')
                    ->whereNotNull('order_number');
            }
        }elseif ($role == 'thirdAuthorizer') {

            if ($third_authorizer_action == 'Pending') {
                return Transactions::query()->where('order_number', Session::get('orderNumber'))
                    ->where('trans_status', 'Pending Approval')
                    ->whereNotNull('order_number');
            }
        }else{
            if ($third_authorizer_action == 'Pending') {
                return Transactions::query()->where('order_number', Session::get('orderNumber'))
                    ->where('trans_status', 'Pending Approval')
                    ->whereNotNull('order_number');
            }
        }

        return Transactions::query()->where('order_number', Session::get('orderNumber'))
            ->where('trans_status', 'none');


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
        return [

            Column::name('typeOfTransfer')
                ->label('Type'),

            Column::raw('issuer_name')
                ->label('Sender'),

            Column::raw('beneficiary_nane')
                ->label('Beneficiary Name'),

            Column::raw('source_branch_account_number')
                ->label('Debit AC'),

            Column::name('destination_branch_account_number')
                ->label('Credit AC'),

            Column::name('destination_bank_name')
                ->label('Institution'),

            Column::name('transaction_amount')
                ->label('Amount'),

            Column::name('description')
                ->label('Description'),

            Column::name('trans_status')
                ->label('Progress'),

            Column::name('payment_status')
                ->label('Status')
        ];
    }


}
