<?php

namespace App\Http\Livewire\Loans;

use App\Models\ClientsModel;
use Livewire\Component;



use App\Models\Clients;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\AccountsModel;
use App\Models\LoansModel;

class GuarantorInfo_m extends Component
{

    public $client;
    public $client_number;
    public $results;
    public $relationship = '';
    public $item = 100;
    public $product_number;





    public function render()
    {


        if($this->client_number){
            $this->client =      ClientsModel::where('client_number', $this->client_number)->get();

        }
        else{
            $this->client = ClientsModel::where('client_number', trim(LoansModel::where('id',Session::get('currentloanID'))->value('guarantor')))->get();

        }


//        }

        return view('livewire.loans.guarantor-info');

    }



    public function set()
    {
        LoansModel::where('id', Session::get('currentloanID'))->update([
            'guarantor' => $this->client_number,
            'relationship'=>$this->relationship
        ]);

    }

}
