<?php

namespace App\Services\StangingOrder;

use App\Jobs\StandingOrder\SendNewStandingOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;


class RegisterService{



    public function register(
        $member_id,
        $source_account_number,
        $source_bank_id,
        $destination_account_name,
        $bank = "NBC",
        $destination_account_id,
        $saccos_branch_id,
        $amount,
        $frequency,
        $start_date,
        $end_date = null,
        $reference_number,
        $service,
        $status = null,
        $loan_id = null,
        $description = null
    ) {
        try {
            // Insert data into the database
          $id=  DB::table('standing_instructions')->insertGetId([
                'member_id' => $member_id,
                'source_account_number' => $source_account_number,
                'source_bank_id' => $source_bank_id,
                'destination_account_name' => $destination_account_name,
                'bank' => $bank,
                'destination_account_id' => $destination_account_id,
                'saccos_branch_id' => $saccos_branch_id,
                'amount' => $amount,
                'frequency' => $frequency,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'reference_number' => $reference_number,
                'service' => $service,
                'status' => $status,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Prepare the data for the API call
            $data = [
                'member_id' => $member_id,
                'source_account_number' => $source_account_number,
                'source_bank_id' => $source_bank_id,
                'destination_account_name' => $destination_account_name,
                'bank' => $bank,
                'destination_account_id' => $destination_account_id,
                'saccos_branch_id' => $saccos_branch_id,
                'amount' => $amount,
                'frequency' => $frequency,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'reference_number' => $reference_number,
                'service' => $service,
                'status' => $status,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
                'id'=>$id
            ];

            // $this->sendDataToApi();
            SendNewStandingOrder::dispatch($data);

            session()->flash('message', 'Standing instruction registration is being processed.');
            return true;

        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage());
            session()->flash('error', 'Failed to register standing instruction. Please try again.');
            return  $e->getMessage();

        }
    }



}
