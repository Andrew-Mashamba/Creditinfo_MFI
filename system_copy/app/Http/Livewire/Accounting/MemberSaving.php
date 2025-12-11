<?php

namespace App\Http\Livewire\Accounting;
use App\Models\Accounting;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\sub_products;

use Livewire\Component;

class MemberSaving extends Component
{


    public function render()
    {
        $members=ClientsModel::get();


        foreach($members as $data){
        $data['name']= $data->first_name.' '.$data->middle_name.' '.$data->last_name;
        $data['balance']= $this->calculateMemberDeposit($data->client_number);
        }

        return view('livewire.accounting.member-saving',['clients'=>$members,'summary'=>$this->savingSummary(),'products'=>$this->getProduct()]);
    }


    function getMemberName($client_number){

        $member=ClientsModel::where('client_number',$client_number)->first();

        return $member->first_name.' '.$member->middle_number.' '.$member->last_name;
    }
    function savingSummary(){
        $member_number= ClientsModel::pluck('client_number')->toArray();

        return AccountsModel::where('product_number',2000)
          ->whereIn('client_number', $member_number)
        ->sum('balance');

    }



    function calculateMemberDeposit($member_number){

        return AccountsModel::where('product_number',2000)
               ->where('client_number',$member_number)
               ->sum('balance');
    }




    function getProduct(){

        return sub_products::where('product_id',2000)->get();

    }

}
