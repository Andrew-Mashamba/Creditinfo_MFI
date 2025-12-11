<?php

namespace App\Http\Livewire\Nodes;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmPassword;
use Livewire\Component;

use App\Models\approvals;
use App\Models\NodesList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;


class DeleteNode extends Component
{

    public $password = null;
    public $nodeSelected;
    public $nodesList;
    public string $NODE_NAME;


    public function boot(): void
    {
        $this->nodesList = NodesList::get();
        $this->NODE_NAME = '';
        //dd($this->nodesList);
    }
    public function updatedNodeSelected(){

    }

    public function delete(): void
    {
        $node = NodesList::where('ID',$this->nodeSelected)->first();

        if ($node) {

            $update_value = approvals::updateOrCreate(
                [
                    'process_id' => $this->nodeSelected,
                    'user_id' => Auth::user()->id

                ],
                [
                    'institution' => '',
                    'process_name' => 'deleteNode',
                    'process_description' => Auth::user()->name.' has requested to delete a NODE - '.$node->NODE_NAME,
                    'approval_process_description' => '',
                    'process_code' => '21',
                    'process_id' => $this->nodeSelected,
                    'process_status' => 'PENDING',
                    'approval_status' => 'PENDING',
                    'user_id'  => Auth::user()->id,
                    'team_id'  => '',
                    'edit_package'=> null
                ]
            );


            // Delete the record
            //$node->delete();
            // Add your logic here for successful deletion
            Session::flash('message', 'Awaiting approval');
            Session::flash('alert-class', 'alert-success');
        } else {
            // Handle case where record was not found
            // Add your logic here
            Session::flash('message', 'Node error');
            Session::flash('alert-class', 'alert-warning');
        }

    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->nodesList = NodesList::get();
        return view('livewire.nodes.delete-node');
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


}
