<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MembershipForm extends Component
{
    use WithFileUploads, WithPagination;

    // Form properties
    public $showUploadModal = false;
    public $showDeleteModal = false;
    public $selectedForm = null;
    public $uploadedFile;
    public $formTitle = '';
    public $formCategory = '';
    public $formDescription = '';
    public $searchTerm = '';
    public $selectedCategory = '';

    // Form categories
    public $formCategories = [
        'membership' => 'Membership Application',
        'loan' => 'Loan Application',
        'pin_change' => 'PIN Change',
        'email_change' => 'Email Change',
        'phone_change' => 'Phone Number Change',
        'account_change' => 'NBC Account Number Change',
        'other' => 'Other Forms'
    ];

    protected $rules = [
        'uploadedFile' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
        'formTitle' => 'required|min:3|max:255',
        'formCategory' => 'required|in:membership,loan,pin_change,email_change,phone_change,account_change,other',
        'formDescription' => 'nullable|max:500'
    ];

    public function mount()
    {
        // Create forms directory if it doesn't exist
        if (!Storage::disk('public')->exists('forms')) {
            Storage::disk('public')->makeDirectory('forms');
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

    public function openDeleteModal($formId)
    {
        $this->selectedForm = $this->getFormById($formId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedForm = null;
    }

    public function resetForm()
    {
        $this->uploadedFile = null;
        $this->formTitle = '';
        $this->formCategory = '';
        $this->formDescription = '';
        $this->resetValidation();
    }

    public function uploadForm()
    {
        $this->validate();

        try {
            $fileName = time() . '_' . Str::slug($this->formTitle) . '.' . $this->uploadedFile->getClientOriginalExtension();
            $filePath = $this->uploadedFile->storeAs('forms', $fileName, 'public');

            // Save form metadata to database or JSON file
            $formData = [
                'id' => Str::uuid(),
                'title' => $this->formTitle,
                'category' => $this->formCategory,
                'description' => $this->formDescription,
                'filename' => $fileName,
                'filepath' => $filePath,
                'original_name' => $this->uploadedFile->getClientOriginalName(),
                'file_size' => $this->uploadedFile->getSize(),
                'file_type' => $this->uploadedFile->getMimeType(),
                'uploaded_at' => now()->toISOString(),
                'uploaded_by' => auth()->id() ?? 'system'
            ];

            $this->saveFormData($formData);

            session()->flash('success', 'Form uploaded successfully!');
            $this->closeUploadModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Error uploading form: ' . $e->getMessage());
        }
    }

    public function downloadForm($formId)
    {
        try {
            $form = $this->getFormById($formId);
            
            if ($form && Storage::disk('public')->exists($form['filepath'])) {
                $this->dispatchBrowserEvent('download-file', [
                    'url' => asset('storage/' . $form['filepath']),
                    'filename' => $form['original_name']
                ]);
            } else {
                session()->flash('error', 'Form file not found.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error downloading form: ' . $e->getMessage());
        }
    }

    public function deleteForm()
    {
        try {
            if ($this->selectedForm && Storage::disk('public')->exists($this->selectedForm['filepath'])) {
                Storage::disk('public')->delete($this->selectedForm['filepath']);
                $this->removeFormData($this->selectedForm['id']);
                session()->flash('success', 'Form deleted successfully!');
            } else {
                session()->flash('error', 'Form file not found.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting form: ' . $e->getMessage());
        }

        $this->closeDeleteModal();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    private function saveFormData($formData)
    {
        $formsFile = storage_path('app/forms_data.json');
        $forms = [];
        
        if (File::exists($formsFile)) {
            $forms = json_decode(File::get($formsFile), true) ?? [];
        }
        
        $forms[] = $formData;
        File::put($formsFile, json_encode($forms, JSON_PRETTY_PRINT));
    }

    private function getFormsData()
    {
        $formsFile = storage_path('app/forms_data.json');
        
        if (File::exists($formsFile)) {
            $forms = json_decode(File::get($formsFile), true) ?? [];
            
            // Filter by search term
            if (!empty($this->searchTerm)) {
                $forms = array_filter($forms, function($form) {
                    return stripos($form['title'], $this->searchTerm) !== false ||
                           stripos($form['description'], $this->searchTerm) !== false;
                });
            }
            
            // Filter by category
            if (!empty($this->selectedCategory)) {
                $forms = array_filter($forms, function($form) {
                    return $form['category'] === $this->selectedCategory;
                });
            }
            
            // Sort by upload date (newest first)
            usort($forms, function($a, $b) {
                return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
            });
            
            return $forms;
        }
        
        return [];
    }

    private function getFormById($formId)
    {
        $forms = $this->getFormsData();
        foreach ($forms as $form) {
            if ($form['id'] === $formId) {
                return $form;
            }
        }
        return null;
    }

    private function removeFormData($formId)
    {
        $formsFile = storage_path('app/forms_data.json');
        
        if (File::exists($formsFile)) {
            $forms = json_decode(File::get($formsFile), true) ?? [];
            $forms = array_filter($forms, function($form) use ($formId) {
                return $form['id'] !== $formId;
            });
            File::put($formsFile, json_encode(array_values($forms), JSON_PRETTY_PRINT));
        }
    }

    public function render()
    {
        $forms = $this->getFormsData();
        
        // Simple pagination for the array
        $perPage = 12;
        $currentPage = $this->page;
        $offset = ($currentPage - 1) * $perPage;
        $paginatedForms = array_slice($forms, $offset, $perPage);
        
        return view('livewire.dashboard.membership-form', [
            'forms' => $paginatedForms,
            'totalForms' => count($forms),
            'totalPages' => ceil(count($forms) / $perPage)
        ]);
    }
}
