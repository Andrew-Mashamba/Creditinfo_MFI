<?php

namespace App\Http\Livewire\Services;

use App\Models\approvals;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use App\Models\NodesList;
use App\Models\servicesModel;
use JetBrains\PhpStorm\NoReturn;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AddService extends Component
{



    public $identifier;

    public $nodes = array();
    public $nodesList = array();
    public $NewNode;
    public $DB_TABLE_SERVICE_IDENTIFIER_VALUE;
    public $DB_TABLE_SERVICE_IDENTIFIER_NAME;
    public $tempArray= [];
    public $name;

   public $service_id;



    function selectedMenu($id){
   $this->service_id=$id;
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

                unset($this->nodesList[$index]);

                break; // Exit the loop after removing the node
            }
        }

        //dd($this->nodesList);
    }

    public function save(): void
    {

        $this->validate([
            'name' => 'required|string|max:50',
            'nodesList' => 'required',
        ]);

        $Service = new servicesModel;
        $Service->NAME = $this->name;
        $Service->NODES = json_encode($this->nodesList);
        $Service->STATUS = 'PENDING';

        if ($Service->save()) {
            $value = approvals::updateOrCreate(
                [
                    'process_id' => $Service->ID,
                    'user_id' => Auth::user()->id

                ],
                [
                    'institution' => '',
                    'process_name' => 'addNode',
                    'process_description' => Auth::user()->name.' has requested to add a SERVICE - '.$this->name,
                    'approval_process_description' => '',
                    'process_code' => '25',
                    'process_id' => $Service->ID,
                    'process_status' => 'PENDING',
                    'approval_status' => 'PENDING',
                    'user_id'  => Auth::user()->id,
                    'team_id'  => ''

                ]
            );


            Session::flash('message', 'Service has been successfully saved, awaiting approval for activation!');
            Session::flash('alert-class', 'alert-success');
            $this->resetVar();
        }




    }

    public function resetVar(): void
    {

        $this->identifier = null;

        $this->nodes = array();
        $this->nodesList = array();
        $this->NewNode = null;
        $this->name = null;
        $this->DB_TABLE_SERVICE_IDENTIFIER_VALUE = null;
        $this->DB_TABLE_SERVICE_IDENTIFIER_NAME = null;
        $this->tempArray= [];
    }


    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        //$this->nodesList = \App\Models\NodesList::get();
        return view('livewire.services.add-service');
    }
}
