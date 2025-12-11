<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Laravel\Jetstream\Jetstream;
use Livewire\Component;

class UpdatePasswordForm extends Component
{
    public $state = [
        'current_password' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    public function updatePassword()
    {
        $user = auth()->user();
        
        // Check if 6 months have passed since last password change
        if ($user->password_changed_at && now()->diffInMonths($user->password_changed_at) < 6) {
            $this->addError('password', 'You can only change your password after 6 months from your last password change.');
            return;
        }

        $this->resetErrorBag();

        $this->validate([
            'state.current_password' => ['required', 'current_password'],
            'state.password' => ['required', 'confirmed', Password::defaults()],
        ]);

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => Hash::make($this->state['password']),
                'password_changed_at' => now(),
            ]);

        $this->state = [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ];

        $this->emit('saved');
    }

    public function render()
    {
        return view('profile.update-password-form');
    }
} 