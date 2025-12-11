<?php

namespace App\Http\Livewire\Email;

use Livewire\Component;
use App\Services\EmailRulesService;
use Illuminate\Support\Facades\Auth;

class EmailRules extends Component
{
    public $emailRules = [];
    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingRule = null;
    
    // Form fields
    public $ruleName = '';
    public $ruleDescription = '';
    public $isActive = true;
    public $priority = 0;
    public $conditionLogic = 'all';
    public $conditions = [];
    public $actions = [];
    
    // Available options
    public $availableConditions = [];
    public $availableActions = [];
    public $ruleTemplates = [];
    
    protected $listeners = ['refreshRules' => 'loadRules'];
    
    protected $rules = [
        'ruleName' => 'required|string|max:255',
        'ruleDescription' => 'nullable|string|max:500',
        'priority' => 'integer|min:0|max:100',
        'conditions' => 'required|array|min:1',
        'actions' => 'required|array|min:1',
    ];
    
    public function mount()
    {
        $this->loadRules();
        $this->loadOptions();
        $this->addCondition();
        $this->addAction();
    }
    
    public function loadRules()
    {
        $rulesService = new EmailRulesService();
        $this->emailRules = $rulesService->getUserRules(Auth::id());
    }
    
    public function loadOptions()
    {
        $rulesService = new EmailRulesService();
        $this->availableConditions = $rulesService->getAvailableConditions();
        $this->availableActions = $rulesService->getAvailableActions();
        $this->ruleTemplates = $rulesService->getRuleTemplates();
    }
    
    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }
    
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }
    
    public function openEditModal($ruleId)
    {
        $rule = collect($this->emailRules)->firstWhere('id', $ruleId);
        if ($rule) {
            $this->editingRule = $rule;
            $this->ruleName = $rule->name;
            $this->ruleDescription = $rule->description;
            $this->isActive = $rule->is_active;
            $this->priority = $rule->priority;
            $this->conditionLogic = $rule->condition_logic;
            $this->conditions = $rule->conditions;
            $this->actions = $rule->actions;
            $this->showEditModal = true;
        }
    }
    
    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetForm();
    }
    
    public function addCondition()
    {
        $this->conditions[] = [
            'type' => 'from_contains',
            'value' => ''
        ];
    }
    
    public function removeCondition($index)
    {
        unset($this->conditions[$index]);
        $this->conditions = array_values($this->conditions);
    }
    
    public function addAction()
    {
        $this->actions[] = [
            'type' => 'move_to_folder',
            'value' => 'inbox'
        ];
    }
    
    public function removeAction($index)
    {
        unset($this->actions[$index]);
        $this->actions = array_values($this->actions);
    }
    
    public function createRule()
    {
        $this->validate();
        
        $rulesService = new EmailRulesService();
        $result = $rulesService->createRule(Auth::id(), [
            'name' => $this->ruleName,
            'description' => $this->ruleDescription,
            'is_active' => $this->isActive,
            'priority' => $this->priority,
            'condition_logic' => $this->conditionLogic,
            'conditions' => $this->conditions,
            'actions' => $this->actions
        ]);
        
        if ($result['success']) {
            session()->flash('message', 'Rule created successfully');
            $this->closeCreateModal();
            $this->loadRules();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function updateRule()
    {
        $this->validate();
        
        $rulesService = new EmailRulesService();
        $result = $rulesService->updateRule($this->editingRule->id, Auth::id(), [
            'name' => $this->ruleName,
            'description' => $this->ruleDescription,
            'is_active' => $this->isActive,
            'priority' => $this->priority,
            'condition_logic' => $this->conditionLogic,
            'conditions' => $this->conditions,
            'actions' => $this->actions
        ]);
        
        if ($result['success']) {
            session()->flash('message', 'Rule updated successfully');
            $this->closeEditModal();
            $this->loadRules();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function deleteRule($ruleId)
    {
        if (confirm('Are you sure you want to delete this rule?')) {
            $rulesService = new EmailRulesService();
            $result = $rulesService->deleteRule($ruleId, Auth::id());
            
            if ($result['success']) {
                session()->flash('message', 'Rule deleted successfully');
                $this->loadRules();
            } else {
                session()->flash('error', $result['message']);
            }
        }
    }
    
    public function toggleRule($ruleId)
    {
        $rule = collect($this->emailRules)->firstWhere('id', $ruleId);
        if ($rule) {
            $rulesService = new EmailRulesService();
            $result = $rulesService->updateRule($ruleId, Auth::id(), [
                'name' => $rule->name,
                'description' => $rule->description,
                'is_active' => !$rule->is_active,
                'priority' => $rule->priority,
                'condition_logic' => $rule->condition_logic,
                'conditions' => $rule->conditions,
                'actions' => $rule->actions
            ]);
            
            if ($result['success']) {
                $this->loadRules();
            }
        }
    }
    
    public function applyTemplate($templateIndex)
    {
        if (isset($this->ruleTemplates[$templateIndex])) {
            $template = $this->ruleTemplates[$templateIndex];
            $this->ruleName = $template['name'];
            $this->ruleDescription = $template['description'];
            $this->conditionLogic = $template['condition_logic'];
            $this->conditions = $template['conditions'];
            $this->actions = $template['actions'];
        }
    }
    
    public function processAllEmails()
    {
        $rulesService = new EmailRulesService();
        $result = $rulesService->processNewEmails(Auth::id());
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    protected function resetForm()
    {
        $this->ruleName = '';
        $this->ruleDescription = '';
        $this->isActive = true;
        $this->priority = 0;
        $this->conditionLogic = 'all';
        $this->conditions = [];
        $this->actions = [];
        $this->editingRule = null;
        $this->addCondition();
        $this->addAction();
    }
    
    public function render()
    {
        return view('livewire.email.email-rules');
    }
}