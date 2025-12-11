<?php

namespace App\Http\Livewire\ProjectsManager;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use App\Models\Project;
use Illuminate\Support\Facades\Session;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;


class ProjectList extends LivewireDatatable
{


    public $exportable = true;
    public $searchable="tender_no,supplier_name,award_date,expected_end_date,status";

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {

        return Project::query();


    }

    public function columns(): array
    {
        return [

            Column::name('created_at')
                ->label('Date Created')->defaultSort(),

            Column::name('tender_no')
            ->label('Tender No'),

            Column::name('supplier_name')
            ->label('Supplier Name'),

            Column::name('award_date')
                ->label('Award Date'),

            Column::name('award_amount')
            ->label('Award Amount'),

            Column::name('expected_end_date')
            ->label('Expected End Date'),

            Column::name('status')
            ->label('Status'),


            Column::callback(['id'], function ($id) {
                return view('livewire.projects-manager.project-list-action', ['id' => $id, 'move' => false]);
            })->unsortable()->label('Action'),



            // Column::name('project_summary')
            // ->label('Project Summary'),



        ];
    }

    public function edit($id){

        $this->emitUp('editProject',$id);


    }
    public function block($id){
        $this->emitUp('blockProject',$id);
    }
    public function viewMemberes($id){
        $this->emitUp('viewProject',$id);
        Session::put('projectOnView', $id);
    }


}





