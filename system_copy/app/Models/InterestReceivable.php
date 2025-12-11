<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestReceivable extends Model
{
    use HasFactory;

    protected $table = 'interest_receivables';
    protected $guarded = [];

    /**
     * Get the member who owns this interest receivable.
     */
    public function member()
    {
        return $this->belongsTo(ClientsModel::class, 'member_id', 'id');
    }
}
