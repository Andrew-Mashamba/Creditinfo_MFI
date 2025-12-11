<?php

namespace App\Http\Livewire\Process;

use App\Console\Commands\DailyReconDataCollection;
use App\Models\NodesList;
use App\Models\UserSubMenu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Process extends Component
{
    public string $processStatus;
    public string $startDate;
    public string $sessionDateStatus;
    public $nodes;
    public bool $oneIsSetClicked =true;
    public bool $twoIsSetClicked = false;
    public bool $threeIsSetClicked = false;
    public $selected;
    public $user_sub_menus;
    public $disableProcess = false;
    public $rerunProcess = false;
    public $sessionDateStatusFiles;
    public $sessionDateStatusDbs;


    public function boot(): void
    {
        $this->selected = 1;
        $this->processStatus = 'PENDING';
        $this->startDate = now()->startOfDay()->format('d-m-Y');


        $this->user_sub_menus = UserSubMenu::where('menu_id',1)->where('user_id', Auth::user()->id)->get();

    }

    // Function to check if connection to host exists
    // Function to check if connection to host exists
    function checkConnectionExists($host, $port, $dbName, $username, $password): bool
    {
        // Set the database connection configuration
        Config::set("database.connections.sqlsrvcheck", [
            'driver' => 'sqlsrv',
            'host' => $host . ',' . $port,
            'database' => $dbName,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ]);

        // Connect to the database
        DB::reconnect('sqlsrvcheck');

        $conn = DB::connection('sqlsrvcheck')->getPdo();

        if ($conn === null) {
            DB::disconnect('sqlsrvOut');
            return false;
        } else {
            DB::disconnect('sqlsrvOut');
            return true;
        }

    }
    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $timestamp = strtotime($this->startDate);
        $formatted_date = date('Y-m-d', $timestamp);

        $this->nodes = NodesList::where('NODE_TYPE', 'PROCESSOR_NODE')->get();
        $this->user_sub_menus = UserSubMenu::where('menu_id',1)->where('user_id', Auth::user()->id)->get();


        Session::put('sessionValueDate',$this->startDate);
        // Fetch node data from the database using Eloquent
        $nodes = NodesList::where('DATA_SOURCE_TYPE', 'Database')->get();
        $count = DB::table('RECON_DATA')->where('VALUE_DATE',$formatted_date)->count();
        if($count > 0){
            $this->sessionDateStatus = "Reconciliation process has already been done for this date. ".$count." transactions";
            $this->processStatus = 'DONE';
            $this->disableProcess = false;
            $this->rerunProcess = true;
        }else{
            $this->sessionDateStatus = "Reconciliation process is pending for this date";
            $this->processStatus = 'PENDING';
            $this->disableProcess = false;
            $this->rerunProcess = false;
        }
        // Loop through each node
        //dd($nodes);
        foreach ($nodes as $node) {

            // Check if connection to host exists
            $isConnectionExists = $this->checkConnectionExists($node->NODE_DB_HOST, $node->NODE_DB_PORT, $node->NODE_DB_NAME, $node->NODE_DB_USERNAME, $node->NODE_DB_PASSWORD);
            // Update NODE_STATUS based on connection existence

            if ($isConnectionExists) {
                $nodeStatus = 'ACTIVE';
            } else {
                $nodeStatus = 'INACTIVE';

                //$this->sessionDateStatusDbs = "One or more nodes are offline, Reconciliation process will not run";
                $this->processStatus = 'PENDING';
                $this->disableProcess = true;
                $this->rerunProcess = false;
            }

            // Update NODE_STATUS in the database using Eloquent
            //Nodes::where('ID', $node->ID)->update(['NODE_STATUS' => $nodeStatus]);

            $nodeToUpdate = NodesList::where('ID', $node->ID)->update(['NODE_STATUS' => $nodeStatus]);
            //dd($post);
        }

        $portalNodeTableNames = NodesList::where('DATA_SOURCE_TYPE', 'Portal')->pluck('NODE_NAME')->toArray();

        $missingThirdPartyFiles = false;

        foreach ($portalNodeTableNames as $table) {
            $count = DB::table($table)->whereDate('DB_TABLE_DATE', $formatted_date)->count();
            if ($count < 1) {
                //dd($table);
                $missingThirdPartyFiles = true;
                break;
            }
        }

        if ($missingThirdPartyFiles) {
            //$this->sessionDateStatusFiles = "One or more third party files are missing, Reconciliation process will not run";
            $this->processStatus = 'PENDING';
            $this->disableProcess = true;
            $this->rerunProcess = false;
        }

        $this->user_sub_menus = UserSubMenu::where('menu_id',1)->where('user_id', Auth::user()->id)->get();


        return view('livewire.process.process');
    }

    public function setView($page){
        $this->selected = $page;
    }

    public function changeTab($index)
    {

        $this->selected = $index;
        if($index == 1){
            $this->oneIsSetClicked = true;
            $this->twoIsSetClicked = false;
            $this->threeIsSetClicked = false;
        }
        if($index == 2){
            $this->oneIsSetClicked = false;
            $this->twoIsSetClicked = true;
            $this->threeIsSetClicked = false;
        }
        if($index == 3){
            $this->oneIsSetClicked = false;
            $this->twoIsSetClicked = false;
            $this->threeIsSetClicked = true;
        }

    }

    public function updatedStartDate(){
        Session::put('sessionValueDate',$this->startDate);
        $this->emit('refreshTables');

    }


    public function run(): void
    {
        if($this->startDate) {

            //Session::get('typeOfTransfer')
            Session::put('sessionValueDate',$this->startDate);
            $f = new DailyReconDataCollection();
            $f->getData();

            $this->emit('refreshTables');
        }else{
            $this->sessionDateStatus = "Please, select a date";
        }
    }
}
