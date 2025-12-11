<?php

namespace App\Http\Livewire\Email;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\EmailSignatureService;

class EmailSignatures extends Component
{
    public $signatures = [];
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showPreviewModal = false;
    public $editingSignature = null;
    
    // Form fields
    public $signatureName = '';
    public $signatureContent = '';
    public $isDefault = false;
    public $includeInReplies = true;
    public $includeInForwards = true;
    
    // Preview
    public $previewSignature = null;
    public $previewVariables = [];
    
    // Editor mode
    public $editorMode = 'visual'; // 'visual' or 'html'
    
    protected $signatureService;
    
    protected $rules = [
        'signatureName' => 'required|min:3|max:255',
        'signatureContent' => 'required'
    ];
    
    protected $listeners = ['refreshSignatures' => 'loadSignatures'];
    
    public function mount()
    {
        $this->signatureService = new EmailSignatureService();
        $this->loadSignatures();
        $this->initializeVariables();
    }
    
    public function loadSignatures()
    {
        $this->signatures = $this->signatureService->getUserSignatures(Auth::id());
    }
    
    public function initializeVariables()
    {
        $user = Auth::user();
        $this->previewVariables = [
            'name' => $user->name ?? 'Your Name',
            'title' => 'Your Title',
            'company' => 'SACCOS Ltd',
            'email' => $user->email ?? 'email@example.com',
            'phone' => '+1 234 567 8900',
            'address' => '123 Main Street, City, Country',
            'website' => 'www.saccos.com',
            'linkedin' => 'linkedin.com/in/yourprofile',
            'twitter' => '@yourhandle'
        ];
    }
    
    public function showCreate()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }
    
    public function createSignature()
    {
        $this->validate();
        
        $result = $this->signatureService->createSignature(Auth::id(), [
            'name' => $this->signatureName,
            'content' => $this->signatureContent,
            'is_default' => $this->isDefault,
            'include_in_replies' => $this->includeInReplies,
            'include_in_forwards' => $this->includeInForwards
        ]);
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->showCreateModal = false;
            $this->resetForm();
            $this->loadSignatures();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function editSignature($signatureId)
    {
        $signature = collect($this->signatures)->firstWhere('id', $signatureId);
        if ($signature) {
            $this->editingSignature = $signature;
            $this->signatureName = $signature->name;
            $this->signatureContent = $signature->content;
            $this->isDefault = $signature->is_default;
            $this->includeInReplies = $signature->include_in_replies;
            $this->includeInForwards = $signature->include_in_forwards;
            $this->showEditModal = true;
        }
    }
    
    public function updateSignature()
    {
        $this->validate();
        
        $result = $this->signatureService->updateSignature(
            $this->editingSignature->id,
            Auth::id(),
            [
                'name' => $this->signatureName,
                'content' => $this->signatureContent,
                'is_default' => $this->isDefault,
                'include_in_replies' => $this->includeInReplies,
                'include_in_forwards' => $this->includeInForwards
            ]
        );
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->showEditModal = false;
            $this->resetForm();
            $this->loadSignatures();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function deleteSignature($signatureId)
    {
        if (confirm('Are you sure you want to delete this signature?')) {
            $result = $this->signatureService->deleteSignature($signatureId, Auth::id());
            
            if ($result['success']) {
                session()->flash('message', $result['message']);
                $this->loadSignatures();
            } else {
                session()->flash('error', $result['message']);
            }
        }
    }
    
    public function setDefault($signatureId)
    {
        $result = $this->signatureService->setDefaultSignature($signatureId, Auth::id());
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->loadSignatures();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function previewSignature($signatureId)
    {
        $signature = collect($this->signatures)->firstWhere('id', $signatureId);
        if ($signature) {
            $this->previewSignature = $signature;
            $this->showPreviewModal = true;
        }
    }
    
    public function useSignature($signatureId)
    {
        $signature = collect($this->signatures)->firstWhere('id', $signatureId);
        if ($signature) {
            $this->emit('signatureSelected', [
                'id' => $signature->id,
                'content' => $signature->content,
                'signature' => $signature
            ]);
            session()->flash('message', 'Signature selected');
        }
    }
    
    public function createDefaultSignatures()
    {
        $this->signatureService->createDefaultSignatures(Auth::id());
        session()->flash('message', 'Default signatures created successfully');
        $this->loadSignatures();
    }
    
    public function insertVariable($variable)
    {
        $this->signatureContent .= '{{' . $variable . '}}';
    }
    
    public function switchEditorMode($mode)
    {
        $this->editorMode = $mode;
    }
    
    protected function resetForm()
    {
        $this->signatureName = '';
        $this->signatureContent = '';
        $this->isDefault = false;
        $this->includeInReplies = true;
        $this->includeInForwards = true;
        $this->editingSignature = null;
        $this->editorMode = 'visual';
    }
    
    public function getPreviewContent()
    {
        if (!$this->previewSignature) {
            return '';
        }
        
        $content = $this->previewSignature->content;
        foreach ($this->previewVariables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }
    
    public function render()
    {
        return view('livewire.email.email-signatures');
    }
}