<?php

namespace App\Http\Livewire\Shares;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\AccountsModel;
use App\Models\sub_products;
use Illuminate\Support\Facades\Session;


class SharesListByProduct extends Component
{

    public $term = "";
    public $showAddUser = false;
    public $memberStatus = 'All';
    public $numberOfProducts;
    public $noOfprodAccount;
    public $sub_product_id;
    public $products;
    public $product_name;
    public $item;



    protected $listeners = ['refreshMembersListComponent' => '$refresh'];



    public function visit($item){

        Session::put('sharesViewItem',$item);
        $this->item = $item;
        $this->emit('refreshShareComponent');
    }


    public function render()
    {
        $this->product_name = sub_products::where('sub_category_code',3010)->get();
        $query = sub_products::where('sub_category_code',3010);
        $this->products = $query->get();
        $this->numberOfProducts = $query->count();
        

        return view('livewire.shares.shares-list-by-product');
    }
}
