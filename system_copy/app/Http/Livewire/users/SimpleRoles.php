<?php

namespace App\Http\Livewire\Users;

use App\Models\Role;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimpleRoles extends Component
{
    use WithPagination;

    // Basic properties
    public $showModal = false;
    public $editingId = null;
    public $search = '';
    
    // Form fields
    public $name = '';
    public $department_id = '';
    public $description = '';
    
    // Simple validation rules
    protected $rules = [
        'name' => 'required|min:3',
        'department_id' => 'required|exists:departments,id',
        'description' => 'nullable|max:500'
    ];

    public function render()
    {
        $roles = Role::with('department')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);
            
        $departments = Department::where('status', true)->get();
        
        return view('livewire.users.simple-roles', [
            'roles' => $roles,
            'departments' => $departments
        ]);
    }

    public function create()
    {
        $this->reset(['name', 'department_id', 'description', 'editingId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->editingId = $id;
        $this->name = $role->name;
        $this->department_id = $role->department_id;
        $this->description = $role->description;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            $data = [
                'name' => $this->name,
                'department_id' => $this->department_id,
                'description' => $this->description,
                'status' => 'ACTIVE'
            ];
            
            if ($this->editingId) {
                Role::find($this->editingId)->update($data);
                session()->flash('message', 'Role updated successfully');
            } else {
                Role::create($data);
                session()->flash('message', 'Role created successfully');
            }
            
            DB::commit();
            $this->closeModal();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving role: ' . $e->getMessage());
            session()->flash('error', 'Error saving role');
        }
    }

    public function delete($id)
    {
        try {
            Role::findOrFail($id)->delete();
            session()->flash('message', 'Role deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting role: ' . $e->getMessage());
            session()->flash('error', 'Cannot delete role - it may be in use');
        }
    }

    public function closeModal()
    {
        $this->reset(['name', 'department_id', 'description', 'editingId']);
        $this->showModal = false;
    }
}