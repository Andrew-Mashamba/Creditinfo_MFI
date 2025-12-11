<?php

namespace App\Http\Livewire\Billing;

use App\Commands\GenerateBillCommand;
use App\Models\ClientsModel;
use App\Models\Service;
use Illuminate\Support\Facades\Bus;
use Livewire\Component;

class CreateBill extends Component
{
    public $client_number;
    public $service_id;
    public $amount;
    public $is_recurring = 1;
    public $payment_mode = 1;
    public $due_date;
    public $is_mandatory = false;
    public $clients = [];
    public $services = [];

    protected $rules = [
        'client_number' => 'required|exists:clients,client_number',
        'service_id' => 'required|exists:services,id',
        'amount' => 'required|numeric|min:0',
        'is_recurring' => 'required|in:1,2',
        'payment_mode' => 'required|in:1,2,3,4,5',
        'due_date' => 'required|date|after:today',
        'is_mandatory' => 'boolean'
    ];

    public function mount()
    {
        $this->due_date = now()->addDays(7)->format('Y-m-d');
        $this->loadClients();
        $this->loadServices();
    }

    public function loadClients()
    {
        $this->clients = ClientsModel::select([
            'id',
            'client_number',
            'first_name',
            'middle_name',
            'last_name',
            'business_name',
            'mobile_phone_number',
            'membership_type'
        ])->get()->map(function ($client) {
            return [
                'id' => $client->id,
                'client_number' => $client->client_number,
                'mobile_phone_number' => $client->mobile_phone_number,
                'full_name' => $client->full_name
            ];
        })->toArray();
    }

    public function loadServices()
    {
        $this->services = Service::all();
    }

    public function createBill()
    {
        $this->validate();

        try {
            $command = new GenerateBillCommand(
                client_number: $this->client_number,
                service_id: $this->service_id,
                amount: $this->amount,
                is_recurring: $this->is_recurring,
                payment_mode: $this->payment_mode,
                due_date: $this->due_date,
                is_mandatory: $this->is_mandatory
            );

            $bill = Bus::dispatch($command);

            session()->flash('success', 'Bill created successfully.');
            $this->reset(['amount', 'due_date', 'is_mandatory']);

            $this->emit('billCreated', $bill->id);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create bill: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.billing.create-bill');
    }
} 