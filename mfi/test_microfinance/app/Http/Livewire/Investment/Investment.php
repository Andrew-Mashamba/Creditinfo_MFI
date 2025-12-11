<?php

namespace App\Http\Livewire\Investment;

use App\Models\AccountsModel;
use App\Models\approvals;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Services\BalanceSheetItemIntegrationService;

class Investment extends Component
{
    public $investment_name;
    public $investment_type;
    public $investment_amount;
    public $expense_account;
    public $showProjectDetails;
    
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create investment account under
    public $other_account_id; // The other account for double-entry (Cash/Bank - credit side)

    protected $rules=['investment_name'=>'required|string',
                       'investment_type'=>'required|int',
                         'investment_amount'=>'required|numeric'
        ];

    public $selected=1;
    public $showCreateInvestmentModal=false;


    public function createInvestmentModal(){
        if($this->showCreateInvestmentModal==false){
            $this->showCreateInvestmentModal=true;}
        else if($this->showCreateInvestmentModal==true){
            $this->showCreateInvestmentModal=false;
            $this->selected=1;
        }  }

    public function  showAddClientModal($id){
        $this->selected=$id;
        $this->createInvestmentModal();
    }


    public function createInvestment(){
        $this->validate();
        // investment instance

        $investment= new \App\Models\Investment();
        $investment->investment_name=$this->investment_name;
        $investment->investment_type=$this->investment_type;
        $investment->investment_amount=$this->investment_amount;
        $investment->save();

        // Use Balance Sheet Integration Service to create accounts and post to GL
        $integrationService = new BalanceSheetItemIntegrationService();
        
        try {
            $integrationService->createInvestmentAccount(
                $investment,
                $this->parent_account_number,  // Parent account to create investment account under
                $this->other_account_id        // The other account for double-entry (Cash/Bank - credit side)
            );
            
        } catch (\Exception $e) {
            \Log::error('Failed to integrate investment with accounts table: ' . $e->getMessage());
        }

        $data=json_encode(['amount'=>$this->investment_amount,'name'=>$this->investment_name,'account_code'=>$this->expense_account]);
        session()->flash('message','New investment has been created successfully');

        //approval
        $approvals=new approvals();
        $approvals->sendApproval($investment->id,'createInvestment','has created new investment','new investment has been created','102',$data);

        // reset data
        $this->resetInvestmentData();
    }




    public function resetInvestmentData(){
        $this->investment_name=null;
        $this->investment_type=null;
        $this->investment_amount=null;
    }

    public function render()
    {
        // Get accounts for account selection
        $parentAccounts = DB::table('accounts')
            ->where('major_category_code', '1000') // Asset accounts
            ->where('account_level', '<=', 2) // Parent level accounts only
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%INVESTMENT%')
                      ->orWhere('account_name', 'LIKE', '%ASSET%');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        $otherAccounts = DB::table('bank_accounts')
            
            


            
            ->select('internal_mirror_account_number', 'bank_name', 'account_number')
            ->where('status', 'ACTIVE')
            ->orderBy('bank_name')
            ->get();

        return view('livewire.investment.investment', [
            'parentAccounts' => $parentAccounts,
            'otherAccounts' => $otherAccounts
        ]);
    }
}
