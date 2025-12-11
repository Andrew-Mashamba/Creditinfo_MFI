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

class CollateralInfo extends Component
{


    use WithFileUploads;

    public $photo;

    public $collateral_type;
    public $collateral_description;
    public $collateral_location;
    public $loan;
    public $collateral_value;


    public function formatedNumber($amount)
    {$amaunt= (int)$amount;

        return number_format($amount);
    }
    function updatedCollateralValue($amount)
    {
        $amaunt= (int)$amount;
        $this->collateral_value= number_format($amaunt);

    }

    function removeNumberFormat($amount)
    {
        return (float) str_replace(',', '', $amount);
    }

    public function boot()
    {

        $this->loan = LoansModel::where('id', Session::get('currentloanID'))->get();

        foreach ($this->loan as $theloan) {
            $this->collateral_value = $this->formatedNumber( $theloan->collateral_value);
            $this->collateral_location = $theloan->collateral_location;
            $this->collateral_description = $theloan->collateral_description;
            $this->collateral_type = $theloan->collateral_type;

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

        $this->collateral_value=$this->removeNumberFormat($this->collateral_value);

        LoansModel::where('id', Session::get('currentloanID'))->update([
            'collateral_value' => $this->collateral_value,
            'collateral_location' => $this->collateral_location,
            'collateral_description' => $this->collateral_description,
            'collateral_type' => $this->collateral_type
        ]);

        return view('livewire.loans.collateral-info');
    }

    public function close($loanID){
        loan_images::find($loanID)->delete();
    }

    public function saveImage()
    {
        $loan_id = LoansModel::where('id', Session::get('currentloanID'))->value('loan_id');
        //$imageUrl = $this->photo->store('avatars', 'public');
        $path = $this->photo->store('photos', 'local');
        $path = str_replace("photos/", "", $path);
        $imageUrl = 'storage/' . $path;
        loan_images::create([
            'loan_id' => $loan_id,
            'category' => 'collateral',
            'url' => $imageUrl,

        ]);
        $this->photo = null;
    }
}

