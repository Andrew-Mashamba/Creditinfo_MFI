<?php

namespace App\Http\Livewire\Services;

use App\Models\approvals;
use App\Models\NodesList;
use App\Models\servicesModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class EditService extends Component
{
    public $servicesList;
    public $service;
    public $serviceData;
    public $serviceId;
    public $serviceName;

    public $nodesList;
    public $nodeIds = [];

    public $serviceToEdit;

    public $NewNode;

    public function updatedNewNodeX(): void
    {


        $this->nodesList[] = array(
            "node_id" => $this->NewNode,
            "service_identifier" => array(
                "table_name" => null,
                "table_value" => null
            )
        );

        $this->NewNode = null;

    }

    public function updatedNewNode(): void
    {
        $nodeId = $this->NewNode;
        $found = false;

        foreach ($this->nodesList as $node) {
            if ($node['node_id'] === $nodeId) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->nodesList[] = array(
                "node_id" => $nodeId,
                "service_identifier" => array(
                    "table_name" => null,
                    "table_value" => null
                )
            );
        }

        $this->NewNode = null;
    }


    public function removeNode($nodeId): void
    {

        foreach ($this->nodesList as $index => $node) {


            if ($node['node_id'] == $nodeId) {
                //dd($node['node_id']);
                unset($this->nodesList[$index]);

                break; // Exit the loop after removing the node
            }
        }

        //dd($this->nodesList);
    }
    public function updatedServiceToEdit($value): void
    {
        $this->serviceData = servicesModel::where('id', $value)->get();
        foreach ($this->serviceData as $service) {

            $this->serviceName = $service->NAME;
            $this->serviceId = $service->ID;
            $this->nodesList = $service->NODES;

            // Decode the JSON string into an array
            $this->nodesList = json_decode($this->nodesList, true);

            // Loop through the array and extract the node_id values

            foreach ($this->nodesList as $item) {
                $this->nodeIds[] = $item['node_id'];
            }

        }
    }

    public function save(): void
    {

        $this->validate([
            'serviceName' => 'required|string|max:50',
            'nodesList' => 'required',
        ]);



        $data = [
            'NAME' => $this->serviceName,
            'NODES' => json_encode($this->nodesList),
            'STATUS' => 'ACTIVE',
            ];



        $update_value = approvals::updateOrCreate(
            [
                'process_id' => $this->serviceId,
                'user_id' => Auth::user()->id

            ],
            [
                'institution' => '',
                'process_name' => 'editService',
                'process_description' => Auth::user()->name.' has requested to edit a SERVICE - '.$this->serviceName,
                'approval_process_description' => '',
                'process_code' => '27',
                'process_id' => $this->serviceId,
                'process_status' => 'PENDING',
                'approval_status' => 'PENDING',
                'user_id'  => Auth::user()->id,
                'team_id'  => '',
                'edit_package'=> json_encode($data),

            ]
        );

        Session::flash('message', 'Service has been successfully edited, sent for approval!');
        Session::flash('alert-class', 'alert-success');

        $this->resetVars();
    }



    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->servicesList = servicesModel::get();
        return view('livewire.services.edit-service');
    }

    private function resetVars(): void
    {
        $this->serviceName = null;
        $this->serviceId = null;
        $this->nodesList = null;
        $this->nodeIds = null;
        $this->serviceToEdit = null;
        $this->NewNode = null;
    }
}
