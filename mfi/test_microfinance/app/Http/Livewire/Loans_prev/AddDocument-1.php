<?php

namespace App\Http\Livewire\Loans;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Rule;
use Livewire\Component;
use App\Models\LoansModel;
use Livewire\WithFileUploads;
use App\Models\loan_images;
use App\Models\loans_schedules;


class AddDocument_1 extends Component
{
    use WithFileUploads;

    public $document_descriptions;
    public $loan_document;
    public $loan_document_data;


    public function save(){
        $this->validate([
            'loan_document'=>'required|mimes:pdf,docx,doc,txt,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,bmp,tif,tiff',
        ]);

        $loan_id = LoansModel::where('id', Session::get('currentloanID'))->value('loan_id');
        //$imageUrl = $this->photo->store('avatars', 'public');
        $filename = time().'_'.$this->loan_document->getClientOriginalName();

        // Store the file in the 'public' disk under the 'Saccoss-request' directory
        $this->loan_document->storeAs('LoanDocument', $filename, 'public');

        // Save the file path
        $file1Path = 'LoanDocument/'.$filename;



        loan_images::create([
            'loan_id' => $loan_id,
            'filename'=> $filename,
            'category' => 'add-document',
            'institution_id'=>auth()->user()->institution_id,
            'document_descriptions' => $this->document_descriptions,
            'url' => $file1Path,
        ]);
        $this->loan_document = null;
        $this->document_descriptions=null;
    }



    public function render()
    {
        $loan_id = LoansModel::where('id', Session::get('currentloanID'))->value('loan_id');
        $this->loan_document_data=loan_images::where('loan_id',$loan_id)->where('category','add-document')->get();
//     dd($this->loan_document_data);

        return view('livewire.loans.add-document');
    }


    public function download2($id){

        $get_document= loan_images::where('id', $id)->value('url');
        $filePath = storage_path('app/public/'.$get_document);
        return response()->download($filePath);
    }
}
