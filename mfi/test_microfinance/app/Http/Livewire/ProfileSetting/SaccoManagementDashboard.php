<?php

namespace App\Http\Livewire\ProfileSetting;

use App\Models\Committee;
use Livewire\Component;

class SaccoManagementDashboard extends Component
{
    public $tab = 'committees';
    public $selectedCommitteeId = null;
    public $selectedMeetingId = null;

    public function selectCommittee($id)
    {
        $this->selectedCommitteeId = $id;
        $this->tab = 'committee_members';
    }

    public function selectMeeting($id)
    {
        $this->selectedMeetingId = $id;
        $this->tab = 'meeting_attendance';
    }

    public function render()
    {
        $committees = Committee::orderBy('name')->get();
        return view('livewire.profile-setting.sacco-management-dashboard', [
            'committees' => $committees
        ]);
    }
} 