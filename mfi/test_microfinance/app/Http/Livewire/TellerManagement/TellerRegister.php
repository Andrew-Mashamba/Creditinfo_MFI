<?php
//
//namespace App\Http\Livewire\TellerManagement;
//
//use App\Models\AccountsModel;
//use App\Models\approvals;
//use App\Models\Teller;
//use App\Models\Employee;
//use Illuminate\Support\Facades\Config;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Hash;
//use Livewire\Component;
//
//class TellerRegister extends Component
//{
//    // define attrubute
//    public  $employee_id;
//    public $branch_id;
//    public $max_amount;
//    public $registered_by_id;
//    public $account_id;
//    public $transaction_type;
//    public $teller_description;
//    public $employee_details;
//    public $teller_account_id;
//
//    //editing
//    public $editTellerInfomation;
//
//    //password
//    public $password;
//    public $deleteTellerModal=false;
//    public $permission;
//
//    public $assignNewTellerModal=false;
//
//
//    public $listeners=['editTeller'=>'editTellerAction',
//        'deleteTeller'=>'deleteModal',
//        'assignTeller'=>'assignNewTeller'
//    ];
//
//    protected $rules=[
//        'branch_id'=>'required|numeric',
//        'max_amount'=>'required',
////                        'registered_by_id'=>'required',
////                         'account_id'=>'required',
//    ];
//
//
//
//
//    public function resetTeller(){
//        $this->branch_id=null;
//        $this->max_amount=null;
//        $this->registered_by_id=null;
//        $this->account_id=null;
//        $this->teller_type=null;
//        $this->teller_description=null;
//    }
//
//    public function createTeller(){
//        $this->validate();
//
//
//        $employeeDetails = Employee::where('id', $this->employee_id)->first();
//
//
//        $edit_package=json_encode(['branch_id'=>$this->branch_id]);
//
//        // teller instance
//        $teller = new \App\Models\Teller();
//        $teller->registered_by_id = auth()->user()->id;
//        $teller->branch_id = $this->branch_id;
//        $teller->max_amount = $this->max_amount;
//        $teller->status="PENDING";
//        $teller->institution_id=auth()->user()->institution_id;
//        $teller->save();
//
//        // send for approval
//
//        $approval = new approvals();
//        $approval->sendApproval($teller->id, 'createTeller', 'has created teller', 'approve new teller', '102',$edit_package);
//        session()->flash('message', 'Successfully created');
////          }
////            catch (\Exception $e){
////                session()->flash('message_fail','fail to create new teller');
////            }
////     }
////     catch (\Exception $e){
////         session()->flash('message_fail',"failed to create teller account");
////     }
//
////     finally {
//        $this->resetTeller();
////     }
//    }
//
//    public $createNewTeller=false;
//
//    public function showRegisterTellerModal(){
//        if($this->createNewTeller==false){
//            $this->createNewTeller=true;
//        }
//        else if($this->createNewTeller==true){
//            $this->createNewTeller=false;
//        }
//    }
//
//
//    public function render()
//    {
//        return view('livewire.teller-management.teller-register');
//    }
//
//
//
//    public function editTellerModal(){
//        if($this->editTellerInfomation==false){
//            $this->editTellerInfomation=true;
//        }else if($this->editTellerInfomation==true){
//            $this->editTellerInfomation=false;
//        }
//    }
//
//
//    public function editTellerAction(){
//
//        $tellers=DB::table('tellers')->where('id','=',session()->get('editTellerId'))->first();
//        $this->employee_id=$tellers->employee_id;
//        $this->branch_id=$tellers->branch_id;
//        $this->max_amount=$tellers->max_amount;
//        $this->registered_by_id=$tellers->registered_by_id;
//        $this->account_id=$tellers->account_id;
//        $this->editTellerModal();
//
////       dd( session()->get('editTellerId'));
//    }
//
//
//    public function updateTeller(){
//        $this->validate();
//        $id=session()->get('editTellerId');
//
//
//        $edit_package=json_encode([
//            'branch_id'=>$this->branch_id,
//            'max_amount'=>$this->max_amount,]);
//        // approval
//        $approval=new approvals();
//        $approval->sendApproval($id,'editTeller','teller has edited ','has to edit new teller','102',$edit_package);
//
//
//
//        session()->flash('message','updated successfully');
//    }
//
//
//    public function deleteModal(){
//        if($this->deleteTellerModal==false){
//            $this->deleteTellerModal=true;
//        }
//        else if($this->deleteTellerModal==true){
//            $this->deleteTellerModal=false;
//        }
//    }
//
//    public function deletetellerAction(){
//        $this->validate(['password'=>'required','permission'=>'required']);
//        $status=$this->permission;
//        if(Hash::Check($this->password,auth()->user()->password)){
//            if(DB::table('tellers')->where('id',session()->get('editTellerId'))->value('status')==$status){
//                session()->flash('message_fail','you can not'.$status.'twice');
//            }else {
//                DB::table('tellers')->where('id', session()->get('editTellerId'))->update(['status' => $status]);
//
//                $edit_package=json_encode($status);
//                //waiting for approval
//                $approval = new approvals();
//                $approval->sendApproval(session()->get('editTellerId'), 'removeTeller', 'has deleted Teller', 'has deleted teller', '102',$edit_package);
//
//                session()->flash('message', 'successfully, wait for approval');
//            }
//        }
//        else{
//            session()->flash('message_fail','invalid password');
//
//        }
//        $this->resetTeller();
//
//    }
//
//
//
//    public function assignNewTeller($id){
//        session()->put('tellerId',$id);
//        if($this->assignNewTellerModal==false){
//            $this->assignNewTellerModal=true;
//        }
//        else if($this->assignNewTellerModal==true){
//            $this->assignNewTellerModal=false;
//        }
//    }
//
//
//    public function assignEmployeeToTellerPosition(){
//
//        // check if id is present
//        $teller_id=Teller::where('employee_id',$this->employee_details)->first();
//
//        if($teller_id){
//
//            session()->flash('message_fail',"Sorry You cannot assign one teller in two position");
//        }
//        else if(Teller::where('id',session()->get('tellerId'))->value('account_id')==null or Teller::where('id',session()->get('tellerId'))->value('employee_id')!=null){
//            session()->flash('message_fail',"Sorry You cannot assign teller for now");
//
//        }
//
//        else{
//            $edit_package=json_encode(['employee_id'=>$this->employee_details]);
//            // send for the approval
//            Teller::where('id',session()->get('tellerId'))->update(['progress_status'=>'PENDING']);
//            $approvals=new approvals();
//            $approvals->sendApproval(session()->get('tellerId'),'assignTeller','has assign new teller','new  teller has been assigned','102',$edit_package);
//            session()->flash('message','successfully assigned');
//        }
//    }
//}


