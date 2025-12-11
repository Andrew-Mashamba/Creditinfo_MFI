<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Communication extends Component
{
    use WithFileUploads, WithPagination;

    // Modal properties
    public $showUploadModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;
    public $selectedCommunication = null;
    public $uploadedFile;
    public $communicationType = '';
    public $subject = '';
    public $referenceNumber = '';
    public $department = '';
    public $fromWhom = '';
    public $toWhom = '';
    public $description = '';
    public $priority = 'normal';
    public $status = 'pending';
    public $dateReceived = '';
    public $dateSent = '';
    public $searchTerm = '';
    public $selectedType = '';
    public $selectedDepartment = '';
    public $selectedStatus = '';

    // Communication types
    public $communicationTypes = [
        'incoming' => 'Incoming Communication',
        'outgoing' => 'Outgoing Communication'
    ];

    // Departments
    public $departments = [
        'management' => 'Management',
        'finance' => 'Finance',
        'operations' => 'Operations',
        'hr' => 'Human Resources',
        'it' => 'Information Technology',
        'marketing' => 'Marketing',
        'legal' => 'Legal',
        'compliance' => 'Compliance',
        'customer_service' => 'Customer Service',
        'other' => 'Other'
    ];

    // Priorities
    public $priorities = [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent'
    ];

    // Statuses
    public $statuses = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'archived' => 'Archived'
    ];

    protected $rules = [
        'uploadedFile' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
        'communicationType' => 'required|in:incoming,outgoing',
        'subject' => 'required|min:3|max:255',
        'referenceNumber' => 'required|min:3|max:100',
        'department' => 'required',
        'fromWhom' => 'required|min:2|max:255',
        'toWhom' => 'required|min:2|max:255',
        'description' => 'nullable|max:1000',
        'priority' => 'required|in:low,normal,high,urgent',
        'status' => 'required|in:pending,in_progress,completed,archived',
        'dateReceived' => 'required_if:communicationType,incoming|date',
        'dateSent' => 'required_if:communicationType,outgoing|date'
    ];

    public function mount()
    {
        // Create communications directory if it doesn't exist
        if (!Storage::disk('public')->exists('communications')) {
            Storage::disk('public')->makeDirectory('communications');
        }
    }

    public function openUploadModal()
    {
        $this->resetForm();
        $this->showUploadModal = true;
    }

    public function closeUploadModal()
    {
        $this->showUploadModal = false;
        $this->resetForm();
    }

    public function openViewModal($communicationId)
    {
        $this->selectedCommunication = $this->getCommunicationById($communicationId);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedCommunication = null;
    }

    public function openDeleteModal($communicationId)
    {
        $this->selectedCommunication = $this->getCommunicationById($communicationId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedCommunication = null;
    }

    public function resetForm()
    {
        $this->uploadedFile = null;
        $this->communicationType = '';
        $this->subject = '';
        $this->referenceNumber = '';
        $this->department = '';
        $this->fromWhom = '';
        $this->toWhom = '';
        $this->description = '';
        $this->priority = 'normal';
        $this->status = 'pending';
        $this->dateReceived = '';
        $this->dateSent = '';
        $this->resetValidation();
    }

    public function updatedCommunicationType()
    {
        if ($this->communicationType === 'incoming') {
            $this->dateSent = '';
        } else {
            $this->dateReceived = '';
        }
    }

    public function uploadCommunication()
    {
        $this->validate();

        try {
            $fileName = time() . '_' . Str::slug($this->subject) . '.' . $this->uploadedFile->getClientOriginalExtension();
            $filePath = $this->uploadedFile->storeAs('communications', $fileName, 'public');

            // Save communication metadata
            $communicationData = [
                'id' => Str::uuid(),
                'type' => $this->communicationType,
                'subject' => $this->subject,
                'reference_number' => $this->referenceNumber,
                'department' => $this->department,
                'from_whom' => $this->fromWhom,
                'to_whom' => $this->toWhom,
                'description' => $this->description,
                'priority' => $this->priority,
                'status' => $this->status,
                'date_received' => $this->dateReceived,
                'date_sent' => $this->dateSent,
                'filename' => $fileName,
                'filepath' => $filePath,
                'original_name' => $this->uploadedFile->getClientOriginalName(),
                'file_size' => $this->uploadedFile->getSize(),
                'file_type' => $this->uploadedFile->getMimeType(),
                'uploaded_at' => now()->toISOString(),
                'uploaded_by' => auth()->id() ?? 'system'
            ];

            $this->saveCommunicationData($communicationData);

            session()->flash('success', 'Communication uploaded successfully!');
            $this->closeUploadModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Error uploading communication: ' . $e->getMessage());
        }
    }

    public function downloadCommunication($communicationId)
    {
        try {
            $communication = $this->getCommunicationById($communicationId);
            
            if ($communication && Storage::disk('public')->exists($communication['filepath'])) {
                $this->dispatchBrowserEvent('download-file', [
                    'url' => asset('storage/' . $communication['filepath']),
                    'filename' => $communication['original_name']
                ]);
            } else {
                session()->flash('error', 'Communication file not found.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error downloading communication: ' . $e->getMessage());
        }
    }

    public function updateStatus($communicationId, $newStatus)
    {
        try {
            $this->updateCommunicationStatus($communicationId, $newStatus);
            session()->flash('success', 'Status updated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating status: ' . $e->getMessage());
        }
    }

    public function deleteCommunication()
    {
        try {
            if ($this->selectedCommunication && Storage::disk('public')->exists($this->selectedCommunication['filepath'])) {
                Storage::disk('public')->delete($this->selectedCommunication['filepath']);
                $this->removeCommunicationData($this->selectedCommunication['id']);
                session()->flash('success', 'Communication deleted successfully!');
            } else {
                session()->flash('error', 'Communication file not found.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting communication: ' . $e->getMessage());
        }

        $this->closeDeleteModal();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedSelectedType()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    private function saveCommunicationData($communicationData)
    {
        $communicationsFile = storage_path('app/communications_data.json');
        $communications = [];
        
        if (File::exists($communicationsFile)) {
            $communications = json_decode(File::get($communicationsFile), true) ?? [];
        }
        
        $communications[] = $communicationData;
        File::put($communicationsFile, json_encode($communications, JSON_PRETTY_PRINT));
    }

    private function getCommunicationsData()
    {
        $communicationsFile = storage_path('app/communications_data.json');
        
        if (File::exists($communicationsFile)) {
            $communications = json_decode(File::get($communicationsFile), true) ?? [];
            
            // Filter by search term
            if (!empty($this->searchTerm)) {
                $communications = array_filter($communications, function($comm) {
                    return stripos($comm['subject'], $this->searchTerm) !== false ||
                           stripos($comm['reference_number'], $this->searchTerm) !== false ||
                           stripos($comm['from_whom'], $this->searchTerm) !== false ||
                           stripos($comm['to_whom'], $this->searchTerm) !== false ||
                           stripos($comm['description'], $this->searchTerm) !== false;
                });
            }
            
            // Filter by type
            if (!empty($this->selectedType)) {
                $communications = array_filter($communications, function($comm) {
                    return $comm['type'] === $this->selectedType;
                });
            }
            
            // Filter by department
            if (!empty($this->selectedDepartment)) {
                $communications = array_filter($communications, function($comm) {
                    return $comm['department'] === $this->selectedDepartment;
                });
            }
            
            // Filter by status
            if (!empty($this->selectedStatus)) {
                $communications = array_filter($communications, function($comm) {
                    return $comm['status'] === $this->selectedStatus;
                });
            }
            
            // Sort by upload date (newest first)
            usort($communications, function($a, $b) {
                return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
            });
            
            return $communications;
        }
        
        return [];
    }

    private function getCommunicationById($communicationId)
    {
        $communications = $this->getCommunicationsData();
        foreach ($communications as $comm) {
            if ($comm['id'] === $communicationId) {
                return $comm;
            }
        }
        return null;
    }

    private function updateCommunicationStatus($communicationId, $newStatus)
    {
        $communicationsFile = storage_path('app/communications_data.json');
        
        if (File::exists($communicationsFile)) {
            $communications = json_decode(File::get($communicationsFile), true) ?? [];
            
            foreach ($communications as &$comm) {
                if ($comm['id'] === $communicationId) {
                    $comm['status'] = $newStatus;
                    break;
                }
            }
            
            File::put($communicationsFile, json_encode($communications, JSON_PRETTY_PRINT));
        }
    }

    private function removeCommunicationData($communicationId)
    {
        $communicationsFile = storage_path('app/communications_data.json');
        
        if (File::exists($communicationsFile)) {
            $communications = json_decode(File::get($communicationsFile), true) ?? [];
            $communications = array_filter($communications, function($comm) use ($communicationId) {
                return $comm['id'] !== $communicationId;
            });
            File::put($communicationsFile, json_encode(array_values($communications), JSON_PRETTY_PRINT));
        }
    }

    public function render()
    {
        $communications = $this->getCommunicationsData();
        
        // Simple pagination for the array
        $perPage = 12;
        $currentPage = $this->page;
        $offset = ($currentPage - 1) * $perPage;
        $paginatedCommunications = array_slice($communications, $offset, $perPage);
        
        return view('livewire.dashboard.communication', [
            'communications' => $paginatedCommunications,
            'totalCommunications' => count($communications),
            'totalPages' => ceil(count($communications) / $perPage)
        ]);
    }
}
