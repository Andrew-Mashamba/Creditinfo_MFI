<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CollateralManager;
use App\Models\CollateralModel;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class CollateralData extends Component
{
    use WithFileUploads;
    public $fileUploads = [];
    public $collateral_category;
    public $collateral_type;
    public $description;
    public $CollateralID;
    public $ClientID;
    public $LoanID;
    public $type_of_owner;
    public $relationship;
    public $collateral_owner_full_name;
    public $collateral_owner_nida;
    public $collateral_owner_contact_number;
    public $collateral_owner_residential_address;
    public $collateral_owner_spouse_full_name;
    public $collateral_owner_spouse_nida;
    public $collateral_owner_spouse_contact_number;
    public $collateral_owner_spouse_residential_address;
    public $company_registered_name;
    public $business_licence_number;
    public $TIN;
    public $director_nida;
    public $director_contact;
    public $director_address;
    public $business_address;
    public $collateral_value;
    public $date_of_valuation;
    public $valuation_method_used;
    public $name_of_valuer;
    public $policy_number;
    public $company_name;
    public $coverage_details;
    public $expiration_date;
    public $disbursement_date;
    public $tenure;
    public $interest;
    public $loan_amount;
    public $physical_condition;
    public $current_status;
    public $region;
    public $district;
    public $ward;
    public $postal_code;
    public $ExpireDates;
    public $address;
    public $building_number;
    public $user;
    public $users;
    public $search = '';
    public $isOpen = false; // Add this property
    public $isOpena;
    public $isOpen2 = false; // Add this property
    public $isOpen2a = false; // Add this property

    public $usera;
    public $clients;
    public $clients2;
    public $clientName;
    public $clientNamea;
    public $search2;
    public $isOpen3;


    protected $listeners=['registerGuarantee'=>'saveCollateral'];

    public function boot()
    {
        $this->CollateralID = $this->generateRandomId();



        $this->ClientID=DB::table('loans')->where('id',session('currentloanID'))->value('client_number');

    }

    public function toggleDropdown()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function closeDropdown($id)
    {
        //dd($id);
        $this->user = $id;
        $this->LoanID =  $id;
        $member_number = DB::table('loans')->where('loan_id',$this->user)->value('member_number');
        $this->ClientID = $member_number;
        $this->clientName = DB::table('members')->where('member_number',$member_number)->value('first_name').' '.
            DB::table('members')->where('member_number',$member_number)->value('middle_name').' '.
            DB::table('members')->where('member_number',$member_number)->value('last_name')
        ;
        $this->isOpen = false;
    }

    public function generateRandomId() {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomLetters = $letters[rand(0, 25)] . $letters[rand(0, 25)]; // Two random capital letters
        $randomDigits = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT); // Six random digits
        return $randomLetters . $randomDigits;
    }


        protected $rules = [
        'collateral_category' => 'required',
        'collateral_type' => 'required',
        'description' => 'required',
        'CollateralID' => 'nullable|required',
        'ClientID' => 'nullable|required',
        'LoanID' => 'nullable|required',
        'type_of_owner' => 'nullable|required',
        'relationship' => 'nullable',
        'collateral_owner_full_name' => 'nullable|required',
        'collateral_owner_nida' => 'nullable|required',
        'collateral_owner_contact_number' => 'nullable|required',
        'collateral_owner_residential_address' => 'nullable|required',
        'collateral_owner_spouse_full_name' => 'nullable',
        'collateral_owner_spouse_nida' => 'nullable',
        'collateral_owner_spouse_contact_number' => 'nullable',
        'collateral_owner_spouse_residential_address' => 'nullable',
        'company_registered_name' => 'nullable',
        'business_licence_number' => 'nullable',
        'TIN' => 'nullable',
        'director_nida' => 'nullable',
        'director_contact' => 'nullable',
        'director_address' => 'nullable',
        'business_address' => 'nullable',
        'collateral_value' => 'nullable',
        'date_of_valuation' => 'nullable',
        'valuation_method_used' => 'nullable',
        'name_of_valuer' => 'nullable',
        'policy_number' => 'nullable',
        'company_name' => 'nullable',
        'coverage_details' => 'nullable',
        'expiration_date' => 'nullable',
        'disbursement_date' => 'nullable',
        'tenure' => 'nullable',
        'interest' => 'nullable',
        'loan_amount' => 'nullable',
        'physical_condition' => 'nullable',
        'current_status' => 'nullable',
        'region' => 'nullable',
        'district' => 'nullable',
        'ward' => 'nullable',
        'postal_code' => 'nullable',
        ];

    public function saveCollateral()
    {
        try{

         //  $this->validate();

        $collateral = new CollateralModel();
        $collateral->collateral_category = $this->collateral_category;
        $collateral->collateral_type = $this->collateral_type;
        $collateral->description = $this->description;
        $collateral->collateral_id = $this->CollateralID;
        $collateral->main_collateral_type=session('main_collateral_type');
        $collateral->member_number = $this->ClientID;
        $collateral->account_id= "00";
        $collateral->client_id=$this->ClientID;
        $collateral->loan_id = $this->LoanID;
        $collateral->type_of_owner = $this->type_of_owner;
        $collateral->relationship = $this->relationship;
        $collateral->collateral_owner_full_name = $this->collateral_owner_full_name;
        $collateral->collateral_owner_nida = $this->collateral_owner_nida;
        $collateral->collateral_owner_contact_number = $this->collateral_owner_contact_number;
        $collateral->collateral_owner_residential_address = $this->collateral_owner_residential_address;
        $collateral->collateral_owner_spouse_full_name = $this->collateral_owner_spouse_full_name;
        $collateral->collateral_owner_spouse_nida = $this->collateral_owner_spouse_nida;
        $collateral->collateral_owner_spouse_contact_number = $this->collateral_owner_spouse_contact_number;
        $collateral->collateral_owner_spouse_residential_address = $this->collateral_owner_spouse_residential_address;
        $collateral->company_registered_name = $this->company_registered_name;
        $collateral->business_licence_number = $this->business_licence_number;
        $collateral->tin = $this->TIN;
        $collateral->director_nida = $this->director_nida;
        $collateral->director_contact = $this->director_contact;
        $collateral->director_address = $this->director_address;
        $collateral->business_address = $this->business_address;
        $collateral->collateral_value = $this->collateral_value;
        $collateral->date_of_valuation = $this->date_of_valuation;
        $collateral->valuation_method_used = $this->valuation_method_used;
        $collateral->name_of_valuer = $this->name_of_valuer;
        $collateral->policy_number = $this->policy_number;
        $collateral->company_name = $this->company_name;
        $collateral->coverage_details = $this->coverage_details;
        $collateral->expiration_date = $this->expiration_date;
        $collateral->disbursement_date = $this->disbursement_date;
        $collateral->tenure = $this->tenure;
        $collateral->interest = $this->interest;
        $collateral->loan_amount = $this->loan_amount;
        $collateral->physical_condition = $this->physical_condition;
        $collateral->approval_status = "PENDING";
        $collateral->region = $this->region;
        $collateral->district = $this->district;
        $collateral->ward = $this->ward;
        $collateral->postal_code = $this->postal_code;
        $collateral->address = $this->address;
        $collateral->building_number = $this->building_number;
        $collateral->current_status = 'un_perfected';

        $collateral->save();




        foreach ($this->fileUploads as $documentId => $file) {
            // If $file is an array (multiple files uploaded), loop through each file
            if (is_array($file)) {
                foreach ($file as $uploadedFile) {
                    $path = '';
                    if ($uploadedFile) {
                        $path = $uploadedFile->store('images', 'public');
                    }
                    // Insert the file path into the database
                    $Dates = null;
                    if(isset($this->ExpireDates[$documentId])){
                        $Dates = $this->ExpireDates[$documentId];
                    }
                    DB::table('document_records')->insert([
                        'document_id' => $documentId,
                        'file_path' => $path,
                        'expiration_date' => $Dates,
                        'collateral_id' => $this->CollateralID,
                    ]);
                }
            } else {
                // If $file is not an array, it should be a single UploadedFile instance
                $path = '';
                if ($file) {
                    $path = $file->store('images', 'public');
                }
                // Insert the file path into the database
                $Dates = null;
                if(isset($this->ExpireDates[$documentId])){
                    $Dates = $this->ExpireDates[$documentId];
                }
                DB::table('document_records')->insert([
                    'document_id' => $documentId,
                    'file_path' => $path,
                    'expiration_date' => $Dates,
                    'collateral_id' => $this->CollateralID,
                ]);
            }
        }



            session()->put('hasBusinessInformation',"YES");
        session()->flash('message_feedback', 'Collateral Record created successfully.');

        Log::info('An error occurred: '  );

        $this->reset(); // Clear form fields after successful save


    }catch(\Exception $e){
        session()->flash('message_feedback_fail', 'something went wrong on saving physical collaterals');

        Log::error('An error occurred: ' . $e->getMessage());

    }


    }



    public function render()
    {

        // if (trim($this->search == '')) {
        //     $this->clients = DB::table('loans')
        //         ->join('members', 'loans.member_number', '=', 'members.member_number')
        //         ->where('loans.status', 'ONPROGRESS')
        //         ->select('loans.*', 'members.*') // Select fields from both tables as needed
        //         ->get();
        // } else {
        //     $this->clients = DB::table('loans')
        //         ->join('members', 'loans.member_number', '=', 'members.member_number')
        //         ->where('loans.status', 'ONPROGRESS')
        //         ->where(function ($query) {
        //             $query->where('loans.loan_id', 'like', "%$this->search%")
        //                 ->orWhere('loans.loan_account_number', 'like', "%$this->search%")
        //                 ->orWhere('loans.loan_sub_product', 'like', "%$this->search%")
        //                 ->orWhere('loans.client_number', 'like', "%$this->search%")
        //                 ->orWhere('loans.guarantor', 'like', "%$this->search%")
        //                 //->orWhere('loans.institution_id', 'like', "%$this->search%")
        //                 ->orWhere('loans.branch_id', 'like', "%$this->search%")
        //                 ->orWhere('loans.business_name', 'like', "%$this->search%")
        //                 ->orWhere('loans.business_category', 'like', "%$this->search%")
        //                 ->orWhere('loans.business_type', 'like', "%$this->search%")
        //                 ->orWhere('loans.business_licence_number', 'like', "%$this->search%")
        //                 ->orWhere('loans.business_tin_number', 'like', "%$this->search%")
        //                 ->orWhere('loans.collateral_location', 'like', "%$this->search%")
        //                 ->orWhere('loans.collateral_description', 'like', "%$this->search%")
        //                 ->orWhere('loans.collateral_type', 'like', "%$this->search%")
        //                 ->orWhere('loans.interest_method', 'like', "%$this->search%")
        //                 ->orWhere('loans.bank_account_number', 'like', "%$this->search%")
        //                 //->orWhere('loans.bank', 'like', "%$this->search%")
        //                 ->orWhere('loans.LoanPhoneNo', 'like', "%$this->search%")
        //                 ->orWhere('loans.status', 'like', "%$this->search%")
        //                 ->orWhere('loans.loan_status', 'like', "%$this->search%")
        //                 ->orWhere('loans.restructure_loanId', 'like', "%$this->search%")
        //                 ->orWhere('loans.heath', 'like', "%$this->search%")
        //                 ->orWhere('loans.created_at', 'like', "%$this->search%")
        //                 ->orWhere('loans.updated_at', 'like', "%$this->search%")
        //                 ->orWhere('loans.phone_number', 'like', "%$this->search%")
        //                 ->orWhere('loans.pay_method', 'like', "%$this->search%")
        //                 ->orWhere('loans.supervisor_name', 'like', "%$this->search%")
        //                 ->orWhere('loans.relationship', 'like', "%$this->search%")
        //                 ->orWhere('loans.loan_type', 'like', "%$this->search%")
        //                 ->orWhere('loans.member_number', 'like', "%$this->search%")
        //                 ->orWhere('loans.business_member_id', 'like', "%$this->search%")
        //                 ->orWhere('loans.member_id', 'like', "%$this->search%")
        //                 ->orWhere('members.first_name', 'like', "%$this->search%")
        //                 ->orWhere('members.middle_name', 'like', "%$this->search%")
        //                 ->orWhere('members.last_name', 'like', "%$this->search%")
        //                 ->orWhere('members.account_number', 'like', "%$this->search%")
        //                 ->orWhere('members.member_savings_account', 'like', "%$this->search%")
        //                 ->orWhere('members.institution_id', 'like', "%$this->search%")
        //                 ->orWhere('members.branch', 'like', "%$this->search%")
        //                 ->orWhere('members.registering_officer', 'like', "%$this->search%")
        //                 ->orWhere('members.loan_officer', 'like', "%$this->search%")
        //                 ->orWhere('members.approving_officer', 'like', "%$this->search%")
        //                 ->orWhere('members.membership_type', 'like', "%$this->search%")
        //                 ->orWhere('members.incorporation_number', 'like', "%$this->search%")
        //                 ->orWhere('members.phone_number', 'like', "%$this->search%")
        //                 ->orWhere('members.mobile_phone_number', 'like', "%$this->search%")
        //                 ->orWhere('members.email', 'like', "%$this->search%")
        //                 ->orWhere('members.place_of_birth', 'like', "%$this->search%")
        //                 ->orWhere('members.marital_status', 'like', "%$this->search%")
        //                 ->orWhere('members.registration_date', 'like', "%$this->search%")
        //                 ->orWhere('members.address', 'like', "%$this->search%")
        //                 ->orWhere('members.notes', 'like', "%$this->search%")
        //                 ->orWhere('members.profile_photo_path', 'like', "%$this->search%")
        //                 ->orWhere('members.branch_id', 'like', "%$this->search%")
        //                 ->orWhere('members.client_status', 'like', "%$this->search%")
        //                 ->orWhere('members.next_of_kin_name', 'like', "%$this->search%")
        //                 ->orWhere('members.next_of_kin_phone', 'like', "%$this->search%")
        //                 ->orWhere('members.tin_number', 'like', "%$this->search%")
        //                 ->orWhere('members.nida_number', 'like', "%$this->search%")
        //                 ->orWhere('members.ref_number', 'like', "%$this->search%")
        //                 ->orWhere('members.shares_ref_number', 'like', "%$this->search%")
        //                 ->orWhere('members.created_at', 'like', "%$this->search%")
        //                 ->orWhere('members.updated_at', 'like', "%$this->search%")
        //                 ->orWhere('members.nationarity', 'like', "%$this->search%")
        //                 ->orWhere('members.member_exit_document', 'like', "%$this->search%")
        //                 ->orWhere('members.end_membership_description', 'like', "%$this->search%")
        //                 ->orWhere('members.full_name', 'like', "%$this->search%")
        //                 ->orWhere('members.national_id', 'like', "%$this->search%")
        //                 ->orWhere('members.client_id', 'like', "%$this->search%")
        //                 ->orWhere('members.customer_code', 'like', "%$this->search%")
        //                 ->orWhere('members.present_surname', 'like', "%$this->search%")
        //                 ->orWhere('members.birth_surname', 'like', "%$this->search%")
        //                 ->orWhere('members.number_of_spouse', 'like', "%$this->search%")
        //                 ->orWhere('members.number_of_children', 'like', "%$this->search%")
        //                 ->orWhere('members.classification_of_individual', 'like', "%$this->search%")
        //                 ->orWhere('members.gender', 'like', "%$this->search%")
        //                 ->orWhere('members.date_of_birth', 'like', "%$this->search%")
        //                 ->orWhere('members.country_of_birth', 'like', "%$this->search%")
        //                 ->orWhere('members.fate_status', 'like', "%$this->search%")
        //                 ->orWhere('members.social_status', 'like', "%$this->search%")
        //                 ->orWhere('members.residency', 'like', "%$this->search%")
        //                 ->orWhere('members.citizenship', 'like', "%$this->search%")
        //                 ->orWhere('members.nationality', 'like', "%$this->search%")
        //                 ->orWhere('members.employment', 'like', "%$this->search%")
        //                 ->orWhere('members.employer_name', 'like', "%$this->search%")
        //                 ->orWhere('members.education', 'like', "%$this->search%")
        //                 ->orWhere('members.business_name', 'like', "%$this->search%")
        //                 ->orWhere('members.income_available', 'like', "%$this->search%")
        //                 ->orWhere('members.monthly_expenses', 'like', "%$this->search%")
        //                 ->orWhere('members.negative_status_of_individual', 'like', "%$this->search%")
        //                 ->orWhere('members.tax_identification_number', 'like', "%$this->search%")
        //                 ->orWhere('members.passport_number', 'like', "%$this->search%");
        //         })
        //         ->select('loans.*', 'members.*') // Select fields from both tables as needed
        //         ->get();
        // }


        $this->LoanID=session('currentloanID');

        return view('livewire.loans.collateral-data');

    }


}
