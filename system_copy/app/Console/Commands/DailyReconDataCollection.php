<?php
namespace App\Console\Commands;

use App\Models\NodesList;
use App\Models\recon_sessions;
use App\Models\Transactions;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class DailyReconDataCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DataCollection:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect data from DB nodes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle():
    int
    {

        $this->getData();
        $this->info('Successfully Collected data from DB nodes. - ' . \Carbon\Carbon::now());
        return 1;
    }

    /**
     * @throws GuzzleException
     */
    public function getData():void
    {



        $NODE_NAME = '';
        $NODE_DB_HOST = '';
        $NODE_DB_DATABASE = '';
        $NODE_DB_USERNAME = '';
        $NODE_DB_PASSWORD = '';
        $NODE_DB_PORT = '';
        $DB_TABLE_CLIENT_IDENTIFIER = '';
        $DB_TABLE_SERVICE_IDENTIFIER = '';
        $DB_TABLE_STATUS = '';
        $DB_TABLE_DESCRIPTION = '';
        $DB_TABLE_TRANSACTION_TYPE = '';
        $DB_TABLE_SENDER = '';
        $DB_TABLE_RECEIVER = '';
        $DB_TABLE_AMOUNT = '';
        $DB_TABLE_DATE = '';
        $DB_TABLE_REFERENCE = '';
        $DB_TABLE_SECONDARY_REFERENCE = '';

        $PAN = '';
        $QUERY = null;
        $updated_at = Carbon::now();
        $created_at = Carbon::now();
        $recon_value_date=null;


        $time = Carbon::now();
        $startTime = $time->format('H:i:s');
        $SESSION_ID = $time->format('Y-m-d\TH:i:s');

        // Get the start date from the session
        $startDate = Session::get('sessionValueDate');



        // Get all nodes that are of type "Database"
        $nodes = NodesList::where('NODE_TYPE', 'INTERNAL_NODE')->where("NODE_DATA_SOURCE","Database")->get();

        // Iterate over the nodes
        foreach ($nodes as $node)
        {


            // Get the node data
            $nodeData = NodesList::where('ID', $node->ID)->get();
            //dd($nodeData);

            foreach ($nodeData as $data) {

                $NODE_NAME = $data->NODE_NAME;
                $NODE_DB_HOST = $data->NODE_DB_HOST;
                $NODE_DB_DATABASE = $data->NODE_DB_DATABASE;
                $NODE_DB_USERNAME = $data->NODE_DB_USERNAME;
                $NODE_DB_PASSWORD = $data->NODE_DB_PASSWORD;
                $NODE_DB_PORT = $data->NODE_DB_PORT;
                $QUERY = $data->QUERY;
                $DB_TABLE_TRANSACTION_TYPE = $data->DB_TABLE_TRANSACTION_TYPE;
                $DB_TABLE_CLIENT_IDENTIFIER = $data->DB_TABLE_CLIENT_IDENTIFIER;
                $DB_TABLE_SERVICE_IDENTIFIER = $data->DB_TABLE_SERVICE_IDENTIFIER;
                $DB_TABLE_STATUS = $data->DB_TABLE_STATUS;
                $DB_TABLE_DESCRIPTION = $data->DB_TABLE_DESCRIPTION;
                $DB_TABLE_SENDER = $data->DB_TABLE_SENDER;
                $DB_TABLE_RECEIVER = $data->DB_TABLE_RECEIVER;
                $DB_TABLE_AMOUNT = $data->DB_TABLE_AMOUNT;
                $DB_TABLE_DATE = $data->DB_TABLE_DATE;
                $DB_TABLE_REFERENCE = $data->DB_TABLE_REFERENCE;
                $DB_TABLE_SECONDARY_REFERENCE = $data->DB_TABLE_SECONDARY_REFERENCE;

                $PAN = $data->PAN;
            }
            Config::set("database.connections.sqlsrvOut", [
                'driver' => 'sqlsrv',
                "host" => $NODE_DB_HOST,
                "database" => $NODE_DB_DATABASE,
                "username" => $NODE_DB_USERNAME,
                "password" => $NODE_DB_PASSWORD,
                "port" => $NODE_DB_PORT,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => false,
            ]);


            /////////////////////////DATABASE NODE/////////////////////////
            if (trim($node->DATA_SOURCE_TYPE) == 'Database')
            {
                //dd('database');
                // Set the connection variables
                Config::set("database.connections.sqlsrvOut", ['driver' => 'sqlsrv', "host" => $NODE_DB_HOST, "database" => $NODE_DB_DATABASE, "username" => $NODE_DB_USERNAME, "password" => $NODE_DB_PASSWORD, "port" => $NODE_DB_PORT, 'charset' => 'utf8', 'collation' => 'utf8_unicode_ci', 'prefix' => '', 'strict' => false, ]);

                // If there is a query, run it
                if ($QUERY)
                {

                    // Get the timestamp of the start date
                    $timestamp = strtotime($startDate);

                    // Format the date
                    $formatted_date = date('Y-m-d', $timestamp);
                    $recon_value_date = $formatted_date;
                    // Replace the placeholder in the query with the formatted date
                    $updated_query = str_replace('date_placeholder', $formatted_date, $QUERY);

                    // Run the query and get the results
                    $array_list = DB::connection('sqlsrvOut')->select($updated_query);

                    // Disconnect from the connection
                    DB::disconnect('sqlsrvOut');



                    // Truncate the table
                    DB::table($NODE_NAME)->truncate();

                    // Iterate over the results
                    foreach ($array_list as $item)
                    {


                        //dd($item->$DB_TABLE_AMOUNT);
                        // Get the amount
                        //$DB_TABLE_AMOUNT = '';
                        $postilion_processed_amount = $item->$DB_TABLE_AMOUNT;

                        // If there is a secondary reference, get it
                        //$DB_TABLE_SECONDARY_REFERENCE = '';
                        if ($DB_TABLE_SECONDARY_REFERENCE || $DB_TABLE_SECONDARY_REFERENCE != '')
                        {
                            if ($item->$DB_TABLE_SECONDARY_REFERENCE || $item->$DB_TABLE_SECONDARY_REFERENCE != '')
                            {
                                $secondary_reference = $item->$DB_TABLE_SECONDARY_REFERENCE;
                            }
                            else
                            {
                                $secondary_reference = 'No ref - ' . rand(10, 1000000000);
                            }
                        }
                        else
                        {
                            $secondary_reference = 'No ref - ' . rand(10, 1000000000);
                        }

                        // If there is a reference, get it
                        //$DB_TABLE_REFERENCE = '';
                        if ($DB_TABLE_REFERENCE || $DB_TABLE_REFERENCE != '')
                        {
                            if ($item->$DB_TABLE_REFERENCE || $item->$DB_TABLE_REFERENCE != '')
                            {
                                $reference = $item->$DB_TABLE_REFERENCE;
                            }
                            else
                            {
                                $reference = 'No ref - ' . rand(10, 1000000000);
                            }
                        }
                        else
                        {
                            $reference = 'No ref - ' . rand(10, 1000000000);
                        }

                        // Trim the transaction type
                        //$DB_TABLE_TRANSACTION_TYPE = '';
                        $DB_TABLE_TRANSACTION_TYPE = trim($DB_TABLE_TRANSACTION_TYPE);

                        // Trim the date
                        //$DB_TABLE_DATE = '';
                        $DB_TABLE_DATE = trim($DB_TABLE_DATE);

                        // Insert the row into the table



                        DB::table($NODE_NAME)
                            ->insert([
                                'SESSION_ID' => $SESSION_ID,
                                'DB_TABLE_TRANSACTION_TYPE' => $item->$DB_TABLE_TRANSACTION_TYPE,
                                'DB_TABLE_CLIENT_IDENTIFIER' => $item->$DB_TABLE_CLIENT_IDENTIFIER,
                                'DB_TABLE_SERVICE_IDENTIFIER' => $item->$DB_TABLE_SERVICE_IDENTIFIER ,
                                'DB_TABLE_STATUS' => $item->$DB_TABLE_STATUS,
                                'DB_TABLE_DESCRIPTION' => $item->$DB_TABLE_DESCRIPTION,
                                'DB_TABLE_SENDER' => $item->$DB_TABLE_SENDER,
                                'DB_TABLE_RECEIVER' => $item->$DB_TABLE_RECEIVER,
                                'DB_TABLE_AMOUNT' => $postilion_processed_amount,
                                'DB_TABLE_DATE' => $item->$DB_TABLE_DATE,
                                'DB_TABLE_REFERENCE' => $reference,
                                'DB_TABLE_SECONDARY_REFERENCE' => $secondary_reference,
                                'PAN' => ''
                            ]);
                    }
                }

            }

            /////////////////////////API NODE////////////////////////////
            if ($node->DATA_SOURCE_TYPE == 'API')
            {

                $client = new Client();

                $response = $client->post($node->DB_TABLE_API_URL, ['headers' => ['Authorization' => 'Bearer ' . $node->DB_TABLE_API_PASSWORD, 'Content-Type' => 'application/json', ], 'body' => json_encode(['DB_TABLE_API_PRIVATE_KEY' => $node->DB_TABLE_API_PRIVATE_KEY, 'DATE' => $node->DATE, 'NODE_NAME' => $node->NODE_NAME, ]) , ]);

                if ($response->getStatusCode() === 200)
                {
                    $data = json_decode($response->getBody() , true);

                    foreach ($data as $item)
                    {

                        $DB_TABLE_SERVICE_IDENTIFIER = '';
                        DB::table($node->NODE_NAME)
                            ->insert(['SESSION_ID' => $SESSION_ID, 'DB_TABLE_TRANSACTION_TYPE' => $item->$DB_TABLE_TRANSACTION_TYPE, 'DB_TABLE_CLIENT_IDENTIFIER' => $item->$DB_TABLE_CLIENT_IDENTIFIER, 'DB_TABLE_SERVICE_IDENTIFIER' => $item->$DB_TABLE_SERVICE_IDENTIFIER, 'DB_TABLE_STATUS' => $item->$DB_TABLE_STATUS, 'DB_TABLE_DESCRIPTION' => $item->$DB_TABLE_DESCRIPTION, 'DB_TABLE_SENDER' => $item->$DB_TABLE_SENDER, 'DB_TABLE_RECEIVER' => $item->$DB_TABLE_RECEIVER, 'DB_TABLE_AMOUNT' => $item->$DB_TABLE_AMOUNT, 'DB_TABLE_DATE' => $item->$DB_TABLE_DATE, 'DB_TABLE_REFERENCE' => $item->$DB_TABLE_REFERENCE, 'DB_TABLE_SECONDARY_REFERENCE' => $item->$DB_TABLE_SECONDARY_REFERENCE, 'PAN' => '', ]);
                    }
                }
                else
                {
                    // Get the HTTP status code
                    $statusCode = $response->getStatusCode();

                    // Get the HTTP body
                    $body = $response->getBody();

                    // Log the error
                    //\Log::error("API error: $statusCode - $body");
                    // Record the error to the database
                    DB::table('api_errors')
                        ->insert(['statusCode' => $statusCode, 'NODE_NAME' => $node->NODE_NAME, 'SESSION_ID' => $SESSION_ID, 'body' => $body, 'created_at' => Carbon::now() , ]);
                }
            }

        }




                ///////////////////////////////////////////RECON PROCESS////////////////////////////////////////////////////////
                DB::table('RECON_DATA')->where('VALUE_DATE', $recon_value_date)->delete();
                $nodes_ = NodesList::where('NODE_TYPE','INTERNAL_NODE')->whereNot('NODE_NAME', 'POSTILION')->get();


                foreach ($nodes_ as $node) {

                    $transactions = DB::table($node->NODE_NAME)->get();


                    if($transactions->count() > 0){



                    foreach ($transactions as $transaction) {


                        $data = [
                            'SESSION_ID' => $transaction->SESSION_ID,
                            'CHANNEL' => $node->NODE_NAME,
                            'VALUE_DATE' => $transaction->DB_TABLE_DATE,
                            'DB_TABLE_REFERENCE' => $transaction->DB_TABLE_REFERENCE,
                            'DB_TABLE_SECONDARY_REFERENCE' => $transaction->DB_TABLE_SECONDARY_REFERENCE,
                            'NODE_DB_TABLE_AMOUNT' => $transaction->DB_TABLE_AMOUNT,
                            'NODE_DB_TABLE_TRANSACTION_TYPE' => $transaction->DB_TABLE_TRANSACTION_TYPE,
                            'NODE_DB_TABLE_SERVICE_IDENTIFIER' => $transaction->DB_TABLE_SERVICE_IDENTIFIER,
                            'NODE_DB_TABLE_STATUS' => $transaction->DB_TABLE_STATUS,
                            'NODE_DB_TABLE_DESCRIPTION' => $transaction->DB_TABLE_DESCRIPTION,
                            'RECON_RESULTS' => 'PASSED'
                        ];

                        DB::table('RECON_DATA')->upsert(
                            [$data],
                            ['DB_TABLE_REFERENCE', 'DB_TABLE_SECONDARY_REFERENCE'],
                            ['RECON_RESULTS']
                        );

                        $id = DB::table('RECON_DATA')
                            ->where('DB_TABLE_REFERENCE', $data['DB_TABLE_REFERENCE'])
                            ->where('DB_TABLE_SECONDARY_REFERENCE', $data['DB_TABLE_SECONDARY_REFERENCE'])
                            ->value('id');





                            $transactions2 = DB::table('POSTILION')->where('DB_TABLE_REFERENCE', $transaction->DB_TABLE_REFERENCE)->get();
                            if ($transactions2) {
                                foreach ($transactions2 as $transaction2) {

                                    $affected = DB::table('RECON_DATA')
                                        ->where('id', $id)
                                        ->update([

                                            'POSTILION_DB_TABLE_AMOUNT' => $transaction2->DB_TABLE_AMOUNT,
                                            'POSTILION_DB_TABLE_TRANSACTION_TYPE' => $transaction2->DB_TABLE_TRANSACTION_TYPE,
                                            'POSTILION_DB_TABLE_SERVICE_IDENTIFIER' => $transaction2->DB_TABLE_SERVICE_IDENTIFIER,
                                            'POSTILION_DB_TABLE_STATUS' => $transaction2->DB_TABLE_STATUS,
                                            'POSTILION_DB_TABLE_DESCRIPTION' => $transaction2->DB_TABLE_DESCRIPTION,
                                        ]);

                                }

                            }

                            $processor_nodes = NodesList::where('NODE_TYPE', 'PROCESSOR_NODE')->get();
                            foreach ($processor_nodes as $processor_node) {
                                $NODE_NAME = $processor_node->NODE_NAME;

                                if (Schema::hasTable($NODE_NAME)) {
                                    if (
                                        DB::table($NODE_NAME)->where('DB_TABLE_REFERENCE', $transaction->DB_TABLE_REFERENCE)->exists() ||
                                        DB::table($NODE_NAME)->where('DB_TABLE_REFERENCE', $transaction->DB_TABLE_SECONDARY_REFERENCE)->exists() ||
                                        DB::table($NODE_NAME)->where('DB_TABLE_SECONDARY_REFERENCE', $transaction->DB_TABLE_SECONDARY_REFERENCE)->exists() ||
                                        DB::table($NODE_NAME)->where('DB_TABLE_SECONDARY_REFERENCE', $transaction->DB_TABLE_REFERENCE)->exists()
                                    ) {

                                        $third_transactions = DB::table($NODE_NAME)->where('DB_TABLE_REFERENCE', $transaction->DB_TABLE_REFERENCE)->get();
                                        if ($third_transactions) {
                                            foreach ($third_transactions as $third_transaction) {

                                                $affected = DB::table('RECON_DATA')
                                                    ->where('id', $id)
                                                    ->update([

                                                        'PROCESSOR_NODE' => $NODE_NAME,
                                                        'PROCESSOR_TABLE_AMOUNT' => $third_transaction->DB_TABLE_AMOUNT,
                                                        'PROCESSOR_TABLE_TRANSACTION_TYPE' => $third_transaction->DB_TABLE_TRANSACTION_TYPE,
                                                        'PROCESSOR_TABLE_SERVICE_IDENTIFIER' => $third_transaction->DB_TABLE_SERVICE_IDENTIFIER,
                                                        'PROCESSOR_TABLE_STATUS' => $third_transaction->DB_TABLE_STATUS,
                                                        'PROCESSOR_TABLE_DESCRIPTION' => $third_transaction->DB_TABLE_DESCRIPTION,
                                                    ]);

                                                $affected = DB::table($NODE_NAME)
                                                    ->where('id', $third_transaction->ID)
                                                    ->update([

                                                        'RECON_RESULTS' => 'PASSED',
                                                    ]);

                                            }
                                        }

                                        $third_transactions1 = DB::table($NODE_NAME)->where('DB_TABLE_REFERENCE', $transaction->DB_TABLE_SECONDARY_REFERENCE)->get();
                                        if ($third_transactions1) {
                                            foreach ($third_transactions1 as $third_transaction1) {

                                                $affected = DB::table('RECON_DATA')
                                                    ->where('id', $id)
                                                    ->update([


                                                        'PROCESSOR_NODE' => $NODE_NAME,
                                                        'PROCESSOR_TABLE_AMOUNT' => $third_transaction1->DB_TABLE_AMOUNT,
                                                        'PROCESSOR_TABLE_TRANSACTION_TYPE' => $third_transaction1->DB_TABLE_TRANSACTION_TYPE,
                                                        'PROCESSOR_TABLE_SERVICE_IDENTIFIER' => $third_transaction1->DB_TABLE_SERVICE_IDENTIFIER,
                                                        'PROCESSOR_TABLE_STATUS' => $third_transaction1->DB_TABLE_STATUS,
                                                        'PROCESSOR_TABLE_DESCRIPTION' => $third_transaction1->DB_TABLE_DESCRIPTION,
                                                    ]);

                                                $affected = DB::table($NODE_NAME)
                                                    ->where('id', $third_transaction1->ID)
                                                    ->update([

                                                        'RECON_RESULTS' => 'PASSED',
                                                    ]);

                                            }
                                        }

                                        $third_transactions2 = DB::table($NODE_NAME)->where('DB_TABLE_SECONDARY_REFERENCE', $transaction->DB_TABLE_SECONDARY_REFERENCE)->get();
                                        if ($third_transactions2) {
                                            foreach ($third_transactions2 as $third_transaction2) {

                                                $affected = DB::table('RECON_DATA')
                                                    ->where('id', $id)
                                                    ->update([


                                                        'PROCESSOR_NODE' => $NODE_NAME,
                                                        'PROCESSOR_TABLE_AMOUNT' => $third_transaction2->DB_TABLE_AMOUNT,
                                                        'PROCESSOR_TABLE_TRANSACTION_TYPE' => $third_transaction2->DB_TABLE_TRANSACTION_TYPE,
                                                        'PROCESSOR_TABLE_SERVICE_IDENTIFIER' => $third_transaction2->DB_TABLE_SERVICE_IDENTIFIER,
                                                        'PROCESSOR_TABLE_STATUS' => $third_transaction2->DB_TABLE_STATUS,
                                                        'PROCESSOR_TABLE_DESCRIPTION' => $third_transaction2->DB_TABLE_DESCRIPTION,
                                                    ]);

                                                $affected = DB::table($NODE_NAME)
                                                    ->where('id', $third_transaction2->ID)
                                                    ->update([

                                                        'RECON_RESULTS' => 'PASSED',
                                                    ]);

                                            }
                                        }

                                        $third_transactions3 = DB::table($NODE_NAME)->where('DB_TABLE_SECONDARY_REFERENCE', $transaction->DB_TABLE_REFERENCE)->get();
                                        if ($third_transactions3) {
                                            foreach ($third_transactions3 as $third_transaction3) {

                                                $affected = DB::table('RECON_DATA')
                                                    ->where('id', $id)
                                                    ->update([

                                                        'PROCESSOR_NODE' => $NODE_NAME,
                                                        'PROCESSOR_TABLE_AMOUNT' => $third_transaction3->DB_TABLE_AMOUNT,
                                                        'PROCESSOR_TABLE_TRANSACTION_TYPE' => $third_transaction3->DB_TABLE_TRANSACTION_TYPE,
                                                        'PROCESSOR_TABLE_SERVICE_IDENTIFIER' => $third_transaction3->DB_TABLE_SERVICE_IDENTIFIER,
                                                        'PROCESSOR_TABLE_STATUS' => $third_transaction3->DB_TABLE_STATUS,
                                                        'PROCESSOR_TABLE_DESCRIPTION' => $third_transaction3->DB_TABLE_DESCRIPTION,
                                                    ]);

                                                $affected = DB::table($NODE_NAME)
                                                    ->where('id', $third_transaction3->ID)
                                                    ->update([

                                                        'RECON_RESULTS' => 'PASSED',
                                                    ]);
                                            }
                                        }

                                    }

                                }

                            }

                            $affected = DB::table('RECON_DATA')
                                ->where('id', $id)
                                ->update([
                                    'RECON_STATUS' => 'DONE',
                                ]);

                            $affected = DB::table('RECON_DATA')
                                ->where('id', $id)
                                ->whereNotNull([
                                    'DB_TABLE_REFERENCE',
                                    'NODE_DB_TABLE_AMOUNT',
                                    'POSTILION_DB_TABLE_AMOUNT',
                                    'POSTILION_DB_TABLE_STATUS',
                                    'PROCESSOR_TABLE_AMOUNT',
                                    'PROCESSOR_TABLE_STATUS',
                                ])
                                ->update([
                                    'RECON_RESULTS' => 'PASSED',
                                ]);



                    }

                }

                }




                $thirdNodes = NodesList::where('NODE_TYPE', 'PROCESSOR_NODE')->get();
                $timestamp = strtotime($startDate);

                    // Format the date
                    $formatted_date = date('Y-m-d', $timestamp);

                foreach ($thirdNodes as $thirdNode) {
                    //$formatted_date = '2023-05-15'; // Modify the date format here if necessary

                    DB::table($thirdNode->NODE_NAME)
                        ->where(DB::raw("CAST(DB_TABLE_DATE AS DATE)"), $formatted_date)
                        ->update(['RECON_STATUS' => null]);
                    $transactionsA = DB::table($thirdNode->NODE_NAME)->whereNull('RECON_STATUS')->get();


                    DB::transaction(function () use ($thirdNode, $transactionsA) {
                        $transactionBData = $transactionsA->map(function ($transactionA) use ($thirdNode) {

                            return [
                                'SESSION_ID' => $transactionA->SESSION_ID,
                                'CHANNEL' => 'UNKNOWN',
                                'VALUE_DATE' => $transactionA->DB_TABLE_DATE,
                                'DB_TABLE_REFERENCE' => $transactionA->DB_TABLE_REFERENCE,
                                'DB_TABLE_SECONDARY_REFERENCE' => $transactionA->DB_TABLE_SECONDARY_REFERENCE,
                                'RECON_STATUS' => 'DONE',

                                'PROCESSOR_NODE' => $thirdNode->NODE_NAME,
                                'PROCESSOR_TABLE_AMOUNT' => $transactionA->DB_TABLE_AMOUNT,
                                'PROCESSOR_TABLE_TRANSACTION_TYPE' => $transactionA->DB_TABLE_TRANSACTION_TYPE,
                                'PROCESSOR_TABLE_SERVICE_IDENTIFIER' => $transactionA->DB_TABLE_SERVICE_IDENTIFIER,
                                'PROCESSOR_TABLE_STATUS' => $transactionA->DB_TABLE_STATUS,
                                'PROCESSOR_TABLE_DESCRIPTION' => $transactionA->DB_TABLE_DESCRIPTION,

                                'RECON_RESULTS' => null,
                                // ...
                            ];
                        });


                        Transactions::insert($transactionBData->toArray());

                        DB::table($thirdNode->NODE_NAME)
                            ->whereIn('ID', $transactionsA->pluck('ID'))
                            ->update(['RECON_STATUS' => 'DONE']);
                    });
                }

                $timestamp = strtotime($startDate);
                $formatted_date = date('Y-m-d', $timestamp);
                $recn_session = new recon_sessions();
                $recn_session->session_id = $SESSION_ID;
                $recn_session->recon_date = $formatted_date;
                $recn_session->start_timestamp = $startTime ;
                $recn_session->save();

    }

}