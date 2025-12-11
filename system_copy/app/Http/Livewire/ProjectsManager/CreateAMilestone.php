<?php

namespace App\Http\Livewire\ProjectsManager;

use App\Models\FilesModal;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\StageAndStatus;
use App\Models\Vault;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Collection;



class CreateAMilestone extends Component
{

    private $projectList;
    public $Project;


    public $files = [];
    public array $descriptions = [];
    public $tempFiles = [];
    /**
     * @var true
     */
    public bool $filesListener_ =false;
    public $pageOnFocus =2;

    public $milestone_name;
    public $start_date;
    public $end_date;

    protected $rules = [
        'milestone_name' => 'required',
        'start_date' => 'required',
        'end_date' => 'required',
        'Project' => 'required'
    ];

    
    public function viewChanger($page): void
    {
        //dd($page);
        $this->pageOnFocus = $page;

    }

    public function store()
    {
        $validatedData = $this->validate();

        $milestone = Milestone::create([
            'milestone_name' => $this->milestone_name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'project_id' => $this->Project,
    
        ]);


        session()->flash('message', 'Your milestone has been created');
        session()->flash('alert-class', 'alert-success');

        $this->resetAll();
    }

    public function resetAll()
    {
        $this->milestone_name = null;
        $this->start_date = null;
        $this->end_date = null;
        $this->Project = null;
    }


  



    
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->projectList = Project::get();
        return view('livewire.projects-manager.create-a-milestone');
    }
}
