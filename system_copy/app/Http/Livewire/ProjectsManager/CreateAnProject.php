<?php

namespace App\Http\Livewire\ProjectsManager;

use App\Models\FilesModal;
use App\Models\Project;
use App\Models\StageAndStatus;
use App\Models\TypesOfExpenditure;
use App\Models\Vault;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Collection;



class CreateAnProject extends Component
{
    public $term = "";
    public $showAddUser = false;
    public $memberStatus = 'All';
    public $numberOfProducts;
    public $products;
    public $item;
    public $category;
    public $account_name;
    public $notes;
    public $account_number;
    public $createNewAccount;



    // new
    public $sub_category_name;
    public $account_code;
    //
    private $typeOfExpenditures;
    public $Expenditure;
    public $accounts;
    public $theVault;
    public $excelFile;
    public $files = [];
    public array $descriptions = [];
    public $tempFiles = [];
    /**
     * @var true
     */
    public bool $filesListener_ =false;
    public $pageOnFocus =2;

    public $tender_no;
    public $procuring_entity;
    public $supplier_name;
    public $award_date;
    public $award_amount;
    public $lot_name;
    public $project_type;
    public $expected_end_date;
    public $project_summary;
    protected $rules = [
        'project_type' => 'required',
        'tender_no' => 'required',
        'procuring_entity' => 'required',
        'supplier_name' => 'required',
        'award_date' => 'nullable|string',
        'award_amount' => 'required|numeric',
        'lot_name' => 'nullable|string',
        'expected_end_date' => 'nullable|string',
        'project_summary' => 'required|string'
    ];
    public function viewChanger($page): void
    {
        //dd($page);
        $this->pageOnFocus = $page;

    }
    public function store()
    {
        $validatedData = $this->validate();
        $project = Project::create([
            'project_type' => $this->project_type,
            'tender_no' => $this->tender_no,
            'procuring_entity' => $this->procuring_entity,
            'supplier_name' => $this->supplier_name,
            'award_date' => $this->award_date,
            'award_amount' => $this->award_amount,
            'lot_name' => $this->lot_name,
            'expected_end_date' => $this->expected_end_date,
            'project_summary' => $this->project_summary,
            'status' => 'ACTIVE',
        ]);
        session()->flash('message', 'Your project has been created');
        session()->flash('alert-class', 'alert-success');
        $this->resetAll();
    }
    public function resetAll()
    {
        $this->project_type = null;
        $this->tender_no = null;
        $this->procuring_entity = null;
        $this->supplier_name = null;
        $this->award_date = null;
        $this->award_amount = null;
        $this->lot_name = null;
        $this->expected_end_date = null;
        $this->project_summary = null;
    }


    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        return view('livewire.projects-manager.create-an-project');
    }


    public function save()
    {


        DB::beginTransaction();

        try{
        $category_code='';

        $category_code=DB::table($this->sub_category_name)->value('category_code');

        $category_code=substr($category_code,0,2);

        $plug_category_code=DB::table($this->sub_category_name)->pluck('sub_category_code');

        foreach ($plug_category_code as $code){
            $category_code=$category_code.''.rand(00,99);
            if($code==$category_code){
                continue;
            }
            else{
                break;
            }
        }

        $tables=DB::table('GL_accounts')->where('id',$this->category)->get();
       foreach($tables as $table_name){
             $table_name=$table_name->account_name;
           }

        $table_name=strtolower($table_name);
        $table_name=str_replace($table_name,' ','_');

        $GN_account_code=DB::table('GL_accounts')->where('id',$this->category)->value('account_code');
        $account_id=DB::table('GL_accounts')->where('id',$this->category)->value('id');



        $id = DB::table($this->sub_category_name)->insert(['expenses_account_code' => $GN_account_code, 'account_code' => $this->account_code, 'name' => $this->account_name]);


        $id = AccountsModel::create([
            'account_use' => 'internal',
            'institution_number' => auth()->user()->institution_id,
            'branch_number' => auth()->user()->branch,
            'member_number' => '0000',
            'major_category_code' => $GN_account_code,
            'category_code'=>$this->category_code,
            'sub_category_code' => $this->account_code,
            'account_name' => $this->account_name,
            'account_number' => str_pad(auth()->user()->institution_id, 2, 0, STR_PAD_LEFT) . '' . str_pad(auth()->user()->branch, 2, 0, STR_PAD_LEFT) . '0000'.$this->account_code,
            'notes' => "  ",

        ])->id;


//        approvals::create([
//            'institution' => $institution,
//            'process_name' => 'createAccount',
//            'process_description' => 'has added a new account',
//            'approval_process_description' => 'has approved a new account',
//            'process_code' => '04',
//            'process_id' => $id,
//            'process_status' => 'Pending',
//            'user_id'  => Auth::user()->id,
//            'team_id'  => ''
//        ]);

        DB::commit();
        $this->resetData();
        Session::flash('message', 'Account has been successfully saved!');
        Session::flash('alert-class', 'alert-success');
        $this->createNewAccount = false;
    }
    catch(\Exception $e){

        Session::flash('message', 'Account has been successfully saved!');
        Session::flash('alert-class', 'alert-success');
        DB::rollBack();

    }
    }
}
