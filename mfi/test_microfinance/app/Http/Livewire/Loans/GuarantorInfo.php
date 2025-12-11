<?php

namespace App\Http\Livewire\Loans;

use App\Models\ClientsModel;
use App\Models\LoansModel;
use App\Models\loan_images;
use App\Models\CollateralModel;
use App\Models\Guarantor;
use App\Models\LoanImage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GuarantorInfo extends Component
{
    use WithFileUploads;

    public $tab_id = '2';
    public $title = 'Loans list';

    public $name;
    public $dob;
    public $nationality;
    public $address;
    public $phone;
    public $email;
    public $id_number;
    public $employment_status;
    public $employer_details;
    public $income;
    public $assets;
    public $liabilities;
    public $credit_score;
    public $guaranteeType;
    public $type;
    public $photo;
    public $photo2;
    public $profile_photo_path;
    public $idx;
    public $loan_id;
    public $member_number;
    //public $collaterals = [];
    public $commonId;

    public $type_of_owner;
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
    public $collateral_photo;
    public $collateral_inspection_certificate;
    public $insurance_company_name;
    public $insurance_validity_period;
    public $company_contact_number;
    public $company_address;
    public $collaterals = [];
    public $photos = [];

    public $collateral_type;
    public $collateral_description;
    public $collateral_location;

    protected $rules = [
        'name' => 'required|string',
        'dob' => 'required|date',
        'nationality' => 'required|string',
        'address' => 'required|string',
        'phone' => 'required|string',
        'email' => 'required|email',
        'id_number' => 'required|string',
        'employment_status' => 'required|string',
        'employer_details' => 'required|string',
        'income' => 'required|numeric',
        'assets' => 'required|numeric',
        'liabilities' => 'required|numeric',
        'credit_score' => 'required|numeric',
        'guaranteeType' => 'required|string',
        'type' => 'required|string',
        'collaterals.*.type' => 'required|string',
        'collaterals.*.document' => 'nullable|file|max:10240',
        'collateral_type' => 'required|string|max:255',
        'collateral_value' => 'required|numeric|min:0',
        'collateral_description' => 'required|string|max:1000',
        'collateral_location' => 'required|string|max:255',
    ];

    function setAccount($account){
        dd($account);
    }
    public function boot()
    {
        $this->tab_id = null;

        $loan = LoansModel::find(Session::get('currentloanID'));

        if ($loan) {
            $this->idx = $loan->id;
            $this->loan_id = $loan->loan_id;
            $this->member_number = $loan->member_number;
        }

        // Initialize collaterals with at least one empty item
        //$this->collaterals = null;
        $this->commonId = Str::random(6);

        //dd($this->loan_id);

        $this->photos = [
            [
                'photo2' => null,
            ],
            // Add more items as needed
        ];
    }

    public function setView($selected)
    {
        $this->tab_id = $selected;
    }

    public function save($index)
    {
        // Ensure the photo exists at the given index
        if (isset($this->photos[$index]['photo2']) && $this->photos[$index]['photo2'] instanceof \Livewire\TemporaryUploadedFile) {
            $filePath = $this->photos[$index]['photo2']->store('collaterals', 'public');

            $loanId = LoansModel::where('id', Session::get('currentloanID'))->value('loan_id');

            loan_images::create([
                'loan_id' => $loanId,
                'url' => $filePath,
                'category' => $this->commonId,
            ]);

            session()->flash('message', 'Image uploaded successfully.');
            // Reset the photo2 attribute after successful upload
            $this->photos[$index]['photo2'] = null;
        } else {
            session()->flash('error', 'Image upload failed.');
        }
    }

    public function close($imageId)
    {
        $image = loan_images::findOrFail($imageId);
        $image->delete();

        session()->flash('message', 'Image deleted successfully.');
    }

    public function saveImage()
    {
        $loanId = LoansModel::where('id', Session::get('currentloanID'))->value('loan_id');
        $path = $this->photo2->store('photos', 'public');
        $imageUrl = 'storage/' . $path;

        loan_images::create([
            'loan_id' => $loanId,
            'category' => 'collateral',
            'url' => $imageUrl,
        ]);

        $this->photo = null;
    }

    public function addCollateral()
    {
        $this->collaterals[] = [
            'type' => '',
            'property_type' => '',
            'vehicle_type' => '',
            'location' => '',
            'evaluation_value' => '',
            'expiration_date' => '',
            'description' => '',
            'document' => null,
            'type_of_owner' => '',
            'collateral_owner_full_name' => '',
            'collateral_owner_nida' => '',
            'collateral_owner_contact_number' => '',
            'collateral_owner_residential_address' => '',
            'collateral_owner_spouse_full_name' => '',
            'collateral_owner_spouse_nida' => '',
            'collateral_owner_spouse_contact_number' => '',
            'collateral_owner_spouse_residential_address' => '',
            'company_registered_name' => '',
            'business_licence_number' => '',
            'TIN' => '',
            'director_nida' => '',
            'director_contact' => '',
            'director_address' => '',
            'business_address' => '',
            'collateral_value' => '',
            'date_of_valuation' => '',
            'valuation_method_used' => '',
            'name_of_valuer' => '',
            'policy_number' => '',
            'company_name' => '',
            'coverage_details' => '',
            'expiration_date' => '',
        ];
    }

    public function removeCollateral($index)
    {
        unset($this->collaterals[$index]);
        $this->collaterals = array_values($this->collaterals);
    }

    public function saveGuarantor()
    {
        //$this->validate();

        $filePath = null;
        if (isset($this->photo) && $this->photo instanceof \Livewire\TemporaryUploadedFile) {
            $filePath = $this->photo->store('guarantor', 'public');
            $this->photo = null;
        }

        // Instead of creating a Guarantor record, we'll store the guarantor info in the loan
        $loan = LoansModel::find(Session::get('currentloanID'));
        if ($loan) {
            // Store guarantor information as JSON in the guarantor field
            $guarantorData = [
                'name' => $this->name,
                'dob' => $this->dob,
                'nationality' => $this->nationality,
                'address' => $this->address,
                'phone' => $this->phone,
                'email' => $this->email,
                'id_number' => $this->id_number,
                'employment_status' => $this->employment_status,
                'employer_details' => $this->employer_details,
                'income' => $this->income,
                'assets' => $this->assets,
                'liabilities' => $this->liabilities,
                'credit_score' => $this->credit_score,
                'guaranteeType' => $this->guaranteeType,
                'type' => $this->type,
                'image' => $filePath,
                'common_id' => $this->commonId ?? null,
            ];

            $loan->update([
                'guarantor' => json_encode($guarantorData)
            ]);

            // Attach collaterals to the loan
            foreach ($this->collaterals as $index => $collateralData) {
                $collateral = new CollateralModel([
                    'loan_id' => $loan->id,
                    'type' => $collateralData['type'] ?? null,
                    'property_type' => $collateralData['property_type'] ?? null,
                    'vehicle_type' => $collateralData['vehicle_type'] ?? null,
                    'location' => $collateralData['location'] ?? null,
                    'evaluation_value' => $collateralData['evaluation_value'] ?? null,
                    'expiration_date' => $collateralData['expiration_date'] ?? null,
                    'description' => $collateralData['description'] ?? null,
                    'document' => $collateralData['document'] ?? null,
                    'common_id' => $this->commonId ?? null,
                    'type_of_owner' => $collateralData['type_of_owner'] ?? null,
                    'collateral_owner_full_name' => $collateralData['collateral_owner_full_name'] ?? null,
                    'collateral_owner_nida' => $collateralData['collateral_owner_nida'] ?? null,
                    'collateral_owner_contact_number' => $collateralData['collateral_owner_contact_number'] ?? null,
                    'collateral_owner_residential_address' => $collateralData['collateral_owner_residential_address'] ?? null,
                    'collateral_owner_spouse_full_name' => $collateralData['collateral_owner_spouse_full_name'] ?? null,
                    'collateral_owner_spouse_nida' => $collateralData['collateral_owner_spouse_nida'] ?? null,
                    'collateral_owner_spouse_contact_number' => $collateralData['collateral_owner_spouse_contact_number'] ?? null,
                    'collateral_owner_spouse_residential_address' => $collateralData['collateral_owner_spouse_residential_address'] ?? null,
                    'company_registered_name' => $collateralData['company_registered_name'] ?? null,
                    'business_licence_number' => $collateralData['business_licence_number'] ?? null,
                    'TIN' => $collateralData['TIN'] ?? null,
                    'director_nida' => $collateralData['director_nida'] ?? null,
                    'director_contact' => $collateralData['director_contact'] ?? null,
                    'director_address' => $collateralData['director_address'] ?? null,
                    'business_address' => $collateralData['business_address'] ?? null,
                    'collateral_value' => $collateralData['collateral_value'] ?? null,
                    'date_of_valuation' => $collateralData['date_of_valuation'] ?? null,
                    'valuation_method_used' => $collateralData['valuation_method_used'] ?? null,
                    'name_of_valuer' => $collateralData['name_of_valuer'] ?? null,
                    'policy_number' => $collateralData['policy_number'] ?? null,
                    'company_name' => $collateralData['company_name'] ?? null,
                    'coverage_details' => $collateralData['coverage_details'] ?? null,
                    'insurance_expiration_date' => $collateralData['expiration_date'] ?? null,
                ]);

                if (isset($collateralData['document']) && !is_string($collateralData['document'])) {
                    $path = $collateralData['document']->store('collaterals', 'public');
                    $collateral->document_path = $path;
                }

                $collateral->save();
            }
        }

        // Clear form fields and show success message
        $this->reset();
        session()->flash('message', 'Guarantor registered successfully!');
    }

    public function registerCollaterals()
    {
        try {
            $this->validate([
                'collateral_type' => 'required|string|max:255',
                'collateral_value' => 'required|numeric|min:0',
                'collateral_description' => 'required|string|max:1000',
                'collateral_location' => 'required|string|max:255',
            ], [
                'collateral_type.required' => 'Please select a collateral type.',
                'collateral_value.required' => 'Please enter the collateral value.',
                'collateral_value.numeric' => 'Collateral value must be a number.',
                'collateral_value.min' => 'Collateral value must be greater than 0.',
                'collateral_description.required' => 'Please provide a description of the collateral.',
                'collateral_location.required' => 'Please specify the collateral location.',
            ]);

            $loanId = session('currentloanID');
            if (!$loanId) {
                throw new \Exception('Loan not found. Please select a valid loan.');
            }

            // Create collateral record
            $collateral = DB::table('collaterals')->insertGetId([
                'loan_id' => $loanId,
                'collateral_type' => $this->collateral_type,
                'collateral_value' => $this->collateral_value,
                'collateral_description' => $this->collateral_description,
                'collateral_location' => $this->collateral_location,
                'institution_id' => auth()->user()->institution_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Reset form
            $this->resetCollateralForm();
            
            // Reload collaterals
            $this->loadCollaterals();
            
            $this->showSuccessMessage("Collateral registered successfully!");
            
            // Auto-save tab state and mark as completed
            $this->saveGuarantorTabState();
            
            // Emit event to parent component
            $this->emit('tabCompleted', 'guarantor');
            
        } catch (\Exception $e) {
            Log::error('Error registering collateral: ' . $e->getMessage());
            $this->showErrorMessage('Error registering collateral: ' . $e->getMessage());
        }
    }

    /**
     * Save guarantor tab state using the tab state service
     */
    protected function saveGuarantorTabState()
    {
        try {
            $loanId = session('currentloanID');
            if (!$loanId) {
                return;
            }

            // Get the tab state service
            $tabStateService = app(\App\Services\LoanTabStateService::class);
            
            // Save guarantor tab state
            $tabStateService->saveTabState($loanId, 'guarantor', [
                'collateral_count' => $this->getCollateralCount($loanId),
                'total_collateral_value' => $this->getTotalCollateralValue($loanId),
                'last_registered' => now()
            ]);

            // Update completion status
            $completedTabs = $tabStateService->getCompletedTabs($loanId);
            $tabStateService->saveTabCompletionStatus($loanId, $completedTabs);

        } catch (\Exception $e) {
            Log::error('Error saving guarantor tab state: ' . $e->getMessage());
        }
    }

    /**
     * Get collateral count for the loan
     */
    protected function getCollateralCount($loanId)
    {
        return DB::table('collaterals')
            ->where('loan_id', $loanId)
            ->count();
    }

    /**
     * Get total collateral value for the loan
     */
    protected function getTotalCollateralValue($loanId)
    {
        return DB::table('collaterals')
            ->where('loan_id', $loanId)
            ->sum('collateral_value');
    }

    /**
     * Reset collateral form fields
     */
    public function resetCollateralForm()
    {
        $this->collateral_type = '';
        $this->collateral_value = '';
        $this->collateral_description = '';
        $this->collateral_location = '';
    }

    /**
     * Load collaterals for the current loan
     */
    public function loadCollaterals()
    {
        $loanId = session('currentloanID');
        if ($loanId) {
            $this->collaterals = DB::table('collaterals')
                ->where('loan_id', $loanId)
                ->get()
                ->toArray();
        }
    }

    /**
     * Show success message
     */
    public function showSuccessMessage($message)
    {
        session()->flash('message', $message);
    }

    /**
     * Show error message
     */
    public function showErrorMessage($message)
    {
        session()->flash('error', $message);
    }

    public function render()
    {
        return view('livewire.loans.guarantor-info');
    }
}
