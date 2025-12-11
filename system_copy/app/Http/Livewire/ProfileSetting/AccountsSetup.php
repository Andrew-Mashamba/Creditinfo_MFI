<?php

namespace App\Http\Livewire\ProfileSetting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class AccountsSetup extends Component
{
    public $left_category;
    public $left_category_code;
    public $left_sub_category_code;
    public $left_account;
    public $right_category;
    public $right_category_code;
    public $right_account;
    public $amount;
    public $narration;
    public $transactionPosted;
    public $left_account_id;
    public $role;
    public $left_account_details;
    public $account_name;
    public $item_name;
    public $left = 'credit';
    public $right = 'debit';
    public $items;
    public $table_name;

    // Institution account properties
    public $share_capital_account;
    public $loan_account;
    public $repayment_account;
    public $savings_account;
    public $registration_account;
    public $subscription_account;
    public $insurance_account;
    public $emergency_account;
    public $pension_account;
    public $building_account;
    public $education_account;

    public $gl_accounts;
    public $institution_id;

    protected $rules = [
        'share_capital_account' => 'required',
        'loan_account' => 'required',
        'repayment_account' => 'required',
        'savings_account' => 'required',
        'registration_account' => 'required',
        'subscription_account' => 'required',
        'insurance_account' => 'required',
        'emergency_account' => 'required',
        'pension_account' => 'required',
        'building_account' => 'required',
        'education_account' => 'required',
    ];

    public function mount()
    {
        $this->gl_accounts = collect(DB::table('GL_accounts')->get()->toArray())->map(function($item) {
            return (object) $item;
        });

        // Get the institution ID from the database
        $institution = DB::table('institutions')->first();
        $this->institution_id = $institution ? $institution->id : null;

        // Load existing institution accounts if any
        if ($this->institution_id) {
            $institution = DB::table('institutions')->where('id', $this->institution_id)->first();
            if ($institution) {
                $this->share_capital_account = $institution->share_capital_account ?? null;
                $this->loan_account = $institution->loan_account ?? null;
                $this->repayment_account = $institution->repayment_account ?? null;
                $this->savings_account = $institution->savings_account ?? null;
                $this->registration_account = $institution->registration_account ?? null;
                $this->subscription_account = $institution->subscription_account ?? null;
                $this->insurance_account = $institution->insurance_account ?? null;
                $this->emergency_account = $institution->emergency_account ?? null;
                $this->pension_account = $institution->pension_account ?? null;
                $this->building_account = $institution->building_account ?? null;
                $this->education_account = $institution->education_account ?? null;
            }
        }
    }

    public function getAccountsForCategory($majorCategory, $category)
    {
        $cacheKey = "accounts_{$majorCategory}_{$category}";
        
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($majorCategory, $category) {
            return DB::table('accounts')
                ->where('major_category_code', $majorCategory)
                ->where('category_code', $category)
                ->get();
        });
    }

    public function saveInstitutionAccounts()
    {
        $this->validate();

        if (!$this->institution_id) {
            session()->flash('error', 'No institution found.');
            return;
        }

        try {
            DB::beginTransaction();

            DB::table('institutions')
                ->where('id', $this->institution_id)
                ->update([
                    'share_capital_account' => $this->share_capital_account,
                    'loan_account' => $this->loan_account,
                    'repayment_account' => $this->repayment_account,
                    'savings_account' => $this->savings_account,
                    'registration_account' => $this->registration_account,
                    'subscription_account' => $this->subscription_account,
                    'insurance_account' => $this->insurance_account,
                    'emergency_account' => $this->emergency_account,
                    'pension_account' => $this->pension_account,
                    'building_account' => $this->building_account,
                    'education_account' => $this->education_account,
                    'updated_at' => now(),
                ]);

            DB::commit();
            
            // Clear the accounts cache
            Cache::tags(['accounts'])->flush();
            
            session()->flash('message', 'Institution accounts have been saved successfully.');
            $this->emit('refreshAccounts');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save institution accounts: ' . $e->getMessage());
        }
    }

    public function set()
    {
        $dd = DB::table($this->right_category)
            ->where('category_code', $this->right_category_code)
            ->value('category_name');
            
        $yy = DB::table($dd)
            ->where('sub_category_code', $this->right_account)
            ->value('sub_category_name');
            
        $account_number = DB::table('accounts')
            ->where('sub_category_code', $this->right_account)
            ->value('account_number');

        DB::table('setup_accounts')
            ->where('id', $this->items)
            ->update([
                'sub_category_code' => $this->right_account,
                'account_number' => $account_number,
                'table_name' => DB::table($this->right_category)
                    ->where('category_code', $this->right_category_code)
                    ->value('category_name'),
                'updated_at' => now(),
            ]);
    }

    public function setTableName($name)
    {
        $this->item_name = $name;
    }

    public function render()
    {
        $gl_accounts = collect(DB::table('GL_accounts')->get()->toArray())->map(function($item) {
            return (object) $item;
        });
        //dd($gl_accounts);
        $left_sub_categories = [];
        $left_accounts = [];
        $right_sub_categories = [];
        $right_accounts = [];

        return view('livewire.profile-setting.accounts-setup', [
            'gl_accounts' => $gl_accounts,
            'left_sub_categories' => $left_sub_categories,
            'left_accounts' => $left_accounts,
            'right_sub_categories' => $right_sub_categories,
            'right_accounts' => $right_accounts,
        ]);
    }
}
