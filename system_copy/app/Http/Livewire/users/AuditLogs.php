<?php

namespace App\Http\Livewire\Users;

use App\Models\UserActionLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogs extends Component
{
    use WithPagination;

    public function render()
    {
        $logs = UserActionLog::with('user')->latest()->paginate(10);
        return view('livewire.users.audit-logs', ['logs' => $logs]);
    }
}
