<?php

namespace App\Http\Livewire\ProfileSetting;

use App\Mail\InstitutionRegistrationConfirmationMail;
use App\Models\Branch;
use App\Models\institutions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class TableAction extends Component
{




    public function render()
    {

        return view('livewire.profile-setting.table-action');
    }
}
