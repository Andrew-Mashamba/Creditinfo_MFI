<?php

namespace App\Http\Livewire\ProfileSetting;

use App\Models\Meeting;
use App\Models\MeetingDocument;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class MeetingDocumentsManager extends Component
{
    use WithFileUploads, WithPagination;

    public $meeting_id;
    public $file;
    public $perPage = 10;

    protected $rules = [
        'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:10240',
    ];

    public function mount($meeting_id)
    {
        $this->meeting_id = $meeting_id;
    }

    public function uploadDocument()
    {
        $this->validate();
        $path = $this->file->store('meeting_documents', 'public');
        MeetingDocument::create([
            'meeting_id' => $this->meeting_id,
            'file_name' => $this->file->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => auth()->id() ?? null,
        ]);
        session()->flash('message', 'Document uploaded successfully.');
        $this->reset('file');
    }

    public function download($id)
    {
        $doc = MeetingDocument::findOrFail($id);
        return Storage::disk('public')->download($doc->file_path, $doc->file_name);
    }

    public function deleteDocument($id)
    {
        $doc = MeetingDocument::findOrFail($id);
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
        session()->flash('message', 'Document deleted successfully.');
    }

    public function render()
    {
        $meeting = Meeting::findOrFail($this->meeting_id);
        $documents = MeetingDocument::where('meeting_id', $this->meeting_id)
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.profile-setting.meeting-documents-manager', [
            'meeting' => $meeting,
            'documents' => $documents
        ]);
    }
}
