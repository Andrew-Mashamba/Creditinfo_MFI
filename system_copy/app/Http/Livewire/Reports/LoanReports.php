<?php

namespace App\Http\Livewire\Reports;

use App\Models\BranchesModel;
use App\Models\LoansModel;
use App\Models\sub_products;
use DateTime;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class LoanReports extends LivewireDatatable
{
    public $value;
    public $exportable=true;
    public $start_date;
    public $end_date;
    public $sortByBranch;

    protected $listeners=['category'=>'reportSection',
                           'updatedStartDate'=>'startDate',
                           'sortByBranchChanged'=>'sortByBranchChanged',
                           'updatedEndDate'=>'endDate',
                           'refresh'=>'$refresh'
                            ];

    public function sortByBranchChanged($value)
    {
        $this->sortByBranch = $value;


    }

   public function endDate($end_date){
       $this->end_date=$end_date;
       $this->end_date=DateTime::createFromFormat('Y-m-d H:i:s', $end_date)->format('Y-m-d H:i:s');

   }
    public function startDate($start_date){
        $this->start_date=$start_date;
            $this->start_date=DateTime::createFromFormat('Y-m-d', $start_date)->format('y-m-d');

//        dd($start_date);
    }

    public function reportSection($selected){
        $this->value=$selected;
    }
  public function builder(){
    //   if($this->value==2){
    //       $this->title="NEW CLIENT";
    //   }
    //   elseif($this->value==3){
    //       $this->title="ONPROGRESS";
    //   }
    //   elseif($this->value==4){
    //       $this->title="AWAITING APPROVAL";
    //   }
    //   elseif($this->value==5){
    //       $this->title="AWAITING DISBURSEMENT";
    //   }
    //   elseif($this->value==6){
    //       $this->title="REJECTED";
    //   }
    //   elseif($this->value==7){
    //       $this->title="ACTIVE";
    //   }
       $query=LoansModel::query(); //->where('status',$this->title)->where('branch_id',session()->get('sortingBranch'));

      if(!empty($this->start_date)){
          $query=$query->where('created_at','>=',$this->start_date)->where('branch_id',session()->get('sortingBranch'));
      }
      elseif(!empty($this->end_date)){
         $query= $query->where('created_at','<=',$this->end_date)->where('branch_id',session()->get('sortingBranch'));
      }
     return  $query;
  }

  public function columns()
  {
      return [
          column::name('created_at')->label('date'),
          column::callback('client_number',function($client_number){

            $member= DB::table('clients')->where('client_number',$client_number)->first();
            return $member ?->first_name.' '. $member ?->middle_name.'  '. $member ?->last_name ;


          })->label('Member Name'),
          column::name('loan_id')->label('id'),

          column::name('loan_account_number')->label('account number'),


          Column::callback(['id'],function ($id){

            $loan=LoansModel::find($id);
            $account= DB::table('sub_accounts')->where('account_number',$loan->loan_account_number)->first();

            if($account){
                return number_format($account->credit,1);
            }
            return  0;

          })->label('credit'),


          Column::callback('id',function ($id){

            $loan=LoansModel::find($id);
            $account= DB::table('sub_accounts')->where('account_number',$loan->loan_account_number)->first();

            if($account){
                return number_format($account->debit,1);
            }
            return  0;

          })->label(' debit'),



          Column::callback(['loan_account_number'],function ($loan_account){

            $account= DB::table('sub_accounts')->where('account_number',$loan_account)->first();
            if($account){
                return number_format($account->balance,1);
            }
            return  0;

          })->label(' account balance'),


          column::name('client_number')->label('client number'),
          column::callback('loan_sub_product',function($sub_product_id){
              return DB::table('loan_sub_products')->where('sub_product_id',$sub_product_id)->value('sub_product_name');
          })->label('product name'),
          column::name('guarantor')->label('guarantor'),
          column::callback('branch_id',function($branch_id){
              return BranchesModel::where('id',$branch_id)->value('name');
          })->label('branch'),
          column::callback('principle',function($principle){
              return number_format($principle);
          })->label('principle')->searchable(),
          column::callback('interest',function ($interest){
              return $interest.'%';
          })->label('interest')->searchable(),
          column::name('business_name')->label('business name'),
          column::name('heath')->label('health'),
          column::name('tenure')->label('tenure'),
          column::name('status')->label('status')


      ];

      // TODO: Change the autogenerated stub
  }

}
