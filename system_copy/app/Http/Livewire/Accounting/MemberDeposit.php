<?php

namespace App\Http\Livewire\Accounting;

use App\Models\Accounting;
use App\Models\ClientsModel;
use App\Models\sub_products;
use App\Models\AccountsModel;
use Livewire\Component;

class MemberDeposit extends Component
{
    public function render()
    {
       // $ids=Accounting::query()->pluck('client_number')->toArray();
        $members=ClientsModel::get();


        foreach($members as $data){
        $data['name']= $data->first_name.' '.$data->middle_name.' '.$data->last_name;
        $data['balance']= $this->calculateMemberDeposit($data->client_number);
        }

        return view('livewire.accounting.member-deposit',['clients'=>$members,'summary'=>$this->depositSummary(),
                                                                      'products'=>$this->getProduct()
                                                                    ]);
    }


    function getMemberName($client_number){

        $member=ClientsModel::where('client_number',$client_number)->first();

        return $member->first_name.' '.$member->middle_number.' '.$member->last_name;
    }
    function depositSummary(){
        $member_number= ClientsModel::pluck('client_number')->toArray();

        return AccountsModel::where('product_number',1000)
          ->whereIn('client_number', $member_number)
        ->sum('balance');

    }



    function calculateMemberDeposit($member_number){

        return AccountsModel::where('product_number',1000)
               ->where('client_number',$member_number)
               ->sum('balance');
    }



    function getProduct(){

        return sub_products::where('product_id',1000)->get();

    }






}
