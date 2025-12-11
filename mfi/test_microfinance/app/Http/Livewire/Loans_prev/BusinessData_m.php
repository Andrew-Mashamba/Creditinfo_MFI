<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;


use Illuminate\Support\Facades\Session;
use Livewire\WithFileUploads;
use App\Models\Clients;
use App\Models\AccountsModel;
use App\Models\Branches;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\loan_images;
use App\Models\LoansModel;

class BusinessData_m extends Component
{


    use WithFileUploads;

    public $photo;
    public $business_category;
    public $business_type;
    public $business_licence_number;
    public $business_tin_number;
    public $business_inventory;
    public $cash_at_hand;
    public $daily_sales;
    public $loan;
    public $business_name;

    public $cost_of_goods_sold;
    public $operating_expenses;
    public $monthly_taxes;
    public $other_expenses;
    public $business_age;


    public function boot()
    {

        $this->loan = LoansModel::where('id',Session::get('currentloanID'))->get();

        foreach ($this->loan as $theloan){
            $this->business_name = $theloan->business_name;
            $this->business_category=$theloan->business_category;
            $this->business_type=$theloan->business_type;
            $this->business_licence_number=$theloan->business_licence_number;
            $this->business_tin_number=$theloan->business_tin_number;
            $this->business_inventory=$theloan->business_inventory;
            $this->cash_at_hand=$theloan->cash_at_hand;
            $this->daily_sales=$theloan->daily_sales;

            $this->cost_of_goods_sold=$theloan->cost_of_goods_sold;
            $this->operating_expenses=$theloan->operating_expenses;
            $this->monthly_taxes=$theloan->monthly_taxes;
            $this->other_expenses=$theloan->other_expenses;
            $this->business_age=$theloan->business_age;
        }


    }

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:1024',// 1MB Max
        ]);
    }


    public function render()
    {

        LoansModel::where('id',Session::get('currentloanID'))->update([
            'business_name'=>$this->business_name,
            'business_category'=>$this->business_category,
            'business_type'=>$this->business_type,
            'business_licence_number'=>$this->business_licence_number,
            'business_tin_number'=>$this->business_tin_number,
            'business_inventory'=>$this->business_inventory,
            'cash_at_hand'=>$this->cash_at_hand,
            'daily_sales'=>$this->daily_sales,

            'cost_of_goods_sold'=>$this->cost_of_goods_sold,
            'operating_expenses'=>$this->operating_expenses,
            'monthly_taxes'=>$this->monthly_taxes,
            'business_age'=>$this->business_age,
            'other_expenses'=>$this->other_expenses

        ]);

        return view('livewire.loans.business-data');
    }

    public function close($loanimageID){
        loan_images::find($loanimageID)->delete();
    }

    public function saveImage(){
        $loan_id = LoansModel::where('id',Session::get('currentloanID'))->value('loan_id');
        //$imageUrl = $this->photo->store('avatars', 'public');
        $path = $this->photo->store('photos', 'local');
        $path = str_replace("photos/", "", $path);
        $imageUrl = 'storage/' . $path;
        loan_images::create([
            'loan_id' => $loan_id,
            'category' => 'business',
            'url' => $imageUrl,

        ]);
        $this->photo = null;
    }
}
