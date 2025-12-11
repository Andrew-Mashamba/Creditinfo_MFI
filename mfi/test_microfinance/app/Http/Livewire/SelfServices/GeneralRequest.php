<?php

namespace App\Http\Livewire\SelfServices;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GeneralRequest extends Component
{
    use WithFileUploads;

    public $selectedTab = 'request'; // request, history, faqs
    public $requestType = '';
    public $subject = '';
    public $priority = 'normal';
    public $description = '';
    public $attachments = [];
    public $showSuccessMessage = false;
    public $requestHistory = [];
    public $faqs = [];
    public $searchFaq = '';

    protected $rules = [
        'requestType' => 'required',
        'subject' => 'required|min:5|max:100',
        'priority' => 'required|in:low,normal,high,urgent',
        'description' => 'required|min:20',
        'attachments.*' => 'nullable|file|max:5120' // 5MB max
    ];

    protected $messages = [
        'subject.min' => 'Subject must be at least 5 characters.',
        'description.min' => 'Please provide a detailed description (minimum 20 characters).',
        'attachments.*.max' => 'Each file must not exceed 5MB.'
    ];

    public function mount()
    {
        $this->loadRequestData();
    }

    public function loadRequestData()
    {
        // Sample request history
        $this->requestHistory = [
            [
                'id' => 'REQ-2025-001',
                'type' => 'IT Support',
                'subject' => 'Laptop keyboard not working',
                'submitted_date' => '2025-07-25',
                'status' => 'resolved',
                'resolved_date' => '2025-07-26',
                'priority' => 'high',
                'assigned_to' => 'IT Department'
            ],
            [
                'id' => 'REQ-2025-002',
                'type' => 'Facilities',
                'subject' => 'Air conditioning not working in conference room',
                'submitted_date' => '2025-07-20',
                'status' => 'in_progress',
                'resolved_date' => null,
                'priority' => 'normal',
                'assigned_to' => 'Facilities Team'
            ],
            [
                'id' => 'REQ-2025-003',
                'type' => 'HR Query',
                'subject' => 'Update emergency contact information',
                'submitted_date' => '2025-07-15',
                'status' => 'pending',
                'resolved_date' => null,
                'priority' => 'low',
                'assigned_to' => 'HR Department'
            ],
            [
                'id' => 'REQ-2025-004',
                'type' => 'Finance',
                'subject' => 'Reimbursement for office supplies',
                'submitted_date' => '2025-07-10',
                'status' => 'resolved',
                'resolved_date' => '2025-07-12',
                'priority' => 'normal',
                'assigned_to' => 'Finance Department'
            ]
        ];

        // Sample FAQs
        $this->faqs = [
            'HR' => [
                ['question' => 'How do I update my personal information?', 'answer' => 'You can update your personal information through the Employee Portal under Profile Settings, or submit a request to HR with the updated details.'],
                ['question' => 'What is the process for emergency leave?', 'answer' => 'For emergency leave, notify your supervisor immediately and submit a leave request within 24 hours with supporting documentation.'],
                ['question' => 'How do I get my employment verification letter?', 'answer' => 'Submit a request through this portal selecting "HR Query" and specify "Employment Verification Letter" in the subject.'],
            ],
            'IT' => [
                ['question' => 'How do I reset my password?', 'answer' => 'Click on "Forgot Password" on the login page or contact IT support for assistance.'],
                ['question' => 'What should I do if my computer is running slow?', 'answer' => 'Try restarting your computer first. If the issue persists, submit an IT support request with details about the problem.'],
                ['question' => 'How do I request new software?', 'answer' => 'Submit an IT support request with the software name, purpose, and business justification.'],
            ],
            'Finance' => [
                ['question' => 'What is the expense reimbursement process?', 'answer' => 'Submit your expense claim with original receipts within 7 days of the expense. Processing takes 5-7 business days.'],
                ['question' => 'How do I update my bank account details?', 'answer' => 'Submit a Finance request with your new bank details and attach a cancelled check or bank statement.'],
                ['question' => 'When is salary credited?', 'answer' => 'Salaries are credited on the last working day of each month.'],
            ],
            'Facilities' => [
                ['question' => 'How do I report a maintenance issue?', 'answer' => 'Submit a Facilities request with the location and description of the issue. Emergency issues should be reported immediately by phone.'],
                ['question' => 'How do I book a conference room?', 'answer' => 'Use the room booking system on the intranet or submit a Facilities request for assistance.'],
                ['question' => 'What are the office timings?', 'answer' => 'Regular office hours are 8:00 AM to 5:00 PM, Monday through Friday.'],
            ]
        ];
    }

    public function getFilteredFaqs()
    {
        if (empty($this->searchFaq)) {
            return $this->faqs;
        }

        $filtered = [];
        foreach ($this->faqs as $category => $questions) {
            $filteredQuestions = array_filter($questions, function($faq) {
                return stripos($faq['question'], $this->searchFaq) !== false || 
                       stripos($faq['answer'], $this->searchFaq) !== false;
            });
            
            if (!empty($filteredQuestions)) {
                $filtered[$category] = $filteredQuestions;
            }
        }
        
        return $filtered;
    }

    public function submitRequest()
    {
        $this->validate();

        // Handle file uploads
        $uploadedFiles = [];
        if ($this->attachments) {
            foreach ($this->attachments as $attachment) {
                $uploadedFiles[] = $attachment->store('general-requests', 'public');
            }
        }

        // Here you would save the request to the database
        $this->showSuccessMessage = true;
        
        // Reset form
        $this->reset(['requestType', 'subject', 'priority', 'description', 'attachments']);
        
        // Reload data
        $this->loadRequestData();
    }

    public function render()
    {
        return view('livewire.self-services.general-request', [
            'filteredFaqs' => $this->getFilteredFaqs()
        ]);
    }
}