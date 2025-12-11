<?php

namespace App\Http\Livewire\Accounting;

use App\Models\Clients;
use Livewire\Component;

class ExitClientAction extends Component
{
    public function render()
    {
        return view('livewire.accounting.exit-member-action');
    }

    public function download(){
        $member_exit_document=Clients::where('id',session()->get('viewClientId_details'))->value('member_exit_document');
        $filePath = storage_path('app/public/' .$member_exit_document);
        return response()->download($filePath);

    }
}
