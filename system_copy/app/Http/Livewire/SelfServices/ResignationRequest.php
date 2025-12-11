<?php

namespace App\Http\Livewire\SelfServices;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ResignationRequest extends Component
{
    use WithFileUploads;

    public $selectedTab = 'request'; // request, status, guidelines
    public $resignationDate = '';
    public $lastWorkingDate = '';
    public $reason = '';
    public $detailedReason = '';
    public $handoverNotes = '';
    public $resignationLetter;
    public $showSuccessMessage = false;
    public $currentStatus = null;
    public $guidelines = [];
    public $exitChecklist = [];
    public $withdrawRequest = false;

    protected $rules = [
        'resignationDate' => 'required|date|after_or_equal:today',
        'lastWorkingDate' => 'required|date|after:resignationDate',
        'reason' => 'required',
        'detailedReason' => 'required|min:50',
        'handoverNotes' => 'required|min:20',
        'resignationLetter' => 'required|file|mimes:pdf,doc,docx|max:2048'
    ];

    protected $messages = [
        'lastWorkingDate.after' => 'Last working date must be after resignation submission date.',
        'detailedReason.min' => 'Please provide a detailed explanation (minimum 50 characters).',
        'resignationLetter.required' => 'Please upload your resignation letter.',
        'resignationLetter.mimes' => 'Resignation letter must be a PDF or Word document.',
    ];

    public function mount()
    {
        $this->loadResignationData();
    }

    public function loadResignationData()
    {
        // Load current resignation status if exists
        $this->currentStatus = [
            'status' => 'draft', // draft, submitted, under_review, approved, withdrawn
            'submitted_date' => null,
            'notice_period' => 30,
            'approval_status' => null,
            'exit_interview_date' => null,
            'clearance_status' => 'pending'
        ];

        // Exit process guidelines
        $this->guidelines = [
            'notice_period' => 'A minimum notice period of 30 days is required as per company policy.',
            'handover' => 'You must complete all pending work and provide comprehensive handover documentation.',
            'company_property' => 'All company property including ID cards, access cards, laptops, and other equipment must be returned.',
            'clearance' => 'Clearance from all departments (IT, Finance, HR, Admin) is mandatory.',
            'final_settlement' => 'Final settlement will be processed after successful completion of exit formalities.',
            'experience_letter' => 'Experience letter and relieving letter will be issued on the last working day.'
        ];

        // Exit checklist items
        $this->exitChecklist = [
            ['item' => 'Submit resignation letter', 'status' => false, 'department' => 'HR'],
            ['item' => 'Complete knowledge transfer', 'status' => false, 'department' => 'Reporting Manager'],
            ['item' => 'Return company laptop', 'status' => false, 'department' => 'IT'],
            ['item' => 'Return access cards', 'status' => false, 'department' => 'Admin'],
            ['item' => 'Clear pending expenses', 'status' => false, 'department' => 'Finance'],
            ['item' => 'Exit interview', 'status' => false, 'department' => 'HR'],
            ['item' => 'Collect relieving letter', 'status' => false, 'department' => 'HR']
        ];
    }

    public function submitResignation()
    {
        $this->validate();

        // Calculate notice period
        $resignDate = Carbon::parse($this->resignationDate);
        $lastDate = Carbon::parse($this->lastWorkingDate);
        $noticeDays = $resignDate->diffInDays($lastDate);

        if ($noticeDays < 30) {
            $this->addError('lastWorkingDate', 'Minimum 30 days notice period is required.');
            return;
        }

        // Here you would save the resignation request to the database
        // Store the uploaded file
        if ($this->resignationLetter) {
            $path = $this->resignationLetter->store('resignations', 'public');
        }

        $this->showSuccessMessage = true;
        $this->currentStatus['status'] = 'submitted';
        $this->currentStatus['submitted_date'] = now()->format('Y-m-d');
        
        // Reset form
        $this->reset(['resignationDate', 'lastWorkingDate', 'reason', 'detailedReason', 'handoverNotes', 'resignationLetter']);
    }

    public function withdrawResignation()
    {
        if ($this->currentStatus['status'] === 'submitted' || $this->currentStatus['status'] === 'under_review') {
            $this->withdrawRequest = true;
        }
    }

    public function confirmWithdraw()
    {
        // Process withdrawal
        $this->currentStatus['status'] = 'withdrawn';
        $this->withdrawRequest = false;
        session()->flash('message', 'Your resignation has been withdrawn successfully.');
    }

    public function render()
    {
        return view('livewire.self-services.resignation-request');
    }
}