<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploader extends Component
{
    use WithFileUploads;

    public $name;
    public $multiple;
    public $accept;
    public $maxSize;
    public $uploadedFiles = [];
    public $preview = false;
    public $showProgress = true;
    public $customClass = '';

    public function mount(
        $name,
        $multiple = false,
        $accept = null,
        $maxSize = 12288,
        $preview = false,
        $showProgress = true,
        $customClass = ''
    ) {
        $this->name = $name;
        $this->multiple = $multiple;
        $this->accept = $accept;
        $this->maxSize = $maxSize;
        $this->preview = $preview;
        $this->showProgress = $showProgress;
        $this->customClass = $customClass;
    }

    public function updatedUploadedFiles()
    {
        $this->validate([
            'uploadedFiles.*' => "file|max:{$this->maxSize}",
        ]);

        $this->dispatch('files-uploaded', [
            'name' => $this->name,
            'files' => $this->uploadedFiles
        ]);
    }

    public function removeFile($index)
    {
        if (isset($this->uploadedFiles[$index])) {
            unset($this->uploadedFiles[$index]);
            $this->uploadedFiles = array_values($this->uploadedFiles);
            
            $this->dispatch('file-removed', [
                'name' => $this->name,
                'index' => $index
            ]);
        }
    }

    public function render()
    {
        return view('livewire.components.file-uploader');
    }
} 