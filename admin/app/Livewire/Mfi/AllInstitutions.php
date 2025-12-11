<?php

namespace App\Livewire\Mfi;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MfiInstitution;

class AllInstitutions extends Component
{
    use WithPagination;

    public $search = '';
    
    public function getMfiInstitutions()
    {
        $query = MfiInstitution::query();
        
        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $this->search . '%');
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    public function render()
    {
        $institutions = $this->getMfiInstitutions();

        return view('livewire.mfi.all-institutions', [
            'institutions' => $institutions
        ])->layout('layouts.app');
    }
}
