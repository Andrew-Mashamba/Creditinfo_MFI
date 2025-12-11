<?php

namespace App\Jobs\StandingOrder;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;

class SendNewStandingOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

     protected $data;
    public function __construct($data)
    {
        $this->data= $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->sendDataToApi($this->data);
    }


    protected function sendDataToApi(array $data)
    {
        Log::info('Attempting to send standing instruction to API with data:', $data);

        $client = new Client();
        $maxRetries = 3; // Maximum number of retry attempts
        $attempt = 0; // Counter for current attempt
        $sent = false; // Flag to track if the request was sent successfully

        while ($attempt < $maxRetries && !$sent) {
            $attempt++;

            try {
                $promise = $client->postAsync('https://api.example.com/standing-instructions', [
                    'json' => $data,
                    'connect_timeout' => 2,
                    'timeout' => 5,
                ]);

                $promise->then(
                    function ($response) use ($data, &$sent) {
                        Log::info('Standing instruction sent successfully: ' . $response->getBody());

                        DB::table('standing_instructions')->where('id', $data['id'])->update(['status' => 'SENT']);
                        session()->flash('message', 'Standing instruction sent successfully.');
                        $sent = true;

                    },
                    function ($e) use ($data) {

                        if ($e instanceof RequestException) {
                            Log::error('HTTP Request error: ' . $e->getMessage());

                            if ($e->hasResponse()) {
                                Log::error('Response status: ' . $e->getResponse()->getStatusCode());
                                Log::error('Response body: ' . $e->getResponse()->getBody());
                            }
                        } elseif ($e instanceof ConnectException) {
                            Log::error('Connection error: ' . $e->getMessage());
                        } elseif ($e instanceof \GuzzleHttp\Exception\TooManyRedirectsException) {
                            Log::error('Too many redirects: ' . $e->getMessage());
                        } elseif ($e instanceof \GuzzleHttp\Exception\RequestException) {
                            Log::error('General request error: ' . $e->getMessage());
                        } else {
                            Log::error('Unknown error occurred: ' . $e->getMessage());
                        }

                        DB::table('standing_instructions')->where('id', $data['id'])->update(['status' => 'FAILED']);
                        session()->flash('error', 'Failed to send standing instruction. Please try again.');
                    }
                );

                $promise->wait();

            } catch (\GuzzleHttp\Exception\RequestException $e) {
                Log::error('RequestException caught: ' . $e->getMessage());

                DB::table('standing_instructions')->where('id', $data['id'])->update(['status' => 'FAILED']);
                session()->flash('error', 'Request failed. Please check your connection or API endpoint.');
                break;

            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                Log::error('ConnectException caught: ' . $e->getMessage());
                DB::table('standing_instructions')->where('id', $data['id'])->update(['status' => 'FAILED']);
                session()->flash('error', 'Failed to connect to the API. Please check your connection.');
                break;


            } catch (\GuzzleHttp\Exception\TooManyRedirectsException $e) {
                Log::error('TooManyRedirectsException caught: ' . $e->getMessage());
                DB::table('standing_instructions')->where('id', $data['id'])->update(['status' => 'FAILED']);
                session()->flash('error', 'Too many redirects. Check the API endpoint URL.');
                break;


            } catch (\Exception $e) {
                Log::error('General exception caught: ' . $e->getMessage());
                DB::table('standing_instructions')->where('id', $data['id'])->update(['status' => 'FAILED']);
                session()->flash('error', 'An unexpected error occurred. Please try again.');
                break;

            }

            sleep(1);

        }

        if (!$sent) {
            Log::error('Failed to send standing instruction after ' . $maxRetries . ' attempts.');
        }
    }


}
