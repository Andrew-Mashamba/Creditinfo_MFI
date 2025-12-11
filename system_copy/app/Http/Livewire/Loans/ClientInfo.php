<?php

namespace App\Http\Livewire\Loans;

use App\Models\MembersModel;
use Livewire\Component;


use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\AccountsModel;
use App\Models\LoansModel;
use Illuminate\Support\Facades\Log;
use App\Services\CreditScoreService;
use App\Services\ClientInformationService;

class ClientInfo extends Component
{

    public function boot()
    {
        Log::info('=== CLIENT INFO COMPONENT BOOT START ===', [
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'currentloanID' => Session::get('currentloanID')
        ]);

        // Initialize services
        $this->creditScoreService = new CreditScoreService();
        $this->clientInformationService = new ClientInformationService();

        // Pre-load all saved client data comprehensively
        $this->preloadAllSavedClientData();

        Log::info('=== CLIENT INFO COMPONENT BOOT END ===', [
            'member_number' => $this->member_number ?? null,
            'data_loaded' => true
        ]);
    }

    public $member;
    public $member_number = '';
    public $item = 100;
    public $product_number;
    public $loan_type;

    public $creditScoreData;
    protected $creditScoreService;

    public $clientInfoData;
    protected $clientInformationService;

    public function mount()
    {
        Log::info('=== CLIENT INFO COMPONENT MOUNT START ===', [
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId()
        ]);

        // Services and data are already initialized in boot() method
        // Just log the completion

        Log::info('=== CLIENT INFO COMPONENT MOUNT END ===', [
            'member_number' => $this->member_number,
            'data_loaded' => true
        ]);
    }

