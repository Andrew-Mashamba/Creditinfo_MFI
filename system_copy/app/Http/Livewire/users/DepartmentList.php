<?php

namespace App\Http\Livewire\Users;
use App\Models\approvals;
use App\Models\Branches;
use App\Models\ChannelsModel;
use App\Models\departmentsList;
use App\Models\Nodes;
use App\Models\NodesList;
use App\Models\servicesModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\ServicesList;
use Livewire\WithFileUploads;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;


class DepartmentList extends LivewireDatatable
{
    use WithFileUploads;
    public $exportable = true;
    public $searchable="process_name, process_description,process_status,process_type,process_status,approval_status";

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {

        return departmentsList::query();


    }

    public function deleteRole($id){




        $update_value = approvals::updateOrCreate(
            [
                'process_id' => $id,
                'user_id' => Auth::user()->id

            ],
            [
                'institution' => '',
                'process_name' => 'deleteDepartment',
                'process_description' => 'A requested to delete a ROLE - '.departmentsList::where('id',$id)->value('department_name'),
                'approval_process_description' => '',
                'process_code' => '30',
                'process_id' => $id,
                'process_status' => 'PENDING',
                'approval_status' => 'PENDING',
                'user_id'  => Auth::user()->id,
                'team_id'  => ''

            ]
        );

        $this->emitUp('refreshData');
    }


    public function columns(): array
    {
        return [


            Column::name('department_name')
                ->label('Role Name'),

            Column::callback(['permissions'], function ($permissions) {
                return view('livewire.users.department-list-action', ['permissions' => $permissions, 'move' => false]);
            })->unsortable()->label('Associated Permissions'),

            Column::name('status')
                ->label('Status'),


            Column::callback(['id'], function ($id) {
                return view('livewire.users.department-list-action-delete', ['id' => $id, 'move' => false]);
            })->unsortable()->label('Delete'),

        ];
    }
}
