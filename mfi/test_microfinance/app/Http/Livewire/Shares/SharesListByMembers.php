<?php

namespace App\Http\Livewire\Shares;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class SharesListByMembers extends Component
{


    public $term = "";
    public $showAddUser = false;
    public $memberStatus = 'All';
    protected $listeners = ['refreshMembersListComponent' => '$refresh'];

    public function resetSearch(){
        $this->term = '';
        Session::put('memberSearchTerm','');
        $this->emit('refreshMembersTable');
    }


    
    public function render()
    {

        if($this->term == ''){
            Session::put('memberStatus',$this->memberStatus);
            Session::put('memberSearchTerm','');
            $this->emit('refreshMembersTable');
        }else{
            Session::put('memberStatus',$this->memberStatus);
            Session::put('memberSearchTerm',$this->term);
            $this->emit('refreshMembersTable');
        }
        return view('livewire.shares.shares-list-by-members');
    }
}
