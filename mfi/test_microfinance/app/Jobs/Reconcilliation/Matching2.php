<?php

namespace App\Jobs;

//use App\Events\ReconciliationCompleted;
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

class ReconciliationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $startDate;
    protected $progress_bar_date;

    public $maxTries = 2;
    public $timeout = 1200; // 20 minutes

    public function __construct($startDate)
    {
        $this->startDate = $startDate;
    }

    public function handle(): void
    {
        $startDate = $this->startDate;

        $timestamp = strtotime($this->startDate);
        $formatted_date = date('Y-m-d', $timestamp);
        $this->progress_bar_date = $formatted_date;

        Log::info("Recon start for date: {$formatted_date}");

        $this->updateProgress($formatted_date, 2);

        try {
            $this->runReconciliation($startDate);
        } catch (Exception $e) {
            Log::error('Job failed: ' . $e->getMessage());
            $this->updateProgress($formatted_date, null, 'FAILED', $e->getMessage());
        }
    }

    private function updateProgress($date, $percent = null, $status = null, $narration = null)
    {
        Log::info("Updating progress for date: {$date} | Percent: {$percent} | Status: {$status} | Narration: {$narration}");

        DB::table('progress')->updateOrInsert(
            ['startDate' => $date],
            array_filter([
                'percentProgress' => $percent,
                'status' => $status,
                'narration' => $narration,
            ])
        );
    }

    public function runReconciliation($startDate): void
    {
        $timestamp = strtotime($startDate);
        $recon_value_date = date('Y-m-d', $timestamp);

        Log::info("Starting reconciliation process for date: {$recon_value_date}");

        // Delete previous data using bulk operations
        $this->deletePreviousData($recon_value_date);

        $this->updateProgress($recon_value_date, 20);

        $nodes = NodesList::where('NODE_TYPE', 'INTERNAL_NODE')
            ->where('NODE_DATA_SOURCE', 'Database')
            ->get();

        Log::info("Processing internal nodes for date: {$recon_value_date} | Node Count: " . count($nodes));

        $this->processInternalNodes($nodes, $recon_value_date);

        $this->updateProgress($recon_value_date, 30);

        $thirdNodes = NodesList::where('NODE_TYPE', 'PROCESSOR_NODE')->get();

        Log::info("Processing processor nodes for date: {$recon_value_date} | Node Count: " . count($thirdNodes));

        $this->processProcessorNodes($thirdNodes, $recon_value_date);

        Log::info("Starting data reconciliation for date: {$recon_value_date}");

        $this->reconcileData();
    }

    private function deletePreviousData($date)
    {
        Log::info("Deleting previous data for date: {$date}");

        DB::transaction(function () use ($date) {
            DB::table('INTERNAL_DATA')->whereDate('DB_TABLE_DATE', $date)->delete();
            DB::table('EXTERNAL_DATA')->whereDate('DB_TABLE_DATE', $date)->delete();
            DB::table('RECONCILIATION_DATA')->whereDate('internal_date', $date)->delete();
        });

        Log::info("Deleted previous data for date: {$date}");
    }

    private function processInternalNodes($nodes, $date)
    {
        foreach ($nodes as $node) {
            Log::info("Processing internal node: {$node->NODE_NAME} for date: {$date}");

            $transactions = DB::table($node->NODE_NAME)
                ->whereDate('DB_TABLE_DATE', $date)
                ->get();

            Log::info("Fetched " . count($transactions) . " transactions from internal node: {$node->NODE_NAME}");

            $internalData = $transactions->map(function ($transaction) use ($node) {
                return [
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
                    'DB_TABLE_REFERENCE' => trim($transaction->DB_TABLE_REFERENCE),
                    'DB_TABLE_SECONDARY_REFERENCE' => trim($transaction->DB_TABLE_SECONDARY_REFERENCE),
                    'DB_TABLE_CLIENT' => trim($transaction->DB_TABLE_CLIENT),
                    'NODE' => trim($node->NODE_NAME),
                ];
            })->toArray();

            Log::info("Prepared data for bulk upsert to INTERNAL_DATA for node: {$node->NODE_NAME}");

            DB::table('INTERNAL_DATA')->upsert(
                $internalData,
                ['DB_TABLE_SECONDARY_REFERENCE']
            );

            Log::info("Upserted data to INTERNAL_DATA for node: {$node->NODE_NAME}");
        }
    }

    private function processProcessorNodes($nodes, $date)
    {
        foreach ($nodes as $node) {
            Log::info("Processing processor node: {$node->NODE_NAME} for date: {$date}");

            $transactions = DB::table($node->NODE_NAME)
                ->whereDate('DB_TABLE_DATE', $date)
                ->get();

            Log::info("Fetched " . count($transactions) . " transactions from processor node: {$node->NODE_NAME}");

            $externalData = $transactions->map(function ($transaction) use ($node) {
                return [
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
                    'DB_TABLE_PROCESSOR' => trim($node->NODE_NAME),
                ];
            })->toArray();

            Log::info("Prepared data for bulk upsert to EXTERNAL_DATA for node: {$node->NODE_NAME}");

            DB::table('EXTERNAL_DATA')->upsert(
                $externalData,
                ['DB_TABLE_REFERENCE']
            );

            Log::info("Upserted data to EXTERNAL_DATA for node: {$node->NODE_NAME}");
        }
    }

    public function reconcileData(): void
    {
        Log::info("Starting reconciliation of data.");

        $this->updateProgress($this->progress_bar_date, 50);

        // Fetch internal transactions in batches to avoid memory overflow
        DB::table('INTERNAL_DATA')
            ->whereNotNull('DB_TABLE_SECONDARY_REFERENCE')
            ->orderBy('ID')
            ->chunk(1000, function ($internalTransactions) {
                Log::info("Processing a batch of " . count($internalTransactions) . " internal transactions.");

                $combinedTransactions = [];

                foreach ($internalTransactions as $internalTransaction) {
                    Log::info("Checking external match for internal transaction ID: {$internalTransaction->ID}");

                    $externalTransaction = DB::table('EXTERNAL_DATA')
                        ->where(function ($query) use ($internalTransaction) {
                            $query->where('DB_TABLE_REFERENCE', $internalTransaction->DB_TABLE_SECONDARY_REFERENCE)
                                ->orWhere('DB_TABLE_SECONDARY_REFERENCE', $internalTransaction->DB_TABLE_SECONDARY_REFERENCE);
                        })
                        ->first();

                    if ($externalTransaction) {
                        Log::info("Match found for internal transaction ID: {$internalTransaction->ID}");

                        $combinedTransaction = (object) array_merge(
                            (array) $internalTransaction,
                            (array) $externalTransaction,
                            [
                                'result' => 'matched',
                                'side' => 'all',
                            ]
                        );

                        // Mark both internal and external as processed
                        DB::transaction(function () use ($internalTransaction, $externalTransaction) {
                            DB::table('INTERNAL_DATA')
                                ->where('ID', $internalTransaction->ID)
                                ->update(['PROCESS_STATUS' => 'PROCESSED']);
                            DB::table('EXTERNAL_DATA')
                                ->where('ID', $externalTransaction->ID)
                                ->update(['PROCESS_STATUS' => 'PROCESSED']);
                        });

                        Log::info("Updated internal and external transaction as processed for ID: {$internalTransaction->ID}");
                    } else {
                        Log::warning("No match found for internal transaction ID: {$internalTransaction->ID}");

                        $combinedTransaction = (object) array_merge(
                            (array) $internalTransaction,
                            [
                                'result' => 'not_matched',
                                'side' => 'internal',
                            ]
                        );
                    }

                    $combinedTransactions[] = (array) $combinedTransaction;
                }

                Log::info("Inserting combined transactions into RECONCILIATION_DATA.");

                DB::table('RECONCILIATION_DATA')->insert($combinedTransactions);
            });

        $this->updateProgress($this->progress_bar_date, 100, 'COMPLETED');

        Log::info("Reconciliation completed successfully.");

        // Trigger an event upon completion
       // event(new ReconciliationCompleted($this->progress_bar_date));

        Log::info("ReconciliationCompleted event triggered for date: {$this->progress_bar_date}");
    }
}
