<?php

namespace App\Http\Livewire\Shared;

use Livewire\Component;

class ToastNotification extends Component
{
    public $show = false;
    public $type = 'success'; // success, error, info, warning
    public $message = '';

    protected $listeners = ['notify' => 'showNotification'];

    public function showNotification($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
        $this->show = true;
    }

    public function dismiss()
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.shared.toast-notification');
    }
} 