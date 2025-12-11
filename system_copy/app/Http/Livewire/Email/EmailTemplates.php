<?php

namespace App\Http\Livewire\Email;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\EmailTemplateService;

class EmailTemplates extends Component
{
    public $templates = [];
    public $categories = [];
    public $selectedCategory = 'all';
    public $searchTerm = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showPreviewModal = false;
    public $editingTemplate = null;
    
    // Form fields
    public $templateName = '';
    public $templateCategory = 'general';
    public $templateDescription = '';
    public $templateSubject = '';
    public $templateBody = '';
    public $templateShared = false;
    
    // Preview fields
    public $previewTemplate = null;
    public $previewVariables = [];
    public $previewSubject = '';
    public $previewBody = '';
    
    protected $templateService;
    
    protected $rules = [
        'templateName' => 'required|min:3|max:255',
        'templateCategory' => 'required',
        'templateSubject' => 'required|max:255',
        'templateBody' => 'required'
    ];
    
    protected $listeners = ['refreshTemplates' => 'loadTemplates'];
    
    public function mount()
    {
        $this->templateService = new EmailTemplateService();
        $this->categories = $this->templateService->getCategories();
        $this->loadTemplates();
    }
    
    public function loadTemplates()
    {
        if ($this->searchTerm) {
            $this->templates = $this->templateService->searchTemplates(
                Auth::id(),
                $this->searchTerm
            );
        } elseif ($this->selectedCategory && $this->selectedCategory !== 'all') {
            $this->templates = $this->templateService->getTemplatesByCategory(
                Auth::id(),
                $this->selectedCategory
            );
        } else {
            $this->templates = $this->templateService->getUserTemplates(
                Auth::id(),
                true
            );
        }
    }
    
    public function updatedSearchTerm()
    {
        $this->loadTemplates();
    }
    
    public function updatedSelectedCategory()
    {
        $this->loadTemplates();
    }
    
    public function showCreate()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }
    
    public function createTemplate()
    {
        $this->validate();
        
        $result = $this->templateService->createTemplate(Auth::id(), [
            'name' => $this->templateName,
            'category' => $this->templateCategory,
            'description' => $this->templateDescription,
            'subject' => $this->templateSubject,
            'body' => $this->templateBody,
            'is_shared' => $this->templateShared
        ]);
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->showCreateModal = false;
            $this->resetForm();
            $this->loadTemplates();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function editTemplate($templateId)
    {
        $template = collect($this->templates)->firstWhere('id', $templateId);
        if ($template) {
            $this->editingTemplate = $template;
            $this->templateName = $template->name;
            $this->templateCategory = $template->category;
            $this->templateDescription = $template->description;
            $this->templateSubject = $template->subject;
            $this->templateBody = $template->body;
            $this->templateShared = $template->is_shared;
            $this->showEditModal = true;
        }
    }
    
    public function updateTemplate()
    {
        $this->validate();
        
        $result = $this->templateService->updateTemplate(
            $this->editingTemplate->id,
            Auth::id(),
            [
                'name' => $this->templateName,
                'category' => $this->templateCategory,
                'description' => $this->templateDescription,
                'subject' => $this->templateSubject,
                'body' => $this->templateBody,
                'is_shared' => $this->templateShared
            ]
        );
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->showEditModal = false;
            $this->resetForm();
            $this->loadTemplates();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function deleteTemplate($templateId)
    {
        if (confirm('Are you sure you want to delete this template?')) {
            $result = $this->templateService->deleteTemplate($templateId, Auth::id());
            
            if ($result['success']) {
                session()->flash('message', $result['message']);
                $this->loadTemplates();
            } else {
                session()->flash('error', $result['message']);
            }
        }
    }
    
    public function cloneTemplate($templateId)
    {
        $result = $this->templateService->cloneTemplate($templateId, Auth::id());
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->loadTemplates();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function previewTemplate($templateId)
    {
        $template = collect($this->templates)->firstWhere('id', $templateId);
        if ($template) {
            $this->previewTemplate = $template;
            
            // Extract variables and initialize with sample values
            $variables = json_decode($template->variables, true) ?? [];
            $this->previewVariables = [];
            
            foreach ($variables as $var) {
                $this->previewVariables[$var] = $this->getSampleValue($var);
            }
            
            $this->updatePreview();
            $this->showPreviewModal = true;
        }
    }
    
    public function updatePreview()
    {
        if ($this->previewTemplate) {
            // Apply variables to template
            $this->previewSubject = $this->previewTemplate->subject;
            $this->previewBody = $this->previewTemplate->body;
            
            foreach ($this->previewVariables as $key => $value) {
                $this->previewSubject = str_replace('{{' . $key . '}}', $value, $this->previewSubject);
                $this->previewBody = str_replace('{{' . $key . '}}', $value, $this->previewBody);
            }
        }
    }
    
    public function useTemplate($templateId)
    {
        // Emit event to parent component (email compose) with template data
        $result = $this->templateService->useTemplate($templateId, Auth::id(), []);
        
        if ($result['success']) {
            $this->emit('templateSelected', [
                'subject' => $result['subject'],
                'body' => $result['body'],
                'template' => $result['template']
            ]);
            $this->dispatchBrowserEvent('template-selected');
            session()->flash('message', 'Template loaded into compose form');
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function createDefaultTemplates()
    {
        $this->templateService->createDefaultTemplates(Auth::id());
        session()->flash('message', 'Default templates created successfully');
        $this->loadTemplates();
    }
    
    protected function resetForm()
    {
        $this->templateName = '';
        $this->templateCategory = 'general';
        $this->templateDescription = '';
        $this->templateSubject = '';
        $this->templateBody = '';
        $this->templateShared = false;
        $this->editingTemplate = null;
    }
    
    protected function getSampleValue($variable)
    {
        $samples = [
            'recipient_name' => 'John Doe',
            'sender_name' => Auth::user()->name ?? 'Your Name',
            'company_name' => 'SACCOS Ltd',
            'sender_title' => 'Manager',
            'meeting_topic' => 'Q4 Planning',
            'option_1' => 'Monday 2:00 PM',
            'option_2' => 'Tuesday 3:00 PM',
            'option_3' => 'Wednesday 10:00 AM',
            'topic' => 'Project Update',
            'follow_up_content' => 'As discussed, here are the next steps...',
            'reason' => 'your assistance',
            'additional_message' => 'Your support made a real difference.',
            'newsletter_title' => 'Monthly Newsletter',
            'month' => date('F'),
            'year' => date('Y'),
            'content' => 'Exciting updates and news...'
        ];
        
        return $samples[$variable] ?? '[' . $variable . ']';
    }
    
    public function render()
    {
        return view('livewire.email.email-templates');
    }
}