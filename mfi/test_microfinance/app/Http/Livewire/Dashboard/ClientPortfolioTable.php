<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\ClientsModel;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Str;
class ClientPortfolioTable extends LivewireDatatable
{
    public function builder()
    {
        return ClientsModel::query()->where('loan_officer', auth()->user()->nida_number);
    }

    public function columns(): array
    {
        return [
            Column::name('first_name')
                ->label('First Name'),
            Column::name('phone_number')
                ->label('Phone Number'),
            Column::name('national_id')
                ->label('NIDA Number'),
            Column::name('branch')
                ->label('Branch'),
            Column::name('amount')
                ->label('Amount'),
            Column::name('client_status')
                ->label('Client Status'),
            Column::callback(['id'], function ($id) {
                return view('livewire.dashboard.portfolio-action', ['id' => $id, 'move' => false]);
            })->unsortable()->label('Action'),


        ];
    }



    public function set($id){

        $this->emitUp('setClientDetails',$id);
        $this->emitUp('closeSetView',$id);
    }

}
