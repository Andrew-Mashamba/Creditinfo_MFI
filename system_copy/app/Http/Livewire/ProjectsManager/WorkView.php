<?php

namespace App\Http\Livewire\ProjectsManager;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use App\Models\Project;


class WorkView extends Component
{

    public $selected;
    public $page;

    public function back(){
        $this->emitUp('closeProjectView');
    }

    public function boot(): void
    {
        $this->selected = 2;
        // $this->user_sub_menus = UserSubMenu::where('menu_id',8)->where('user_id', Auth::user()->id)->get();
    }

    public function setView($page): void
    {
        $this->selected = $page;
    }

    public function render()
    {
        return view('livewire.projects-manager.work-view');
    }
    
}
