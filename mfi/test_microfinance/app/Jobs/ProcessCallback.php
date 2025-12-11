<?php

namespace App\Jobs;

use App\Mail\EmployeeRegisterMail;
use App\Mail\InstitutionRegistrationConfirmationMail;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $call_back_url;
    public  $reference_number;
    public function __construct($reference_number,$call_back_url)
    {
        $this->reference_number=$reference_number;

        $this->call_back_url=$call_back_url;
    }

    public function handle()
    {
        sleep(2);

        Mail::to('percyegno@gmail.com')->send(new InstitutionRegistrationConfirmationMail('confirmation detail'));

        $this->processCallBackForInternalBankTransfer();
        
    }


    public function processCallBackForInternalBankTransfer(){



        $requestBody=[
            'id'=>$this->reference_number,
            'message'=>'00',
            'reference_number'=>'123456789',
        ];
        try {
            $client = new Client();
            $headers = [
                'Accept-Language' => 'en-US', // Replace 'en-US' with the desired language code
            ];
            $options = [
                'headers' => $headers,
                'json' => $requestBody,
            ];

            $response = $client->request("POST", $this->call_back_url, $options);
            $statusCode = $response->getStatusCode();
            $reasonPhrase = $response->getReasonPhrase();

        } catch (GuzzleException $e) {

            // Handle the error and retry the API call if needed
            return null;

        }
        return null;
    }
}
