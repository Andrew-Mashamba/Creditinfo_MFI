<?php

namespace App\Http\Livewire\Channels;


use App\Models\approvals;
use App\Models\servicesModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use App\Models\ChannelsModel;
use App\Models\ServicesList;

class AddChannel extends Component
{



    public $identifier;

    public $services = array();
    public $servicesList = array();
    public $NewServices;

    public $tempArray= [];
    public $name;
    public mixed $NewService;


    public function updatedNewServicex(): void
    {


        $this->servicesList[] = array(
            "service_id" => $this->NewService
        );

        $this->NewService = null;

    }

    public function updatedNewService(): void
    {
        $serviceIds = array_column($this->servicesList, 'service_id');
        if (in_array($this->NewService, $serviceIds)) {
            $this->NewService = null;
            return;
        }

        $this->servicesList[] = array(
            "service_id" => $this->NewService
        );

        $this->NewService = null;
    }

    public function removeService($serviceId): void
    {

        foreach ($this->servicesList as $index => $service) {


            if ($service['service_id'] == $serviceId) {

                unset($this->servicesList[$index]);

                break; // Exit the loop after removing the node
            }
        }

        //dd($this->nodesList);
    }

    public function save(): void
    {

        $this->validate([
            'name' => 'required|string|max:50',
            'servicesList' => 'required',
        ]);

        $Channel = new ChannelsModel;
        $Channel->NAME = $this->name;
        $Channel->SERVICES = json_encode($this->servicesList);
        $Channel->STATUS = 'PENDING';

        if ($Channel->save()) {
            $value = approvals::updateOrCreate(
                [
                    'process_id' => $Channel->ID,
                    'user_id' => Auth::user()->id

                ],
                [
                    'institution' => '',
                    'process_name' => 'addChannel',
                    'process_description' => Auth::user()->name.' has requested to add a CHANNEL - '.$this->name,
                    'approval_process_description' => '',
                    'process_code' => '25',
                    'process_id' => $Channel->ID,
                    'process_status' => 'PENDING',
                    'approval_status' => 'PENDING',
                    'user_id'  => Auth::user()->id,
                    'team_id'  => ''

                ]
            );


            Session::flash('message', 'Channel has been successfully saved, awaiting approval for activation!');
            Session::flash('alert-class', 'alert-success');
            $this->resetVar();
        }




    }

    public function resetVar(): void
    {

        $this->identifier = null;
        $this->services = array();
        $this->servicesList = array();
        $this->NewService = null;
        $this->tempArray= [];
        $this->name = null;
    }


    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        return view('livewire.channels.add-channel');
    }
}
