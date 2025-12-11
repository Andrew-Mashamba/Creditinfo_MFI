<?php

namespace App\Http\Livewire\SelfServices;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TravelRequest extends Component
{
    use WithFileUploads;

    public $selectedTab = 'request'; // request, history, policy
    public $travelType = '';
    public $destination = '';
    public $departureDate = '';
    public $returnDate = '';
    public $purpose = '';
    public $detailedItinerary = '';
    public $estimatedBudget = '';
    public $accommodationRequired = false;
    public $transportMode = '';
    public $advanceRequired = false;
    public $advanceAmount = '';
    public $supportingDocuments = [];
    public $showSuccessMessage = false;
    public $travelHistory = [];
    public $travelPolicies = [];
    public $perDiemRates = [];

    protected $rules = [
        'travelType' => 'required',
        'destination' => 'required|min:3',
        'departureDate' => 'required|date|after_or_equal:today',
        'returnDate' => 'required|date|after:departureDate',
        'purpose' => 'required|min:20',
        'detailedItinerary' => 'required|min:50',
        'estimatedBudget' => 'required|numeric|min:0',
        'transportMode' => 'required',
        'advanceAmount' => 'required_if:advanceRequired,true|nullable|numeric|min:0'
    ];

    protected $messages = [
        'returnDate.after' => 'Return date must be after departure date.',
        'purpose.min' => 'Please provide a detailed purpose (minimum 20 characters).',
        'detailedItinerary.min' => 'Please provide a detailed itinerary (minimum 50 characters).',
        'advanceAmount.required_if' => 'Please specify the advance amount required.',
    ];

    public function mount()
    {
        $this->loadTravelData();
    }

    public function loadTravelData()
    {
        // Sample travel history
        $this->travelHistory = [
            [
                'id' => 1,
                'destination' => 'Dar es Salaam',
                'type' => 'Business Meeting',
                'departure' => '2025-06-15',
                'return' => '2025-06-18',
                'status' => 'completed',
                'approved_by' => 'John Manager',
                'total_expense' => 850000,
                'purpose' => 'Client meeting and contract negotiation'
            ],
            [
                'id' => 2,
                'destination' => 'Arusha',
                'type' => 'Training',
                'departure' => '2025-08-05',
                'return' => '2025-08-07',
                'status' => 'approved',
                'approved_by' => 'Jane Director',
                'total_expense' => 650000,
                'purpose' => 'Leadership training workshop'
            ],
            [
                'id' => 3,
                'destination' => 'Mwanza',
                'type' => 'Conference',
                'departure' => '2025-08-20',
                'return' => '2025-08-22',
                'status' => 'pending',
                'approved_by' => null,
                'total_expense' => 750000,
                'purpose' => 'Annual industry conference'
            ]
        ];

        // Travel policies
        $this->travelPolicies = [
            'advance_notice' => 'Travel requests must be submitted at least 7 days before departure.',
            'approval_hierarchy' => 'All travel requires approval from your immediate supervisor and department head.',
            'expense_limits' => 'Daily accommodation limit: TSH 150,000. Meal allowance: TSH 50,000 per day.',
            'documentation' => 'All expenses must be supported by original receipts.',
            'reporting' => 'Travel report must be submitted within 5 days of return.',
            'advance_settlement' => 'Travel advances must be settled within 7 days of return.'
        ];

        // Per diem rates by location
        $this->perDiemRates = [
            ['location' => 'Dar es Salaam', 'accommodation' => 150000, 'meals' => 50000, 'incidentals' => 20000],
            ['location' => 'Arusha/Moshi', 'accommodation' => 120000, 'meals' => 40000, 'incidentals' => 15000],
            ['location' => 'Other Urban', 'accommodation' => 100000, 'meals' => 35000, 'incidentals' => 15000],
            ['location' => 'Rural Areas', 'accommodation' => 80000, 'meals' => 30000, 'incidentals' => 10000],
            ['location' => 'International', 'accommodation' => 200000, 'meals' => 70000, 'incidentals' => 30000]
        ];
    }

    public function updatedAdvanceRequired($value)
    {
        if (!$value) {
            $this->advanceAmount = '';
        }
    }

    public function submitTravelRequest()
    {
        $this->validate();

        // Calculate trip duration
        $departure = Carbon::parse($this->departureDate);
        $return = Carbon::parse($this->returnDate);
        $tripDays = $departure->diffInDays($return) + 1;

        // Check if request is submitted with enough notice
        $daysUntilDeparture = Carbon::now()->diffInDays($departure);
        if ($daysUntilDeparture < 7) {
            $this->addError('departureDate', 'Travel requests must be submitted at least 7 days in advance.');
            return;
        }

        // Here you would save the travel request to the database
        // Handle file uploads if any
        if ($this->supportingDocuments) {
            foreach ($this->supportingDocuments as $document) {
                $path = $document->store('travel-documents', 'public');
            }
        }

        $this->showSuccessMessage = true;
        
        // Reset form
        $this->reset(['travelType', 'destination', 'departureDate', 'returnDate', 'purpose', 
                     'detailedItinerary', 'estimatedBudget', 'accommodationRequired', 
                     'transportMode', 'advanceRequired', 'advanceAmount', 'supportingDocuments']);
        
        // Reload data
        $this->loadTravelData();
    }

    public function render()
    {
        return view('livewire.self-services.travel-request');
    }
}