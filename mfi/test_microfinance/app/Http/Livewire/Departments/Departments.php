<?php

namespace App\Http\Livewire\Departments;

use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class Departments extends Component
{
    use WithPagination;

    public function render()
    {
        $departments = Department::with('users')->paginate(10);
        return view('livewire.departments.departments', ['departments' => $departments]);
    }
}
