<?php

namespace App\Http\Livewire\ProjectsManager;

use App\Models\FilesModal;
use App\Models\Project;
use App\Models\Issue;
use App\Models\StageAndStatus;
use App\Models\Vault;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Collection;



class CreateAnIssue extends Component
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

    public $issue_title;
    public $issue_desc;
    public $project_id;

    protected $rules = [
        'issue_title' => 'required',
        'issue_desc' => 'required|string',
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

        $issue = Issue::create([
            'issue_title' => $this->issue_title,
            'issue_desc' => $this->issue_desc,
            'project_id' => $this->Project,
        ]);


        session()->flash('message', 'Your issue has been created');
        session()->flash('alert-class', 'alert-success');

        $this->resetAll();
    }

    public function resetAll()
    {
        $this->issue_title = null;
        $this->issue_desc = null;
        $this->Project = null;
    }


  



    
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->projectList = Project::get();
        return view('livewire.projects-manager.create-an-issue');
    }
}
