<?php

namespace App\Http\Livewire\Accounting;

use App\Models\ExpensesCategory;
use App\Models\general_ledger;
use App\Models\MembersModel;
use App\Models\TeamUser;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

use Exception;
use App\Models\ExpensesModel;
use App\Models\approvals;
use App\Models\AccountsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;
use App\Services\TransactionPostingService;

class Expenses extends Component
{

    public $title = 'Expenses list';
    public $selected;
    public $showCreateNewExpense;
    public $showDeleteExpense;
    public $ExpenseSelected;
    public $showEditExpense;
    public $Expense;
    public $showAddExpense;
    public $vendor;
    public $category;
    public $amount;
    public $paymentMethod;
    public $expenditureAccount;
    public $destinationAccount;
    public $employeeId;
    public $departmentId;
    public $expenseDate;
    public $confirmingUserDeletion = false;

    public $branches;
    public $activeExpensesCount;
    public $inactiveExpensesCount;
    public $pendingExpensename;
    public $photo;
    public $profile_photo_path;
    public $selectedCategory = null;
    public $approveExpenses=false;
    public $member_name;
    public $source_account;
    public $source_account_name;
    public $request_amount;
    public $description;
    public $account_balance;



    protected $listeners = [
        'showUsersList' => 'showUsersList',
        'editExpense' => 'editExpenseModal',
        'approveExpenses'=>'approveExpenseModal'
    ];

    protected $rules = [
        'vendor' => 'required|min:3',
        'category' => 'required|min:3',
        'amount' => 'required|numeric',
        'paymentMethod' => 'required|min:3',
        'expenditureAccount' => 'required|min:3',
        'destinationAccount' => 'required|min:3',
        'employeeId' => 'required|integer',
        'departmentId' => 'required|integer',
        'expenseDate' => 'required|date'
    ];
    public $categories;
    public $expenditureAccounts;
    public $member_number;
    public $sub_category_code;
    public $expenses_id;



    public function desplayExpensesData($id){
        ///
        $expenses=  DB::table('expenses')->where('id',$id)->first();
        $this->expenses_id=$expenses->id;
        $this->member_number = $expenses->member_number;
        $this->sub_category_code = $expenses->sub_category_code;
        $this->member_name =MembersModel::where('client_number',$expenses->member_number)
                            ->selectRaw("CONCAT(first_name,' ',middle_name,'  ',last_name) as name")->value('name');
         $this->source_account=AccountsModel::where('sub_category_code',$expenses->sub_category_code)->value('account_number');
         $this->source_account_name=AccountsModel::where('sub_category_code',$expenses->sub_category_code)->value('account_name');
         $this->account_balance= $this->availableBalance($expenses->sub_category_code);
         $this->request_amount=$expenses->amount;
         $this->description=$expenses->notes;

    }

    public function availableBalance($expense_sub_category){
        $currentMonth = date('n');
        $unusedBudget = DB::table('main_budget')->where('sub_category_code', $expense_sub_category)
            ->selectRaw('
                                    SUM(
                                        CASE
                                            WHEN ? = 1 THEN january
                                            WHEN ? = 2 THEN january + february
                                            WHEN ? = 3 THEN january + february + march
                                            WHEN ? = 4 THEN january + february + march + april
                                            WHEN ? = 5 THEN january + february + march + april + may
                                            WHEN ? = 6 THEN january + february + march + april + may + june
                                            WHEN ? = 7 THEN january + february + march + april + may + june + july
                                            WHEN ? = 8 THEN january + february + march + april + may + june + july + august
                                            WHEN ? = 9 THEN january + february + march + april + may + june + july + august + september
                                            WHEN ? = 10 THEN january + february + march + april + may + june + july + august + september + october
                                            WHEN ? = 11 THEN january + february + march + april + may + june + july + august + september + october + november
                                            ELSE january + february + march + april + may + june + july + august + september + october + november + december
                                        END
                                    ) AS unused_budget',
                [$currentMonth, $currentMonth, $currentMonth, $currentMonth, $currentMonth, $currentMonth, $currentMonth, $currentMonth, $currentMonth, $currentMonth, $currentMonth]
            )
            ->first();

        return $unusedBudget->unused_budget;
    }





    public function showAddExpenseModal($selected)
    {

        $this->selected = $selected;
        $this->showAddExpense = true;
    }

    public function updatedExpense()
    {
        $expenseData = ExpensesModel::where('id', $this->Expense)->get();
        foreach ($expenseData as $expense) {
            $this->vendor = $expense->vendor;
            $this->category = $expense->category;
            $this->amount = $expense->amount;
            $this->paymentMethod = $expense->paymentMethod;
            $this->expenditureAccount = $expense->expenditureAccount;
            $this->destinationAccount = $expense->destinationAccount;
            $this->employeeId = $expense->employeeId;
            $this->departmentId = $expense->departmentId;
            $this->expenseDate = $expense->expenseDate;

            // Perform further operations with the retrieved data as needed
        }
    }

