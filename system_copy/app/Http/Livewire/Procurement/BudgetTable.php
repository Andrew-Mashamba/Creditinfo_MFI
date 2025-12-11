<?php

namespace App\Http\Livewire\Procurement;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BudgetTable extends Component
{


    public function boot()
    {



        $user = auth()->user();
        $institution_id = $user->institution_id ;
        // Set the database connection based on the selected SACCO
        if ($institution_id) {
            $institution = \App\Models\institutions::find($institution_id);
            //dd($institution);
            if ($institution) {
                // Set the database connection
                Config::set('database.connections.institution', [
                    'driver' => 'mysql',
                    'host' => $institution->db_host,
                    'port' => $institution->db_port,
                    'database' => $institution->db_name,
                    'username' => $institution->db_username,
                    'password' => $institution->db_password,
                ]);
                DB::setDefaultConnection('institution');

            }
        }


    }

    public function render()
    {
        return view('livewire.procurement.budget-table');
    }
}
