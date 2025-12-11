<?php

namespace App\Imports;


use App\Exports\CustomExport;
use App\Models\NodesList;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToModel;

use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Facades\Excel;
use Log;


class ImportTransactions extends DefaultValueBinder implements ToModel,WithCustomValueBinder
{
    /**
     * @param array $row
     *
     * @return Cashbook
     */

    public $errors = [];

    public $Excelrows=[];
    public $x = 0;

    public function model(array $row): \Illuminate\Database\Eloquent\Model|array|string|null
    {
        try {

            $nodeName = Session::get('nodeName');

            $node_data_source = trim(NodesList::where('NODE_NAME', $nodeName)->value('NODE_DATA_SOURCE'));
            $data_source_type = trim(NodesList::where('NODE_NAME', $nodeName)->value('DATA_SOURCE_TYPE'));



            if($nodeName == 'VODACOM'){
                if(($data_source_type == 'Database' && $node_data_source == 'Database') || ($data_source_type == 'Portal' && $node_data_source == 'File') ){
                    $amount = $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_AMOUNT')];
                }else{
                    $amount = $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_AMOUNT')];
                }

                $y = $amount ?? null;
                //dd($y);
                if($y) {

                }else{

                    if(($data_source_type == 'Database' && $node_data_source == 'Database') || ($data_source_type == 'Portal' && $node_data_source == 'File') ){
                        $amount = $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_AMOUNT')+1];
                    }else{
                        $amount = $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_AMOUNT')+1];
                    }
                }

                $amount = (float)$amount * -1;

            }else{

                if(($data_source_type == 'Database' && $node_data_source == 'Database') || ($data_source_type == 'Portal' && $node_data_source == 'File') ){

                    $amount = $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_AMOUNT')];
                }else{

                    $amount = $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_AMOUNT')];
                }


            }

            $processed_amount = $amount ?? null;
            $include = false;
            $description = trim($row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_DESCRIPTION')]);



            if($nodeName == 'VODACOM'){

                if (str_contains($description, 'Payment to') || str_contains($description, 'Business Buy Goods via API') || str_contains($description, 'Pay Bill from') || str_contains($description, 'Real Time Settlement from')) {
                    $include =true;
                }
                else
                {
                    $include =false;
                }
            }
            else
            {
                $include =true;
            }

            if($processed_amount) {





                // Remove non-numeric characters (except decimal point)
                $amount = str_replace(',', '', $amount); // Remove comma
                $variations = array('Tsh', 'tzs', '/=', 'TZS');
                $amount = str_ireplace($variations, '', $amount);

                //$amount = preg_replace('/[^0-9.]/', '', $amount); // Remove non-numeric characters except decimal point

                // Convert to float
                $floatValue = (float) $amount;

                if (is_numeric($floatValue)) {

                    if(($data_source_type == 'Database' && $node_data_source == 'Database') || ($data_source_type == 'Portal' && $node_data_source == 'File') ){
                        $excelDate = $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_DATE')];
                    }else{
                        $excelDate = $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_DATE')];
                    }

                    $excelDate = str_replace("\t", "", $excelDate);

                    if (is_numeric($excelDate)) {
                        if($nodeName == 'NMB'){
                            $date = Carbon::parse($excelDate);
                            $date->format('Y-m-d');
                            //dd($date);
                        }else{
                            $date = Carbon::createFromTimestamp( ( $excelDate - 25569 ) * 86400 );
                            $date->format('Y-m-d'); // Output: 2023-05-10
                        }


                    } else {
                        //dd($excelDate);
                        $date = Carbon::parse($excelDate);
                        $date->format('Y-m-d');
                        //$date = Carbon::createFromFormat('d-m-Y H:i:s', trim($excelDate));
                        //$date->format('Y-m-d');
                    }



                    if(($data_source_type == 'Database' && $node_data_source == 'Database') || ($data_source_type == 'Portal' && $node_data_source == 'File') ){
                        $secondary_ref_number = NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_SECONDARY_REFERENCE');
                    }else{
                        $secondary_ref_number = NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_SECONDARY_REFERENCE');
                    }

                    if($secondary_ref_number){
                        $DB_TABLE_SECONDARY_REFERENCE =  $row[$secondary_ref_number];
                    }else{
                        $DB_TABLE_SECONDARY_REFERENCE = null;
                    }

                    $REF = $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_REFERENCE')] ?? null;
                    $REF_FILE = $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_REFERENCE')] ?? null;

                    DB::table($nodeName)->where('DB_TABLE_REFERENCE', '=', strval($REF_FILE))->delete();
                    DB::table($nodeName)->where('DB_TABLE_REFERENCE', '=', strval($REF))->delete();

                    if($nodeName == 'EDITPACKAGE')
                    {
                        if((
                                ($row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_SERVICE_IDENTIFIER')]  == 'SMS600R' and
                                    ($row[13] = '435731' or '472133' or '402141')
                                ) or
                                ($row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_SERVICE_IDENTIFIER')]  == 'SMS601R' and
                                    $row[13] = '435731'
                                ) or
                                ($row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_SERVICE_IDENTIFIER')]  == 'SMS601T' and
                                    $row[13] = '490862'
                                )

                            )
                            and
                            $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_STATUS')]  == '00'
                        ){
                            $include = true;
                        }else{
                            $include = false;
                        }



                    }

                    if(($data_source_type == 'Database' && $node_data_source == 'Database') || ($data_source_type == 'Portal' && $node_data_source == 'File') ){
                        if($nodeName == 'NMB')
                        {
                            $REF = str_replace("AGPAY", "", $REF);

                        }
                        if($nodeName == 'EDITPACKAGE')
                        {

                            $REF = $REF.str_pad($row[4], 6, "0", STR_PAD_LEFT);

                        }
                        $transaction = DB::table($nodeName)->where('DB_TABLE_REFERENCE', '=', strval($REF))->first();
                        if($transaction){
                            //DB::table($nodeName)->where('DB_TABLE_REFERENCE', '=', strval($REF))->delete();

                            //continue;
                        }
                    }else{
                        if($nodeName == 'NMB')
                        {
                            $REF_FILE = str_replace("AGPAY", "", $REF_FILE);

                        }
                        if($nodeName == 'EDITPACKAGE')
                        {

                            $REF_FILE = $REF_FILE.str_pad($row[4], 6, "0", STR_PAD_LEFT);

                        }

                        $transaction = DB::table($nodeName)->where('DB_TABLE_REFERENCE', '=', strval($REF_FILE))->first();
                        if($transaction){
                            //DB::table($nodeName)->where('DB_TABLE_REFERENCE', '=', strval($REF_FILE))->delete();

                            //continue;
                        }


                    }



                    if($transaction) {


                        if(($data_source_type == 'Database' && $node_data_source == 'Database') || ($data_source_type == 'Portal' && $node_data_source == 'File') ){
                            $dubError = 'Duplicate reference - '.$row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_REFERENCE')];
                        }else{
                            $dubError = 'Duplicate reference - '.$row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_REFERENCE')];


                        }
                        //deleting nmb reversals i.e have negative values but same nmb ref
                        if($nodeName == 'NMB' && $processed_amount < 0){
                            DB::table('NMB')->where('DB_TABLE_REFERENCE', '=', strval($REF))->delete();
                        }
                        if($nodeName == 'EDITPACKAGE' && ($row[6]== '420' or $row[6]=='422')){
                            DB::table('EDITPACKAGE')->where('DB_TABLE_REFERENCE', '=', strval($REF))->delete();
                        }
                        $row[] = ['Error', $dubError];
                        Log::info("NMB Skipped: ".json_decode($REF));
                        $this->Excelrows[] = $row;
                    } else {
                        try {
                            // Transaction does not exist, insert

                            if(($data_source_type == 'Database' && $node_data_source == 'Database') || ($data_source_type == 'Portal' && $node_data_source == 'File') ){


                                $value = $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_REFERENCE')];

                            }else{
                                $value = $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_REFERENCE')];


                            }


                            if($nodeName == 'NMB')
                            {
                                $value = str_replace("AGPAY", "", $value);

                            }
                            if($nodeName == 'EDITPACKAGE')
                            {

                                $value = $value.str_pad($row[4], 6, "0", STR_PAD_LEFT);

                                if($row[6]== '420' or $row[6]=='422'){
                                    DB::table('EDITPACKAGE')->where('DB_TABLE_REFERENCE', '=', $value)->delete();
                                    $include = false;
                                }

                            }



                            if($include)
                            {
                                if(($data_source_type == 'Database' && $node_data_source == 'Database') || ($data_source_type == 'Portal' && $node_data_source == 'File') ){
                                    DB::table($nodeName)->insert([
                                        'SESSION_ID' => Session::get('sessionID'),
                                        'DB_TABLE_TRANSACTION_TYPE' => $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_TRANSACTION_TYPE')],
                                        'DB_TABLE_CLIENT_IDENTIFIER' => $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_CLIENT_IDENTIFIER')],
                                        'DB_TABLE_SERVICE_IDENTIFIER' => $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_SERVICE_IDENTIFIER')],
                                        'DB_TABLE_STATUS' => $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_STATUS')],
                                        'DB_TABLE_DESCRIPTION' => $description,
                                        'DB_TABLE_SENDER' => $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_SENDER')],
                                        'DB_TABLE_RECEIVER' => $row[NodesList::where('NODE_NAME', $nodeName)->value('DB_TABLE_RECEIVER')],
                                        'DB_TABLE_AMOUNT' => $processed_amount,
                                        'DB_TABLE_DATE' => $date,
                                        'DB_TABLE_REFERENCE' => strval($value),
                                        'DB_TABLE_SECONDARY_REFERENCE' => strval($DB_TABLE_SECONDARY_REFERENCE),
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                                }else{

                                    DB::table($nodeName)->insert([
                                        'SESSION_ID' => Session::get('sessionID'),
                                        'DB_TABLE_TRANSACTION_TYPE' => $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_TRANSACTION_TYPE')],
                                        'DB_TABLE_CLIENT_IDENTIFIER' => $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_CLIENT_IDENTIFIER')],
                                        'DB_TABLE_SERVICE_IDENTIFIER' => $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_SERVICE_IDENTIFIER')],
                                        'DB_TABLE_STATUS' => $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_STATUS')],
                                        'DB_TABLE_DESCRIPTION' => $description,
                                        'DB_TABLE_SENDER' => $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_SENDER')],
                                        'DB_TABLE_RECEIVER' => $row[NodesList::where('NODE_NAME', $nodeName.'_file')->value('DB_TABLE_RECEIVER')],
                                        'DB_TABLE_AMOUNT' => $processed_amount,
                                        'DB_TABLE_DATE' => $date,
                                        'DB_TABLE_REFERENCE' => strval($value),
                                        'DB_TABLE_SECONDARY_REFERENCE' => strval($DB_TABLE_SECONDARY_REFERENCE),
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                                }
                            }
                            else{
                                $this->errors[] = "errorMessage";
                                $row[] = ['Error', "include error"];
                                $this->Excelrows[] = $row;
                            }


                        } catch (\Illuminate\Database\QueryException $e) {
                            $errorCode = $e->getCode();
                            $errorMessage = $e->getMessage();

                            if ($errorCode === "42S22") {
                                $this->errors[] = $errorMessage;
                                $row[] = ['Error', $errorMessage];
                                $this->Excelrows[] = $row;
                            } else {
                                $this->errors[] = $errorMessage;
                                $row[] = ['Error', $errorMessage];
                                $this->Excelrows[] = $row;
                            }
                        }


                    }


                    //deleting nmb reversals i.e have negative values but same nmb ref
                    if($nodeName == 'NMB' && $processed_amount < 0){
                        DB::table('NMB')->where('DB_TABLE_REFERENCE', '=', strval($REF))->delete();
                    }
                    //$row[] = ['Error', "Reversal transaction: ".$REF];
                    //Log::info("NMB Skipped: ".json_decode($REF));
                    //$this->Excelrows[] = $row;

                    //end of modification for deleting nmb

                }else{

                    $this->Excelrows[] = $row;
                }
            }else{
                $this->errors[] = 'A row with a wrong format detected, skipping';
                $row[] = ['Error', 'A row with a wrong format detected, skipping'];
                $this->Excelrows[] = $row;
            }

        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            $row[] = ['Error', $e->getMessage()];
            $this->Excelrows[] = $row;
        }

        return null;


    }





}
