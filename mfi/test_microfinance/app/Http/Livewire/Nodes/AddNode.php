<?php

namespace App\Http\Livewire\Nodes;

use App\Models\approvals;
use App\Models\NodesList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AddNode extends Component
{

    /**
     * @var null
     */
    public $nodeToEditId;
    /**
     * @var null
     */
    public $NODE_NAME;
    /**
     * @var null
     */
    public $NODE_DB_HOST;
    /**
     * @var null
     */
    public $NODE_DB_PORT;
    /**
     * @var null
     */
    public $NODE_DB_DATABASE;
    /**
     * @var null
     */
    public $NODE_DB_USERNAME;
    /**
     * @var null
     */
    public $NODE_DB_PASSWORD;
    /**
     * @var null
     */
    public $DB_TABLE_TRANSACTION_TYPE;
    /**
     * @var null
     */
    public $DB_TABLE_CLIENT_IDENTIFIER;
    /**
     * @var null
     */
    public $DB_TABLE_SERVICE_IDENTIFIER;
    /**
     * @var null
     */
    public $DB_TABLE_STATUS;
    /**
     * @var null
     */
    public $DB_TABLE_DESCRIPTION;
    /**
     * @var null
     */
    public $DB_TABLE_SENDER;
    /**
     * @var null
     */
    public $DB_TABLE_AMOUNT;
    /**
     * @var null
     */
    public $DB_TABLE_RECEIVER;
    /**
     * @var null
     */
    public $DB_TABLE_DATE;
    /**
     * @var null
     */
    public $DB_TABLE_REFERENCE;
    /**
     * @var null
     */
    public $DB_TABLE_SECONDARY_REFERENCE;
    /**
     * @var null
     */
    public $DB_TABLE_NAME;
    /**
     * @var null
     */
    public $nodeType;
    /**
     * @var null
     */
    public $QUERY = null;
    /**
     * @var null
     */
    public $DATASOURCETYPE ='Portal';
    public $Node;
    public $NODE_STATUS;
    public $DB_TABLE_API_URL;
    public $DB_TABLE_API_PASSWORD;
    public $DB_TABLE_API_PRIVATE_KEY;

    protected $listeners = ['clearBag' => 'clearErrorBag'];

    public function save(): void
    {


            if($this->DATASOURCETYPE == 'Database'){

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
                    'DATASOURCETYPE' => 'required|min:3',
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

            }elseif ($this->DATASOURCETYPE == 'API'){



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


            if (Schema::hasTable($this->NODE_NAME))
            {
                Session::flash('message', 'This node is already available!');
                Session::flash('alert-class', 'alert-warning');
            }else{

                $Node = new NodesList;
                $Node->NODE_NAME = $this->NODE_NAME;
                $Node->NODE_DB_HOST = $this->NODE_DB_HOST;
                $Node->NODE_DB_PORT = $this->NODE_DB_PORT;
                $Node->NODE_DB_DATABASE = $this->NODE_DB_DATABASE;
                $Node->NODE_DB_USERNAME = $this->NODE_DB_USERNAME;
                $Node->NODE_DB_PASSWORD = $this->NODE_DB_PASSWORD;
                $Node->DB_TABLE_TRANSACTION_TYPE = $this->DB_TABLE_TRANSACTION_TYPE;
                $Node->DB_TABLE_CLIENT_IDENTIFIER = $this->DB_TABLE_CLIENT_IDENTIFIER;
                $Node->DB_TABLE_SERVICE_IDENTIFIER = $this->DB_TABLE_SERVICE_IDENTIFIER;
                $Node->DB_TABLE_STATUS = $this->DB_TABLE_STATUS;
                $Node->DB_TABLE_DESCRIPTION = $this->DB_TABLE_DESCRIPTION;
                $Node->DB_TABLE_SENDER = $this->DB_TABLE_SENDER;
                $Node->DB_TABLE_RECEIVER = $this->DB_TABLE_RECEIVER;
                $Node->DB_TABLE_AMOUNT = $this->DB_TABLE_AMOUNT;
                $Node->DB_TABLE_DATE = $this->DB_TABLE_DATE;
                $Node->DB_TABLE_REFERENCE = $this->DB_TABLE_REFERENCE;
                $Node->DB_TABLE_SECONDARY_REFERENCE = $this->DB_TABLE_SECONDARY_REFERENCE;
                $Node->DB_TABLE_NAME = $this->DB_TABLE_NAME;
                $Node->NODE_TYPE = $this->nodeType;
                $Node->QUERY = $this->QUERY;
                $Node->NODE_STATUS = 'PENDING';
                $Node->DATA_SOURCE_TYPE = $this->DATASOURCETYPE;
                $Node->DB_TABLE_API_URL = $this->DB_TABLE_API_URL;
                $Node->DB_TABLE_API_PASSWORD = $this->DB_TABLE_API_PASSWORD;
                $Node->DB_TABLE_API_PRIVATE_KEY = $this->DB_TABLE_API_PRIVATE_KEY;


                if ($Node->save()) {


                    Schema::create($this->NODE_NAME, function($table) {
                        $table->increments('ID');
                        $table->string('SESSION_ID');
                        $table->string('PAN');
                        $table->string('DB_TABLE_TRANSACTION_TYPE');
                        $table->string('DB_TABLE_CLIENT_IDENTIFIER');
                        $table->string('DB_TABLE_SERVICE_IDENTIFIER');
                        $table->string('DB_TABLE_STATUS');
                        $table->string('DB_TABLE_DESCRIPTION');
                        $table->string('DB_TABLE_SENDER');
                        $table->string('DB_TABLE_RECEIVER');
                        $table->string('DB_TABLE_AMOUNT');
                        $table->string('DB_TABLE_DATE');
                        $table->string('DB_TABLE_REFERENCE');
                        $table->string('DB_TABLE_SECONDARY_REFERENCE');
                        $table->string('RECON_STATUS');
                        $table->string('RECON_RESULTS');
                        $table->string('DATA_SOURCE_TYPE');
                        $table->string('DB_TABLE_API_URL');
                        $table->string('DB_TABLE_API_PASSWORD');
                        $table->string('DB_TABLE_API_PRIVATE_KEY');
                        $table->dateTime('updated_at');
                        $table->dateTime('created_at');
                    });




                    $update_value = approvals::updateOrCreate(
                        [
                            'process_id' => $Node->ID,
                            'user_id' => Auth::user()->id

                        ],
                        [
                            'institution' => '',
                            'process_name' => 'addNode',
                            'process_description' => Auth::user()->name.' has requested to add a NODE - '.$this->NODE_NAME,
                            'approval_process_description' => '',
                            'process_code' => '25',
                            'process_id' => $Node->ID,
                            'process_status' => 'PENDING',
                            'approval_status' => 'PENDING',
                            'user_id'  => Auth::user()->id,
                            'team_id'  => ''

                        ]
                    );


                    Session::flash('message', 'Node has been successfully saved, awaiting approval for activation!');
                    Session::flash('alert-class', 'alert-success');

                }
                //


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
        $this->DATASOURCETYPE = null;
        $this->nodeToEditId = null;
        $this->DB_TABLE_API_URL = null;
        $this->DB_TABLE_API_PASSWORD = null;
        $this->DB_TABLE_API_PRIVATE_KEY = null;
        $this->resetErrorBag();
    }

    public function updatedDATASOURCETYPE(){
        //dd('hhh');
        $this->resetErrorBag();
    }


    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view('livewire.nodes.add-node');
    }
}
