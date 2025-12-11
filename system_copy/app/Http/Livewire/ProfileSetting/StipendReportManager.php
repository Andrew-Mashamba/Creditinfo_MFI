<?php

namespace App\Http\Livewire\ProfileSetting;

use App\Models\MeetingAttendance;
use App\Models\LeaderShipModel;
use App\Models\Meeting;
use Livewire\Component;
use Livewire\WithPagination;

class StipendReportManager extends Component
{
    use WithPagination;

    public $leader_id = '';
    public $meeting_id = '';
    public $paid_status = '';
    public $date_from = '';
    public $date_to = '';
    public $search = '';
    public $perPage = 10;
    public $showExportModal = false;
    public $exportFields = ['leader', 'meeting', 'stipend_amount', 'stipend_paid', 'created_at'];
    public $selectedExportFields = [];
    public $exportFormat = 'csv';
    public $selectedRecords = [];
    public $selectAll = false;
    public $showPaymentModal = false;
    public $paymentMethod = 'internal_transfer';
    public $paymentMethods = ['internal_transfer' => 'Internal Transfer', 'cash' => 'Cash', 'tips' => 'TIPS'];
    public $paymentDetails = [];

    public function openExportModal()
    {
        $this->selectedExportFields = $this->exportFields;
        $this->exportFormat = 'csv';
        $this->showExportModal = true;
    }
    public function closeExportModal()
    {
        $this->showExportModal = false;
    }
    public function exportStipends()
    {
        $fields = $this->selectedExportFields;
        $format = $this->exportFormat;
        $filename = 'stipend_report_' . now()->format('Ymd_His') . '.' . $format;
        $records = $this->getFilteredQuery()->get();
        $exportData = $records->map(function($item) use ($fields) {
            $row = [];
            foreach ($fields as $field) {
                if ($field === 'leader') {
                    $row['Leader'] = $item->leader->full_name ?? '';
                } elseif ($field === 'meeting') {
                    $row['Meeting'] = $item->meeting->title ?? '';
                } else {
                    $row[ucfirst(str_replace('_', ' ', $field))] = $item[$field];
                }
            }
            return $row;
        });
        $headers = array_keys($exportData->first() ?? []);
        $exportArray = [$headers];
        foreach ($exportData as $row) {
            $exportArray[] = array_values($row);
        }
        if ($format === 'csv') {
            $csv = fopen('php://memory', 'r+');
            foreach ($exportArray as $row) {
                fputcsv($csv, $row);
            }
            rewind($csv);
            $content = stream_get_contents($csv);
            fclose($csv);
            return response($content)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } else {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericArrayExport($exportArray), $filename);
        }
        $this->showExportModal = false;
        $this->emit('notify', 'success', 'Stipend report exported successfully!');
    }
    public function getFilteredQuery()
    {
        return MeetingAttendance::with(['leader', 'meeting'])
            ->when($this->leader_id, fn($q) => $q->where('leader_id', $this->leader_id))
            ->when($this->meeting_id, fn($q) => $q->where('meeting_id', $this->meeting_id))
            ->when($this->paid_status !== '', fn($q) => $q->where('stipend_paid', $this->paid_status))
            ->when($this->date_from, fn($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('created_at', '<=', $this->date_to))
            ->when($this->search, function($q) {
                $q->whereHas('leader', function($q2) {
                    $q2->where('full_name', 'like', '%'.$this->search.'%');
                });
            });
    }
    public function updatedSelectAll($value)
    {
        if ($value) {
            $ids = $this->getFilteredQuery()->orderByDesc('created_at')->pluck('id')->toArray();
            $this->selectedRecords = $ids;
        } else {
            $this->selectedRecords = [];
        }
    }

    public function openPaymentModal()
    {
        $this->showPaymentModal = true;
        $this->paymentMethod = 'internal_transfer';
        $this->paymentDetails = [];
    }
    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
    }
    public function processStipendPayments()
    {
        $records = \App\Models\MeetingAttendance::whereIn('id', $this->selectedRecords)->get();
        foreach ($records as $record) {
            $leader = $record->leader;
            $amount = $record->stipend_amount;
            $result = null;
            if ($this->paymentMethod === 'internal_transfer') {
                // Example: Use TransactionPostingService for internal transfer
                $transactionService = app('App\\Services\\TransactionPostingService');
                $saccoAccount = $this->paymentDetails['sacco_account'] ?? '1001';
                $leaderAccount = $leader->account_number ?? null;
                if ($leaderAccount) {
                    $result = $transactionService->postTransaction([
                        'first_account' => $saccoAccount, // Debit SACCO
                        'second_account' => $leaderAccount, // Credit Leader
                        'amount' => $amount,
                        'narration' => 'Stipend Payment: ' . ($record->meeting->title ?? '-') . ' to ' . $leader->full_name,
                        'action' => 'stipend_payment'
                    ]);
                }
            } elseif ($this->paymentMethod === 'cash') {
                // Mark as cash disbursed (no transaction)
                $result = ['status' => 'success', 'message' => 'Cash disbursed'];
            } elseif ($this->paymentMethod === 'tips') {
                // Example: Use TIPS/mobile logic (pseudo-code)
                $result = ['status' => 'success', 'message' => 'TIPS/mobile payment sent'];
            }
            if ($result && $result['status'] === 'success') {
                $record->stipend_paid = true;
                $record->save();
                if ($leader && $leader->email) {
                    $leader->notify(new \App\Notifications\StipendPaidNotification($record));
                }
                $this->emit('notify', 'success', 'Stipend paid for ' . $leader->full_name);
            } else {
                $this->emit('notify', 'error', 'Payment failed for ' . $leader->full_name . ': ' . ($result['message'] ?? 'Unknown error'));
            }
        }
        $this->showPaymentModal = false;
        $this->selectedRecords = [];
        $this->selectAll = false;
    }

    public function render()
    {
        $leaders = LeaderShipModel::orderBy('full_name')->get();
        $meetings = Meeting::orderByDesc('meeting_date')->get();
        $records = $this->getFilteredQuery()->orderByDesc('created_at')->paginate($this->perPage);
        $totalPaid = $this->getFilteredQuery()->where('stipend_paid', true)->sum('stipend_amount');
        $totalUnpaid = $this->getFilteredQuery()->where('stipend_paid', false)->sum('stipend_amount');
        $countPaid = $this->getFilteredQuery()->where('stipend_paid', true)->count();
        $countUnpaid = $this->getFilteredQuery()->where('stipend_paid', false)->count();
        return view('livewire.profile-setting.stipend-report-manager', [
            'leaders' => $leaders,
            'meetings' => $meetings,
            'records' => $records,
            'totalPaid' => $totalPaid,
            'totalUnpaid' => $totalUnpaid,
            'countPaid' => $countPaid,
            'countUnpaid' => $countUnpaid,
        ]);
    }
}
