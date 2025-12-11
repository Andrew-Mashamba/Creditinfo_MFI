<?php

namespace App\Http\Livewire\Approvals;

use Livewire\Component;
use App\Models\ApprovalMatrixConfig;
use App\Models\Role;
use App\Models\SubRole;

class ApprovalMatrixManager extends Component
{
    public $processType;
    public $processName;
    public $processCode;
    public $level;
    public $approverRole;
    public $approverSubRole;
    public $minAmount;
    public $maxAmount;
    public $isActive = true;
    public $additionalConditions = [];
    public $editingId;
    public $showForm = false;

    protected $rules = [
        'processType' => 'required|string',
        'processName' => 'required|string',
        'processCode' => 'required|string',
        'level' => 'required|integer|min:1',
        'approverRole' => 'required|string',
        'approverSubRole' => 'nullable|string',
        'minAmount' => 'nullable|numeric|min:0',
        'maxAmount' => 'nullable|numeric|min:0|gt:minAmount',
        'isActive' => 'boolean',
        'additionalConditions' => 'nullable|array'
    ];

    public function render()
    {
        $configs = ApprovalMatrixConfig::orderBy('process_type')
            ->orderBy('level')
            ->get();
        
        $roles = Role::all();
        $subRoles = SubRole::all();

        return view('livewire.approvals.approval-matrix-manager', [
            'configs' => $configs,
            'roles' => $roles,
            'subRoles' => $subRoles
        ]);
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $config = ApprovalMatrixConfig::findOrFail($id);
        $this->editingId = $id;
        $this->processType = $config->process_type;
        $this->processName = $config->process_name;
        $this->processCode = $config->process_code;
        $this->level = $config->level;
        $this->approverRole = $config->approver_role;
        $this->approverSubRole = $config->approver_sub_role;
        $this->minAmount = $config->min_amount;
        $this->maxAmount = $config->max_amount;
        $this->isActive = $config->is_active;
        $this->additionalConditions = $config->additional_conditions;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'process_type' => $this->processType,
            'process_name' => $this->processName,
            'process_code' => $this->processCode,
            'level' => $this->level,
            'approver_role' => $this->approverRole,
            'approver_sub_role' => $this->approverSubRole,
            'min_amount' => $this->minAmount,
            'max_amount' => $this->maxAmount,
            'is_active' => $this->isActive,
            'additional_conditions' => $this->additionalConditions
        ];

        if ($this->editingId) {
            ApprovalMatrixConfig::find($this->editingId)->update($data);
            $this->emit('notify', ['type' => 'success', 'message' => 'Approval matrix updated successfully']);
        } else {
            ApprovalMatrixConfig::create($data);
            $this->emit('notify', ['type' => 'success', 'message' => 'Approval matrix created successfully']);
        }

        $this->reset();
        $this->showForm = false;
    }

    public function delete($id)
    {
        ApprovalMatrixConfig::find($id)->delete();
        $this->emit('notify', ['type' => 'success', 'message' => 'Approval matrix deleted successfully']);
    }

    public function toggleStatus($id)
    {
        $config = ApprovalMatrixConfig::find($id);
        $config->is_active = !$config->is_active;
        $config->save();
        $this->emit('notify', ['type' => 'success', 'message' => 'Status updated successfully']);
    }
} 