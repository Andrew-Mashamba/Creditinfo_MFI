<?php

namespace App\Http\Livewire\Services;

use App\Models\approvals;
use App\Models\NodesList;
use App\Models\servicesModel;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class DeleteService extends Component
{
    public $servicesList;
    public $serviceSelected;

    public $password = null;

    public function render(): Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->servicesList = servicesModel::get();
        return view('livewire.services.delete-service');
    }

    public function confirmPassword(): void
    {
        // Check if password matches for logged-in user
        if (Hash::check($this->password, auth()->user()->password)) {
            //dd('password matches');
            $this->delete();
        } else {
            //dd('password does not match');
            Session::flash('message', 'This password does not match our records');
            Session::flash('alert-class', 'alert-warning');
        }
        $this->resetPassword();


    }

    public function resetPassword(): void
    {
        $this->password = null;
    }



    public function delete(): void
    {
        $service = servicesModel::where('ID',$this->serviceSelected)->first();

        if ($service) {

            $update_value = approvals::updateOrCreate(
                [
                    'process_id' => $this->serviceSelected,
                    'user_id' => Auth::user()->id

                ],
                [
                    'institution' => '',
                    'process_name' => 'deleteService',
                    'process_description' => Auth::user()->name.' has requested to delete a SERVICE - '.$service->NAME,
                    'approval_process_description' => '',
                    'process_code' => '28',
                    'process_id' => $this->serviceSelected,
                    'process_status' => 'PENDING',
                    'approval_status' => 'PENDING',
                    'user_id'  => Auth::user()->id,
                    'team_id'  => '',
                    'edit_package'=> null
                ]
            );



            Session::flash('message', 'Awaiting approval');
            Session::flash('alert-class', 'alert-success');
        } else {
            // Handle case where record was not found
            // Add your logic here
            Session::flash('message', 'Service error');
            Session::flash('alert-class', 'alert-warning');
        }

        $this->resetVars();

    }

    private function resetVars(): void
    {
        $this->serviceSelected = null;
        $this->password = null;

    }


}
