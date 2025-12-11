<?php

namespace App\Http\Livewire\HR;

use Livewire\Component;

class NavigationHandler extends Component
{
    public $currentComponent = 'hr.dashboard';
    public $currentSection = 'Dashboard';
    public $currentItem = 'Dashboard';
    public $menuConfig;
    public $breadcrumbs = [];

    public function mount()
    {
        // Load menu configuration from the system file
        $this->menuConfig = require resource_path('views/livewire/h-r/system.php.blade.php');
        $this->updateBreadcrumbs();
    }

    public function navigateTo($component, $section = null, $item = null)
    {
        $this->emit('navigating');
        
        $this->currentComponent = $component;
        $this->currentSection = $section;
        $this->currentItem = $item;
        
        $this->updateBreadcrumbs();
        
        $this->emit('navigated');
    }

    protected function updateBreadcrumbs()
    {
        $this->breadcrumbs = [];

        // Add Dashboard as first item
        $this->breadcrumbs[] = [
            'title' => 'Dashboard',
            'component' => 'hr.dashboard',
            'section' => 'Dashboard',
            'item' => 'Dashboard',
            'active' => $this->currentComponent === 'hr.dashboard'
        ];

        // If not on dashboard, add section and item
        if ($this->currentComponent !== 'hr.dashboard') {
            // Add section
            $this->breadcrumbs[] = [
                'title' => $this->currentSection,
                'component' => $this->currentComponent,
                'section' => $this->currentSection,
                'item' => $this->currentItem,
                'active' => false
            ];

            // Add current item
            $this->breadcrumbs[] = [
                'title' => $this->currentItem,
                'component' => $this->currentComponent,
                'section' => $this->currentSection,
                'item' => $this->currentItem,
                'active' => true
            ];
        }
    }

    public function render()
    {
        return view('livewire.h-r.navigation-handler');
    }
} 