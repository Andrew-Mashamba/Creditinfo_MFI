<?php

namespace App\Http\Livewire\Procurement;

use App\Models\Vendor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Kdion4891\LaravelLivewireTables\Column;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class VendorTable extends LivewireDatatable
{

    public $exportable=true;

    public function builder(){
      return Vendor::query();
  }


  public function columns():array
  {

    return [  \Mediconesystems\LivewireDatatables\Column::name('id')->label('ID')->sortable(),
      \Mediconesystems\LivewireDatatables\Column::name('organization_name')->label('organization Name')->searchable(),
      \Mediconesystems\LivewireDatatables\Column::name('email')->label('Organization email')->sortable(),
      \Mediconesystems\LivewireDatatables\Column::name('organization_tin_number')->label('Organization Tin number')->sortable(),
      \Mediconesystems\LivewireDatatables\Column::name('organization_license_number')->label('License Number')->sortable(),
        \Mediconesystems\LivewireDatatables\Column::name('status')->label('Status')->sortable(),
        \Mediconesystems\LivewireDatatables\Column::callback(['id','status'],function($id,$status){
            return  view('livewire.procurement.table-action',['id'=>$id]);
        }
        )->label('Action')->sortable(),

    ];
  }

  public function edit($id){
        $this->emit('editVendor',$id);
  }

  public function delete($id){
      $this->emit('deleteVendor',$id);
  }


}
