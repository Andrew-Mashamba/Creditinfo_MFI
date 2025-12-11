<?php

namespace App\Imports;

use App\Models\NodesList;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use JetBrains\PhpStorm\ArrayShape;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Events\AfterImport;

class ImportTransactions extends DefaultValueBinder implements ToModel, WithCustomValueBinder, WithEvents
{
    public array $errors = [];
    public array $Excelrows = [];
    public int $maxChunkSize = 50;

    public string $nodeName;
    public ?array $nodeData = null;
    public ?int $amountColumn = null;
    public array $nodesListCache = [];
    public array $nonDuplicateData = [];
    public array $recentReferences = []; // Array to store recent references
    public $currency;

    public function __construct()
    {
        $this->loadNodesListCache();
        $this->nodeName = Session::get('nodeName', '');

        if ($this->nodeName && isset($this->nodesListCache[$this->nodeName])) {
            $this->nodeData = $this->nodesListCache[$this->nodeName];
            $this->amountColumn = $this->getAmountColumn($this->nodeData);
            $this->loadRecentReferences(); // Load recent references for duplicate checking
        }
    }

    // Load all NodesList entries and store them by NODE_NAME
    private function loadNodesListCache(): void
    {
        $this->nodesListCache = NodesList::all()->keyBy('NODE_NAME')->toArray();
    }

    // Load recent DB_TABLE_REFERENCE values within the last three months into $recentReferences array
    private function loadRecentReferences(): void
    {
        $threeMonthsAgo = Carbon::now()->subMonths(3)->format('Y-m-d');

        $this->recentReferences = DB::table($this->nodeName)
            ->where('DB_TABLE_DATE', '>=', $threeMonthsAgo)
            ->pluck('DB_TABLE_REFERENCE')
            ->toArray();
    }