    public function updateExpense(): void
    {
        $member_account=AccountsModel::where('member_number',$this->member_number)->where('sub_product_number','10001')->first();
        $member_new_balance = (double)$member_account->balance - (double)$this->request_amount;
        // AccountsModel::where('account_number',$member_account->account_number)->update(['balance'=>$member_new_balance]);
        $strongRoom=AccountsModel::where('sub_category_code',$this->sub_category_code)->first();
        $strong_room_new_balance=(double)$strongRoom->balance + (double)$this->request_amount;
        // AccountsModel::where('sub_category_code',$this->sub_category_code)->update(['balance'=>$strong_room_new_balance]);
        $transactionService = new TransactionPostingService();
        $transactionService->postTransaction([
            'first_account' => $member_account->account_number,
            'second_account' => $strongRoom->account_number,
            'amount' => $this->request_amount,
            'narration' => 'Expense payment',
        ]);
        $reference=time();
        // record on debit
        $record_on_general_ledger=new general_ledger();
        $record_on_general_ledger->debit(auth()->user()->institution_id,$member_account->account_number,$this->request_amount,$this->source_account_name,
            '000',$strongRoom->account_number,'successfully','Expense payment',$this->member_name,
            $member_account->account_number,$member_new_balance,'0000','0000',$reference
        );
        // record on credit
        $record_on_general_ledger->credit(auth()->user()->institution_id,$strongRoom->account_number,$this->request_amount,$strong_room_new_balance,$member_account->account_number,
            $this->member_number,$this->source_account_name,$this->member_name,'Expense payment','0000',$reference,'successfully','Expense payment'
            ,'000'
        );



        // Your array mapping months to their corresponding columns
        $monthColumns = [
            1 => 'january',
            2 => 'february',
            3 => 'march',
            4 => 'april',
            5 => 'may',
            6 => 'june',
            7 => 'july',
            8 => 'august',
            9 => 'september',
            10 => 'october',
            11 => 'november',
            12 => 'december',
        ];



        $monthValues = [];

// Get the current month
        $currentMonth = date('n');

// Initialize variables to keep track of available budget and the amount to deduct
        $availableBudget = 0; // Change this to the initial available budget value
        $amountToDeduct = $this->request_amount; // Change this to the amount you want to deduct

// Loop through the monthColumns array to retrieve values and deduct from January to the current month
        foreach ($monthColumns as $monthNumber => $columnName) {
            // Use Eloquent to retrieve the value of the specified month where id is 10
            $value = DB::table('main_budget')->where('sub_category_code', $this->sub_category_code)->value($columnName);

            // Store the value in the monthValues array
            $monthValues[$monthNumber] = $value;

            // Start deducting from January and move upward
            if ($monthNumber <= $currentMonth) {
                $budgetForMonth = $value;

                if ($amountToDeduct >= $budgetForMonth) {
                    //dd('hapa');
                    // Deduct the entire month's budget
                    if($budgetForMonth > 0){
                        $amountToDeduct = $amountToDeduct - $budgetForMonth;
                    }

                    // Update the month value to 0 after deduction
                    DB::table('main_budget')->where('sub_category_code', $this->sub_category_code)->update([$columnName => 0]);
                } else {
                    //dd($amountToDeduct);

                    $newMonthValue = $budgetForMonth - $amountToDeduct;

                    // Update the month value with the new value after deduction
                    DB::table('main_budget')->where('sub_category_code', $this->sub_category_code)->update([$columnName => $newMonthValue]);

                    break;
                }
            }
        }




        ExpensesModel::where('id',$this->expenses_id)->update(['status'=>'PAID']);
        // reset data
        $this->resetData();

        Session::flash('message', 'Expense has been successfully updated!');
        Session::flash('alert-class', 'alert-success');
        $this->emit('refreshExpensesTable');
        sleep(3);
        $this->approveExpenses = false;

    }

    public function addExpense()
    {
        $this->validate();

        ExpensesModel::create([
            'vendor' => $this->vendor,
            'category' => $this->category,
            'amount' => $this->amount,
            'paymentMethod' => $this->paymentMethod,
            'expenditureAccount' => $this->expenditureAccount,
            'destinationAccount' => $this->destinationAccount,
            'employeeId' => $this->employeeId,
            'departmentId' => $this->departmentId,
            'expenseDate' => $this->expenseDate,
        ]);

        // reset data
        $this->resetData();

        Session::flash('message', 'A new expense has been successfully created!');
        Session::flash('alert-class', 'alert-success');

        $this->showAddExpense = false;
    }

