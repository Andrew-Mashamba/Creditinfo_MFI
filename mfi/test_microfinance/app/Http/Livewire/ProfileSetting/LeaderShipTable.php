<?php

namespace App\Http\Livewire\ProfileSetting;

use App\Models\LeaderShipModel;
use Livewire\Component;
use Livewire\WithPagination;

class LeaderShipTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $updatesQueryString = ['search', 'page'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $leaders = LeaderShipModel::where('institution_id', auth()->user()->institution_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('full_name', 'like', '%'.$this->search.'%')
                      ->orWhere('position', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.profile-setting.leader-ship-table', [
            'leaders' => $leaders
        ]);
    }
}
