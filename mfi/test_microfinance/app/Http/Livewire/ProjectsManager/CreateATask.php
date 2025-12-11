<?php

namespace App\Http\Livewire\ProjectsManager;

use App\Models\FilesModal;
use App\Models\Project;
use App\Models\Task;
use App\Models\StageAndStatus;
use App\Models\Vault;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Collection;



class CreateATask extends Component
{

    private $projectList;
    public $Project;

    public $excelFile;


    public $files = [];
    public array $descriptions = [];
    public $tempFiles = [];
    /**
     * @var true
     */
    public bool $filesListener_ =false;
    public $pageOnFocus =2;

    public $task_name;
    public $task_desc;
    public $project_id;

    protected $rules = [
        'task_name' => 'required',
        'task_desc' => 'required|string',
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

        $task = Task::create([
            'task_name' => $this->task_name,
            'task_desc' => $this->task_desc,
            'project_id' => $this->Project,
        ]);


        session()->flash('message', 'Your task has been created');
        session()->flash('alert-class', 'alert-success');

        $this->resetAll();
    }

    public function resetAll()
    {
        $this->task_name = null;
        $this->task_desc = null;
        $this->Project = null;
    }


  



    
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->projectList = Project::get();
        return view('livewire.projects-manager.create-a-task');
    }
}