    /**
     * Comprehensive method to pre-load all saved client data
     * This ensures all data is available when the client info page opens
     */
    private function preloadAllSavedClientData(): void
    {
        try {
            Log::info('=== PRELOAD ALL SAVED CLIENT DATA START ===');

            // 1. Load loan and member data
            $this->loadLoanAndMemberData();

            // 2. Load credit score data
            if ($this->member_number) {
                $this->loadCreditScoreData($this->member_number);
            }

            // 3. Load client information data
            if ($this->member_number) {
                $this->loadClientInformationData($this->member_number);
            }

            Log::info('=== PRELOAD ALL SAVED CLIENT DATA END ===', [
                'member_number' => $this->member_number,
                'member_loaded' => isset($this->member),
                'credit_score_loaded' => isset($this->creditScoreData),
                'client_info_loaded' => isset($this->clientInfoData)
            ]);

        } catch (\Exception $e) {
            Log::error('Error preloading client data: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Load loan and member data
     */
    private function loadLoanAndMemberData(): void
    {
        // Get loan ID from session
        $loanId = Session::get('currentloanID');
        
        if ($loanId) {
            // Get loan data first
            $loan = LoansModel::find($loanId);
            if ($loan) {
                $this->member_number = $loan->client_number;
                $this->member = DB::table('clients')->where('client_number', $this->member_number)->get();
                
                Log::info('Loan and member data loaded from loan', [
                    'loan_id' => $loanId,
                    'client_number' => $this->member_number,
                    'member_found' => $this->member->count() > 0
                ]);
            } else {
                // Fallback to session if loan not found
                $this->member_number = Session::get('currentloanClient');
                $this->member = DB::table('clients')->where('client_number', $this->member_number)->get();
                
                Log::info('Member data loaded from session fallback', [
                    'client_number' => $this->member_number,
                    'member_found' => $this->member->count() > 0
                ]);
            }
        } else {
            // Fallback to session if no loan ID
            $this->member_number = Session::get('currentloanClient');
            $this->member = DB::table('clients')->where('client_number', $this->member_number)->get();
            
            Log::info('Member data loaded from session only', [
                'client_number' => $this->member_number,
                'member_found' => $this->member->count() > 0
            ]);
        }
    }




    public function updated($fieldName, $value)
    {
        try {
            Log::info('=== CLIENT INFO UPDATED METHOD CALLED ===', [
                'field_name' => $fieldName,
                'value' => $value,
                'value_type' => gettype($value),
                'timestamp' => now()->toISOString()
            ]);

            // Handle specific field updates
            if ($fieldName === 'loan_type') {
                $this->handleLoanTypeUpdate($value);
            } elseif ($fieldName === 'product_number') {
                $this->handleProductNumberUpdate($value);
            } elseif ($fieldName === 'member_number') {
                $this->handleMemberNumberUpdate($value);
            }

            // Auto-save client info data after any field update
            $this->autoSaveClientInfoData();

        } catch (\Exception $e) {
            Log::error('Error updating client info field: ' . $e->getMessage());
            session()->flash('message', 'Error updating field. Please try again.');
        }
    }

    /**
     * Handle loan type update
     */
    private function handleLoanTypeUpdate($value)
    {
        Log::info('Loan type updated', [
            'loan_type' => $value
        ]);
    }

    /**
     * Handle product number update
     */
    private function handleProductNumberUpdate($value)
    {
        Log::info('Product number updated', [
            'product_number' => $value
        ]);
    }

    /**
     * Handle member number update
     */
    private function handleMemberNumberUpdate($value)
    {
        Log::info('Member number updated', [
            'member_number' => $value
        ]);
    }

    /**
     * Auto-save client info data to database
     */
    private function autoSaveClientInfoData()
    {
        try {
            $loanId = Session::get('currentloanID');
            if (!$loanId) {
                Log::warning('No loan ID available for auto-saving client info data');
                return;
            }

            // Save loan type if it's set
            if ($this->loan_type) {
                LoansModel::where('id', $loanId)->update(['loan_type' => $this->loan_type]);
                
                Log::info('Client info data auto-saved', [
                    'loan_id' => $loanId,
                    'loan_type' => $this->loan_type
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error auto-saving client info data: ' . $e->getMessage());
        }
    }

    public function setValue(){
        $this->validate(['loan_type'=>'required']);
        session()->put('loan_type',$this->loan_type);
        LoansModel::where('id', Session::get('currentloanID'))->update(['loan_type'=>$this->loan_type]);
        session()->flash('message','successfully');
        $this->emit('refreshClientInfoPage');
    }

    public function render()
    {
        // Data is already pre-loaded in mount() method
        // Just return the view with existing data
        return view('livewire.loans.client-info');
    }

    public function back()
    {

        Session::put('memberToViewId', false);
        $this->emit('refreshClientsListComponent');
    }

    public function set()
    {

        $institution_id = '';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
            $institution_id = $User->team_id;
        }

        $accountNumber = str_pad(DB::table('clients')->where('client_number', $this->member_number)->value('branch'), 2, '0', STR_PAD_LEFT) . '104' . str_pad(MembersModel::where('client_number', $this->member_number)->value('id'), 5, '0', STR_PAD_LEFT);
        $loan_id = time();

        $id = AccountsModel::create([
            'account_use' => 'external',
            'institution_number' => '999999',
            'branch_number' => str_pad(DB::table('clients')->where('client_number', $this->member_number)->value('branch'), 2, '0', STR_PAD_LEFT),
            'client_number' => $this->member_number,
            'product_number' => $this->product_number,
            'sub_product_number' => $this->product_number,
            'account_name' => DB::table('clients')->where('membership_number', $this->member_number)->value('first_name') . ' ' . MembersModel::where('client_number', $this->member_number)->value('middle_name') . ' ' . MembersModel::where('client_number', $this->member_number)->value('last_name'),
            'account_number' => $accountNumber,

        ])->id;

        $this->sendApproval($id,'has created a new loan account','09');

        LoansModel::create([

            'loan_id' => $loan_id,
            'loan_account_number' => $accountNumber,
            'loan_sub_product' => $this->product_number,
            'client_number' => $this->member_number,
            'status' => 'Pending',
            'institution_id' => $institution_id,
            'branch_id' =>DB::table('clients')->where('client_number', $this->member_number)->value('branch'),
        ]);

        Session::put('loanEdited', $loan_id);
        $this->sendApproval($loan_id,'has created a new loan request','10');

    }
    public function sendApproval($id,$msg,$code){

        $user = auth()->user();

        $institution='';

        approvals::create([
            'institution' => $institution,
            'process_name' => 'createBranch',
            'process_description' => $msg,
            'approval_process_description' => 'has approved a transaction',
            'process_code' => $code,
            'process_id' => $id,
            'process_status' => 'PENDING',
            'user_id'  => Auth::user()->id,
            'team_id'  => ""
        ]);

    }


        /**
     * Load credit score data for the client
     */
    private function loadCreditScoreData($clientNumber)
    {
        try {
            if ($this->creditScoreService) {
                $this->creditScoreData = $this->creditScoreService->getClientCreditScore($clientNumber);
            } else {
                // Fallback if service is not initialized
                $this->creditScoreData = [
                    'score' => 500,
                    'grade' => 'E',
                    'trend' => 'Stable',
                    'probability_of_default' => 'High',
                    'reasons' => ['Service not available'],
                    'date' => now(),
                    'risk_level' => 'E',
                    'risk_color' => '#FF0000',
                    'risk_description' => 'Service Error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error loading credit score data: ' . $e->getMessage());
            $this->creditScoreData = [
                'score' => 500,
                'grade' => 'E',
                'trend' => 'Stable',
                'probability_of_default' => 'High',
                'reasons' => ['Error loading credit score'],
                'date' => now(),
                'risk_level' => 'E',
                'risk_color' => '#FF0000',
                'risk_description' => 'Error Loading Data'
            ];
        }
    }


        /**
     * Load client information data
     */
    private function loadClientInformationData($clientNumber)
    {
        try {
            if ($this->clientInformationService) {
                $this->clientInfoData = $this->clientInformationService->getClientInformation($clientNumber);
            } else {
                // Fallback if service is not initialized
                $this->clientInfoData = [
                    'basic_info' => [
                        'full_name' => 'N/A',
                        'client_number' => $clientNumber ?? 'N/A'
                    ],
                    'contact_info' => ['phone_number' => 'N/A'],
                    'employment_info' => ['employment_status' => 'N/A'],
                    'financial_info' => ['savings_balance' => 0],
                    'risk_indicators' => [],
                    'demographics' => ['dependent_count' => 0],
                    'status_indicators' => ['client_status' => 'N/A']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error loading client information data: ' . $e->getMessage());
            $this->clientInfoData = [
                'basic_info' => [
                    'full_name' => 'Error Loading Data',
                    'client_number' => $clientNumber ?? 'N/A'
                ],
                'contact_info' => ['phone_number' => 'N/A'],
                'employment_info' => ['employment_status' => 'N/A'],
                'financial_info' => ['savings_balance' => 0],
                'risk_indicators' => [],
                'demographics' => ['dependent_count' => 0],
                'status_indicators' => ['client_status' => 'N/A']
            ];
        }
    }

}
