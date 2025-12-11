<?php

namespace App\Http\Livewire\Branches;

use App\Models\approvals;
use App\Models\BranchesModel;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\NodesList;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Livewire\WithPagination;

class BranchesList extends LivewireDatatable
{
    use WithFileUploads;
    use WithPagination;
    protected $listeners = ['refreshBranchesList' => '$refresh'];

    public $exportable = true;

    public function builder()
    {
        return BranchesModel::query();
    }

    public function columns()
    {
        return [
            Column::name('branchNumber')
                ->label('Branch #')
                ->sortable()
                ->searchable(),

            Column::name('name')
                ->label('Branch Name')
                ->sortable()
                ->searchable(),

            Column::name('region')
                ->label('Region')
                ->sortable()
                ->searchable(),

            Column::name('wilaya')
                ->label('Wilaya')
                ->sortable()
                ->searchable(),

            Column::name('status')
                ->label('Status')
                ->sortable(),

            Column::callback(['id'], function($id) {
                return view('livewire.branches.branch-actions', ['id' => $id]);
            })->label('Actions')
        ];
    }

    public function edit($id){
        $this->emitUp('editBranch',$id);
    }

    public function block($id){
        $this->emitUp('blockBranch',$id);
    }

    public function viewBranchDetails($id){
        $this->emit('viewBranchId',$id);
    }

    public function render()
    {
        return view('livewire.branches.branches-list', [
            'branches' => $this->builder()->paginate(10)
        ]);
    }
}
