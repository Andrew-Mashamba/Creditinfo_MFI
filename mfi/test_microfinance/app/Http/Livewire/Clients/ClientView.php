<?php

namespace App\Http\Livewire\Clients;

use App\Models\LoansModel;
use Livewire\Component;
use App\Models\Clients;
use Illuminate\Support\Facades\Session;

class ClientView extends Component
{

    public $member;
    public $loanStatus;


    public $member_number = '';
    public $item = 100;
    public $product_number;

    public function boot(){

        $this->member = Clients::where('id', Session::get('memberToViewId'))->first();
        $this->loanStatus=LoansModel::where('client_number',Session::get('memberToViewId'))->get();

        $this->variables = [
            // Individual & Company
            [
                'name' => 'customer_code',
                'label' => 'Customer Code',
                'type' => 'text',
                'for' => 'both',
            ],
            // Individual
            [
                'name' => 'present_surname',
                'label' => 'Present Surname',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'birth_surname',
                'label' => 'Birth Surname',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'first_name',
                'label' => 'First Name',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'middle_names',
                'label' => 'Middle Names',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'full_name',
                'label' => 'Full Name',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'number_of_spouse',
                'label' => 'Number of Spouse',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'number_of_children',
                'label' => 'Number of Children',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'classification_of_individual',
                'label' => 'Classification of Individual',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'gender',
                'label' => 'Gender',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'date_of_birth',
                'label' => 'Date of Birth',
                'type' => 'date',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'country_of_birth',
                'label' => 'Country of Birth',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'marital_status',
                'label' => 'Marital Status',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'fate_status',
                'label' => 'Fate Status',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'social_status',
                'label' => 'Social Status',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'residency',
                'label' => 'Residency',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'citizenship',
                'label' => 'Citizenship',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'nationality',
                'label' => 'Nationality',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'employment',
                'label' => 'Employment',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'employer_name',
                'label' => 'Employer Name',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'education',
                'label' => 'Education',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'income_available',
                'label' => 'Income Available',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'monthly_expenses',
                'label' => 'Monthly Expenses',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Individual
            [
                'name' => 'negative_status_of_individual',
                'label' => 'Negative Status of Individual',
                'type' => 'text',
                'for' => 'individual',
            ],
            // Both
            [
                'name' => 'tax_identification_number',
                'label' => 'Tax Identification Number',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'national_id',
                'label' => 'National ID',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'passport_number',
                'label' => 'Passport Number',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'passport_issuer_country',
                'label' => 'Passport Issuer Country',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'driving_license_number',
                'label' => 'Driving License Number',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'voters_id',
                'label' => "Voter's ID",
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'foreign_unique_id',
                'label' => 'Foreign Unique ID',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'custom_id_number_1',
                'label' => 'Custom ID Number 1',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'custom_id_number_2',
                'label' => 'Custom ID Number 2',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'main_address',
                'label' => 'Main Address',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'street',
                'label' => 'Street',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'number_of_building',
                'label' => 'Number of Building',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'postal_code',
                'label' => 'Postal Code',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'region',
                'label' => 'Region',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'district',
                'label' => 'District',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'country',
                'label' => 'Country',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'mobile_phone',
                'label' => 'Mobile Phone',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'fixed_line',
                'label' => 'Fixed Line',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'text',
                'for' => 'both',
            ],
            // Both
            [
                'name' => 'web_page',
                'label' => 'Web Page',
                'type' => 'text',
                'for' => 'both',
            ],
            // Company
            [
                'name' => 'trade_name',
                'label' => 'Trade Name',
                'type' => 'text',
                'for' => 'company',
            ],
            // Company
            [
                'name' => 'legal_form',
                'label' => 'Legal Form',
                'type' => 'text',
                'for' => 'company',
            ],
            // Company
            [
                'name' => 'establishment_date',
                'label' => 'Establishment Date',
                'type' => 'date',
                'for' => 'company',
            ],
            // Company
            [
                'name' => 'registration_country',
                'label' => 'Registration Country',
                'type' => 'text',
                'for' => 'company',
            ],
            // Company
            [
                'name' => 'industry_sector',
                'label' => 'Industry Sector',
                'type' => 'text',
                'for' => 'company',
            ],
            // Company
            [
                'name' => 'registration_number',
                'label' => 'Registration Number',
                'type' => 'text',
                'for' => 'company',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.clients.member-view');
    }

    public function back(){
        Session::put('memberToViewId', false);
        $this->emit('refreshClientsListComponent');
    }
}
