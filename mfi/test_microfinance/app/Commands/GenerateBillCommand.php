<?php

namespace App\Commands;

class GenerateBillCommand
{
    public $client_number;
    public $service_id;
    public $amount;
    public $is_recurring;
    public $payment_mode;
    public $due_date;
    public $is_mandatory;

    public function __construct(
        string $client_number,
        int $service_id,
        float $amount,
        int $is_recurring = 1,
        int $payment_mode = 1,
        string $due_date = null,
        bool $is_mandatory = false
    ) {
        $this->client_number = $client_number;
        $this->service_id = $service_id;
        $this->amount = $amount;
        $this->is_recurring = $is_recurring;
        $this->payment_mode = $payment_mode;
        $this->due_date = $due_date ?? now()->endOfMonth()->format('Y-m-d');
        $this->is_mandatory = $is_mandatory;
    }
} 