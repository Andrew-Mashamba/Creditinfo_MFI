<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Session;
use App\Models\Clients;
use App\Models\AccountsModel;
use App\Models\Branches;
use App\Models\loan_images;
use App\Models\LoansModel;

class BusinessData extends Component
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

    protected $listeners = [
        'updatedDailySales',
        'updatedCashAtHand',
        'updatedBusinessInventory',
        'updatedCostOfGoodsSold',
        'updatedOperatingExpenses',
        'updatedMonthlyTaxes',
        'updatedOtherExpenses'
    ];

    public function mount()
    {
        $loan = LoansModel::findOrFail(Session::get('currentloanID'));

        $this->loan = $loan;
        $this->business_name = $loan->business_name;
        $this->business_category = $loan->business_category;
        $this->business_type = $loan->business_type;
        $this->business_licence_number = $loan->business_licence_number;
        $this->business_tin_number = $loan->business_tin_number;
        $this->business_inventory = $this->formatNumber($loan->business_inventory);
        $this->cash_at_hand = $this->formatNumber($loan->cash_at_hand);
        $this->daily_sales = $this->formatNumber($loan->daily_sales);
        $this->cost_of_goods_sold = $this->formatNumber($loan->cost_of_goods_sold);
        $this->operating_expenses = $this->formatNumber($loan->operating_expenses);
        $this->monthly_taxes = $this->formatNumber($loan->monthly_taxes);
        $this->other_expenses = $this->formatNumber($loan->other_expenses);
        $this->business_age = $loan->business_age;
    }

    public function formatNumber($amount)
    {
        return number_format((int)$amount);
    }

    public function updatedField($field, $value)
    {
        $this->{$field} = $this->formatNumber($value);
    }

    public function removeNumberFormat($amount)
    {
        return (float)str_replace(',', '', $amount);
    }

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:1024', // 1MB Max
        ]);
    }

    public function render()
    {
        $loanData = [
            'business_name' => $this->business_name,
            'business_category' => $this->business_category,
            'business_type' => $this->business_type,
            'business_licence_number' => $this->business_licence_number,
            'business_tin_number' => $this->business_tin_number,
            'business_inventory' => $this->removeNumberFormat($this->business_inventory),
            'cash_at_hand' => $this->removeNumberFormat($this->cash_at_hand),
            'daily_sales' => $this->removeNumberFormat($this->daily_sales),
            'cost_of_goods_sold' => $this->removeNumberFormat($this->cost_of_goods_sold),
            'operating_expenses' => $this->removeNumberFormat($this->operating_expenses),
            'monthly_taxes' => $this->removeNumberFormat($this->monthly_taxes),
            'other_expenses' => $this->removeNumberFormat($this->other_expenses),
            'business_age' => $this->business_age,
        ];

        LoansModel::where('id', Session::get('currentloanID'))->update($loanData);

        return view('livewire.loans.business-data');
    }

    public function close($loanImageID)
    {
        loan_images::findOrFail($loanImageID)->delete();
    }

    public function saveImage()
    {
        $loan_id = LoansModel::where('id', Session::get('currentloanID'))->value('loan_id');
        $filename = time() . '_' . $this->photo->getClientOriginalName();
        $imageUrl = $this->photo->store('business', 'public');

        loan_images::create([
            'loan_id' => $loan_id,
            'category' => 'business',
            'url' => $imageUrl,
        ]);

        $this->photo = null;
    }
}
