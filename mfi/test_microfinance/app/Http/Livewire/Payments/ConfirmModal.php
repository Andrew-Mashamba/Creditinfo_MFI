<?php

namespace App\Http\Livewire\Payments;

use App\Models\Transactions;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class ConfirmModal extends Component
{
    public function render()
    {
        return view('livewire.payments.confirm-modal');
    }

    public function deleteEntryConfirmed(){
//Session::get('deleteTransactionID')
        //Session::put('deleteTransactionID', $value);
        $res=Transactions::where('id',Session::get('deleteTransactionID'))->delete();
        $this->deleteEntryModal=false;

        $this->emit('closeDeleteEntryModal');
    }

}
