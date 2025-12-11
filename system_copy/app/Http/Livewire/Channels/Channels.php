<?php

namespace App\Http\Livewire\Channels;

use App\Models\UserSubMenu;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

use App\Models\ServicesList;


class Channels extends Component
{

    public $totalChannels;
    public $inActiveChannels;
    public $channels;
    public $selected;
    public $changeView;
    public $user_sub_menus;

    public function boot(){
        $this->totalChannels = \App\Models\ChannelsModel::count();
        $this->inActiveChannels = \App\Models\ChannelsModel::where('STATUS', 'INACTIVE')->orWhere('STATUS', 'DELETED')->count();
        $this->selected = 1;
        $this->user_sub_menus = UserSubMenu::where('menu_id',4)->where('user_id', Auth::user()->id)->get();
    }

    public function setView($page){
        $this->selected = $page;
    }


    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->channels = \App\Models\ChannelsModel::get();
        $this->user_sub_menus = UserSubMenu::where('menu_id',4)->where('user_id', Auth::user()->id)->get();
        return view('livewire.channels.channels');
    }
}
