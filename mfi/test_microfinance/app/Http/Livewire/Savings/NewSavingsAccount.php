<?php

namespace App\Http\Livewire\Savings;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\AccountsModel;
use App\Models\ClientsModel;
use Illuminate\Support\Facades\Session;


use App\Models\approvals;
use App\Models\TeamUser;
use Illuminate\Support\Facades\Auth;



class NewSavingsAccount extends Component
{


    public $member;
    public $product;
    public $number_of_shares;
    public $linked_savings_account;
    public $account_number;
    public $balance;
    public $nominal_price;


    public function save()
    {

        $branch = ClientsModel::where('client_number', $this->member)->value('branch');
        $id = ClientsModel::where('client_number', $this->member)->value('id');


        $id = AccountsModel::create([
            'account_use' => 'external',
            'institution_number' => '1001',
            'branch_number' => str_pad($branch, 2, '0', STR_PAD_LEFT),
            'member_number' => $this->member,
            'product_number' => '12',
            'sub_product_number' => $this->product,
            'account_name' => ClientsModel::where('client_number', $this->member)->value('first_name') . ' ' . ClientsModel::where('client_number', $this->member)->value('middle_name') . ' ' . ClientsModel::where('client_number', $this->member)->value('last_name'),
            'account_number' => str_pad($branch, 2, '0', STR_PAD_LEFT) . '112' . str_pad($id, 5, '0', STR_PAD_LEFT),

        ])->id;


        $user = auth()->user();

        $team = $user->currentTeam;

        $institution = TeamUser::where('user_id',Auth::user()->id)->value('institution');

        approvals::create([
            'institution' => $institution,
            'process_name' => 'createAccount',
            'process_description' => 'has added a new account',
            'approval_process_description' => 'has approved a new account',
            'process_code' => '04',
            'process_id' => $id,
            'process_status' => 'Pending',
            'user_id'  => Auth::user()->id,
            'team_id'  => ""
        ]);

        $this->resetData();

        Session::flash('message', 'Shares has been successfully issued!');
        Session::flash('alert-class', 'alert-success');
    }

    public function resetData()
    {
        $this->member = '';
        $this->product = '';

    }

    public function render()
    {
        return view('livewire.savings.new-savings-account');
    }
}

