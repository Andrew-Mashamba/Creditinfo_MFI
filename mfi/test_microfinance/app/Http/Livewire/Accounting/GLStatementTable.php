<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;

/**
 * @deprecated This component is no longer used. 
 * The GL Statement functionality has been integrated directly into GLStatement.php
 * with a custom bank statement-like table design.
 * 
 * This file is kept for backward compatibility only.
 * Please use GLStatement component instead.
 */
class GLStatementTable extends Component
{
    public function render()
    {
        // Redirect to the main GL Statement component
        return view('livewire.accounting.g-l-statement-table');
    }
}