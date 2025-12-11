<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class System extends Component
{
    public $menu_id = 0;
    public $loading = false;
    public $error = null;

    protected $listeners = ['menuItemClicked' => 'handleMenuItemClick'];

    public function handleMenuItemClick($menuId)
    {
        try {
            $this->loading = true;
            $this->error = null;
            $this->menu_id = $menuId;
        } catch (\Exception $e) {
            Log::error('Error handling menu click', [
                'error' => $e->getMessage(),
                'menu_id' => $menuId
            ]);
            $this->error = 'Failed to load menu item. Please try again.';
        } finally {
            $this->loading = false;
        }
        
    }


    public function render()
    { 
        return view('livewire.system');
    }
}
