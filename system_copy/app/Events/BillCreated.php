<?php

namespace App\Events;

use App\Models\Bill;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bill;

    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
    }
} 