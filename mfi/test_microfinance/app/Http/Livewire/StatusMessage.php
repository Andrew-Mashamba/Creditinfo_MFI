<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class StatusMessage extends Component
{
    public string $status;

    public function mount(string $status)
    {
        $this->status = $status;
        
        // Logout the user
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    public function render()
    {
        return view('livewire.status-message');
    }
} 