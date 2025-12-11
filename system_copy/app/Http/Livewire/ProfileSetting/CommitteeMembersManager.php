<?php

namespace App\Http\Livewire\ProfileSetting;

use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\LeaderShipModel;
use Livewire\Component;
use Livewire\WithPagination;

class CommitteeMembersManager extends Component
{
    use WithPagination;

    public $committee_id;
    public $leader_id;
    public $role;
    public $search = '';
    public $perPage = 10;

    protected $rules = [
        'leader_id' => 'required|exists:leaderships,id',
        'role' => 'required|string|max:50',
    ];

    public function mount($committee_id)
    {
        $this->committee_id = $committee_id;
    }

    public function addMember()
    {
        $this->validate();
        CommitteeMember::create([
            'committee_id' => $this->committee_id,
            'leader_id' => $this->leader_id,
            'role' => $this->role,
            'joined_at' => now(),
        ]);
        session()->flash('message', 'Member added to committee.');
        $this->reset(['leader_id', 'role']);
    }

    public function removeMember($id)
    {
        CommitteeMember::findOrFail($id)->delete();
        session()->flash('message', 'Member removed from committee.');
    }

    public function render()
    {
        $committee = Committee::findOrFail($this->committee_id);
        $members = CommitteeMember::with('leader')
            ->where('committee_id', $this->committee_id)
            ->when($this->search, function ($query) {
                $query->whereHas('leader', function ($q) {
                    $q->where('full_name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $availableLeaders = LeaderShipModel::whereNotIn('id',
            CommitteeMember::where('committee_id', $this->committee_id)->pluck('leader_id')->toArray()
        )->get();

        return view('livewire.profile-setting.committee-members-manager', [
            'committee' => $committee,
            'members' => $members,
            'availableLeaders' => $availableLeaders
        ]);
    }
}
