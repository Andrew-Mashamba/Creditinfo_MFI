<?php

namespace App\Http\Livewire\Users;

use App\Models\Department;
use App\Models\departmentsList;
use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimpleDepartments extends Component
{
    use WithPagination;

    // Basic properties
    public $showModal = false;
    public $editingId = null;
    public $search = '';
    
    // Form fields
    public $department_name = '';
    public $department_code = '';
    public $branch_id = '';
    public $description = '';
    
    protected $rules = [
        'department_name' => 'required|min:3',
        'department_code' => 'required|min:2',
        'branch_id' => 'required|exists:branches,id'
    ];

    public function render()
    {
        $departments = departmentsList::with('branch')
            ->when($this->search, function($query) {
                $query->where('department_name', 'like', '%' . $this->search . '%')
                      ->orWhere('department_code', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);
            
        $branches = Branch::all();
        
        return view('livewire.users.simple-departments', [
            'departments' => $departments,
            'branches' => $branches
        ]);
    }

    public function create()
    {
        $this->reset(['department_name', 'department_code', 'branch_id', 'description', 'editingId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $dept = departmentsList::findOrFail($id);
        $this->editingId = $id;
        $this->department_name = $dept->department_name;
        $this->department_code = $dept->department_code ?? $dept->code;
        $this->branch_id = $dept->branch_id;
        $this->description = $dept->description;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            $data = [
                'department_name' => $this->department_name,
                'department_code' => $this->department_code,
                'branch_id' => $this->branch_id,
                'description' => $this->description,
                'status' => true,
                'institution_id' => 11
            ];
            
            if ($this->editingId) {
                departmentsList::find($this->editingId)->update($data);
                session()->flash('message', 'Department updated successfully');
            } else {
                departmentsList::create($data);
                session()->flash('message', 'Department created successfully');
            }
            
            DB::commit();
            $this->closeModal();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving department: ' . $e->getMessage());
            session()->flash('error', 'Error saving department');
        }
    }

    public function delete($id)
    {
        try {
            departmentsList::findOrFail($id)->delete();
            session()->flash('message', 'Department deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting department: ' . $e->getMessage());
            session()->flash('error', 'Cannot delete department - it may be in use');
        }
    }

    public function closeModal()
    {
        $this->reset(['department_name', 'department_code', 'branch_id', 'description', 'editingId']);
        $this->showModal = false;
    }
}