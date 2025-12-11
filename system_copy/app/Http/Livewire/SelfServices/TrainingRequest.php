<?php

namespace App\Http\Livewire\SelfServices;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrainingRequest extends Component
{
    use WithFileUploads;

    public $selectedTab = 'request'; // request, history, catalog
    public $trainingType = '';
    public $trainingTitle = '';
    public $provider = '';
    public $startDate = '';
    public $endDate = '';
    public $duration = '';
    public $cost = '';
    public $location = '';
    public $deliveryMode = '';
    public $objectives = '';
    public $benefits = '';
    public $justification = '';
    public $trainingBrochure;
    public $showSuccessMessage = false;
    public $trainingHistory = [];
    public $trainingCatalog = [];
    public $certifications = [];

    protected $rules = [
        'trainingType' => 'required',
        'trainingTitle' => 'required|min:5',
        'provider' => 'required|min:3',
        'startDate' => 'required|date|after_or_equal:today',
        'endDate' => 'required|date|after_or_equal:startDate',
        'duration' => 'required',
        'cost' => 'required|numeric|min:0',
        'location' => 'required',
        'deliveryMode' => 'required',
        'objectives' => 'required|min:30',
        'benefits' => 'required|min:30',
        'justification' => 'required|min:50'
    ];

    protected $messages = [
        'objectives.min' => 'Please provide detailed training objectives (minimum 30 characters).',
        'benefits.min' => 'Please explain how this training will benefit your role (minimum 30 characters).',
        'justification.min' => 'Please provide a detailed justification (minimum 50 characters).',
    ];

    public function mount()
    {
        $this->loadTrainingData();
    }

    public function loadTrainingData()
    {
        // Sample training history
        $this->trainingHistory = [
            [
                'id' => 1,
                'title' => 'Project Management Professional (PMP)',
                'provider' => 'PMI Tanzania Chapter',
                'dates' => '2025-03-15 to 2025-03-20',
                'status' => 'completed',
                'cost' => 1500000,
                'certificate' => true,
                'rating' => 5
            ],
            [
                'id' => 2,
                'title' => 'Advanced Excel for Financial Analysis',
                'provider' => 'Tech Academy',
                'dates' => '2025-08-10 to 2025-08-12',
                'status' => 'approved',
                'cost' => 450000,
                'certificate' => false,
                'rating' => null
            ],
            [
                'id' => 3,
                'title' => 'Leadership Development Program',
                'provider' => 'Management Institute',
                'dates' => '2025-09-01 to 2025-09-05',
                'status' => 'pending',
                'cost' => 850000,
                'certificate' => true,
                'rating' => null
            ]
        ];

        // Sample training catalog
        $this->trainingCatalog = [
            'technical' => [
                ['title' => 'Python Programming', 'duration' => '5 days', 'level' => 'Beginner', 'cost' => 600000],
                ['title' => 'Data Analysis with R', 'duration' => '3 days', 'level' => 'Intermediate', 'cost' => 450000],
                ['title' => 'Cloud Computing Basics', 'duration' => '4 days', 'level' => 'Beginner', 'cost' => 700000],
                ['title' => 'Cybersecurity Fundamentals', 'duration' => '3 days', 'level' => 'Beginner', 'cost' => 550000]
            ],
            'soft_skills' => [
                ['title' => 'Effective Communication', 'duration' => '2 days', 'level' => 'All Levels', 'cost' => 300000],
                ['title' => 'Time Management', 'duration' => '1 day', 'level' => 'All Levels', 'cost' => 200000],
                ['title' => 'Conflict Resolution', 'duration' => '2 days', 'level' => 'Intermediate', 'cost' => 350000],
                ['title' => 'Presentation Skills', 'duration' => '2 days', 'level' => 'All Levels', 'cost' => 300000]
            ],
            'management' => [
                ['title' => 'Strategic Planning', 'duration' => '3 days', 'level' => 'Advanced', 'cost' => 800000],
                ['title' => 'Performance Management', 'duration' => '2 days', 'level' => 'Intermediate', 'cost' => 500000],
                ['title' => 'Change Management', 'duration' => '3 days', 'level' => 'Advanced', 'cost' => 750000],
                ['title' => 'Risk Management', 'duration' => '4 days', 'level' => 'Intermediate', 'cost' => 900000]
            ]
        ];

        // My certifications
        $this->certifications = [
            ['name' => 'PMP', 'issuer' => 'PMI', 'date' => '2025-03-20', 'expiry' => '2028-03-20', 'status' => 'active'],
            ['name' => 'ITIL Foundation', 'issuer' => 'AXELOS', 'date' => '2024-06-15', 'expiry' => '2027-06-15', 'status' => 'active'],
            ['name' => 'Six Sigma Green Belt', 'issuer' => 'ASQ', 'date' => '2023-09-10', 'expiry' => '2026-09-10', 'status' => 'active']
        ];
    }

    public function submitTrainingRequest()
    {
        $this->validate();

        // Check if request is submitted with enough notice (14 days)
        $start = Carbon::parse($this->startDate);
        $daysUntilStart = Carbon::now()->diffInDays($start);
        
        if ($daysUntilStart < 14) {
            $this->addError('startDate', 'Training requests must be submitted at least 14 days in advance.');
            return;
        }

        // Handle file upload if any
        if ($this->trainingBrochure) {
            $path = $this->trainingBrochure->store('training-brochures', 'public');
        }

        $this->showSuccessMessage = true;
        
        // Reset form
        $this->reset(['trainingType', 'trainingTitle', 'provider', 'startDate', 'endDate', 
                     'duration', 'cost', 'location', 'deliveryMode', 'objectives', 
                     'benefits', 'justification', 'trainingBrochure']);
        
        // Reload data
        $this->loadTrainingData();
    }

    public function render()
    {
        return view('livewire.self-services.training-request');
    }
}