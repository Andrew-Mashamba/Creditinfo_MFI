<?php

namespace App\Http\Livewire\Nodes;

use App\Models\NodesList;
use App\Models\UserSubMenu;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Nodes extends Component
{
    public $totalNodes;
    public $inActiveNodes;
    public $nodes;
    public $selected;
    public $changeView;
    public $user_sub_menus;

    public function boot(): void
    {
        $this->totalNodes = \App\Models\NodesList::count();
        $this->inActiveNodes = \App\Models\NodesList::where('NODE_STATUS', 'INACTIVE')->orWhere('NODE_STATUS', 'DELETED')->count();
        $this->selected = 1;
        $this->user_sub_menus = UserSubMenu::where('menu_id',3)->where('user_id', Auth::user()->id)->get();
    }

    public function setView($page){
        $this->selected = $page;
    }
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->nodes = \App\Models\NodesList::get();
        $this->user_sub_menus = UserSubMenu::where('menu_id',3)->where('user_id', Auth::user()->id)->get();
        return view('livewire.nodes.nodes');
    }
}
