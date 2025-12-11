<?php

namespace App\Http\Livewire\Loans;

use App\Models\Committee;
use App\Models\User;
use Livewire\Component;
use App\Traits\Livewire\WithModulePermissions;
use Illuminate\Support\Facades\Session;


use App\Models\LoansModel;
use App\Models\approvals;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Hash;


use App\Models\AccountsModel;
use App\Models\Clients;
use App\Models\TeamUser;


use Livewire\WithFileUploads;
use App\Models\issured_loans;



use App\Models\general_ledger;


class Loans extends Component
{
    use WithModulePermissions;


    public $tab_id ;
    public $title = 'Loans list';

    public $selected;
    public $sortByBranch;
    public $filterLoanOfficer;
    public $selected_loan_id=1;




    public $activeLoansCount;
    public $inactiveLoansCount;
    public $showCreateNewLoansAccount;
    public $name;
    public $region;
    public $wilaya;
    public $membershipNumber;
    public $parentLoansAccount;
    public $showDeleteLoansAccount;
    public $LoansAccountSelected;
    public $showEditLoansAccount;
    public $pendingLoansAccount;
    public $LoansList;
    public $pendingLoansAccountname;
    public $LoansAccount;
    public $showAddLoansAccount;


    public $email;
    public $Loansstatus;
    public $permission = 'BLOCKED';
    public $password;

    public $member;
    public $product;
    public $number_of_loans;
    public $linked_loans_account;
    public $account_number;
    public $balance;
    public $nominal_price;
    public $showIssueNewLoans;




    public $accountSelected;
    public $sub_product_number;
    public $loansAvailable;

    public $selectedMenuItem = 1;
    public $view_path;

    public $showDropdown = false;

    public $search = ''; // Property to store the search term
    public $results = []; // Property to store the search results
    public $selectedAccount = [];
    public $selectedAccountTwo=[];
    public $showDropdownTwo=false;
    public $resultsTwo;
    public $searchTwo,$source_one,$source_two;
    public $isTableVisible = true;
    public $isTableVisibleTwo=true;



    protected $rules = [
        'member'=> 'required|min:1',
        'product'=> 'required|min:1',
        'number_of_loans'=> 'required|min:1',
        'linked_loans_account'=> 'required|min:1',
        'account_number'=> 'required|min:1',
    ];



        public function mount(){
            // Initialize the permission system for this module
            $this->initializeWithModulePermissions();
            
            Session::put('currentloanID', null);
            Session::put('currentloanClient', null);
            $this->view_path = 'loans.new-loans';
        }
        
        /**
         * Override to specify the module name for permissions
         * 
         * @return string
         */
        protected function getModuleName(): string
        {
            return 'loans';
        }

        public function selectedMenu($menuId)
        {
            $this->selectedMenuItem = $menuId;
            session()->put('tabId',$menuId);
            Session::forget('currentloanClient');
            Session::forget('currentloanID');


        }


    public function updatedSearch()
    {
        if (strlen($this->search) > 0) {
            $searchTerm = '%' . strtolower($this->search) . '%';

//            $accountsQuery = DB::table('loans')
//                ->select('id', 'loan_account_number', 'client_number', 'balance', 'principle')
//                ->where(function ($query) use ($searchTerm) {
//                    $query->where(DB::raw('LOWER(loan_account_number)'), 'LIKE', $searchTerm)
//                        ->orWhere(DB::raw('LOWER(client_number)'), 'LIKE', $searchTerm)
//                        ->orWhere(DB::raw('LOWER(status)'), 'LIKE', $searchTerm)
//                        ->orWhere(DB::raw('LOWER(principle)'), 'LIKE', $searchTerm);
//                });

            $accountsQuery = DB::table('loans')
                ->select(
                    'loans.id',
                    'loans.loan_account_number',
                    'loans.client_number',
                    'loans.principle',
                    'loans.loan_sub_product',
                    'loans.branch_id',
                    'loans.loan_type_2',
                    'loans.supervisor_id',
                    'loans.status',
                    // Joining tables and selecting formatted columns
                    DB::raw("CONCAT(clients.first_name, ' ', clients.middle_name, ' ', clients.last_name) as member_name"),
                    DB::raw('loan_sub_products.sub_product_name as loan_product_name'),
                    DB::raw("CONCAT(employees.first_name, ' ', employees.middle_name, ' ', employees.last_name) as loan_officer_name")
                )
                ->join('clients', 'clients.client_number', '=', 'loans.client_number')
                ->join('loan_sub_products', 'loan_sub_products.sub_product_id', '=', 'loans.loan_sub_product')
                ->leftJoin('employees', 'employees.id', '=', 'loans.supervisor_id') // Using LEFT JOIN for optional data
                ->where(function ($query) use ($searchTerm) {
                    $query->where(DB::raw('LOWER(loans.loan_account_number)::text'), 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere(DB::raw('LOWER(loans.client_number::text)'), 'LIKE', '%' . $searchTerm . '%')  // Explicitly casting to text
                        ->orWhere(DB::raw('LOWER(loans.status)::text'), 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere(DB::raw('LOWER(loans.principle::text)'), 'LIKE', '%' . $searchTerm . '%')  // Explicitly casting to text
                        ->orWhere(DB::raw('LOWER(clients.first_name)::text'), 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere(DB::raw('LOWER(clients.middle_name)::text'), 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere(DB::raw('LOWER(clients.last_name)::text'), 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere(DB::raw('LOWER(loan_sub_products.sub_product_name)::text'), 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere(DB::raw('LOWER(employees.first_name)::text'), 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere(DB::raw('LOWER(employees.middle_name)::text'), 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere(DB::raw('LOWER(employees.last_name)::text'), 'LIKE', '%' . $searchTerm . '%');
                });




            $loanIds = $accountsQuery->pluck('id')->toArray();


            session(['loan_ids' => $loanIds]);

            // Emit the event with the loan IDs
            $this->emit('loanIdsFetched', $loanIds);

           // dd($accountsQuery->get());

            // Combine results from both queries with union
            $this->results = $accountsQuery->get()->toArray();

            $this->isTableVisible = true; // Show table on search
            $this->showDropdown = true;

        } else {
            $this->showDropdown = false;
            $this->results = [];
        }
    }





    public function render()
    {
        $this->activeLoansCount = LoansModel::where('status', 'ACTIVE')->count();
        $this->inactiveLoansCount = LoansModel::where('status', 'PENDING')->count();
        $this->LoansList = LoansModel::get();
        return view('livewire.loans.loans', array_merge(
            $this->permissions,
            [
                'permissions' => $this->permissions
            ]
        ));
    }


}
