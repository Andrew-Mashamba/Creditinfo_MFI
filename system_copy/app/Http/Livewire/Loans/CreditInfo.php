<?php

namespace App\Http\Livewire\Loans;

use Carbon\Carbon;
use Exception;
use Livewire\Component;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use nusoap_client;
use SimpleXMLElement;
use Symfony\Component\Mime\Crypto\SMimeSigner;

class CreditInfo extends Component
{
    public $currentStep;
    public $showDialog=true;

    public $selectedHeader;

    public $selectedMenu;
    public $responseXml;

    public $show=false;

    public $customerBodyInfo;

    public $table_headers=[];

    public $client_nida_number;

    public $stockPrices;


    public function boot(){
        $this->loadTrend();
    }

    public function loadTrend()
    {
        $stockPrices = DB::table('scores')
            ->where('client_id', 111222333)
            ->orderBy('date', 'asc')
            ->get();

        $formattedData = $stockPrices->map(function ($price) {
            return [
                'data' => floatval($price->score),
                'month' => Carbon::parse($price->date)->format('Y M'),
            ];
        })->toArray();

        $this->stockPrices = $formattedData;
        $this->dispatchBrowserEvent('refreshChart');
    }


    public function render()
    {

        return view('livewire.loans.credit-info');
    }




}
