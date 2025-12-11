<?php

namespace App\Http\Livewire\ProjectsManager;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Facades\Session;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;


class TaskList extends LivewireDatatable
{


    public $exportable = true;
    public $searchable="process_name, process_description,process_status,process_type,process_status,approval_status";

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {

        return Task::query()->where('project_id', Session::get('projectOnView'));


    }

    public function columns(): array
    {
        return [

            Column::name('created_at')
                ->label('Date Created')->defaultSort(),

            Column::name('task_name')
            ->label('Task Name'),

            // Column::name('project_id')
            // ->label('Project'),

            Column::callback(['project_id'], function ($id) {
                if($id){
                    return Project::find($id)->tender_no;
                }else{
                    return '';
                }

            })->unsortable()->label('Tender Number'),

        ];
    }

}





