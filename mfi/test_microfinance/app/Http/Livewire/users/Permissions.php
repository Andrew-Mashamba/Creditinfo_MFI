<?php

namespace App\Http\Livewire\Users;

use App\Models\Permission;
use Livewire\Component;
use Livewire\WithPagination;

class Permissions extends Component
{
    use WithPagination;

    public function render()
    {
        $permissions = Permission::with('roles')->paginate(10);
        return view('livewire.users.permissions', ['permissions' => $permissions]);
    }
}
