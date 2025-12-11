<?php

namespace App\Http\Livewire\Reports;

use App\Models\CommiteeMinutes;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;


class CommiteeMinuteTable extends LivewireDatatable
{

    use WithFileUploads;
    public function builder()
    {
        return CommiteeMinutes::query();
        //->leftJoin('branches', 'branches.id', 'clients.branch')
    }


    public function columns(): array
    {
        return [


            Column::name('id')
                ->label('id')->searchable(),


            Column::name('committee_name')
                ->label('committee name')->searchable(),

            Column::name('descriptions')
                ->label('descriptions '),

            Column::name('meeting_date')
                ->label('meeting date '),

                Column::callback(['file_path'], function($file_path) {
                    $html = '<div class="w-8 h-8 bg-blue-900 rounded-full cursor-pointer" wire:click="download(\'' . htmlspecialchars($file_path, ENT_QUOTES) . '\')">';
                    $html .= '<svg data-slot="icon" fill="white" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">';
                    $html .= '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v6.75m0 0-3-3m3 3 3-3m-8.25 6a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z"></path>';
                    $html .= '</svg></div>';
                    return $html;
                })->label('file')

        ];

    }


    function download($path){
  // Retrieve the file path from the database using the file ID

      $filePath = '/'.$path;

      if (Storage::exists($filePath)) {
        // Return a download response
        return response()->download(Storage::path($filePath));
    } else {
        session()->flash('error', 'File not found.');
    }

  }



}
