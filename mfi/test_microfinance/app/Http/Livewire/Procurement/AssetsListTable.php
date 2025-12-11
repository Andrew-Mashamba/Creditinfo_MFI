<?php

namespace App\Http\Livewire\Procurement;

use App\Models\Inventory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class AssetsListTable extends LivewireDatatable
{

    
    public function builder()
    {
        return Inventory::query();
    }


    public function columns():array{
        return [
            Column::name('item_name')->label('Iterm name')->searchable(),
            Column::name('item_amount')->label('Item amount')->sortable(),
            Column::name('item_expiration_date')->label('Expiration date'),
            Column::name('item_description')->label('item description'),
//            Column::name('item_expiration_date')->label('tender description'),
            Column::name('status')->label(' status'),
            Column::callback('id',function($id){
                return view('livewire.procurement.tender-action',['id'=>$id]);
            })->label('action')
        ];
    }

}
