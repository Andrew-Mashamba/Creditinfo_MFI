<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanLossReserve extends Model
{
    protected $guarded = [];

    private mixed $initial_allocation = 0; // Default initialization
    private mixed $adjustments = 0; // Default initialization
    private mixed $total_allocation = 0; // Default initialization

    public $table = 'loss_reserves';

    // Method to calculate total allocation based on initial allocation and adjustments
    public function calculateTotalAllocation(): mixed
    {
        return $this->initial_allocation + $this->adjustments;
    }

    // Function to finalize at year end
    public function finalizeAtYearEnd(float $actualLoanLosses): void
    {
        // Step 1: Calculate the total allocation
        $this->total_allocation = $this->calculateTotalAllocation();

        // Step 2: Compare total allocation with actual loan losses
        if ($this->total_allocation < $actualLoanLosses) {
            // If total allocation is less than actual loan losses, adjust the reserve amount
            $difference = $actualLoanLosses - $this->total_allocation;
            $this->adjustments += $difference;
            $this->total_allocation += $difference; // Update total allocation
        } elseif ($this->total_allocation > $actualLoanLosses) {
            // If total allocation exceeds actual loan losses, consider adjusting downwards
            $difference = $this->total_allocation - $actualLoanLosses;
            $this->adjustments -= $difference;
            $this->total_allocation -= $difference; // Update total allocation
        }

        // Step 3: Save the changes to the database
        $this->save();
    }

    // Optional: Setters and Getters
    public function setInitialAllocation(mixed $value): void
    {
        $this->initial_allocation = $value;
        $this->total_allocation = $this->calculateTotalAllocation();
    }

    public function setAdjustments(mixed $value): void
    {
        $this->adjustments = $value;
        $this->total_allocation = $this->calculateTotalAllocation();
    }

    public function getTotalAllocation(): mixed
    {
        return $this->total_allocation;
    }
}
