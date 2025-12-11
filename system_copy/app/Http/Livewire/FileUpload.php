<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class FileUpload extends Component
{
    use WithFileUploads;

    public $field;
    public $label;
    public $multiple = false;
    public $accept;
    public $files = [];

    protected $listeners = ['fileRemoved' => 'handleFileRemoved'];

    public function mount($field, $label, $multiple = false, $accept = null)
    {
        $this->field = $field;
        $this->label = $label;
        $this->multiple = $multiple;
        $this->accept = $accept;
        $this->files = [];
    }

    public function updated($propertyName)
    {
        if ($propertyName === $this->field) {
            $this->files = $this->{$this->field};
            $this->emit('filesUpdated', $this->field, $this->files);
        }
    }

    public function removeFile($field, $index = null)
    {
        if ($field !== $this->field) {
            return;
        }

        if ($this->multiple) {
            if (isset($this->files[$index])) {
                unset($this->files[$index]);
                $this->files = array_values($this->files);
                $this->{$this->field} = $this->files;
            }
        } else {
            $this->files = [];
            $this->{$this->field} = null;
        }
        
        $this->emit('fileRemoved', $this->field, $index);
    }

    public function handleFileRemoved($field, $index)
    {
        if ($field === $this->field) {
            $this->removeFile($field, $index);
        }
    }

    public function render()
    {
        return view('livewire.file-upload');
    }
} 