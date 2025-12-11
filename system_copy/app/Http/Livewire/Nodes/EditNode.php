<?php

namespace App\Http\Livewire\Nodes;

use App\Models\approvals;
use App\Models\NodesList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class EditNode extends Component
{

    public $Node;
    public $DATA_SOURCE_TYPE='';
    public $NODE_NAME ='';
    public $NODE_DB_HOST='';
    public $NODE_DB_PORT='';
    public $NODE_DB_DATABASE='';
    public $NODE_DB_USERNAME='';
    public $NODE_DB_PASSWORD='';
    public $DB_TABLE_TRANSACTION_TYPE='';
    public $DB_TABLE_CLIENT_IDENTIFIER='';
    public $DB_TABLE_SERVICE_IDENTIFIER='';
    public $DB_TABLE_STATUS='';
    public $DB_TABLE_DESCRIPTION='';
    public $DB_TABLE_SENDER='';
    public $DB_TABLE_RECEIVER='';
    public $DB_TABLE_AMOUNT='';
    public $DB_TABLE_DATE='';
    public $DB_TABLE_REFERENCE='';
    public $DB_TABLE_SECONDARY_REFERENCE='';
    public $DB_TABLE_NAME='';
    public $columns;
    public $nodeType='';
    public $newNode = false;

    public $NODE_STATUS='';
    public $nodeToEditId;
    public $QUERY ='';
    public $nodesList;
    public $nodeSelected;
    public $DB_TABLE_API_URL;
    public $DB_TABLE_API_PASSWORD;
    public $DB_TABLE_API_PRIVATE_KEY;


    public function boot(){
        $this->nodesList = NodesList::get();
        $this->NODE_NAME = '';
        //dd($this->nodesList);
    }
    public function updatedNodeSelected(){

       $this->nodeToEdit($this->nodeSelected);
    }

    public function nodeToEdit($id)
    {
        //dd($id);
        $this->nodeToEditId = $id;
        $this->Node = NodesList::where('ID',$id)->get();
        foreach ($this->Node as $nodeData){
            $this->NODE_NAME = $nodeData->NODE_NAME;
            $this->NODE_DB_HOST = $nodeData->NODE_DB_HOST;
            $this->NODE_DB_PORT = $nodeData->NODE_DB_PORT;
            $this->NODE_DB_DATABASE = $nodeData->NODE_DB_DATABASE;
            $this->NODE_DB_USERNAME = $nodeData->NODE_DB_USERNAME;
            $this->NODE_DB_PASSWORD = $nodeData->NODE_DB_PASSWORD;
            $this->DB_TABLE_TRANSACTION_TYPE = $nodeData->DB_TABLE_TRANSACTION_TYPE;
            $this->DB_TABLE_CLIENT_IDENTIFIER = $nodeData->DB_TABLE_CLIENT_IDENTIFIER;
            $this->DB_TABLE_SERVICE_IDENTIFIER = $nodeData->DB_TABLE_SERVICE_IDENTIFIER;
            $this->DB_TABLE_STATUS = $nodeData->DB_TABLE_STATUS;
            $this->DB_TABLE_DESCRIPTION = $nodeData->DB_TABLE_DESCRIPTION;
            $this->DB_TABLE_SENDER = $nodeData->DB_TABLE_SENDER;
            $this->DB_TABLE_RECEIVER = $nodeData->DB_TABLE_RECEIVER;
            $this->DB_TABLE_AMOUNT = $nodeData->DB_TABLE_AMOUNT;
            $this->DB_TABLE_DATE = $nodeData->DB_TABLE_DATE;
            $this->DB_TABLE_REFERENCE = $nodeData->DB_TABLE_REFERENCE;
            $this->DB_TABLE_SECONDARY_REFERENCE = $nodeData->DB_TABLE_SECONDARY_REFERENCE;
            $this->DB_TABLE_NAME = $nodeData->DB_TABLE_NAME;
            $this->nodeType = $nodeData->NODE_TYPE;
            $this->DB_TABLE_API_URL = $nodeData->DB_TABLE_API_URL;
            $this->DB_TABLE_API_PASSWORD = $nodeData->DB_TABLE_API_PASSWORD;
            $this->DB_TABLE_API_PRIVATE_KEY = $nodeData->DB_TABLE_API_PRIVATE_KEY;
            $this->NODE_STATUS = $nodeData->NODE_STATUS;
            $this->QUERY = $nodeData->QUERY;
            $this->DATA_SOURCE_TYPE = trim($nodeData->DATA_SOURCE_TYPE);
            //dd(trim($this->DATA_SOURCE_TYPE));
        }
        //$this->upperShowTrue();
    }





    public function save(): void
    {

        if($this->DATA_SOURCE_TYPE == 'Database'){

            $this->validate([

                'nodeType' => 'required|min:3',
                'NODE_NAME' => 'required|min:3',
                'NODE_DB_HOST' => 'required|min:3',
                'NODE_DB_PORT' => 'required|min:3',
                'NODE_DB_DATABASE' => 'required|min:3',
                'NODE_DB_USERNAME' => 'required|min:3',
                'NODE_DB_PASSWORD' => 'required|min:3',
                'DB_TABLE_TRANSACTION_TYPE' => 'required|min:3',
                'DB_TABLE_CLIENT_IDENTIFIER' => 'required|min:3',
                'DATA_SOURCE_TYPE' => 'required|min:3',
                'DB_TABLE_SERVICE_IDENTIFIER' => 'required|min:3',
                'DB_TABLE_STATUS' => 'required|min:3',
                'DB_TABLE_DESCRIPTION' => 'required|min:3',
                'DB_TABLE_SENDER' => 'required|min:3',
                'DB_TABLE_RECEIVER' => 'required|min:3',
                'DB_TABLE_AMOUNT' => 'required|min:3',
                'DB_TABLE_DATE' => 'required|min:3',
                'DB_TABLE_REFERENCE' => 'required|min:3',
                'DB_TABLE_NAME' => 'required|min:3',
                'QUERY' =>  'required|min:3',
            ]);

        }elseif ($this->DATA_SOURCE_TYPE == 'API'){



            $this->validate([

                'NODE_NAME' => 'required|string|max:50',
                'nodeType' => 'required|string|max:50',
                'DB_TABLE_TRANSACTION_TYPE' => 'nullable|string|max:50',
                'DB_TABLE_CLIENT_IDENTIFIER' => 'nullable|string|max:50',
                'DB_TABLE_SERVICE_IDENTIFIER' => 'nullable|string|max:50',
                'DB_TABLE_DESCRIPTION' => 'nullable|string|max:50',
                'DB_TABLE_SENDER' => 'nullable|string|max:50',
                'DB_TABLE_AMOUNT' => 'required|string|max:50',
                'DB_TABLE_DATE' => 'required|string|max:50',
                'DB_TABLE_REFERENCE' => 'required|string|max:50',

                'DB_TABLE_API_URL' => 'required|string|max:150',
                'DB_TABLE_API_PASSWORD' => 'required|string|max:150',
                'DB_TABLE_API_PRIVATE_KEY' => 'required|string|max:350',


            ]);


        }else{
            $this->validate([

                'NODE_NAME' => 'required|string|max:50',
                'nodeType' => 'required|string|max:50',
                'DB_TABLE_TRANSACTION_TYPE' => 'nullable|string|max:50',
                'DB_TABLE_CLIENT_IDENTIFIER' => 'nullable|string|max:50',
                'DB_TABLE_SERVICE_IDENTIFIER' => 'nullable|string|max:50',
                'DB_TABLE_DESCRIPTION' => 'nullable|string|max:50',
                'DB_TABLE_SENDER' => 'nullable|string|max:50',
                'DB_TABLE_AMOUNT' => 'required|string|max:50',
                'DB_TABLE_DATE' => 'required|string|max:50',
                'DB_TABLE_REFERENCE' => 'required|string|max:50',


            ]);


        }

        if($this->nodeToEditId){


            $data = [

                'NODE_NAME'  => $this->NODE_NAME,
                'NODE_DB_HOST'  => $this->NODE_DB_HOST,
                'NODE_DB_PORT'  => $this->NODE_DB_PORT,
                'NODE_DB_DATABASE'  => $this->NODE_DB_DATABASE,
                'NODE_DB_USERNAME '  =>  $this->NODE_DB_USERNAME,
                'NODE_DB_PASSWORD'  =>  $this->NODE_DB_PASSWORD,
                'DB_TABLE_TRANSACTION_TYPE'  => $this->DB_TABLE_TRANSACTION_TYPE,
                'DB_TABLE_CLIENT_IDENTIFIER'  =>  $this->DB_TABLE_CLIENT_IDENTIFIER,
                'DB_TABLE_SERVICE_IDENTIFIER'  =>  $this->DB_TABLE_SERVICE_IDENTIFIER,
                'DB_TABLE_STATUS'  => $this->DB_TABLE_STATUS,
                'DB_TABLE_DESCRIPTION'  => $this->DB_TABLE_DESCRIPTION,
                'DB_TABLE_SENDER'  => $this->DB_TABLE_SENDER,
                'DB_TABLE_RECEIVER'  => $this->DB_TABLE_RECEIVER,
                'DB_TABLE_AMOUNT'  => $this->DB_TABLE_AMOUNT,
                'DB_TABLE_DATE'  => $this->DB_TABLE_DATE,
                'DB_TABLE_REFERENCE'  => $this->DB_TABLE_REFERENCE,
                'DB_TABLE_SECONDARY_REFERENCE'  => $this->DB_TABLE_SECONDARY_REFERENCE,
                'DB_TABLE_NAME'  => $this->DB_TABLE_NAME,
                'DB_TABLE_API_URL'  => $this->DB_TABLE_API_URL,
                'DB_TABLE_API_PASSWORD'  => $this->DB_TABLE_API_PASSWORD,
                'DB_TABLE_API_PRIVATE_KEY'  => $this->DB_TABLE_API_PRIVATE_KEY,
                'NODE_TYPE'  => $this->nodeType,
                'QUERY'  => $this->QUERY,
                'NODE_STATUS'  =>  'ACTIVE',
                'DATA_SOURCE_TYPE'  => $this->DATA_SOURCE_TYPE

            ];


            $update_value = approvals::updateOrCreate(
                [
                    'process_id' => $this->nodeToEditId,
                    'user_id' => Auth::user()->id

                ],
                [
                    'institution' => '',
                    'process_name' => 'editNode',
                    'process_description' => Auth::user()->name.' has requested to edit a NODE - '.$this->NODE_NAME,
                    'approval_process_description' => '',
                    'process_code' => '22',
                    'process_id' => $this->nodeToEditId,
                    'process_status' => 'PENDING',
                    'approval_status' => 'PENDING',
                    'user_id'  => Auth::user()->id,
                    'team_id'  => '',
                    'edit_package'=> json_encode($data),

                ]
            );
            Session::flash('message', 'Awaiting approval');
            Session::flash('alert-class', 'alert-success');

        }else{

            Session::flash('message', 'Please select a node to edit!');
            Session::flash('alert-class', 'alert-warning');


        }


        $this->resetData();

    }

    public function resetData(): void
    {
        $this->NODE_NAME = null;
        $this->NODE_DB_HOST = null;
        $this->NODE_DB_PORT = null;
        $this->NODE_DB_DATABASE = null;
        $this->NODE_DB_USERNAME = null;
        $this->NODE_DB_PASSWORD = null;
        $this->DB_TABLE_TRANSACTION_TYPE = null;
        $this->DB_TABLE_CLIENT_IDENTIFIER = null;
        $this->DB_TABLE_SERVICE_IDENTIFIER = null;
        $this->DB_TABLE_STATUS = null;
        $this->DB_TABLE_DESCRIPTION = null;
        $this->DB_TABLE_SENDER = null;
        $this->DB_TABLE_RECEIVER = null;
        $this->DB_TABLE_AMOUNT = null;
        $this->DB_TABLE_DATE = null;
        $this->DB_TABLE_REFERENCE = null;
        $this->DB_TABLE_SECONDARY_REFERENCE = null;
        $this->DB_TABLE_NAME = null;
        $this->nodeType = null;
        $this->NODE_STATUS = null;
        $this->QUERY = null;
        $this->DATA_SOURCE_TYPE = null;
        $this->nodeToEditId = null;
        $this->DB_TABLE_API_URL = null;
        $this->DB_TABLE_API_PASSWORD = null;
        $this->DB_TABLE_API_PRIVATE_KEY = null;
        $this->nodeSelected = null;
    }



    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        $this->nodesList = NodesList::get();
        //dd($this->nodesList );
        return view('livewire.nodes.edit-node');
    }
}
