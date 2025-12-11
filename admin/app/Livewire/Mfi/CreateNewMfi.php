<?php

namespace App\Livewire\Mfi;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Services\MfiCreationService;
use App\Models\MfiInstitution;
use Exception;

class CreateNewMfi extends Component
{
    public $mfi_name = '';
    public $mfi_code = '';
    public $contact_person = '';
    public $contact_email = '';
    public $contact_phone = '';
    public $address = '';
    public $license_number = '';
    public $admin_first_name = '';
    public $admin_last_name = '';
    public $admin_email = '';
    public $admin_password = '';
    public $admin_password_confirmation = '';

    protected $rules = [
        'mfi_name' => 'required|min:3|max:255',
        'mfi_code' => 'required|min:2|max:20|alpha_dash|unique:mfi_institutions,code',
        'contact_person' => 'required|min:3|max:255',
        'contact_email' => 'required|email|max:255',
        'contact_phone' => 'required|min:10|max:20',
        'address' => 'required|min:10|max:500',
        'license_number' => 'nullable|max:100',
        'admin_first_name' => 'required|min:2|max:100',
        'admin_last_name' => 'required|min:2|max:100',
        'admin_email' => 'required|email|max:255|unique:users,email',
        'admin_password' => 'required|min:8|max:255',
        'admin_password_confirmation' => 'required|same:admin_password',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        // Auto-generate MFI code from name
        if ($propertyName === 'mfi_name') {
            $this->mfi_code = Str::slug(Str::limit($this->mfi_name, 20), '_');
        }
    }

    public function createMfi()
    {
        $this->validate();

        try {
            $mfiCreationService = new MfiCreationService();
            
            $mfiInstitution = $mfiCreationService->createMfiInstance([
                'mfi_name' => $this->mfi_name,
                'mfi_code' => $this->mfi_code,
                'contact_person' => $this->contact_person,
                'contact_email' => $this->contact_email,
                'contact_phone' => $this->contact_phone,
                'address' => $this->address,
                'license_number' => $this->license_number,
                'admin_first_name' => $this->admin_first_name,
                'admin_last_name' => $this->admin_last_name,
                'admin_email' => $this->admin_email,
                'admin_password' => $this->admin_password,
            ]);

            session()->flash('message', 'MFI Instance "' . $this->mfi_name . '" has been created successfully! Database: ' . $this->mfi_code . '_db, Folder: /mfi/' . $this->mfi_code);
            
            // Reset form
            $this->reset();
            
            // Redirect to all institutions
            return redirect()->route('mfi.all-institutions');

        } catch (Exception $e) {
            session()->flash('error', 'Failed to create MFI instance: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.mfi.create-new-mfi')->layout('layouts.app');
    }
}
