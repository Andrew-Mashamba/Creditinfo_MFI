<?php

namespace App\Http\Livewire\Channels;

use App\Models\approvals;
use App\Models\ChannelsModel;
use App\Models\servicesModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class EditChannel extends Component
{

    public $channelsList;
    public $channels;
    public $channelData;
    public $channelId;
    public $channelName;

    public $servicesList;
    public $serviceIds = [];

    public $channelToEdit;

    public $NewService;
    public $serviceData;
    public $channelList;



    public function updatedNewServiceX(): void
    {


        $this->servicesList[] = array(
            "service_id" => $this->NewService,
        );

        //$this->NewService = null;

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

        //$this->NewService = null;
    }

    public function removeService($serviceId): void
    {

        foreach ($this->servicesList as $index => $service) {


            if ($service['service_id'] == $serviceId) {
                //dd($node['node_id']);
                unset($this->servicesList[$index]);

                break; // Exit the loop after removing the node
            }
        }

        //dd($this->nodesList);
    }
    public function updatedChannelToEdit($value): void
    {
        $this->channelData = ChannelsModel::where('ID', $value)->get();
        foreach ($this->channelData as $channel) {

            $this->channelName = $channel->NAME;
            $this->channelId = $channel->ID;
            $this->servicesList = $channel->SERVICES;



            // Decode the JSON string into an array
            $this->servicesList = json_decode($this->servicesList, true);

            // Loop through the array and extract the node_id values

            foreach ($this->servicesList as $item) {

                $this->serviceIds[] = $item;
            }

        }
    }

    public function save(): void
    {

        $this->validate([
            'channelName' => 'required|string|max:50',
            'servicesList' => 'required',
        ]);



        $data = [
            'NAME' => $this->channelName,
            'SERVICES' => json_encode($this->servicesList),
            'STATUS' => 'ACTIVE',
        ];



        $update_value = approvals::updateOrCreate(
            [
                'process_id' => $this->channelId,
                'user_id' => Auth::user()->id

            ],
            [
                'institution' => '',
                'process_name' => 'editChannel',
                'process_description' => Auth::user()->name.' has requested to edit a CHANNEL - '.$this->channelName,
                'approval_process_description' => '',
                'process_code' => '28',
                'process_id' => $this->channelId,
                'process_status' => 'PENDING',
                'approval_status' => 'PENDING',
                'user_id'  => Auth::user()->id,
                'team_id'  => '',
                'edit_package'=> json_encode($data),

            ]
        );

        Session::flash('message', 'Channel has been successfully edited, sent for approval!');
        Session::flash('alert-class', 'alert-success');

        $this->resetVars();
    }




    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view('livewire.channels.edit-channel');
    }

    private function resetVars(): void
    {
        $this->channelName = null;
        $this->channelId = null;
        $this->servicesList = null;
        $this->serviceIds = null;
        $this->channelToEdit = null;
        $this->NewService = null;
        $this->serviceData = null;
        $this->channelList = null;
    }
}
