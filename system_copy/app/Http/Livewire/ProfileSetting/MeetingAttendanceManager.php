<?php

namespace App\Http\Livewire\ProfileSetting;

use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\LeaderShipModel;
use Livewire\Component;
use Livewire\WithPagination;

class MeetingAttendanceManager extends Component
{
    use WithPagination;

    public $meeting_id;
    public $leader_id;
    public $status = 'present';
    public $notes;
    public $search = '';
    public $perPage = 10;
    public $stipend_amount;

    protected $rules = [
        'leader_id' => 'required|exists:leaderships,id',
        'status' => 'required|string',
        'notes' => 'nullable|string',
    ];

    public function mount($meeting_id)
    {
        $this->meeting_id = $meeting_id;
    }

    public function markAttendance()
    {
        $this->validate();
        MeetingAttendance::updateOrCreate(
            [
                'meeting_id' => $this->meeting_id,
                'leader_id' => $this->leader_id,
            ],
            [
                'status' => $this->status,
                'notes' => $this->notes,
                'stipend_amount' => $this->stipend_amount,
            ]
        );
        session()->flash('message', 'Attendance marked.');
        $this->reset(['leader_id', 'status', 'notes', 'stipend_amount']);
    }

    public function toggleStipendPaid($id)
    {
        $record = MeetingAttendance::findOrFail($id);
        $record->stipend_paid = !$record->stipend_paid;
        $record->save();
        session()->flash('message', 'Stipend status updated.');
    }

    public function render()
    {
        $meeting = Meeting::findOrFail($this->meeting_id);
        $attendance = MeetingAttendance::with('leader')
            ->where('meeting_id', $this->meeting_id)
            ->when($this->search, function ($query) {
                $query->whereHas('leader', function ($q) {
                    $q->where('full_name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $availableLeaders = LeaderShipModel::all();

        return view('livewire.profile-setting.meeting-attendance-manager', [
            'meeting' => $meeting,
            'attendance' => $attendance,
            'availableLeaders' => $availableLeaders
        ]);
    }
}
