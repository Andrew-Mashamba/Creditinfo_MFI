<?php

namespace App\Http\Livewire\Savings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\MandatorySavingsService;
use App\Models\MandatorySavingsTracking;
use App\Models\MandatorySavingsNotification;
use App\Models\MandatorySavingsSettings;
use App\Models\ClientsModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MandatorySavingsManagement extends Component
{
    use WithPagination;

    // Properties for filters and data
    public $selectedYear;
    public $selectedMonth;
    public $selectedStatus = '';
    public $searchTerm = '';
    public $showArrearsOnly = false;

    // Properties for settings
    public $monthlyAmount;
    public $dueDay;
    public $gracePeriodDays;
    public $enableNotifications;
    public $firstReminderDays;
    public $secondReminderDays;
    public $finalReminderDays;

    // Properties for operations
    public $isLoading = false;
    public $processingMessage = '';
    public $successMessage = '';
    public $errorMessage = '';

    // Properties for modals
    public $showSettingsModal = false;
    public $showArrearsModal = false;
    public $selectedMember = null;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'selectedYear' => 'required|integer|min:2020|max:2030',
        'selectedMonth' => 'required|integer|min:1|max:12',
        'monthlyAmount' => 'required|numeric|min:0',
        'dueDay' => 'required|integer|min:1|max:31',
        'gracePeriodDays' => 'required|integer|min:0|max:30',
        'firstReminderDays' => 'required|integer|min:1|max:30',
        'secondReminderDays' => 'required|integer|min:1|max:30',
        'finalReminderDays' => 'required|integer|min:1|max:30',
    ];

    public function mount()
    {
        $this->selectedYear = Carbon::now()->year;
        $this->selectedMonth = Carbon::now()->month;
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $settings = MandatorySavingsSettings::forInstitution('1');
        if ($settings) {
            $this->monthlyAmount = $settings->monthly_amount;
            $this->dueDay = $settings->due_day;
            $this->gracePeriodDays = $settings->grace_period_days;
            $this->enableNotifications = $settings->enable_notifications;
            $this->firstReminderDays = $settings->first_reminder_days;
            $this->secondReminderDays = $settings->second_reminder_days;
            $this->finalReminderDays = $settings->final_reminder_days;
        }
    }

    public function render()
    {
        $query = MandatorySavingsTracking::with(['client', 'account'])
            ->where('year', $this->selectedYear)
            ->where('month', $this->selectedMonth);

        // Apply status filter
        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        // Apply search filter
        if ($this->searchTerm) {
            $query->whereHas('client', function ($q) {
                $q->where('first_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('client_number', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Apply arrears filter
        if ($this->showArrearsOnly) {
            $query->where('balance', '>', 0);
        }

        $trackingRecords = $query->paginate(20);

        // Get summary statistics
        $service = new MandatorySavingsService();
        $summaryStats = $service->getSummaryStatistics($this->selectedYear, $this->selectedMonth);

        return view('livewire.savings.mandatory-savings-management', [
            'trackingRecords' => $trackingRecords,
            'summaryStats' => $summaryStats
        ]);
    }

    public function generateTrackingRecords()
    {
        try {
            $this->isLoading = true;
            $this->processingMessage = 'Generating tracking records...';

            $service = new MandatorySavingsService();
            $result = $service->generateTrackingRecords($this->selectedYear, $this->selectedMonth);

            $this->successMessage = "Successfully generated {$result['created']} new records and updated {$result['updated']} existing records for {$result['total_members']} members.";
            $this->isLoading = false;
            $this->processingMessage = '';

        } catch (\Exception $e) {
            $this->errorMessage = 'Error generating tracking records: ' . $e->getMessage();
            $this->isLoading = false;
            $this->processingMessage = '';
            Log::error('Error generating tracking records: ' . $e->getMessage());
        }
    }

    public function updateFromPayments()
    {
        try {
            $this->isLoading = true;
            $this->processingMessage = 'Updating tracking records from payments...';

            $service = new MandatorySavingsService();
            $result = $service->updateTrackingFromPayments($this->selectedYear, $this->selectedMonth);

            $this->successMessage = "Successfully updated {$result['updated_records']} records from {$result['total_payments']} payments.";
            $this->isLoading = false;
            $this->processingMessage = '';

        } catch (\Exception $e) {
            $this->errorMessage = 'Error updating from payments: ' . $e->getMessage();
            $this->isLoading = false;
            $this->processingMessage = '';
            Log::error('Error updating from payments: ' . $e->getMessage());
        }
    }

    public function generateNotifications()
    {
        try {
            $this->isLoading = true;
            $this->processingMessage = 'Generating notifications...';

            $service = new MandatorySavingsService();
            $result = $service->generateNotifications($this->selectedYear, $this->selectedMonth);

            $this->successMessage = "Successfully generated {$result['notifications_created']} notifications for {$result['members_notified']} members.";
            $this->isLoading = false;
            $this->processingMessage = '';

        } catch (\Exception $e) {
            $this->errorMessage = 'Error generating notifications: ' . $e->getMessage();
            $this->isLoading = false;
            $this->processingMessage = '';
            Log::error('Error generating notifications: ' . $e->getMessage());
        }
    }

    public function processOverdueRecords()
    {
        try {
            $this->isLoading = true;
            $this->processingMessage = 'Processing overdue records...';

            $service = new MandatorySavingsService();
            $result = $service->processOverdueRecords();

            $this->successMessage = "Successfully processed {$result['updated_records']} overdue records.";
            $this->isLoading = false;
            $this->processingMessage = '';

        } catch (\Exception $e) {
            $this->errorMessage = 'Error processing overdue records: ' . $e->getMessage();
            $this->isLoading = false;
            $this->processingMessage = '';
            Log::error('Error processing overdue records: ' . $e->getMessage());
        }
    }

    public function showArrearsReport()
    {
        try {
            $this->isLoading = true;
            $this->processingMessage = 'Calculating arrears...';

            $service = new MandatorySavingsService();
            $arrearsData = $service->calculateArrears();

            $this->selectedMember = null;
            $this->showArrearsModal = true;
            $this->isLoading = false;
            $this->processingMessage = '';

            return $arrearsData;

        } catch (\Exception $e) {
            $this->errorMessage = 'Error calculating arrears: ' . $e->getMessage();
            $this->isLoading = false;
            $this->processingMessage = '';
            Log::error('Error calculating arrears: ' . $e->getMessage());
        }
    }

    public function showSettings()
    {
        $this->showSettingsModal = true;
    }

    public function saveSettings()
    {
        $this->validate();

        try {
            $settings = MandatorySavingsSettings::forInstitution('1');
            
            if (!$settings) {
                $settings = new MandatorySavingsSettings();
                $settings->institution_id = '1';
            }

            $settings->update([
                'monthly_amount' => $this->monthlyAmount,
                'due_day' => $this->dueDay,
                'grace_period_days' => $this->gracePeriodDays,
                'enable_notifications' => $this->enableNotifications,
                'first_reminder_days' => $this->firstReminderDays,
                'second_reminder_days' => $this->secondReminderDays,
                'final_reminder_days' => $this->finalReminderDays,
            ]);

            $this->successMessage = 'Settings saved successfully.';
            $this->showSettingsModal = false;

        } catch (\Exception $e) {
            $this->errorMessage = 'Error saving settings: ' . $e->getMessage();
            Log::error('Error saving settings: ' . $e->getMessage());
        }
    }

    public function viewMemberDetails($clientNumber)
    {
        $this->selectedMember = ClientsModel::where('client_number', $clientNumber)->first();
        if ($this->selectedMember) {
            $this->showArrearsModal = true;
        }
    }

    public function clearMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['selectedYear', 'selectedMonth', 'selectedStatus', 'searchTerm', 'showArrearsOnly'])) {
            $this->resetPage();
        }
    }
}
