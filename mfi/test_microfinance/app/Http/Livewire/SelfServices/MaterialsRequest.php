<?php

namespace App\Http\Livewire\SelfServices;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MaterialsRequest extends Component
{
    public $selectedTab = 'request'; // request, history, inventory
    public $materialCategory = '';
    public $materialItem = '';
    public $quantity = '';
    public $justification = '';
    public $urgency = 'normal';
    public $requestHistory = [];
    public $availableMaterials = [];
    public $showSuccessMessage = false;
    public $selectedCategory = '';

    protected $rules = [
        'materialCategory' => 'required',
        'materialItem' => 'required',
        'quantity' => 'required|integer|min:1',
        'justification' => 'required|min:10',
        'urgency' => 'required|in:low,normal,high,urgent'
    ];

    public function mount()
    {
        $this->loadMaterialsData();
    }

    public function loadMaterialsData()
    {
        // Sample available materials by category
        $this->availableMaterials = [
            'office_supplies' => [
                ['id' => 1, 'name' => 'A4 Paper (Ream)', 'stock' => 50, 'unit' => 'reams'],
                ['id' => 2, 'name' => 'Pens (Box)', 'stock' => 30, 'unit' => 'boxes'],
                ['id' => 3, 'name' => 'Notebooks', 'stock' => 25, 'unit' => 'pieces'],
                ['id' => 4, 'name' => 'Staplers', 'stock' => 10, 'unit' => 'pieces'],
                ['id' => 5, 'name' => 'Paper Clips (Box)', 'stock' => 40, 'unit' => 'boxes'],
                ['id' => 6, 'name' => 'Folders', 'stock' => 35, 'unit' => 'pieces'],
            ],
            'it_equipment' => [
                ['id' => 7, 'name' => 'USB Flash Drive', 'stock' => 15, 'unit' => 'pieces'],
                ['id' => 8, 'name' => 'Mouse', 'stock' => 8, 'unit' => 'pieces'],
                ['id' => 9, 'name' => 'Keyboard', 'stock' => 6, 'unit' => 'pieces'],
                ['id' => 10, 'name' => 'HDMI Cable', 'stock' => 12, 'unit' => 'pieces'],
                ['id' => 11, 'name' => 'Ethernet Cable', 'stock' => 20, 'unit' => 'meters'],
            ],
            'cleaning_supplies' => [
                ['id' => 12, 'name' => 'Hand Sanitizer', 'stock' => 25, 'unit' => 'bottles'],
                ['id' => 13, 'name' => 'Tissue Paper (Box)', 'stock' => 40, 'unit' => 'boxes'],
                ['id' => 14, 'name' => 'Cleaning Cloth', 'stock' => 30, 'unit' => 'pieces'],
                ['id' => 15, 'name' => 'Disinfectant Spray', 'stock' => 15, 'unit' => 'bottles'],
            ],
            'safety_equipment' => [
                ['id' => 16, 'name' => 'Face Masks (Box)', 'stock' => 20, 'unit' => 'boxes'],
                ['id' => 17, 'name' => 'Safety Gloves', 'stock' => 25, 'unit' => 'pairs'],
                ['id' => 18, 'name' => 'First Aid Kit', 'stock' => 5, 'unit' => 'kits'],
            ]
        ];

        // Sample request history
        $this->requestHistory = [
            [
                'id' => 1,
                'category' => 'Office Supplies',
                'item' => 'A4 Paper (Ream)',
                'quantity' => 2,
                'requested_date' => '2025-07-25',
                'status' => 'approved',
                'approved_by' => 'John Manager',
                'collection_date' => '2025-07-26',
                'urgency' => 'normal'
            ],
            [
                'id' => 2,
                'category' => 'IT Equipment',
                'item' => 'USB Flash Drive',
                'quantity' => 1,
                'requested_date' => '2025-07-20',
                'status' => 'pending',
                'approved_by' => null,
                'collection_date' => null,
                'urgency' => 'high'
            ],
            [
                'id' => 3,
                'category' => 'Office Supplies',
                'item' => 'Notebooks',
                'quantity' => 5,
                'requested_date' => '2025-07-15',
                'status' => 'collected',
                'approved_by' => 'Jane Supervisor',
                'collection_date' => '2025-07-16',
                'urgency' => 'normal'
            ],
            [
                'id' => 4,
                'category' => 'Cleaning Supplies',
                'item' => 'Hand Sanitizer',
                'quantity' => 3,
                'requested_date' => '2025-07-10',
                'status' => 'rejected',
                'approved_by' => 'Mike Director',
                'collection_date' => null,
                'urgency' => 'low'
            ]
        ];
    }

    public function getFilteredMaterials()
    {
        if (!$this->materialCategory || !isset($this->availableMaterials[$this->materialCategory])) {
            return [];
        }
        return $this->availableMaterials[$this->materialCategory];
    }

    public function submitMaterialRequest()
    {
        $this->validate();

        // Here you would save the request to the database
        // For now, we'll just show a success message
        
        $this->showSuccessMessage = true;
        
        // Reset form
        $this->reset(['materialCategory', 'materialItem', 'quantity', 'justification', 'urgency']);
        
        // Reload data
        $this->loadMaterialsData();
    }

    public function render()
    {
        return view('livewire.self-services.materials-request', [
            'filteredMaterials' => $this->getFilteredMaterials()
        ]);
    }
}