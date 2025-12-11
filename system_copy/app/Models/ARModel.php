<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ARModel extends Model
{
    use HasFactory;

    // Table name, if different from the default
    protected $table = 'receivables';

    // Define the fields that are mass assignable
    protected $guarded = [];

    /**
     * Define relationships if any
     */

    // Assuming ARModel is linked to a customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // You might want to relate this to invoices if applicable
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_number');
    }

    /**
     * You can add methods for business logic here
     */

    // Method to check if AR is overdue
    public function isOverdue()
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }

    // Method to mark AR as paid
    public function markAsPaid()
    {
        $this->status = 'paid';
        $this->save();
    }
}