    #[ArrayShape([AfterImport::class => "\Closure"])]
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                Log::info("After import process started.");
                $this->insertDataInChunks();
                Log::info("After import process completed.");
            },
        ];
    }

    // Insert non-duplicate data into the database in chunks
    private function insertDataInChunks(): void
    {
        collect($this->nonDuplicateData)->chunk($this->maxChunkSize)->each(function ($chunk) {
            DB::table(Session::get('nodeName'))->insert($chunk->toArray());
        });
        $this->nonDuplicateData = [];
    }

    public function model(array $row): \Illuminate\Database\Eloquent\Model|array|string|null
    {

        //dd($this->recentReferences);
        try {
            if (empty($this->nodeData) || $this->amountColumn === null) return null;

            //$amount = $row[$this->amountColumn] ?? null;
            $processedAmount = $this->processAmount($row, trim($this->nodeData['DATA_SOURCE_TYPE']), trim($this->nodeData['NODE_DATA_SOURCE']));

            $description = trim($row[$this->nodeData['DB_TABLE_DESCRIPTION']]);
            if ($this->shouldIncludeTransaction($description,$row) && $processedAmount) {
                $date = $this->parseDate($row);
                $secondaryRef = $this->getSecondaryReference($row, trim($this->nodeData['DATA_SOURCE_TYPE']), trim($this->nodeData['NODE_DATA_SOURCE']));


                $ref = $this->getREF($row, trim($this->nodeData['DATA_SOURCE_TYPE']), trim($this->nodeData['NODE_DATA_SOURCE']),trim($this->nodeName));
                if (!$this->isDuplicateTransaction($ref)) {
                    $this->insertTransaction($row,trim($this->nodeData['DATA_SOURCE_TYPE']), trim($this->nodeData['NODE_DATA_SOURCE']), $description, $processedAmount, $date, $secondaryRef, $ref);
                }else{
                    $this->errors[] = 'Duplicate Transaction';
                    $row[] = ['Error', 'Duplicate Transaction'];
                    $this->Excelrows[] = $row;
                }
            }else{
                $this->errors[] = 'Skipped due to policy';
                $row[] = ['Error', 'Skipped due to policy'];
                $this->Excelrows[] = $row;
            }
        } catch (\Exception $e) {
            Log::error("Error processing row: " . $e->getMessage());
            $this->errors[] = $e->getMessage();
            $row[] = ['Error', $e->getMessage()];
            $this->Excelrows[] = $row;
        }

        return null;
    }


    private function getREF(array $row, string $data_source_type, string $node_data_source, string $nodeName): ?string
    {
        // Initialize the reference variables
        $REF = $row[$this->nodeData['DB_TABLE_REFERENCE']] ?? null;

        // Process based on data source conditions
        if (($data_source_type == 'Database' && $node_data_source == 'Database') ||
            ($data_source_type == 'Portal' && $node_data_source == 'File')) {

            // Specific adjustments for NMB and EDITPACKAGE nodes
            if ($nodeName == 'NMB') {
                $REF = str_replace("AGPAY", "", $REF);
            } elseif ($nodeName == 'EDITPACKAGE') {
                $REF = $REF . str_pad($row[4], 6, "0", STR_PAD_LEFT);
            }


            return $REF; // Return the modified REF if no duplicate found

        } else {
            $REF_FILE = $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_REFERENCE')] ?? null;
            // Alternative processing for non-Database and non-Portal conditions
            if ($nodeName == 'NMB') {
                $REF_FILE = str_replace("AGPAY", "", $REF_FILE);
            } elseif ($nodeName == 'EDITPACKAGE') {
                $REF_FILE = $REF_FILE . str_pad($row[4], 6, "0", STR_PAD_LEFT);
            }

            return $REF_FILE; // Return the modified REF_FILE if no duplicate found
        }
    }


    // Get the correct column for amount based on node and data source type
    private function getAmountColumn(array $nodeData): int
    {
        //dd($nodeData['DB_TABLE_AMOUNT']);
        return $nodeData['DB_TABLE_AMOUNT'];
    }



    private function processAmount(array $row, string $data_source_type, string $node_data_source): float
    {
        $amount = null;

        if ($this->nodeName == 'VODACOM') {
            // VODACOM-specific processing with different data source conditions
            if (($data_source_type == 'Database' && $node_data_source == 'Database') ||
                ($data_source_type == 'Portal' && $node_data_source == 'File')) {
                $amount = $row[$this->nodeData['DB_TABLE_AMOUNT']] ?? null;
            } else {
                $amount = $row[NodesList::where('NODE_NAME', $this->nodeName . '_file')->value('DB_TABLE_AMOUNT')] ?? null;
            }

            // Fallback if amount is not set
            if (!$amount) {
                if (($data_source_type == 'Database' && $node_data_source == 'Database') ||
                    ($data_source_type == 'Portal' && $node_data_source == 'File')) {
                    $amount = $row[$this->nodeData['DB_TABLE_AMOUNT']+ 1] ?? null;
                } else {
                    $amount = $row[NodesList::where('NODE_NAME', $this->nodeName . '_file')->value('DB_TABLE_AMOUNT') + 1] ?? null;
                }
            }

            // Process the amount as a negative value for VODACOM
            $amount = (float)str_replace([',', 'Tsh', 'tzs', '/=', 'TZS'], '', $amount) * -1;
        } else {
            // Non-VODACOM node processing
            if (($data_source_type == 'Database' && $node_data_source == 'Database') ||
                ($data_source_type == 'Portal' && $node_data_source == 'File')) {
                if($this->nodeName == 'EDITPACKAGE'){
                    if(trim($row[9]) == 'TZS'){
                        $amount = $row[$this->nodeData['DB_TABLE_AMOUNT']] ?? null;
                        $this->currency = 'TZS';
                    }else{
                        $amount = $row[10] ?? null;
                        $this->currency = 'USD';
                    }
                }else{
                    $amount = $row[$this->nodeData['DB_TABLE_AMOUNT']] ?? null;
                }
            } else {
                $amount = $row[NodesList::where('NODE_NAME', $this->nodeName . '_file')->value('DB_TABLE_AMOUNT')] ?? null;
            }

            // Process the amount as a positive value for other nodes
            $amount = (float)str_replace([',', 'Tsh', 'tzs', '/=', 'TZS'], '', $amount);
        }




        return $amount;
    }




    // Determine if the transaction should be included based on description
    private function shouldIncludeTransaction(string $description, array $row): bool
    {
        if ($this->nodeName === 'VODACOM') {
            return str_contains($description, 'Payment to') ||
                str_contains($description, 'Business Buy Goods via API') ||
                str_contains($description, 'Pay Bill from') ||
                str_contains($description, 'Real Time Settlement from');
        }

        if ($this->nodeName === 'EDITPACKAGE') {

            if((
                    ($row[$this->nodeData['DB_TABLE_SERVICE_IDENTIFIER']]  == 'SMS600R' and
                        ($row[13] = '435731' or '472133' or '402141')
                    ) or
                    ($row[$this->nodeData['DB_TABLE_SERVICE_IDENTIFIER']]  == 'SMS601R' and
                        $row[13] = '435731'
                    ) or
                    ($row[$this->nodeData['DB_TABLE_SERVICE_IDENTIFIER']]  == 'SMS601T' and
                        $row[13] = '490862'
                    )

                )
                and
                (int)$row[$this->nodeData['DB_TABLE_STATUS']]  == 0
            ){
                return true;
            }else{
                return  false;
            }


        }

        // Default to including the transaction if no specific conditions are met
        return true;
    }

    private function parseDate(array $row): string
    {
        $dateColumn = $this->nodeData['DB_TABLE_DATE'];
        $excelDate = str_replace("\t", "", $row[$dateColumn]);

        if (is_numeric($excelDate)) {
            return $this->nodeName == 'NMB'
                ? Carbon::parse($excelDate)->format('Y-m-d')
                : Carbon::createFromTimestamp(($excelDate - 25569) * 86400)->format('Y-m-d');
        } else {
            return Carbon::parse($excelDate)->format('Y-m-d');
        }
    }

    private function getSecondaryReference(array $row, string $data_source_type, string $node_data_source): ?string
    {
        // Determine the secondary reference column based on data source type
        $secondaryRefColumn = ($data_source_type === 'Database' && $node_data_source === 'Database') ||
        ($data_source_type === 'Portal' && $node_data_source === 'File')
            ? $this->nodeData['DB_TABLE_SECONDARY_REFERENCE']
            : NodesList::where('NODE_NAME', "{$this->nodeName}_file")->value('DB_TABLE_SECONDARY_REFERENCE');

        // Retrieve and return the secondary reference if the column exists in $row
        return isset($secondaryRefColumn) && isset($row[$secondaryRefColumn])
            ? $row[$secondaryRefColumn]
            : null;
    }


    // Check if a reference is duplicate using the in-memory $recentReferences array
    private function isDuplicateTransaction(?string $ref): bool
    {
        return in_array($ref, $this->recentReferences, true);
    }

    private function insertTransaction(array $row, string $data_source_type, string $node_data_source, string $description, float $amount, string $date, ?string $secondaryRef, string $ref): void
    {
        // Determine the reference source for column names based on data source type
        $isDatabaseSource = ($data_source_type === 'Database' && $node_data_source === 'Database') ||
            ($data_source_type === 'Portal' && $node_data_source === 'File');

        // Define the column mappings based on the source
        $columns = [
            'DB_TABLE_TRANSACTION_TYPE' => $isDatabaseSource ? $this->nodeData['DB_TABLE_TRANSACTION_TYPE'] : NodesList::where('NODE_NAME', "{$this->nodeName}_file")->value('DB_TABLE_TRANSACTION_TYPE'),
            'DB_TABLE_CLIENT_IDENTIFIER' => $isDatabaseSource ? $this->nodeData['DB_TABLE_CLIENT_IDENTIFIER'] : NodesList::where('NODE_NAME', "{$this->nodeName}_file")->value('DB_TABLE_CLIENT_IDENTIFIER'),
            'DB_TABLE_SERVICE_IDENTIFIER' => $isDatabaseSource ? $this->nodeData['DB_TABLE_SERVICE_IDENTIFIER'] : NodesList::where('NODE_NAME', "{$this->nodeName}_file")->value('DB_TABLE_SERVICE_IDENTIFIER'),
            'DB_TABLE_STATUS' => $isDatabaseSource ? $this->nodeData['DB_TABLE_STATUS'] : NodesList::where('NODE_NAME', "{$this->nodeName}_file")->value('DB_TABLE_STATUS'),
            'DB_TABLE_SENDER' => $isDatabaseSource ? $this->nodeData['DB_TABLE_SENDER'] : NodesList::where('NODE_NAME', "{$this->nodeName}_file")->value('DB_TABLE_SENDER'),
            'DB_TABLE_RECEIVER' => $isDatabaseSource ? $this->nodeData['DB_TABLE_RECEIVER'] : NodesList::where('NODE_NAME', "{$this->nodeName}_file")->value('DB_TABLE_RECEIVER')
        ];

        // Prepare the transaction data array using the selected column mappings
        $transactionData = [
            'SESSION_ID' => Session::get('sessionID'),
            'DB_TABLE_TRANSACTION_TYPE' => $row[$columns['DB_TABLE_TRANSACTION_TYPE']] ?? null,
            'DB_TABLE_CLIENT_IDENTIFIER' => $row[$columns['DB_TABLE_CLIENT_IDENTIFIER']] ?? null,
            'DB_TABLE_SERVICE_IDENTIFIER' => $row[$columns['DB_TABLE_SERVICE_IDENTIFIER']] ?? null,
            'DB_TABLE_STATUS' => $row[$columns['DB_TABLE_STATUS']] ?? null,
            'DB_TABLE_SENDER' => $row[$columns['DB_TABLE_SENDER']] ?? null,
            'DB_TABLE_RECEIVER' => $row[$columns['DB_TABLE_RECEIVER']] ?? null,
            'DB_TABLE_DESCRIPTION' => $description,
            'DB_TABLE_AMOUNT' => $amount,
            'DB_TABLE_DATE' => $date,
            'DB_TABLE_REFERENCE' => strval($ref),
            'DB_TABLE_SECONDARY_REFERENCE' => strval($secondaryRef),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Conditionally add the currency field for the 'EDITPACKAGE' node
        if ($this->nodeName === 'EDITPACKAGE') {
            $transactionData['DB_TABLE_SETTLEMENT_CURRENCY'] = $this->currency;
        }

        // Add to non-duplicate data for batch insert
        $this->nonDuplicateData[] = $transactionData;
    }


}
