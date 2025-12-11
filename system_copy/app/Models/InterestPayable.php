<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestPayable extends Model
{
    use HasFactory;

    protected $table = 'interest_payables';
    protected $guarded = [];

    /**
     * Get the member who owns this interest payable.
     */
    public function member()
    {
        return $this->belongsTo(ClientsModel::class, 'member_id', 'id');
    }
}