    public function resetData()
    {
        $this->vendor = '';
        $this->category = '';
        $this->amount = '';
        $this->paymentMethod = '';
        $this->expenditureAccount = '';
        $this->destinationAccount = '';
        $this->employeeId = '';
        $this->departmentId = '';
        $this->expenseDate = '';
    }

    // ... the rest of the methods

    public function sendApproval($id,$msg,$code){

        $user = auth()->user();


        approvals::create([
            'institution' => $id,
            'process_name' => 'createExpense',
            'process_description' => $msg,
            'approval_process_description' => 'has approved a transaction',
            'process_code' => $code,
            'process_id' => $id,
            'process_status' => 'Pending',
            'user_id'  => Auth::user()->id,
            'team_id'  => $id
        ]);

    }


    public function submit()
    {

        $institution_id='';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User){
            $institution_id=$User->team_id;
        }

        $this->validate();

        // Execution doesn't reach here if validation fails.

        $id =  ExpensesModel::create([
            'name' => $this->name,
            'region' => $this->region,
            'wilaya' => $this->wilaya,
            'expenseshipNumber' => $this->expenseshipNumber,
            'parentExpense' => $this->parentExpense,
         
            'Expense_status'  => 'Pending'
        ])->id;

        $user = auth()->user();


        approvals::create([
            'institution' => '',
            'process_name' => 'createExpense',
            'process_description' => 'has created a new Expense',
            'approval_process_description' => 'has approved a new Expense',
            'process_code' => '01',
            'process_id' => $id,
            'process_status' => 'Pending',
            'user_id'  => session()->get('currentUser')->id,
            'team_id'  => ""
        ]);



        $this->resetData();

        Session::flash('message', 'A new Expense has been successfully created!');
        Session::flash('alert-class', 'alert-success');
    }




    public function menuItemClicked($tabId){
        $this->tab_id = $tabId;
        if($tabId == '1'){
            $this->title = 'Expenses list';
        }
        if($tabId == '2'){
            $this->title = 'Enter new Expense details';
        }
    }


    public function createNewExpense()
    {


        $this->showCreateNewExpense = true;
    }

    public function approveExpenseModal($id)
    {
        $this->desplayExpensesData($id);
        $this->approveExpenses = true;
        $this->ExpenseSelected = $id;
    }

    public function editExpenseModal($id)
    {
        $this->showEditExpense = true;
        $this->pendingExpense = $id;
        $this->Expense = $id;
        $this->pendingExpensename = ExpensesModel::where('id',$id)->value('first_name');
        $this->updatedExpense();

    }

    public function closeModal(){
        $this->showCreateNewExpense = false;
        $this->showDeleteExpense = false;
        $this->showEditExpense = false;
    }

    public function confirmPassword(): void
    {
        // Check if password matches for logged-in user
        if (Hash::check($this->password, auth()->user()->password)) {
            //dd('password matches');
            $this->delete();
        } else {
            //dd('password does not match');
            Session::flash('message', 'This password does not match our records');
            Session::flash('alert-class', 'alert-warning');
        }
        $this->resetPassword();


    }

    public function resetPassword(): void
    {
        $this->password = null;
    }

    public function delete(): void
    {
        $user = User::where('id',$this->userSelected)->first();
        $action = '';
        if ($user) {

            if($this->permission == 'BLOCKED'){
                $action = 'blockUser';
            }
            if($this->permission == 'ACTIVE'){
                $action = 'activateUser';
            }
            if($this->permission == 'DELETED'){
                $action = 'deleteUser';
            }

            $update_value = approvals::updateOrCreate(
                [
                    'process_id' => $this->userSelected,
                    'user_id' => Auth::user()->id

                ],
                [
                    'institution' => '',
                    'process_name' => $action,
                    'process_description' => $this->permission.' user - '.$user->name,
                    'approval_process_description' => '',
                    'process_code' => '29',
                    'process_id' => $this->userSelected,
                    'process_status' => $this->permission,
                    'approval_status' => 'PENDING',
                    'user_id'  => Auth::user()->id,
                    'team_id'  => '',
                    'edit_package'=> null
                ]
            );


            // Delete the record
            //$node->delete();
            // Add your logic here for successful deletion
            Session::flash('message', 'Awaiting approval');
            Session::flash('alert-class', 'alert-success');

            $this->closeModal();
            $this->render();


        } else {
            // Handle case where record was not found
            // Add your logic here
            Session::flash('message', 'Node error');
            Session::flash('alert-class', 'alert-warning');
        }

    }

    public function render()
    {
//        $this->categories  = ExpensesCategory::get();
        $this->expenditureAccounts = AccountsModel::where('product_number', '10')->get();
        return view('livewire.accounting.expenses');
    }
}
