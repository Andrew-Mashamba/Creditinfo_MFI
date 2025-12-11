<?php

namespace App\Http\Livewire\Users;

use App\Models\approvals;
use App\Models\Currencies;
use App\Models\institutions;
use App\Models\TeamUser;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class InstitutionSettings extends Component
{


    public $sub_product_name;
    public $prefix;
    public $productStatus;
    public $currency;
    public $currencies;
    public $deposit;
    public $deposit_charge;
    public $deposit_charge_min_value;
    public $deposit_charge_max_value;
    public $withdraw;
    public $withdraw_charge;
    public $withdraw_charge_min_value;
    public $withdraw_charge_max_value;
    public $interest_value;
    public $interest_tenure;
    public $maintenance_fees;
    public $profit_account;
    public $inactivity;
    public $create_during_registration;
    public $activated_by_lower_limit;
    public $requires_approval;
    public $generate_atm_card_profile;
    public $allow_statement_generation;
    public $send_notifications;
    public $require_image_member;
    public $require_image_id;
    public $require_mobile_number;
    public $number_of_accounts;
    public $total_value_of_accounts;
    public $profits;
    public $notes;
    public $interest;
    public $ledger_fees;
    public $ledger_fees_value;
    public $maintenance_fees_value;
    public $product_id;
    public $generate_mobile_profile;


    public $total_shares;
    public $shares_per_member;
    public $nominal_price;
    public $available_shares;

    public $institution_id;
    public $name;
    public $region;
    public $wilaya;
    public $phone_number;
    public $email;
    public $institution_status;
    public $admin;
    public $application_fee;
    public $initial_shares;
    public $value_per_share;
    public $process_id;
    public $fees_holding_account;
    public $temp_shares_holding_account;
    public $minimum_shares;
    public $dormant_period_months;

    public function boot(){
        $institution = institutions::where('institution_id',Session::get('institution'))->get();
        foreach ($institution as $institutionData){
            $this->process_id = $institutionData->id;
            $this->institution_id = $institutionData->institution_id;
            $this->name = $institutionData->name;
            $this->region = $institutionData->region;
            $this->wilaya = $institutionData->wilaya;
            $this->phone_number = $institutionData->phone_number;
            $this->email = $institutionData->email;
            $this->institution_status = $institutionData->institution_status;
            $this->admin = $institutionData->admin;
            $this->application_fee = $institutionData->application_fee;
            $this->initial_shares = $institutionData->initial_shares;
            $this->value_per_share = $institutionData->value_per_share;
            $this->fees_holding_account = $institutionData->fees_holding_account;
            $this->temp_shares_holding_account = $institutionData->temp_shares_holding_account;
            $this->minimum_shares = $institutionData->minimum_shares;
            $this->dormant_period_months = $institutionData->dormant_period_months ?? 6;
        }
    }
    public function updateInstitution(){

        $user = auth()->user();

        $team = $user->currentTeam;

        $institution = TeamUser::where('user_id',Auth::user()->id)->value('institution');

        $data = [

                'name' => $this->name,
                'region' => $this->region,
                'wilaya' => $this->wilaya,
                'phone_number' => $this->phone_number,
                'email' => $this->email,
                'institution_status' => $this->institution_status,
                'admin' => $this->admin,
                'application_fee' => $this->application_fee,
                'initial_shares' => $this->initial_shares,
                'value_per_share' => $this->value_per_share,
                'fees_holding_account' => $this->fees_holding_account,
                'minimum_shares' => $this->minimum_shares,
                'temp_shares_holding_account' => $this->temp_shares_holding_account,
                'dormant_period_months' => $this->dormant_period_months
        ];

        $update_value = approvals::updateOrCreate(
            [
                'process_id' => $this->process_id,
                'user_id' => Auth::user()->id

            ],
            [
                'institution' => $institution,
                'process_name' => 'editInstitution',
                'process_description' => 'has edited institution data',
                'approval_process_description' => 'has approved changes to institution data',
                'process_code' => '14',
                'process_id' => $this->process_id,
                'process_status' => 'Pending',
                'user_id'  => Auth::user()->id,
                'team_id'  => "",
                'edit_package'=> json_encode($data)
            ]
        );


    }




    public function render()
    {



        $this->currencies = Currencies::get();
        return view('livewire.users.institution-settings');
    }
}
