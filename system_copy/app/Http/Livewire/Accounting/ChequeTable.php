<?php

namespace App\Http\Livewire\Accounting;

use App\Http\Livewire\Branches\Branches;
use App\Models\AccountsModel;
use App\Models\BranchesModel;
use App\Models\ChequeModel;
use App\Models\Employee;
use App\Models\ClientsModel;
use App\Models\MembersModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ChequeTable extends LivewireDatatable
{

    public  $exportable=true;



    public function builder(): \Illuminate\Database\Eloquent\Builder
    {




            return ChequeModel::query(); // You can modify the ordering as per your requirement

    }


    public function columns(): array
    {
        return [

            Column::callback(['customer_account'],function ($memberNumber){
               $memberNumber=AccountsModel::where('account_number',$memberNumber)->value('client_number');
                if($memberNumber){
                    return ClientsModel::where('client_number',$memberNumber)->value('first_name').' '.ClientsModel::where('client_number',$memberNumber)->value('middle_name').' '.ClientsModel::where('client_number',$memberNumber)->value('last_name')."($memberNumber)";
                }else{
                    return null;
                }

            })->label('full name')->searchable(),
            Column::name('customer_account')->label('account number'),
            Column::callback('branch',function($branch_id){
                return BranchesModel::where('id',$branch_id)->value('name').'('.BranchesModel::where('id',$branch_id)->value('region').','.BranchesModel::where('id',$branch_id)->value('wilaya').')';
            })->label('branch'),
            Column::name('amount')->label('amount'),
            Column::name('status')->label('status'),
            Column::callback('id',function($id){
                return view('livewire.profile-setting.table-action',['id'=>$id]);
            })->label('action'),


             ];
    }



    public function approveInstitution($id){
        $this->emitUp('cheques',$id);
    }

    public function declineRequest($id){
        session()->put('declineCheckModal',$id);
        dd('decline check');
    }


}
