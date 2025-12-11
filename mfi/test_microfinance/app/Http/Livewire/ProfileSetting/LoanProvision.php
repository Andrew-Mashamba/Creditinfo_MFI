<?php

namespace App\Http\Livewire\ProfileSetting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LoanProvision extends Component
{
    public $provisions;
    public $has_clicked=false;
    public $per;
    public $percent;
    public $description;
    public $has_clicked_update=false,$has_clicked_delete=false;
      public    $selected_id;


    public function render()
    {
        $this->provisions=DB::table('loan_provision_settings')->orderBy('per','desc')->get();
        return view('livewire.profile-setting.loan-provision');
    }

    function createModal(){
        $this->has_clicked=!$this->has_clicked;
    }

    function register(){

        $this->validate([
            'per'=>'unique:loan_provision_settings,per',
            'percent' => 'required|numeric|min:0|max:100',
            'description'=>'required'
        ]);


        DB::table('loan_provision_settings')->insert([

            'per'=>$this->per,
            'percent'=>$this->percent,
            'description'=>$this->description,
            'status'=>true,
            'institution_id'=>1
        ]);


        $this->has_clicked=false;
    }

    function editModal($id){
        $data= DB::table('loan_provision_settings')
                 ->find($id);
                 $this->selected_id=$id;

                 $this->fill($data);

                 $this->has_clicked_update=!$this->has_clicked_update;
    }


    function deleteModal($id){
        $this->selected_id=$id;
        $this->has_clicked_delete=!$this->has_clicked_delete;
    }


    function update(){

        if(DB::table('loan_provision_settings')->whereNot('id',$this->selected_id)->where('per',$this->per)->exists()){
        session()->flash('message_fail','invalid per number');

        }else{

            DB::table('loan_provision_settings')->where('id',$this->selected_id)
            ->update([
                'per'=>$this->per,
                'percent'=>$this->percent,
                'description'=>$this->description,
                'status'=>true,
                'institution_id'=>1
            ]);

            $this->has_clicked_update=!$this->has_clicked_update;

        }

    }


    function delete(){

        $record = DB::table('loan_provision_settings')->find($this->selected_id);

        if ($record) {
            DB::table('loan_provision_settings')->where('id', $this->selected_id)->delete();
            $this->has_clicked_delete=!$this->has_clicked_delete;

        }


    }
}