namespace App\Http\Livewire\TellerManagement;

use App\Models\AccountsModel;
use App\Models\approvals;
use App\Models\Teller;
use App\Models\Employee;
use App\Models\BranchesModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class TellerRegister extends Component
{
    use WithPagination;

    // Form properties
    public $employee_id;
    public $branch_id;
    public $max_amount;
    public $registered_by_id;
    public $account_id;
    public $transaction_type;
    public $teller_description;
    public $employee_details;
    public $teller_account_id;
    public $teller_name;
    public $notes;

    // Modal states
    public $createNewTeller = false;
    public $editTellerInfomation = false;
    public $deleteTellerModal = false;
    public $assignNewTellerModal = false;
    public $viewTellerModal = false;
    public $tab_id = 1;

    // Table properties
    public $search = '';
    public $statusFilter = '';
    public $branchFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // Password and permissions
    public $password;
    public $permission;

    // Selected teller for operations
    public $selectedTellerId;
    public $selectedTeller;


    public $listeners = ['editTeller' => 'editTellerAction',
        'deleteTeller' => 'deleteModal',
        'assignTeller' => 'assignNewTeller'
    ];

    // Validation rules
    protected $rules = [
        'branch_id' => 'required|numeric',
        'max_amount' => 'required|numeric|min:0',
        'teller_name' => 'required|string|max:100',
        'notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'branch_id.required' => 'Branch selection is required.',
        'max_amount.required' => 'Maximum transaction amount is required.',
        'max_amount.numeric' => 'Maximum amount must be a valid number.',
        'max_amount.min' => 'Maximum amount cannot be negative.',
        'teller_name.required' => 'Teller name is required.',
        'teller_name.max' => 'Teller name cannot exceed 100 characters.',
    ];


    public function resetTeller()
    {
        $this->branch_id = null;
        $this->max_amount = null;
        $this->registered_by_id = null;
        $this->account_id = null;
        $this->teller_description = null;
        $this->teller_name = null;
        $this->notes = null;
        $this->employee_id = null;
    }

    public function createTeller()
    {
        $this->validate();

        try {
            $edit_package = json_encode([
                'branch_id' => $this->branch_id,
                'max_amount' => $this->max_amount,
                'teller_name' => $this->teller_name,
                'notes' => $this->notes
            ]);

            // Create teller instance
            $teller = new Teller();
            $teller->registered_by_id = auth()->user()->id;
            $teller->branch_id = $this->branch_id;
            $teller->max_amount = $this->max_amount;
            $teller->teller_name = $this->teller_name;
            $teller->notes = $this->notes;
            $teller->status = "PENDING";
            $teller->save();

            // Send for approval
            $approval = new approvals();
            $approval->sendApproval($teller->id, 'createTeller', auth()->user()->name . ' has created teller', 'approve new teller', '102', $edit_package);
            
            session()->flash('message', 'Teller created successfully and sent for approval');
            $this->resetTeller();
            $this->createNewTeller = false;
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create teller: ' . $e->getMessage());
        }
    }

    public function showRegisterTellerModal()
    {
        if ($this->createNewTeller == false) {
            $this->createNewTeller = true;
        } else if ($this->createNewTeller == true) {
            $this->createNewTeller = false;
        }
    }


    // Table methods
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedBranchFilter()
    {
        $this->resetPage();
    }

    // CRUD methods
    public function viewTeller($id)
    {
        $this->selectedTeller = Teller::with(['employee', 'branch'])->findOrFail($id);
        $this->viewTellerModal = true;
    }

    public function editTeller($id)
    {
        $teller = Teller::findOrFail($id);
        $this->selectedTellerId = $id;
        $this->branch_id = $teller->branch_id;
        $this->max_amount = $teller->max_amount;
        $this->teller_name = $teller->teller_name;
        $this->notes = $teller->notes;
        $this->employee_id = $teller->employee_id;
        $this->editTellerInfomation = true;
    }

    public function deleteTeller($id)
    {
        $this->selectedTellerId = $id;
        $this->deleteTellerModal = true;
    }

    public function exportToExcel()
    {
        // Implementation for Excel export
        session()->flash('message', 'Export functionality will be implemented soon.');
    }

    public function mount()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Teller::with(['employee', 'branch']);

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('teller_name', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%')
                  ->orWhereHas('employee', function ($emp) {
                      $emp->where('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Apply status filter
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // Apply branch filter
        if (!empty($this->branchFilter)) {
            $query->where('branch_id', $this->branchFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $tellers = $query->paginate($this->perPage);
        $branches = BranchesModel::all();
        $employees = Employee::whereNotIn('id', Teller::whereNotNull('employee_id')->pluck('employee_id'))->get();

        return view('livewire.teller-management.teller-register', [
            'tellers' => $tellers,
            'branches' => $branches,
            'employees' => $employees
        ]);
    }


    public function editTellerModal()
    {
        if ($this->editTellerInfomation == false) {
            $this->editTellerInfomation = true;
        } else if ($this->editTellerInfomation == true) {
            $this->editTellerInfomation = false;
        }
    }


    public function editTellerAction()
    {

        $tellers = DB::table('tellers')->where('id', '=', session()->get('editTellerId'))->first();
        $this->employee_id = $tellers->employee_id;
        $this->branch_id = $tellers->branch_id;
        $this->max_amount = $tellers->max_amount;
        $this->registered_by_id = $tellers->registered_by_id;
        $this->account_id = $tellers->account_id;
        $this->editTellerModal();

    }


    public function updateTeller()
    {
        $this->validate();
        
        try {
            $id = $this->selectedTellerId;

            // Check if teller has balance
            $teller_account_id = Teller::where('id', $id)->value('account_id');
            if ($teller_account_id && AccountsModel::where('id', $teller_account_id)->value('balance') > 0) {
                session()->flash('error', 'Cannot edit teller with existing balance. Please close the till first.');
                return;
            }

            $edit_package = json_encode([
                'branch_id' => $this->branch_id,
                'max_amount' => $this->max_amount,
                'teller_name' => $this->teller_name,
                'notes' => $this->notes
            ]);

            // Send for approval
            $approval = new approvals();
            $approval->sendApproval($id, 'editTeller', auth()->user()->name . ' has edited teller', 'has to edit teller', '102', $edit_package);

            session()->flash('message', 'Teller update sent for approval');
            $this->editTellerInfomation = false;
            $this->resetTeller();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update teller: ' . $e->getMessage());
        }
    }


    public function deleteModal()
    {
        if ($this->deleteTellerModal == false) {
            $this->deleteTellerModal = true;
        } else if ($this->deleteTellerModal == true) {
            $this->deleteTellerModal = false;
        }
    }

    public function deletetellerAction()
    {
        $this->validate(['password' => 'required', 'permission' => 'required']);
        
        try {
            $id = $this->selectedTellerId;
            $status = $this->permission;

            // Verify password
            if (!Hash::check($this->password, auth()->user()->password)) {
                session()->flash('error', 'Invalid password');
                return;
            }

            // Check if teller has balance
            $teller_account_id = Teller::where('id', $id)->value('account_id');
            if ($teller_account_id && AccountsModel::where('id', $teller_account_id)->value('balance') > 0) {
                session()->flash('error', 'Cannot delete teller with existing balance. Please close the till first.');
                return;
            }

            // Check if status is already set
            if (DB::table('tellers')->where('id', $id)->value('status') == $status) {
                session()->flash('error', 'Cannot set status to ' . $status . ' twice');
                return;
            }

            // Update status
            DB::table('tellers')->where('id', $id)->update(['status' => $status]);

            // Send for approval
            $edit_package = json_encode($status);
            $approval = new approvals();
            $approval->sendApproval($id, 'removeTeller', auth()->user()->name . ' has deleted Teller', 'has deleted teller', '102', $edit_package);

            session()->flash('message', 'Teller deletion sent for approval');
            $this->deleteTellerModal = false;
            $this->resetTeller();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete teller: ' . $e->getMessage());
        }
    }


    public function assignNewTeller($id)
    {
        session()->put('tellerId', $id);
        if ($this->assignNewTellerModal == false) {
            $this->assignNewTellerModal = true;
        } else if ($this->assignNewTellerModal == true) {
            $this->assignNewTellerModal = false;
        }
    }


    public function assignEmployeeToTellerPosition()
    {
        try {
            // Check if employee is already assigned to another teller
            $existingTeller = Teller::where('employee_id', $this->employee_details)->first();
            if ($existingTeller) {
                session()->flash('error', 'Employee is already assigned to another teller position');
                return;
            }

            // Check if teller can be assigned
            $teller = Teller::find(session()->get('tellerId'));
            if (!$teller) {
                session()->flash('error', 'Teller not found');
                return;
            }

            if ($teller->account_id == null || $teller->employee_id != null) {
                session()->flash('error', 'Cannot assign employee to this teller position at this time');
                return;
            }

            // Send for approval
            $edit_package = json_encode(['employee_id' => $this->employee_details]);
            Teller::where('id', session()->get('tellerId'))->update(['progress_status' => 'PENDING']);
            
            $approvals = new approvals();
            $approvals->sendApproval(session()->get('tellerId'), 'assignTeller', auth()->user()->name . ' has assigned new teller', 'new teller has been assigned', '102', $edit_package);
            
            session()->flash('message', 'Employee assignment sent for approval');
            $this->assignNewTellerModal = false;
            $this->resetTeller();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to assign employee: ' . $e->getMessage());
        }
    }
}
