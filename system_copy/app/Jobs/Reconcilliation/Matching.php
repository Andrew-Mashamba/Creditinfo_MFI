<?php

namespace App\Jobs;

use App\Events\ReconciliationCompleted;
use App\Models\NodesList;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class reconciliationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $startDate; // Add this property
    protected $progress_bar_date;
    /**
     * The maximum number of times the job may be attempted.
     *
     * @var int
     */
    public $maxTries = 2;
    public $timeout = 1200; // 20 minutes (adjust as needed)
    /**
     * Create a new job instance.
     *
     * @param mixed $startDate
     * @return void
     */
    public function __construct($startDate)
    {
        $this->startDate = $startDate;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        //
        $startDate = $this->startDate;

        $timestamp = strtotime($this->startDate);
        $formatted_date = date('Y-m-d', $timestamp);
        $progress_date = $formatted_date;
        $this->progress_bar_date = $progress_date;
        Log::info("Recon start");
        DB::table('progress')->updateOrInsert(
            ['startDate' => $progress_date], // Condition to check if the record exists
            ['percentProgress' => 2]         // Data to insert or update
        );

        try {
            // Job logic here
            $this->runReconciliation($startDate);
        } catch (Exception $e) {
            // Handle job failure here
            Log::error('Job failed: ' . $e->getMessage());

            DB::table('progress')->updateOrInsert(
                ['startDate' => $progress_date], // Condition to check if the record exists
                ['status' => 'FAILED','narration'=>$e->getMessage()]         // Data to insert or update
            );
        }


    }




    public function runReconciliation($startDate): void
    {
        Log::info("...............................................................................".$startDate);

        Log::info("Recon date ".$startDate);
        Log::info("runReconciliation start ");
        $timestamp = strtotime($startDate);

        // Format the date
        $formatted_date = date('Y-m-d', $timestamp);
        $recon_value_date = $formatted_date;


        DB::table('INTERNAL_DATA')
            ->where(DB::raw("CAST(DB_TABLE_DATE AS DATE)"), $recon_value_date)
            ->delete();
        DB::table('EXTERNAL_DATA')
            ->where(DB::raw("CAST(DB_TABLE_DATE AS DATE)"), $recon_value_date)
            ->delete();

        DB::table('RECONCILIATION_DATA')
            ->where(DB::raw("CAST(internal_date AS DATE)"), $recon_value_date)
            ->delete();


        $timestamp = strtotime($this->startDate);
        $formatted_date = date('Y-m-d', $timestamp);
        $progress_date = $formatted_date;

        DB::table('progress')->updateOrInsert(
            ['startDate' => $progress_date], // Condition to check if the record exists
            ['percentProgress' => 20]         // Data to insert or update
        );


        $nodes = NodesList::where('NODE_TYPE', 'INTERNAL_NODE')
            ->where('NODE_DATA_SOURCE', 'Database')
            ->get();

        foreach ($nodes as $node) {
            $transactions = DB::table($node->NODE_NAME)
                ->where(DB::raw("CAST(DB_TABLE_DATE AS DATE)"), $recon_value_date)
                ->get();

            if ($transactions->isNotEmpty()) {
                foreach ($transactions as $transaction) {
                    $refNo = trim($transaction->DB_TABLE_REFERENCE);

                    $data = [
                        'SESSION_ID' => trim($transaction->SESSION_ID),
                        'DB_TABLE_TRANSACTION_TYPE' => trim($node->DB_TABLE_TRANSACTION_TYPE),
                        'DB_TABLE_CLIENT_IDENTIFIER' => trim($transaction->DB_TABLE_CLIENT_IDENTIFIER),
                        'DB_TABLE_SERVICE_IDENTIFIER' => trim($transaction->DB_TABLE_SERVICE_IDENTIFIER),
                        'DB_TABLE_STATUS' => trim($transaction->DB_TABLE_STATUS),
                        'DB_TABLE_DESCRIPTION' => trim($transaction->DB_TABLE_DESCRIPTION),
                        'DB_TABLE_SENDER' => trim($transaction->DB_TABLE_SENDER),
                        'DB_TABLE_RECEIVER' => trim($transaction->DB_TABLE_RECEIVER),
                        'DB_TABLE_AMOUNT' => trim($transaction->DB_TABLE_AMOUNT),
                        'DB_TABLE_DATE' => trim($transaction->DB_TABLE_DATE),
                        'DB_TABLE_REFERENCE' => $refNo,
                        'DB_TABLE_SECONDARY_REFERENCE' => trim($transaction->DB_TABLE_SECONDARY_REFERENCE),
                        'DB_TABLE_CLIENT' => trim($transaction->DB_TABLE_CLIENT),
                        'NODE' => trim($node->NODE_NAME),
                    ];

                    DB::table('INTERNAL_DATA')->upsert(
                        $data,
                        ['DB_TABLE_SECONDARY_REFERENCE']
                    );
                }
            }


        }


        $timestamp = strtotime($this->startDate);
        $formatted_date = date('Y-m-d', $timestamp);
        $progress_date = $formatted_date;

        DB::table('progress')->updateOrInsert(
            ['startDate' => $progress_date], // Condition to check if the record exists
            ['percentProgress' => 30]         // Data to insert or update
        );

        $thirdNodes = NodesList::where('NODE_TYPE', 'PROCESSOR_NODE')->get();
        Log::info("thirdNodes ".json_encode($thirdNodes));
        foreach ($thirdNodes as $thirdNode) {

            $transactions = DB::table($thirdNode->NODE_NAME)
                ->where(DB::raw("CAST(DB_TABLE_DATE AS DATE)"), $recon_value_date)
                ->get();

            Log::info("thirdNode ".json_encode($thirdNode->NODE_NAME));
            //Log::info("recon_value_date ".json_encode($recon_value_date));
            //Log::info("transactions ".json_encode($transactions));
            if ($transactions->count() > 0) {
                foreach ($transactions as $transaction) {

                    $data = [
                        'SESSION_ID' => trim($transaction->SESSION_ID),
                        'DB_TABLE_TRANSACTION_TYPE' => trim($transaction->DB_TABLE_TRANSACTION_TYPE),
                        'DB_TABLE_CLIENT_IDENTIFIER' => trim($transaction->DB_TABLE_CLIENT_IDENTIFIER),
                        'DB_TABLE_SERVICE_IDENTIFIER' => trim($transaction->DB_TABLE_SERVICE_IDENTIFIER),
                        'DB_TABLE_STATUS' => trim($transaction->DB_TABLE_STATUS),
                        'DB_TABLE_DESCRIPTION' => trim($transaction->DB_TABLE_DESCRIPTION),
                        'DB_TABLE_SENDER' => trim($transaction->DB_TABLE_SENDER),
                        'DB_TABLE_RECEIVER' => trim($transaction->DB_TABLE_RECEIVER),
                        'DB_TABLE_AMOUNT' => trim($transaction->DB_TABLE_AMOUNT),
                        'DB_TABLE_DATE' => trim($transaction->DB_TABLE_DATE),
                        'DB_TABLE_REFERENCE' => trim($transaction->DB_TABLE_REFERENCE),
                        'DB_TABLE_SECONDARY_REFERENCE' => trim($transaction->DB_TABLE_SECONDARY_REFERENCE),
                        'DB_TABLE_PROCESSOR' => trim($thirdNode->NODE_NAME)

                    ];
                    //dd($data);

                    DB::table('EXTERNAL_DATA')->upsert(
                        [$data],
                        ['DB_TABLE_REFERENCE']
                    );



                }

            }

        }

        $timestamp = strtotime($this->startDate);
        $formatted_date = date('Y-m-d', $timestamp);
        $progress_date = $formatted_date;
        /*try{
        DB::table('progress')->updateOrInsert(
            ['startDate' => $progress_date], // Condition to check if the record exists
            ['percentProgress' => 50]         // Data to insert or update
        );
        Log::info("runReconciliation end");*/
        $this->reconcileData();
        /*}
        catch(Exception $e){
        Log::info("ReconcileDataException ".$e->getMessage());
        }*/
    }

    public function reconcileData(): void
    {

        Log::info("reconcileData start");

        DB::table('progress')->insert(
            ['startDate' => $this->progress_bar_date], // Condition to check if the record exists
            ['percentProgress' => 50]         // Data to insert or update
        );
        $internalTransactions = DB::table('INTERNAL_DATA')
            ->whereNotNull('INTERNAL_DATA.DB_TABLE_SECONDARY_REFERENCE')
            ->select(
                'INTERNAL_DATA.ID as internal_id',
                'INTERNAL_DATA.SESSION_ID as internal_session_id',
                'INTERNAL_DATA.DB_TABLE_TRANSACTION_TYPE as internal_transaction_type',
                'INTERNAL_DATA.DB_TABLE_CLIENT_IDENTIFIER as internal_client_identifier',
                'INTERNAL_DATA.DB_TABLE_SERVICE_IDENTIFIER as internal_service_identifier',
                'INTERNAL_DATA.DB_TABLE_STATUS as internal_status',
                'INTERNAL_DATA.DB_TABLE_DESCRIPTION as internal_description',
                'INTERNAL_DATA.DB_TABLE_SENDER as internal_sender',
                'INTERNAL_DATA.DB_TABLE_RECEIVER as internal_receiver',
                'INTERNAL_DATA.DB_TABLE_AMOUNT as internal_amount',
                'INTERNAL_DATA.DB_TABLE_DATE as internal_date',
                'INTERNAL_DATA.DB_TABLE_REFERENCE as internal_reference',
                'INTERNAL_DATA.PAN as internal_pan',
                'INTERNAL_DATA.DB_TABLE_SECONDARY_REFERENCE as internal_secondary_reference',
                'INTERNAL_DATA.updated_at as internal_updated_at',
                'INTERNAL_DATA.created_at as internal_created_at',
                'INTERNAL_DATA.DB_TABLE_CLIENT as internal_client',
                'INTERNAL_DATA.NODE as internal_node'

            )
            ->get();

        Log::info("Transactions ".json_encode($internalTransactions));


        $combinedTransactions = [];

        //Log::info("reconcileData start 1 ");

        foreach ($internalTransactions as $internalTransaction) {

            Log::info("Transactions Loop ".json_encode($internalTransaction->internal_id));

            $externalTransaction = DB::table('EXTERNAL_DATA')
                ->where(function ($query) use ($internalTransaction) {
                    $query->where('DB_TABLE_REFERENCE', $internalTransaction->internal_secondary_reference)
                        ->orWhere('DB_TABLE_SECONDARY_REFERENCE', $internalTransaction->internal_secondary_reference);
                })
                ->select(
                    'EXTERNAL_DATA.ID as external_id',
                    'EXTERNAL_DATA.SESSION_ID as external_session_id',
                    'EXTERNAL_DATA.DB_TABLE_TRANSACTION_TYPE as external_transaction_type',
                    'EXTERNAL_DATA.DB_TABLE_CLIENT_IDENTIFIER as external_client_identifier',
                    'EXTERNAL_DATA.DB_TABLE_SERVICE_IDENTIFIER as external_service_identifier',
                    'EXTERNAL_DATA.DB_TABLE_STATUS as external_status',
                    'EXTERNAL_DATA.DB_TABLE_DESCRIPTION as external_description',
                    'EXTERNAL_DATA.DB_TABLE_SENDER as external_sender',
                    'EXTERNAL_DATA.DB_TABLE_RECEIVER as external_receiver',
                    'EXTERNAL_DATA.DB_TABLE_AMOUNT as external_amount',
                    'EXTERNAL_DATA.DB_TABLE_DATE as external_date',
                    'EXTERNAL_DATA.DB_TABLE_REFERENCE as external_reference',
                    'EXTERNAL_DATA.PAN as external_pan',
                    'EXTERNAL_DATA.DB_TABLE_SECONDARY_REFERENCE as external_secondary_reference',
                    'EXTERNAL_DATA.updated_at as external_updated_at',
                    'EXTERNAL_DATA.created_at as external_created_at',
                    'EXTERNAL_DATA.DB_TABLE_PROCESSOR as external_processor'
                )
                ->first();

            if ($externalTransaction) {
                $combinedTransaction = (object) array_merge(
                    (array) $internalTransaction,
                    (array) $externalTransaction,
                    [
                        'result' => 'matched',
                        'side' => 'all',
                    ]
                );

                // Update the PROCESS_STATUS to 'DONE' in the EXTERNAL_DATA table
                DB::table('EXTERNAL_DATA')
                    ->where('ID', $externalTransaction->external_id) // Assuming 'ID' is the primary key
                    ->update(['PROCESS_STATUS' => 'DONE']);

                // Update the PROCESS_STATUS to 'DONE' in the EXTERNAL_DATA table
                $update = DB::table('INTERNAL_DATA')
                    ->where('ID', $internalTransaction->internal_id) // Assuming 'ID' is the primary key
                    ->update(['PROCESS_STATUS' => 'DONE']);

                $combinedTransactions[] = $combinedTransaction;


            }

            //Log::info("reconcileData start 1 - ".$internalTransaction->internal_secondary_reference);

        }

        //Log::info("going 70% start");

        $timestamp = strtotime($this->startDate);
        $formatted_date = date('Y-m-d', $timestamp);
        $progress_date = $formatted_date;

        DB::table('progress')->updateOrInsert(
            ['startDate' => $progress_date], // Condition to check if the record exists
            ['percentProgress' => 70]         // Data to insert or update
        );
        Log::info("reconcileData end");
        $this->insertMatchedRecords($combinedTransactions);

    }


    private function insertMatchedRecords($matchingRecords): void
    {

        Log::info("insertMatchedRecords start");
        foreach ($matchingRecords as $record) {

            //dd($record);

            DB::table('RECONCILIATION_DATA')->insert([
                'internal_id' => $record->internal_id,
                'internal_session_id' => $record->internal_session_id,
                'internal_transaction_type' => $record->internal_transaction_type,
                'internal_client_identifier' => $record->internal_client_identifier,
                'internal_service_identifier' => $record->internal_service_identifier,
                'internal_status' => $record->internal_status,
                'internal_description' => $record->internal_description,
                'internal_sender' => $record->internal_sender,
                'internal_receiver' => $record->internal_receiver,
                'internal_amount' => $record->internal_amount,
                'internal_date' => $record->internal_date,
                'internal_reference' => $record->internal_reference,
                'internal_pan' => $record->internal_pan,
                'internal_secondary_reference' => $record->internal_secondary_reference,
                'internal_updated_at' => $record->internal_updated_at,
                'internal_created_at' => $record->internal_created_at,
                'internal_client' => $record->internal_client,
                'internal_node' => $record->internal_node,
                'external_id' => $record->external_id,
                'external_session_id' => $record->external_session_id,
                'external_transaction_type' => $record->external_transaction_type,
                'external_client_identifier' => $record->external_client_identifier,
                'external_service_identifier' => $record->external_service_identifier,
                'external_status' => $record->external_status,
                'external_description' => $record->external_description,
                'external_sender' => $record->external_sender,
                'external_receiver' => $record->external_receiver,
                'external_amount' => $record->external_amount,
                'external_date' => $record->external_date,
                'external_reference' => $record->external_reference,
                'external_pan' => $record->external_pan,
                'external_secondary_reference' => $record->external_secondary_reference,
                'external_updated_at' => $record->external_updated_at,
                'external_created_at' => $record->external_created_at,
                'external_processor' => $record->external_processor,
                'result' => $record->result,
                'side' => $record->side
            ]);
        }


        Log::info("matchingRecords end");
        DB::table('INTERNAL_DATA')
            ->where('PROCESS_STATUS','DONE')
            ->delete();
        DB::table('EXTERNAL_DATA')
            ->where('PROCESS_STATUS','DONE')
            ->delete();


        $timestamp = strtotime($this->startDate);
        $formatted_date = date('Y-m-d', $timestamp);
        $progress_date = $formatted_date;

        DB::table('progress')->updateOrInsert(
            ['startDate' => $progress_date], // Condition to check if the record exists
            ['percentProgress' => 100,'status' => 'COMPLETED','narration'=>'Completed']         // Data to insert or update
        );
        Log::info("Recon end");

    }








}

