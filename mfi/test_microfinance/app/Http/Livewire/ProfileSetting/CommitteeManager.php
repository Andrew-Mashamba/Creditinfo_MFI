<?php

namespace App\Http\Livewire\ProfileSetting;

use App\Models\Committee;
use Livewire\Component;
use Livewire\WithPagination;

class CommitteeManager extends Component
{
    use WithPagination;

    public $name;
    public $type = 'committee';
    public $description;
    public $committee_id;
    public $editMode = false;
    public $search = '';
    public $perPage = 10;
    public $showExportModal = false;
    public $exportFields = ['name', 'type', 'description'];
    public $selectedExportFields = [];
    public $exportFormat = 'csv';
    public $filterType = '';
    public $filterCreatedFrom = '';
    public $filterCreatedTo = '';
    public $showFilters = false;
    public $department_id;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|string|max:50',
        'description' => 'nullable|string',
    ];

    public function resetForm()
    {
        $this->name = '';
        $this->type = 'committee';
        $this->description = '';
        $this->department_id = null;
        $this->committee_id = null;
        $this->editMode = false;
    }

    public function createCommittee()
    {
        $this->validate();
        Committee::create([
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'department_id' => $this->department_id,
        ]);
        session()->flash('message', 'Committee created successfully.');
        $this->resetForm();
    }

    public function editCommittee($id)
    {
        $committee = Committee::findOrFail($id);
        $this->committee_id = $committee->id;
        $this->name = $committee->name;
        $this->type = $committee->type;
        $this->description = $committee->description;
        $this->department_id = $committee->department_id;
        $this->editMode = true;
    }

    public function updateCommittee()
    {
        $this->validate();
        $committee = Committee::findOrFail($this->committee_id);
        $committee->update([
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'department_id' => $this->department_id,
        ]);
        session()->flash('message', 'Committee updated successfully.');
        $this->resetForm();
    }

    public function deleteCommittee($id)
    {
        Committee::findOrFail($id)->delete();
        session()->flash('message', 'Committee deleted successfully.');
        $this->resetForm();
    }

    public function openExportModal()
    {
        $this->selectedExportFields = $this->exportFields;
        $this->exportFormat = 'csv';
        $this->showExportModal = true;
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
    }

    public function exportCommittees()
    {
        $fields = $this->selectedExportFields;
        $format = $this->exportFormat;
        $filename = 'committees_export_' . now()->format('Ymd_His') . '.' . $format;
        $committees = \App\Models\Committee::select($fields)->get();
        $exportData = $committees->map(function($item) use ($fields) {
            return collect($fields)->mapWithKeys(function($field) use ($item) {
                return [$field => $item[$field]];
            })->toArray();
        });
        $headers = collect($fields)->map(function($field) {
            return ucfirst(str_replace('_', ' ', $field));
        })->toArray();
        $exportArray = [$headers];
        foreach ($exportData as $row) {
            $exportArray[] = array_values($row);
        }
        if ($format === 'csv') {
            $csv = fopen('php://memory', 'r+');
            foreach ($exportArray as $row) {
                fputcsv($csv, $row);
            }
            rewind($csv);
            $content = stream_get_contents($csv);
            fclose($csv);
            return response($content)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } else {
            // Excel export using Laravel Excel
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericArrayExport($exportArray), $filename);
        }
        $this->showExportModal = false;
        $this->emit('notify', 'success', 'Committees exported successfully!');
    }

    public function applyFilters()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->filterType = '';
        $this->filterCreatedFrom = '';
        $this->filterCreatedTo = '';
        $this->resetPage();
    }

    public function render()
    {
        $committees = \App\Models\Committee::when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('type', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterType, function ($query) {
                $query->where('type', $this->filterType);
            })
            ->when($this->filterCreatedFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->filterCreatedFrom);
            })
            ->when($this->filterCreatedTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->filterCreatedTo);
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.profile-setting.committee-manager', [
            'committees' => $committees
        ]);
    }
}
