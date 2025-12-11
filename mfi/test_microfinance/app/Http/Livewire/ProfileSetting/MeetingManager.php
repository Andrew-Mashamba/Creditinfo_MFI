<?php

namespace App\Http\Livewire\ProfileSetting;

use App\Models\Committee;
use App\Models\Meeting;
use Livewire\Component;
use Livewire\WithPagination;

class MeetingManager extends Component
{
    use WithPagination;

    public $committee_id;
    public $meeting_id;
    public $title;
    public $agenda;
    public $meeting_date;
    public $location;
    public $notes;
    public $editMode = false;
    public $search = '';
    public $perPage = 10;

    protected $rules = [
        'title' => 'required|string|max:255',
        'agenda' => 'nullable|string',
        'meeting_date' => 'required|date',
        'location' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
    ];

    public function mount($committee_id)
    {
        $this->committee_id = $committee_id;
    }

    public function resetForm()
    {
        $this->meeting_id = null;
        $this->title = '';
        $this->agenda = '';
        $this->meeting_date = '';
        $this->location = '';
        $this->notes = '';
        $this->editMode = false;
    }

    public function createMeeting()
    {
        $this->validate();
        Meeting::create([
            'committee_id' => $this->committee_id,
            'title' => $this->title,
            'agenda' => $this->agenda,
            'meeting_date' => $this->meeting_date,
            'location' => $this->location,
            'notes' => $this->notes,
        ]);
        session()->flash('message', 'Meeting created successfully.');
        $this->resetForm();
    }

    public function editMeeting($id)
    {
        $meeting = Meeting::findOrFail($id);
        $this->meeting_id = $meeting->id;
        $this->title = $meeting->title;
        $this->agenda = $meeting->agenda;
        $this->meeting_date = $meeting->meeting_date;
        $this->location = $meeting->location;
        $this->notes = $meeting->notes;
        $this->editMode = true;
    }

    public function updateMeeting()
    {
        $this->validate();
        $meeting = Meeting::findOrFail($this->meeting_id);
        $meeting->update([
            'title' => $this->title,
            'agenda' => $this->agenda,
            'meeting_date' => $this->meeting_date,
            'location' => $this->location,
            'notes' => $this->notes,
        ]);
        session()->flash('message', 'Meeting updated successfully.');
        $this->resetForm();
    }

    public function deleteMeeting($id)
    {
        Meeting::findOrFail($id)->delete();
        session()->flash('message', 'Meeting deleted successfully.');
        $this->resetForm();
    }

    public function render()
    {
        $committee = Committee::findOrFail($this->committee_id);
        $meetings = Meeting::where('committee_id', $this->committee_id)
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%')
                      ->orWhere('agenda', 'like', '%'.$this->search.'%')
                      ->orWhere('location', 'like', '%'.$this->search.'%');
            })
            ->orderByDesc('meeting_date')
            ->paginate($this->perPage);

        return view('livewire.profile-setting.meeting-manager', [
            'committee' => $committee,
            'meetings' => $meetings
        ]);
    }
}
