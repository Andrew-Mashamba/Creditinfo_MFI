<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Models\Accounting;
use App\Models\ClientsModel;
use App\Models\sub_products;
use App\Models\AccountsModel;
use Illuminate\Support\Facades\DB;

class MemberShare extends Component
{
    public function render()
    {
        $members = ClientsModel::get();


        foreach ($members as $data) {
            $data['name'] = $data->first_name . ' ' . $data->middle_name . ' ' . $data->last_name;
            $data['balance'] = $this->calculateMemberShares($data->client_number);
            $data['share']=$this->noOfShares($data->client_number);

        }

        return view('livewire.accounting.member-share', ['clients' => $members, 'summary' => $this->shareSummary(),'total_share'=>$this->totalShares()
       , 'products'=>$this->getProduct()
    ]);
    }


    function noOfShares($client_number){
        return DB::table('share_registers')
            ->where('client_number', $client_number)
            ->sum('shares');
    }

    function totalShares(){
        $member_number = ClientsModel::pluck('client_number')->toArray();

        return DB::table('share_registers')
            ->whereIn('client_number', $member_number)
            ->sum('shares');
    }


    function getMemberName($client_number)
    {

        $member = ClientsModel::where('client_number', $client_number)->first();
        return $member->first_name . ' ' . $member->middle_number . ' ' . $member->last_name;
    }
    function shareSummary()
    {
       // $member_number = ClientsModel::pluck('client_number')->toArray();

        return DB::table('accounts')->whereIn('sub_category_code', ['3003','3010','3030','3040'])
           // ->whereIn('client_number', $member_number)
            ->sum('balance');


    }

    function calculateMemberShares($member_number)
    {
        return  AccountsModel::whereIn('sub_category_code', [3003,3010,3030,3040])
            ->where('client_number', $member_number)
            ->sum('balance');
    }


    function getProduct(){

        return sub_products::where('product_id',3000)->get();

    }



}
