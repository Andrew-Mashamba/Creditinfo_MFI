<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\CommiteeMinutes; // Ensure you import your model
use Livewire\WithFileUploads;
class Commitee extends Component
{
    public $enableModal;

    use WithFileUploads;

    public $committee_name;
    public $meeting_date;
    public $descriptions;
    public $file_path;
    public $isEdit = false;
    public $minuteId;

    protected $rules = [
        'committee_name' => 'required|string|max:255',
        'meeting_date' => 'required|date',
        'descriptions' => 'nullable|string|max:1000',
        'file_path' => 'nullable|file|mimes:pdf,doc,docx,txt|max:2048',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $this->isEdit = true;
            $minute = CommiteeMinutes::find($id);
            $this->minuteId = $minute->id;
            $this->committee_name = $minute->committee_name;
            $this->meeting_date = $minute->meeting_date;
            $this->descriptions = $minute->descriptions;
            // You might want to handle file_path differently if needed
        }

    }

    public function save()
    {
        $this->validate();

        if ($this->file_path) {
            $this->file_path = $this->file_path->store('documents', 'public');
            $file_name=$this->file_path->getClientOriginalName();
        }

        try{
            CommiteeMinutes::create(
                [
                    'committee_name' => $this->committee_name,
                    'meeting_date' => $this->meeting_date,
                    'descriptions' => $this->descriptions,
                    'file_path' => $file_name,
                ]
            );

            session()->flash('message', $this->isEdit ? 'Minutes updated successfully.' : 'Minutes added successfully.');

            $this->resetForm();

        }catch(\Exception $e){
            dd($e->getMessage());
        }

    }

    private function resetForm()
    {
        $this->committee_name = '';
        $this->meeting_date = '';
        $this->descriptions = '';
        $this->file_path = null;
        $this->isEdit = false;
        $this->minuteId = null;
    }

    public function render()
    {
        return view('livewire.reports.commitee');
    }

    function createNewMinute(){

        $this->enableModal=!$this->enableModal;
    }
}
